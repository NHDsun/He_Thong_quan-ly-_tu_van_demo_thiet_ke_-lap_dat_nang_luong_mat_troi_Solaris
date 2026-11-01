<?php
/**
 * =========================================================================
 * HOOK: UseSolarCalculator.php
 * PHỤ TRÁCH: THÀNH VIÊN 3 (FULLSTACK / SOLAR SIMULATOR & DATA HOOKS)
 * =========================================================================
 * Thuật toán tính toán điện mặt trời theo chuẩn Section 4.3 Tài liệu Đặc tả Solaris.
 * Đóng vai trò là "Single Source of Truth" cho toàn bộ tính toán năng lượng,
 * quy mô giàn pin, chi phí, hoàn vốn và tác động môi trường.
 */

namespace App\Hooks;

class UseSolarCalculator
{
    // Đơn giá điện trung bình tính toán (VND/kWh)
    public const ELECTRICITY_PRICE_KWH = 3000;

    // Số giờ nắng hiệu dụng trung bình tại Việt Nam (giờ/ngày)
    public const PEAK_SUN_HOURS = 4.5;

    // Công suất định danh của 1 tấm pin mặt trời chuẩn (Watts)
    public const PANEL_WATTAGE = 550;

    // Diện tích lắp đặt chiếm dụng trung bình của 1 tấm pin 550W kèm khoảng cách bảo dưỡng (m2)
    public const SQM_PER_PANEL = 2.5;

    // Tỷ lệ tiết kiệm hóa đơn điện kỳ vọng
    public const SAVINGS_RATIO = 0.90;

    // Hệ số phát thải CO2 trung bình lưới điện Việt Nam (kg CO2 / kWh)
    public const CO2_KG_PER_KWH = 0.7221;

    // Suất đầu tư theo từng gói hệ thống (VND / kWp)
    public const PACKAGE_PRICING = [
        'residential' => [
            'name' => 'Hòa Lưới Dân Dụng (Hộ Gia Đình)',
            'price_per_kwp' => 14000000,
            'desc' => 'Tối ưu cắt giảm tiền điện bậc thang giá cao, hoàn vốn nhanh 3.5 - 4 năm.'
        ],
        'commercial' => [
            'name' => 'Thương Mại & Công Nghiệp (C&I)',
            'price_per_kwp' => 13000000,
            'desc' => 'Quy mô công suất lớn cho nhà xưởng, văn phòng, giảm giá điện giờ cao điểm.'
        ],
        'hybrid' => [
            'name' => 'Lưu Trữ Hybrid ESS (Pin Lithium)',
            'price_per_kwp' => 22000000,
            'desc' => 'Tích hợp pin Lithium LiFePO4 cấp điện 24/7 liên tục ngay cả khi mất điện lưới.'
        ]
    ];

