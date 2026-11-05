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
     * Phân tích tương thích và quy tắc ghép đôi thiết bị (System Pairing & Compatibility Advisor)
     * Trả về bảng trạng thái 7 nhóm thiết bị, tỷ lệ DC/AC, cảnh báo kỹ thuật và gợi ý thiết bị cần phối ghép.
     *
     * @param array $cartItems Danh sách thiết bị trong kế hoạch dự định ($_SESSION['solar_cart'])
     * @return array Kết quả phân tích ghép đôi chuyên sâu
     */
    public static function analyzeCompatibility(array $cartItems): array
    {
        $panels = [];
        $inverters = [];
        $batteries = [];
        $protectionBoxes = [];
        $mountingRails = [];
        $cables = [];
        $smartMeters = [];
        $accessories = [];

        foreach ($cartItems as $item) {
            $catName = mb_strtolower((string)($item['category_name'] ?? ''));
            $name = mb_strtolower((string)($item['name'] ?? ''));
            $code = strtoupper((string)($item['product_code'] ?? ''));
            $slug = mb_strtolower((string)($item['category_slug'] ?? ''));
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $watts = (float)($item['capacity_watts'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $warranty = (int)($item['warranty_years'] ?? 0);

            $itemData = [
                'id' => (int)($item['id'] ?? 0),
                'product_code' => $code,
                'name' => (string)($item['name'] ?? ''),
                'category_name' => (string)($item['category_name'] ?? ''),
                'category_slug' => $slug,
                'quantity' => $qty,
                'capacity_watts' => $watts,
                'price' => $price,
                'warranty_years' => $warranty,
                'technical_specs' => $item['technical_specs'] ?? '{}',
                'total_watts' => $watts * $qty,
                'total_price' => $price * $qty,
                'image_url' => $item['image_url'] ?? null
            ];

            if (str_contains($catName, 'tấm pin') || str_contains($catName, 'tam-pin') || str_contains($slug, 'tam-pin') || str_contains($code, 'PAN') || str_contains($name, 'tấm pin') || str_contains($name, 'solar panel')) {
                $panels[] = $itemData;
            } elseif (str_contains($catName, 'biến tần') || str_contains($catName, 'inverter') || str_contains($slug, 'inverter') || str_contains($code, 'INV') || str_contains($name, 'biến tần') || str_contains($name, 'inverter')) {
                $inverters[] = $itemData;
            } elseif (str_contains($catName, 'pin lưu trữ') || str_contains($catName, 'lithium') || str_contains($slug, 'pin-luu-tru') || str_contains($code, 'BAT') || str_contains($name, 'pin lưu trữ') || str_contains($name, 'lithium') || str_contains($name, 'bess')) {
                $batteries[] = $itemData;
            } elseif (str_contains($name, 'tủ điện') || str_contains($name, 'đóng cắt') || str_contains($name, 'box') || str_contains($slug, 'tu-dien') || str_contains($code, 'PRO')) {
                $protectionBoxes[] = $itemData;
            } elseif (str_contains($name, 'rail') || str_contains($name, 'kẹp') || str_contains($name, 'khung') || str_contains($slug, 'khung-gia') || str_contains($code, 'MTG')) {
                $mountingRails[] = $itemData;
            } elseif (str_contains($name, 'cáp') || str_contains($name, 'mc4') || str_contains($name, 'dây') || str_contains($slug, 'day-cap') || str_contains($code, 'CAB')) {
                $cables[] = $itemData;
            } elseif (str_contains($name, 'đồng hồ') || str_contains($name, 'meter') || str_contains($name, 'công tơ') || str_contains($slug, 'dong-ho') || str_contains($code, 'MET')) {
                $smartMeters[] = $itemData;
            } else {
                $accessories[] = $itemData;
            }
        }

        // Thống kê tấm pin
        $totalPanels = array_sum(array_column($panels, 'quantity'));
        $totalPanelWatts = array_sum(array_column($panels, 'total_watts'));
        $totalPanelKwp = round($totalPanelWatts / 1000, 2);

        // Thống kê inverter
        $totalInverters = array_sum(array_column($inverters, 'quantity'));
        $totalInverterWatts = array_sum(array_column($inverters, 'total_watts'));
        $totalInverterKw = round($totalInverterWatts / 1000, 2);
        $hasHybridInverter = false;
        foreach ($inverters as $inv) {
            if (str_contains(mb_strtolower($inv['name']), 'hybrid')) {
                $hasHybridInverter = true;
                break;
            }
        }

        // Thống kê pin lưu trữ
        $totalBatteries = array_sum(array_column($batteries, 'quantity'));
        $totalBatteryKwh = round(array_sum(array_column($batteries, 'total_watts')) / 1000, 2);

        // Thống kê phụ kiện, bảo vệ và đo đếm
        $hasProtectionBox = count($protectionBoxes) > 0;
        $hasMountingRail = count($mountingRails) > 0;
        $hasCables = count($cables) > 0;
        $hasSmartMeter = count($smartMeters) > 0;

        // Đánh giá tỷ lệ DC/AC giữa Tấm pin và Biến tần
        $dcAcRatio = null;
        if ($totalInverterKw > 0 && $totalPanelKwp > 0) {
            $dcAcRatio = round($totalPanelKwp / $totalInverterKw, 2);
        }

        // Bản đồ 7 nhóm thiết bị theo chuẩn Sơ đồ cấu hình hệ thống
        $groups = [
            'tam-pin' => [
                'slug' => 'tam-pin', 'label' => 'Tấm pin', 'icon' => '☀️',
                'is_selected' => count($panels) > 0,
                'count' => $totalPanels,
                'kwp' => $totalPanelKwp,
                'items' => $panels
            ],
            'inverter' => [
                'slug' => 'inverter', 'label' => 'Inverter', 'icon' => '⚡',
                'is_selected' => count($inverters) > 0,
                'count' => $totalInverters,
                'kw' => $totalInverterKw,
                'has_hybrid' => $hasHybridInverter,
                'items' => $inverters
            ],
            'tu-dien' => [
                'slug' => 'tu-dien', 'label' => 'Tủ điện', 'icon' => '🛡️',
                'is_selected' => $hasProtectionBox,
                'count' => array_sum(array_column($protectionBoxes, 'quantity')),
                'items' => $protectionBoxes
            ],
            'khung-gia' => [
                'slug' => 'khung-gia', 'label' => 'Khung giá', 'icon' => '🔩',
                'is_selected' => $hasMountingRail,
                'count' => array_sum(array_column($mountingRails, 'quantity')),
                'items' => $mountingRails
            ],
            'day-cap' => [
                'slug' => 'day-cap', 'label' => 'Cáp & MC4', 'icon' => '🔌',
                'is_selected' => $hasCables,
                'count' => array_sum(array_column($cables, 'quantity')),
                'items' => $cables
            ],
            'dong-ho' => [
                'slug' => 'dong-ho', 'label' => 'Công tơ', 'icon' => '📟',
                'is_selected' => $hasSmartMeter,
                'count' => array_sum(array_column($smartMeters, 'quantity')),
                'items' => $smartMeters
            ],
            'pin-luu-tru' => [
                'slug' => 'pin-luu-tru', 'label' => 'Pin lưu trữ', 'icon' => '🔋',
                'is_selected' => count($batteries) > 0,
                'count' => $totalBatteries,
                'kwh' => $totalBatteryKwh,
                'items' => $batteries
            ]
        ];

        // Phân tích trạng thái ghép đôi & quy tắc khuyến nghị
        $rules = [];

        // 1. Kiểm tra cặp Tấm pin - Inverter
        if ($totalPanels > 0 && $totalInverters > 0) {
            if ($dcAcRatio >= 0.9 && $dcAcRatio <= 1.30) {
                $rules[] = [
                    'type' => 'success',
                    'icon' => '✅',
                    'title' => "Ghép đôi Tấm pin & Inverter hoàn hảo (Tỷ lệ DC/AC: {$dcAcRatio})",
                    'detail' => "Giàn pin {$totalPanelKwp} kWp phối hợp tối ưu với Biến tần Inverter {$totalInverterKw} kW. Tỷ lệ 1.0 - 1.25 giúp hệ thống đạt hiệu suất chuyển đổi cao nhất cả vào đầu giờ sáng và cuối giờ chiều."
                ];
            } elseif ($dcAcRatio > 1.30) {
                $rules[] = [
                    'type' => 'warning',
                    'icon' => '⚠️',
                    'title' => "Giàn pin lớn hơn Inverter đáng kể (Tỷ lệ DC/AC: {$dcAcRatio})",
                    'detail' => "Giàn pin {$totalPanelKwp} kWp có thể bị cắt giảm công suất đỉnh (clipping) vào giữa trưa nắng to khi qua Inverter {$totalInverterKw} kW. Bạn có thể cân nhắc nâng cấp Inverter lên khoảng " . round($totalPanelKwp * 0.9, 1) . " - " . ceil($totalPanelKwp) . " kW."
                ];
            } else {
                $rules[] = [
                    'type' => 'info',
                    'icon' => '💡',
                    'title' => "Inverter dư công suất so với giàn pin (Tỷ lệ DC/AC: {$dcAcRatio})",
                    'detail' => "Inverter {$totalInverterKw} kW hiện chỉ sử dụng {$totalPanelKwp} kWp pin. Bạn có thể bổ sung thêm khoảng " . ceil(($totalInverterKw * 1.15 - $totalPanelKwp) * 1000 / 550) . " tấm pin 550W để tận dụng tối đa công suất biến tần."
                ];
            }
        } elseif ($totalPanels > 0 && $totalInverters === 0) {
            $suggestedInvKw = round($totalPanelKwp * 0.9, 1);
            $rules[] = [
                'type' => 'danger',
                'icon' => '⚡',
                'title' => "Cần ghép thêm Biến Tần Inverter!",
                'detail' => "Bạn đã chọn {$totalPanels} tấm pin ({$totalPanelKwp} kWp). Bắt buộc cần ghép thêm 01 Biến tần Inverter công suất từ {$suggestedInvKw} kW đến " . ceil($totalPanelKwp) . " kW để chuyển đổi điện DC sang điện AC hòa lưới."
            ];
        } elseif ($totalPanels === 0 && $totalInverters > 0) {
            $suggestedPanels = ceil(($totalInverterKw * 1.1) * 1000 / 550);
            $rules[] = [
                'type' => 'danger',
                'icon' => '☀️',
                'title' => "Cần ghép thêm Tấm Pin Mặt Trời!",
                'detail' => "Bạn đã chọn Inverter {$totalInverterKw} kW nhưng chưa có tấm pin nào. Cần ghép thêm khoảng {$suggestedPanels} tấm pin mặt trời (khoảng " . round($totalInverterKw * 1.1, 1) . " kWp) để sinh điện."
            ];
        } else {
            $rules[] = [
                'type' => 'neutral',
                'icon' => '📋',
                'title' => "Chưa có thiết bị phát điện & chuyển đổi chính",
                'detail' => "Vui lòng chọn Tấm pin năng lượng mặt trời và Biến tần Inverter tương ứng để bắt đầu thiết lập hệ thống."
            ];
        }

        // 2. Kiểm tra Pin lưu trữ Lithium
        if ($totalBatteries > 0) {
            if ($totalInverters > 0 && $hasHybridInverter) {
                $rules[] = [
                    'type' => 'success',
                    'icon' => '🔋',
                    'title' => "Tương thích Pin lưu trữ & Inverter Hybrid",
                    'detail' => "Tuyệt vời! Pin lưu trữ ({$totalBatteryKwh} kWh) được phối ghép cùng Inverter Hybrid, hỗ trợ tự động sạc khi nắng to và cấp điện dự phòng ban đêm hoặc khi cúp điện."
                ];
            } elseif ($totalInverters > 0 && !$hasHybridInverter) {
                $rules[] = [
                    'type' => 'danger',
                    'icon' => '❌',
                    'title' => "Xung đột: Pin Lithium cần Inverter HYBRID!",
                    'detail' => "Bạn đã chọn Pin lưu trữ Lithium ({$totalBatteryKwh} kWh) nhưng Biến tần trong kế hoạch là loại On-Grid hòa lưới thường! Vui lòng chọn Biến tần HYBRID (Deye / GoodWe Hybrid) để quản lý nạp xả pin."
                ];
            } else {
                $rules[] = [
                    'type' => 'warning',
                    'icon' => '⚠️',
                    'title' => "Pin lưu trữ cần kết hợp với Inverter Hybrid",
                    'detail' => "Pin lưu trữ Lithium cần có Biến tần Inverter Hybrid tương ứng để nạp và phát điện dự phòng."
                ];
            }
        }

        // 3. Kiểm tra Tủ điện đóng cắt & Khung giá
        if (!$hasProtectionBox) {
            $boxType = ($totalInverterKw >= 10 || $totalPanelKwp >= 10) ? 'Tủ điện đóng cắt 3 Pha 10kW' : 'Tủ điện đóng cắt DC/AC & Chống Sét 5kW';
            $rules[] = [
                'type' => 'warning',
                'icon' => '🛡️',
                'title' => "Khuyến nghị bổ sung Tủ điện an toàn AC/DC",
                'detail' => "Hệ thống cần 01 {$boxType} tích hợp chống sét lan truyền SPD và Aptomat DC/AC chuyên dụng để đảm bảo an toàn PCCC."
            ];
        }

        if (!$hasMountingRail && $totalPanels > 0) {
            $rules[] = [
                'type' => 'info',
                'icon' => '🔧',
                'title' => "Khuyến nghị bổ sung Khung ray nhôm & Kẹp pin",
                'detail' => "Để lắp đặt {$totalPanels} tấm pin an toàn chịu bão, bạn cần bổ sung Thanh Rail nhôm định hình và Bộ kẹp giữa / kẹp biên tương ứng."
            ];
        }

        if (!$hasCables && $totalPanels > 0) {
            $rules[] = [
                'type' => 'info',
                'icon' => '🔌',
                'title' => "Khuyến nghị bổ sung Cáp điện DC Solar & MC4",
                'detail' => "Để kết nối chuỗi tấm pin xuống Inverter an toàn, hệ thống cần Cáp DC chuyên dụng 4.0mm² chống tia UV và Cặp đầu nối chống nước MC4 1500VDC."
            ];
        }

        // Tính điểm hoàn thiện cấu hình (Completion Score 0 - 100%)
        $score = 0;
        if ($totalPanels > 0) $score += 25;
        if ($totalInverters > 0) $score += 25;
        if ($totalPanels > 0 && $totalInverters > 0 && $dcAcRatio !== null && $dcAcRatio >= 0.85 && $dcAcRatio <= 1.35) $score += 15;
        if ($hasProtectionBox) $score += 10;
        if ($hasMountingRail) $score += 10;
        if ($hasCables) $score += 5;
        if ($hasSmartMeter) $score += 5;
        if ($totalBatteries > 0 && $hasHybridInverter) $score += 5;
        if ($score > 100) $score = 100;

        return [
            'panels' => $panels,
            'inverters' => $inverters,
            'batteries' => $batteries,
            'protection_boxes' => $protectionBoxes,
            'mounting_rails' => $mountingRails,
            'cables' => $cables,
            'smart_meters' => $smartMeters,
            'accessories' => $accessories,
            'groups' => $groups,
            'total_panels' => $totalPanels,
            'total_panel_kwp' => $totalPanelKwp,
            'total_inverters' => $totalInverters,
            'total_inverter_kw' => $totalInverterKw,
            'has_hybrid_inverter' => $hasHybridInverter,
            'total_batteries' => $totalBatteries,
            'total_battery_kwh' => $totalBatteryKwh,
            'has_protection_box' => $hasProtectionBox,
            'has_mounting_rail' => $hasMountingRail,
            'has_cables' => $hasCables,
            'has_smart_meter' => $hasSmartMeter,
            'dc_ac_ratio' => $dcAcRatio,
            'completion_score' => $score,
            'rules' => $rules
        ];
    }

    /**
     * Tính toán năng lượng, số KW điện tạo ra, số tiền tạo ra hàng tháng và thời gian hoàn vốn
     * trực tiếp từ Kế hoạch dự định thiết bị
     *
     * @param array $cartItems Danh sách thiết bị trong kế hoạch ($_SESSION['solar_cart'])
     * @param float|null $roofArea Diện tích mái nhà người dùng (m2)
     * @param string $roofType Loại kết cấu mái
     * @return array Kết quả tính toán chi tiết
     */
    public static function calculateFromPlan(
        array $cartItems,
        ?float $roofArea = null,
        string $roofType = 'metal_roof'
    ): array {
        $compat = self::analyzeCompatibility($cartItems);

        // Xác định công suất giàn pin (kWp)
        $actualKwp = $compat['total_panel_kwp'];
        $panelCount = $compat['total_panels'];
        $hasCustomPanels = ($panelCount > 0 && $actualKwp > 0);
        $hasCustomInverter = ($compat['total_inverters'] > 0 && $compat['total_inverter_kw'] > 0);

        // Nếu người dùng chưa chọn tấm pin nhưng có chọn Inverter, lấy công suất Inverter để mô phỏng
        if ($actualKwp <= 0 && $compat['total_inverter_kw'] > 0) {
            $actualKwp = $compat['total_inverter_kw'];
            $panelCount = (int) ceil(($actualKwp * 1000) / self::PANEL_WATTAGE);
        }

        // Nếu giỏ hàng hoàn toàn trống hoặc không có công suất, fallback về cấu hình hộ gia đình 5.5 kWp (10 tấm pin)
        if ($actualKwp <= 0) {
            $actualKwp = 5.5;
            $panelCount = 10;
        }

        // 1. Số KW điện tạo ra:
        // - Sản lượng điện tạo ra mỗi ngày (kWh/ngày) = kWp * 4.5h nắng * 0.82 (hiệu suất hệ thống)
        $dailyKwh = round($actualKwp * self::PEAK_SUN_HOURS * 0.82, 2);
        // - Sản lượng điện tạo ra trong 1 tháng (kWh/tháng) = kWh/ngày * 30 ngày
        $monthlyKwh = round($dailyKwh * 30, 1);
        // - Sản lượng điện tạo ra trong 1 năm (kWh/năm)
        $yearlyKwh = round($monthlyKwh * 12);

        // 2. Số tiền điện tạo ra trong 1 tháng (VNĐ/tháng):
        // Giá điện chuẩn: 3.000 VNĐ / kWh
        $monthlyMoney = round($monthlyKwh * self::ELECTRICITY_PRICE_KWH);
        $monthlyMoneyFormatted = number_format($monthlyMoney, 0, ',', '.') . ' VNĐ';

        // Số tiền tạo ra trong 1 năm (VNĐ/năm):
        $yearlyMoney = $monthlyMoney * 12;
        $yearlyMoneyFormatted = number_format($yearlyMoney, 0, ',', '.') . ' VNĐ';

        // 3. Tính toán tổng chi phí đầu tư thiết bị trong kế hoạch:
        $deviceCost = 0;
        foreach ($cartItems as $item) {
            $deviceCost += ((float)($item['price'] ?? 0)) * max(1, (int)($item['quantity'] ?? 1));
        }

        // Bổ sung chi phí thiết bị phát điện còn thiếu (nếu người dùng chỉ mới chọn phụ kiện/đồng hồ)
        // để thời gian hoàn vốn ROI phản ánh đúng thực tế của một hệ thống hoạt động trọn vẹn
        $isEstimatedHardware = false;
        if (!$hasCustomPanels) {
            $isEstimatedHardware = true;
            $deviceCost += $panelCount * 2600000; // Tấm pin Mono 550W Tier-1 chuẩn ~2.6 triệu/tấm
        }
        if (!$hasCustomInverter) {
            $isEstimatedHardware = true;
            $deviceCost += round($actualKwp * 2500000); // Inverter hòa lưới chuẩn ~2.5 triệu/kWp
        }

        // Chi phí nhân công lắp đặt, nghiệm thu trọn gói (định mức ~10-12% chi phí thiết bị, tối thiểu 4 triệu VNĐ)
        $installCost = max(4000000, round($deviceCost * 0.10));
        $totalInvestment = $deviceCost + $installCost;

        // Nếu dữ liệu giá = 0 (dữ liệu mock), tính theo suất đầu tư tiêu chuẩn
        if ($totalInvestment <= 0) {
            $pkgKey = $compat['has_hybrid_inverter'] ? 'hybrid' : 'residential';
            $totalInvestment = round($actualKwp * self::PACKAGE_PRICING[$pkgKey]['price_per_kwp']);
            $deviceCost = round($totalInvestment * 0.88);
            $installCost = $totalInvestment - $deviceCost;
        }

        $totalInvestmentFormatted = number_format($totalInvestment, 0, ',', '.') . ' VNĐ';

        // 4. Dự tính bao lâu thì gỡ lại số tiền (Thời gian hoàn vốn ROI):
        // Số năm = Tổng chi phí đầu tư / Số tiền tạo ra trong 1 năm
        $paybackYearsDecimal = $yearlyMoney > 0 ? round($totalInvestment / $yearlyMoney, 2) : 0;
        $paybackYears = (int) floor($paybackYearsDecimal);
        $paybackMonths = (int) round(($paybackYearsDecimal - $paybackYears) * 12);
        if ($paybackMonths >= 12) {
            $paybackYears += 1;
            $paybackMonths = 0;
        }

        $paybackText = $paybackYears > 0 ? "{$paybackYears} năm" : "";
        if ($paybackMonths > 0) {
            $paybackText .= ($paybackText !== "" ? " " : "") . "{$paybackMonths} tháng";
        }
        if ($paybackText === "") {
            $paybackText = "Chưa xác định";
        }

        // 5. Lợi ích tài chính 25 năm & ESG:
        $total25YearSavings = $yearlyMoney * 25;
        $net25YearProfit = $total25YearSavings - $totalInvestment;
        $roiPercentage = $totalInvestment > 0 ? round(($net25YearProfit / $totalInvestment) * 100) : 0;
        $co2SavedKgYearly = round($yearlyKwh * self::CO2_KG_PER_KWH);
        $treesEquivalentYearly = (int) round($co2SavedKgYearly / 20);

        // 6. Diện tích mái & Ma trận mặt bằng:
        $requiredRoofArea = round($panelCount * self::SQM_PER_PANEL, 1);
        $isRoofSufficient = true;
        $maxPanelsByRoof = null;
        if ($roofArea !== null && $roofArea > 0) {
            $maxPanelsByRoof = (int) floor($roofArea / self::SQM_PER_PANEL);
            if ($requiredRoofArea > $roofArea) {
                $isRoofSufficient = false;
            }
        }

        $layoutMatrix = self::suggestPanelLayout($panelCount);
        $bom = self::generateBOMFromPlan($cartItems, $actualKwp, $panelCount, $compat, $roofType);

        return [
            'is_from_plan'           => true,
            'source'                 => 'plan',
            'compatibility'          => $compat,
            'groups'                 => $compat['groups'] ?? [],
            'primary_panel'          => $compat['panels'][0] ?? null,
            'primary_inverter'       => $compat['inverters'][0] ?? null,
            'primary_battery'        => $compat['batteries'][0] ?? null,
            'primary_box'            => $compat['protection_boxes'][0] ?? null,
            'primary_meter'          => $compat['smart_meters'][0] ?? null,
            'package_key'            => $compat['has_hybrid_inverter'] ? 'hybrid' : 'residential',
            'package_name'           => $compat['has_hybrid_inverter'] ? 'Hệ Thống Lưu Trữ Hybrid (Từ Kế Hoạch)' : 'Hệ Thống Điện Mặt Trời (Từ Kế Hoạch)',
            'roof_type'              => $roofType,
            'panel_count'            => $panelCount,
            'estimated_kwp'          => $actualKwp,
            'daily_kwh'              => $dailyKwh,
            'monthly_kwh'            => $monthlyKwh,
            'yearly_kwh_produced'    => $yearlyKwh,
            'monthly_bill'           => $monthlyMoney,
            'monthly_bill_formatted' => $monthlyMoneyFormatted,
            'monthly_money'          => $monthlyMoney,
            'monthly_money_formatted'=> $monthlyMoneyFormatted,
            'monthly_savings'        => $monthlyMoney,
            'monthly_savings_formatted' => $monthlyMoneyFormatted,
            'yearly_savings'         => $yearlyMoney,
            'yearly_savings_formatted'  => $yearlyMoneyFormatted,
            'device_cost'            => $deviceCost,
            'install_cost'           => $installCost,
            'estimated_total_cost'   => $totalInvestment,
            'estimated_total_cost_formatted' => $totalInvestmentFormatted,
            'payback_years_decimal'  => $paybackYearsDecimal,
            'payback_years'          => $paybackYears,
            'payback_months'         => $paybackMonths,
            'payback_text'           => $paybackText,
            'total_25yr_savings'     => $total25YearSavings,
            'net_25yr_profit'        => $net25YearProfit,
            'net_25yr_profit_formatted' => number_format($net25YearProfit, 0, ',', '.') . ' VNĐ',
            'roi_percentage'         => $roiPercentage,
            'required_roof_area'     => $requiredRoofArea,
            'provided_roof_area'     => $roofArea,
            'is_roof_sufficient'     => $isRoofSufficient,
            'max_panels_by_roof'     => $maxPanelsByRoof,
            'co2_saved_kg_yearly'    => $co2SavedKgYearly,
            'trees_equivalent_yearly'=> $treesEquivalentYearly,
            'has_custom_panels'      => $hasCustomPanels,
            'is_estimated_hardware'  => $isEstimatedHardware,
            'layout_matrix'          => $layoutMatrix,
            'bom'                    => $bom
        ];
    }

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
            'has_custom_panels'      => false,
            'is_estimated_hardware'  => false,
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
     * Sinh danh sách bảng bóc tách vật tư & cấu hình thiết bị (Bill of Materials) trực tiếp từ Kế hoạch dự định
     */
    public static function generateBOMFromPlan(
        array $cartItems,
        float $actualKwp,
        int $panelCount,
        array $compat,
        string $roofType
    ): array {
        if (empty($cartItems)) {
            return self::generateBOM($actualKwp, $panelCount, $compat['has_hybrid_inverter'] ? 'hybrid' : 'residential', $roofType);
        }

        $bom = [];
        $hasPanels = false;
        $hasInverter = false;
        $hasMounting = false;
        $hasBox = false;
        $hasCables = false;
        $hasMeter = false;
        $hasBattery = false;

        foreach ($cartItems as $item) {
            $name = (string)($item['name'] ?? '');
            $code = (string)($item['product_code'] ?? '');
            $qty = max(1, (int)($item['quantity'] ?? 1));
            $warranty = (int)($item['warranty_years'] ?? 0);
            $catName = mb_strtolower((string)($item['category_name'] ?? ''));
            $lower = mb_strtolower($name . ' ' . $catName . ' ' . $code);

            // Xác định ĐVT và thời hạn bảo hành
            $unit = 'Bộ';
            $warrantyText = $warranty > 0 ? "{$warranty} năm chính hãng" : "5 năm theo tiêu chuẩn";
            if (str_contains($lower, 'tấm pin') || str_contains($code, 'PAN')) {
                $unit = 'Tấm';
                $hasPanels = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm vật lý, 25 năm hiệu suất" : "12 năm vật lý, 25 năm hiệu suất";
            } elseif (str_contains($lower, 'inverter') || str_contains($lower, 'biến tần') || str_contains($code, 'INV')) {
                $unit = 'Bộ';
                $hasInverter = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm đổi mới" : "5 năm đổi mới chính hãng";
            } elseif (str_contains($lower, 'pin lưu trữ') || str_contains($lower, 'lithium') || str_contains($code, 'BAT')) {
                $unit = 'Bộ ESS';
                $hasBattery = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm bảo hành dung lượng" : "10 năm bảo hành dung lượng";
            } elseif (str_contains($lower, 'tủ điện') || str_contains($lower, 'box') || str_contains($code, 'PRO')) {
                $unit = 'Tủ';
                $hasBox = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm tiêu chuẩn" : "3 năm tiêu chuẩn IEC";
            } elseif (str_contains($lower, 'rail') || str_contains($lower, 'thanh') || str_contains($lower, 'kẹp') || str_contains($lower, 'khung') || str_contains($code, 'MTG')) {
                $unit = str_contains($lower, 'kẹp') ? 'Bộ' : 'Thanh';
                $hasMounting = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm chịu bão" : "10 năm chịu bão";
            } elseif (str_contains($lower, 'cáp') || str_contains($lower, 'mc4') || str_contains($code, 'CAB')) {
                $unit = str_contains($lower, 'mc4') ? 'Cặp' : 'Cuộn (100m)';
                $hasCables = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm an toàn điện" : "25 năm an toàn điện";
            } elseif (str_contains($lower, 'meter') || str_contains($lower, 'đồng hồ') || str_contains($code, 'MET')) {
                $unit = 'Bộ';
                $hasMeter = true;
                $warrantyText = $warranty > 0 ? "{$warranty} năm kiểm định" : "2 năm kiểm định";
            }

            // Đọc specs từ JSON
            $specsStr = '';
            if (!empty($item['technical_specs'])) {
                $specsArr = is_array($item['technical_specs']) ? $item['technical_specs'] : json_decode((string)$item['technical_specs'], true);
                if (is_array($specsArr)) {
                    $parts = [];
                    foreach ($specsArr as $k => $v) {
                        if (is_scalar($v) && $v !== '') {
                            $parts[] = ucfirst(str_replace('_', ' ', (string)$k)) . ': ' . $v;
                        }
                    }
                    $specsStr = implode(', ', array_slice($parts, 0, 3));
                }
            }
            if ($specsStr === '') {
                $specsStr = !empty($item['capacity_watts']) 
                    ? ("Công suất định mức: " . (int)$item['capacity_watts'] . " W")
                    : "Thiết bị chính hãng đồng bộ theo chuẩn Solaris";
            }

            $bom[] = [
                'item' => $name . ($code !== '' ? " ({$code})" : ""),
                'specs' => $specsStr,
                'quantity' => $qty,
                'unit' => $unit,
                'warranty' => $warrantyText,
                'is_from_cart' => true,
                'price' => (float)($item['price'] ?? 0),
                'total_price' => ((float)($item['price'] ?? 0)) * $qty
            ];
        }

        // Bổ sung các hạng mục kỹ thuật thiết yếu nếu người dùng chưa chọn trong giỏ hàng:
        if (!$hasPanels && $panelCount > 0) {
            array_unshift($bom, [
                'item' => 'Tấm pin mặt trời Tier-1 Mono 550W (Ước tính theo công suất)',
                'specs' => 'Công nghệ Mono PERC Half-cell 550Wp, Hiệu suất > 21.3%, Hãng Tier-1',
                'quantity' => $panelCount,
                'unit' => 'Tấm',
                'warranty' => '12 năm vật lý, 25 năm hiệu suất',
                'is_supplemental' => true
            ]);
        }

        if (!$hasInverter && $actualKwp > 0) {
            $invKw = round($actualKwp * 1.0, 1);
            $bom[] = [
                'item' => "Biến tần Inverter hòa lưới {$invKw}kW (Ước tính theo giàn pin)",
                'specs' => "Công suất {$invKw} kW, Hiệu suất chuyển đổi > 98%, Giám sát Cloud 24/7",
                'quantity' => 1,
                'unit' => 'Bộ',
                'warranty' => '5 năm đổi mới chính hãng',
                'is_supplemental' => true
            ];
        }

        if (!$hasMounting && $panelCount > 0) {
            $roofStructureName = match ($roofType) {
                'tile_roof' => 'Bộ phụ kiện kẹp móc ngói Inox 304 & Ray nhôm định hình Anode',
                'concrete_roof' => 'Hệ giàn khung nghiêng chân đỡ mạ kẽm nhúng nóng + ray nhôm',
                default => 'Hệ thống kẹp sóng tôn Seamlock/Kliplok & Ray nhôm Anode chống bão'
            };
            $bom[] = [
                'item' => 'Hệ thống khung giá đỡ & ray nhôm định hình',
                'specs' => $roofStructureName,
                'quantity' => 1,
                'unit' => 'Hệ trọn gói',
                'warranty' => '10 năm chịu bão cấp 12',
                'is_supplemental' => true
            ];
        }

        if (!$hasBox) {
            $boxSpecs = ($actualKwp >= 10) ? 'Tủ điện đóng cắt 3 Pha 10kW tích hợp SPD Type 2' : 'Tủ điện đóng cắt DC/AC & Chống sét lan truyền 5kW';
            $bom[] = [
                'item' => 'Tủ điện an toàn đóng cắt AC/DC & Chống sét lan truyền',
                'specs' => $boxSpecs . ', Vỏ tủ IP65 chống nước ngoài trời',
                'quantity' => 1,
                'unit' => 'Tủ',
                'warranty' => '3 năm tiêu chuẩn IEC',
                'is_supplemental' => true
            ];
        }

        if (!$hasCables) {
            $bom[] = [
                'item' => 'Cáp điện DC Solar 4.0mm² & Đầu nối MC4 IP68',
                'specs' => 'Cáp DC chuyên dụng chống tia UV, điện áp 1500VDC, đầu nối chống nước IP68',
                'quantity' => 1,
                'unit' => 'Gói tiêu chuẩn',
                'warranty' => '25 năm an toàn điện',
                'is_supplemental' => true
            ];
        }

        // Hạng mục nhân công thi công lắp đặt và hồ sơ nghiệm thu EVN
        $bom[] = [
            'item' => 'Nhân công thi công, Kiểm định an toàn & Bàn giao EVN',
            'specs' => 'Lắp đặt chuẩn kỹ thuật PCCC, thử nghiệm đo điện trở tiếp địa, nghiệm thu công tơ 2 chiều EVN',
            'quantity' => 1,
            'unit' => 'Hợp đồng trọn gói',
            'warranty' => 'Bảo trì kỹ thuật 2 năm',
            'is_supplemental' => true
        ];

        return $bom;
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