    /**
     * Tính toán cấu hình điện mặt trời toàn diện từ Hóa đơn tiền điện hoặc Công suất hoặc Diện tích mái
     *
     * @param float $monthlyBill Tiền điện trung bình hàng tháng (VNĐ)
     * @param float|null $roofArea Diện tích mái nhà khả dụng (m2, nếu có)
     * @param string $packageKey Mã gói ('residential', 'commercial', 'hybrid')
     * @param string $roofType Loại mái ('metal_roof', 'tile_roof', 'concrete_roof')
     * @return array Kết quả tính toán đầy đủ thông số kỹ thuật và tài chính
     */
    public static function calculate(
        float $monthlyBill = 3000000,
        ?float $roofArea = null,
        string $packageKey = 'residential',
        string $roofType = 'metal_roof'
    ): array {
        // Đảm bảo hóa đơn tối thiểu 500,000 VND để tính toán có nghĩa
        $monthlyBill = max(500000, $monthlyBill);
        $packageKey = array_key_exists($packageKey, self::PACKAGE_PRICING) ? $packageKey : 'residential';

        // 1. Điện năng tiêu thụ hàng tháng ước tính (kWh)
        // Công thức: kWh_thang = Hoa_don / 3.000 VND
        $monthlyKwh = $monthlyBill / self::ELECTRICITY_PRICE_KWH;

        // 2. Công suất đỉnh khuyến nghị (kWp)
        // Công thức: kWp = kWh_thang / (4.5 gio nang * 30 ngay)
        $estimatedSystemKwp = $monthlyKwh / (self::PEAK_SUN_HOURS * 30);
        $estimatedSystemKwp = round($estimatedSystemKwp, 2);

        // 3. Số lượng tấm pin 550W cần lắp
        // Công thức: So_tam = ceil(kWp * 1.000 / 550)
        $panelCount = (int) ceil(($estimatedSystemKwp * 1000) / self::PANEL_WATTAGE);
        if ($panelCount < 4) {
            $panelCount = 4; // Cấu hình tối thiểu để biến tần hòa lưới hoạt động ổn định
        }

        // Tính lại công suất thực tế dựa trên số tấm pin đã làm tròn
        $actualKwp = round(($panelCount * self::PANEL_WATTAGE) / 1000, 2);

        // 4. Diện tích mái yêu cầu (m2)
        // Công thức: Dien_tich_m2 = So_tam * 2.5 m2
        $requiredRoofArea = round($panelCount * self::SQM_PER_PANEL, 1);

        // Kiểm tra tính khả thi nếu người dùng nhập diện tích mái thực tế
        $isRoofSufficient = true;
        $maxPanelsByRoof = null;
        if ($roofArea !== null && $roofArea > 0) {
            $maxPanelsByRoof = (int) floor($roofArea / self::SQM_PER_PANEL);
            if ($requiredRoofArea > $roofArea) {
                $isRoofSufficient = false;
            }
        }

        // 5. Dự toán đầu tư trọn gói (VND)
        $pricePerKwp = self::PACKAGE_PRICING[$packageKey]['price_per_kwp'];
        $estimatedTotalCost = round($actualKwp * $pricePerKwp);

        // 6. Tiết kiệm tài chính
        $monthlySavings = round($monthlyBill * self::SAVINGS_RATIO);
        $yearlySavings = $monthlySavings * 12;

        // 7. Thời gian hoàn vốn (năm)
        // Công thức: Nam_hoan_von = Chi_phi_dau_tu / (Hoa_don_thang * 12 * 0.9)
        $paybackYears = 0;
        if ($yearlySavings > 0) {
            $paybackYears = round($estimatedTotalCost / $yearlySavings, 1);
        }

        // 8. Phân tích lợi ích tài chính 25 năm (vòng đời tiêu chuẩn bảo hành tấm pin)
        $total25YearSavings = $yearlySavings * 25;
        $net25YearProfit = $total25YearSavings - $estimatedTotalCost;
        $roiPercentage = $estimatedTotalCost > 0 ? round(($net25YearProfit / $estimatedTotalCost) * 100) : 0;

        // 9. Sản lượng điện hàng năm và Chỉ số phát thải xanh (ESG)
        // Hệ số suy hao hệ thống trung bình 18% (Performance Ratio = 0.82)
        $yearlyKwhProduced = round($actualKwp * self::PEAK_SUN_HOURS * 365 * 0.82);
        $co2SavedKgYearly = round($yearlyKwhProduced * self::CO2_KG_PER_KWH);
        $treesEquivalentYearly = (int) round($co2SavedKgYearly / 20); // 1 cây hấp thụ ~20kg CO2/năm

        // 10. Gợi ý cấu hình ma trận bố trí tấm pin 2D (Grid layout)
        $layoutMatrix = self::suggestPanelLayout($panelCount);

        // 11. Bóc tách vật tư chính (Bill of Materials - BOM)
        $bom = self::generateBOM($actualKwp, $panelCount, $packageKey, $roofType);

        return [
            'monthly_bill'           => $monthlyBill,
            'monthly_bill_formatted' => number_format($monthlyBill, 0, ',', '.') . ' VNĐ',
            'package_key'            => $packageKey,
            'package_name'           => self::PACKAGE_PRICING[$packageKey]['name'],
            'roof_type'              => $roofType,
            'monthly_kwh'            => round($monthlyKwh, 1),
            'estimated_kwp'          => $actualKwp,
            'panel_count'            => $panelCount,
            'panel_wattage'          => self::PANEL_WATTAGE,
            'required_roof_area'     => $requiredRoofArea,
            'provided_roof_area'     => $roofArea,
            'is_roof_sufficient'     => $isRoofSufficient,
            'max_panels_by_roof'     => $maxPanelsByRoof,
            'price_per_kwp'          => $pricePerKwp,
            'estimated_total_cost'   => $estimatedTotalCost,
            'estimated_total_cost_formatted' => number_format($estimatedTotalCost, 0, ',', '.') . ' VNĐ',
            'monthly_savings'        => $monthlySavings,
            'monthly_savings_formatted' => number_format($monthlySavings, 0, ',', '.') . ' VNĐ',
            'yearly_savings'         => $yearlySavings,
            'yearly_savings_formatted'  => number_format($yearlySavings, 0, ',', '.') . ' VNĐ',
            'payback_years'          => $paybackYears,
            'total_25yr_savings'     => $total25YearSavings,
            'net_25yr_profit'        => $net25YearProfit,
            'net_25yr_profit_formatted' => number_format($net25YearProfit, 0, ',', '.') . ' VNĐ',
            'roi_percentage'         => $roiPercentage,
            'yearly_kwh_produced'    => $yearlyKwhProduced,
            'co2_saved_kg_yearly'    => $co2SavedKgYearly,
            'trees_equivalent_yearly'=> $treesEquivalentYearly,
            'layout_matrix'          => $layoutMatrix,
            'bom'                    => $bom
        ];
    }

    /**
     * Gợi ý phân bố hàng x cột cho mô phỏng sơ đồ mặt bằng 2D
     */
    public static function suggestPanelLayout(int $panelCount): array
    {
        // Ưu tiên bố trí tỷ lệ gần với hình chữ nhật 2:1 hoặc 3:2
        $bestRows = 2;
        $bestCols = (int) ceil($panelCount / 2);

        if ($panelCount >= 16) {
            $bestRows = 4;
            $bestCols = (int) ceil($panelCount / 4);
        } elseif ($panelCount >= 9) {
            $bestRows = 3;
            $bestCols = (int) ceil($panelCount / 3);
        }

        return [
            'total_panels'   => $panelCount,
            'rows'           => $bestRows,
            'columns'        => $bestCols,
            'optimal_tilt'   => '12° - 15°',
            'azimuth'        => 'Hướng Nam (180°) hoặc Đông Nam (135°)',
            'spacing_meters' => '0.8m hành lang kỹ thuật'
        ];
    }

    /**
     * Sinh danh sách bảng bóc tách vật tư & cấu hình thiết bị (Bill of Materials)
     */
    public static function generateBOM(float $kwp, int $panelCount, string $packageKey, string $roofType): array
    {
        $inverterKva = round($kwp * 1.1, 1);
        $inverterType = ($packageKey === 'hybrid') 
            ? 'Inverter Hybrid thông minh đa kênh sạc ESS' 
            : ($kwp >= 10 ? 'Biến tần Inverter hòa lưới 3 Pha IP65' : 'Biến tần Inverter hòa lưới 1 Pha IP65');

        $roofStructureName = match ($roofType) {
            'tile_roof' => 'Bộ phụ kiện kẹp móc ngói Inox 304 & Ray nhôm định hình Anode',
            'concrete_roof' => 'Hệ giàn khung nghiêng chân đỡ mạ kẽm nhúng nóng + ray nhôm',
            default => 'Hệ thống kẹp sóng tôn Seamlock/Kliplok & Ray nhôm Anode chống bão'
        };

        $bom = [
            [
                'item' => 'Tấm pin mặt trời Tier-1',
                'specs' => 'Công nghệ Mono Perc Half-cell 550Wp, Hiệu suất > 21.3%, Hãng Tier-1 (Longi / Jinko / Canadian)',
                'quantity' => $panelCount,
                'unit' => 'Tấm',
                'warranty' => '12 năm vật lý, 25 năm hiệu suất > 80%'
            ],
            [
                'item' => 'Biến tần Inverter hòa lưới',
                'specs' => "Công suất {$inverterKva} kW, {$inverterType}, Giám sát WiFi/Cloud 24/7",
                'quantity' => 1,
                'unit' => 'Bộ',
                'warranty' => '5 năm đổi mới chính hãng'
            ],
            [
                'item' => 'Hệ thống giá đỡ cơ khí',
                'specs' => $roofStructureName,
                'quantity' => 1,
                'unit' => 'Hệ trọn gói',
                'warranty' => '10 năm chịu bão cấp 12'
            ],
            [
                'item' => 'Tủ điện an toàn AC/DC & SPD',
                'specs' => 'Tích hợp chống sét lan truyền SPD Type 2, Aptomat DC 1000V, AC RCBO chống dòng rò',
                'quantity' => 1,
                'unit' => 'Tủ',
                'warranty' => '3 năm tiêu chuẩn IEC'
            ],
            [
                'item' => 'Cáp điện & Phụ kiện đấu nối',
                'specs' => 'Cáp DC chuyên dụng chống UV 1x4mm², Cáp AC Cadivi đồng nguyên chất, Đầu nối MC4 IP68',
                'quantity' => 1,
                'unit' => 'Gói',
                'warranty' => '5 năm an toàn điện'
            ],
            [
                'item' => 'Thi công, Kiểm định & Nghiệm thu EVN',
                'specs' => 'Lắp đặt chuẩn kỹ thuật PCCC, hỗ trợ hồ sơ thỏa thuận đấu nối và nghiệm thu hòa lưới EVN',
                'quantity' => 1,
                'unit' => 'Hợp đồng',
                'warranty' => 'Bảo trì kỹ thuật 2 năm'
            ]
        ];

        if ($packageKey === 'hybrid') {
            $batteryCapacityKwh = round($kwp * 1.5, 1);
            array_splice($bom, 2, 0, [[
                'item' => 'Bộ Pin Lưu Trữ Điện Lithium',
                'specs' => "Cell pin LiFePO4 dung lượng {$batteryCapacityKwh} kWh, 6.000 chu kỳ nạp xả DOD 90%",
                'quantity' => 1,
                'unit' => 'Bộ ESS',
                'warranty' => '10 năm bảo hành dung lượng'
            ]]);
        }

        return $bom;
    }
}
