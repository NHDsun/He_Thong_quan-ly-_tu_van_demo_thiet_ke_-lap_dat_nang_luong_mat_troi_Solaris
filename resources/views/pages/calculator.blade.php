<?php
/**
 * View: resources/views/pages/calculator.blade.php
 * Phân hệ 3: Tư Vấn & Demo Thiết Kế (Solar Calculator & Simulator)
 * Phụ trách: Thành viên 3 - Mai Đức Toàn (Sizing Engine, 2D Simulator & Data)
 * 
 * QUY TRÌNH LUỒNG 4 BƯỚC CHUẨN KỸ THUẬT:
 * 1. Đồng bộ từ Kế hoạch dự định sang & Thiết lập mặt bằng mái
 * 2. Nhấn nút Cập nhật -> Hiển thị Mô phỏng bố trí các thiết bị đã cài đặt (Sơ đồ 2D Topology)
 * 3. Hiển thị các thông số kỹ thuật & giá trị tài chính khách hàng cần biết
 * 4. Nút xác nhận để chuyển sang chức năng Quản lý lắp đặt (TV4)
 */

$isPlanSource = ($source ?? 'bill') === 'plan';
$hasCart = !empty($cart_products);
$planItems = $cart_products ?? [];
$compat = $compatibility ?? [
    'panels' => [], 'inverters' => [], 'batteries' => [], 'accessories' => [],
    'total_panels' => 0, 'total_panel_kwp' => 0, 'total_inverters' => 0, 'total_inverter_kw' => 0,
    'total_batteries' => 0, 'total_battery_kwh' => 0, 'completion_score' => 0, 'rules' => []
];

// Lấy thông tin người dùng nếu đã đăng nhập để tự động điền form
$loggedInUser = $currentUser ?? null;
$defaultCustomerName = $loggedInUser['full_name'] ?? '';
$defaultCustomerPhone = $loggedInUser['phone'] ?? '';
$defaultCustomerEmail = $loggedInUser['email'] ?? '';

// Thông tin loại mái
$roofTypeName = match ($calc['roof_type'] ?? 'metal_roof') {
    'tile_roof' => 'Mái ngói Thái / Nhật',
    'concrete_roof' => 'Sân thượng Bê tông cốt thép',
    default => 'Mái tôn sóng / Seamlock'
};
?>

<div class="sol-container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <!-- Tiêu đề phân hệ & Giới thiệu -->
    <div class="sol-section-header" style="margin-bottom: 1.75rem;">
        <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.5rem;">
            ⚡ Phân Hệ 3 • Solar Sizing Engine & 2D Equipment Simulator
        </span>
        <h1 class="sol-section-header__title" style="font-size: 2.25rem;">
            Tư Vấn Kỹ Thuật & Mô Phỏng Cấu Hình Điện Mặt Trời
        </h1>
        <p class="sol-section-header__desc">
            Quy trình khép kín: Đồng bộ thiết bị từ <strong>Kế hoạch dự định</strong> → Mô phỏng <strong>bố trí thiết bị trên mặt bằng 2D</strong> → Tính toán <strong>3 chỉ số cốt lõi</strong> (Số KW, Tiền tạo ra/tháng, Thời gian gỡ vốn) → <strong>Xác nhận chuyển sang Quản lý lắp đặt</strong>.
        </p>
    </div>

    <!-- Thông báo Alert nếu có -->
    <?php if (!empty($alert)): ?>
        <div style="margin-bottom: 2rem; padding: 1.15rem 1.4rem; border-radius: var(--radius-md); background: <?= $alert['type'] === 'success' ? 'rgba(16, 185, 129, 0.15)' : ($alert['type'] === 'warning' ? 'rgba(245, 158, 11, 0.15)' : 'rgba(244, 63, 94, 0.15)') ?>; border: 1px solid <?= $alert['type'] === 'success' ? '#10b981' : ($alert['type'] === 'warning' ? '#f59e0b' : '#f43f5e') ?>; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #ffffff; font-size: 0.95rem;">
                <span style="font-size: 1.35rem;"><?= $alert['type'] === 'success' ? '✅' : ($alert['type'] === 'warning' ? '⚠️' : '❌') ?></span>
                <span><?= htmlspecialchars($alert['message']) ?></span>
            </div>
            <a href="index.php?page=calculator<?= $isPlanSource ? '&source=plan' : '' ?>" style="color: var(--text-muted); text-decoration: none; font-size: 1.25rem;">&times;</a>
        </div>
    <?php endif; ?>

    <!-- THANH ĐIỀU HƯỚNG NGUỒN DỮ LIỆU & TIẾN TRÌNH 4 BƯỚC -->
    <div class="sol-glass-card" style="padding: 1.25rem 1.5rem; margin-bottom: 2.25rem; border-color: rgba(245, 158, 11, 0.35);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #ffffff; text-transform: uppercase;">
                    Nguồn Dữ Liệu Tính Toán:
                </span>
                <div style="display: inline-flex; background: rgba(8, 12, 20, 0.75); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 4px; gap: 4px;">
                    <a href="index.php?page=calculator&source=plan" class="sol-btn <?= $isPlanSource ? 'sol-btn--solar' : 'sol-btn--secondary' ?>" style="padding: 0.45rem 1rem; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <span>📋</span> Lấy Thiết Bị Từ Kế Hoạch Dự Định
                        <?php if ($hasCart): ?>
                            <span style="background: rgba(0,0,0,0.3); padding: 1px 7px; border-radius: 999px; font-size: 0.75rem; font-weight: 800;"><?= count($planItems) ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="index.php?page=calculator&source=bill" class="sol-btn <?= !$isPlanSource ? 'sol-btn--solar' : 'sol-btn--secondary' ?>" style="padding: 0.45rem 1rem; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <span>⚙️</span> Ước Tính Theo Hóa Đơn Điện
                    </a>
                </div>
            </div>

            <div>
                <?php if ($isPlanSource): ?>
                    <span class="sol-badge sol-badge--emerald" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                        ● Đang đồng bộ từ Kế hoạch (<?= count($planItems) ?> loại thiết bị)
                    </span>
                <?php else: ?>
                    <span class="sol-badge sol-badge--amber" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                        ● Đang tính theo biểu giá hóa đơn EVN
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thanh chỉ báo 4 bước trực quan -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
            <a href="#step-1-plan" style="text-decoration: none; display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(245, 158, 11, 0.15); border: 1px solid var(--accent-amber); border-radius: var(--radius-sm); color: #ffffff;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: var(--accent-amber); color: #080c14; font-weight: 800; font-size: 0.75rem;">1</span>
                <span style="font-size: 0.8rem; font-weight: 600;">1. Đồng Bộ Thiết Bị</span>
            </a>
            <a href="#step-2-simulation" style="text-decoration: none; display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--radius-sm); color: #ffffff;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #0284c7; color: #ffffff; font-weight: 800; font-size: 0.75rem;">2</span>
                <span style="font-size: 0.8rem; font-weight: 600;">2. Mô Phỏng Bố Trí 2D</span>
            </a>
            <a href="#step-3-metrics" style="text-decoration: none; display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-sm); color: #ffffff;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: #080c14; font-weight: 800; font-size: 0.75rem;">3</span>
                <span style="font-size: 0.8rem; font-weight: 600;">3. Thông Số & Tài Chính</span>
            </a>
            <a href="#step-4-confirm" style="text-decoration: none; display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(236, 72, 153, 0.1); border: 1px solid rgba(236, 72, 153, 0.3); border-radius: var(--radius-sm); color: #ffffff;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #ec4899; color: #ffffff; font-weight: 800; font-size: 0.75rem;">4</span>
                <span style="font-size: 0.8rem; font-weight: 600;">4. Xác Nhận Cấu Hình</span>
            </a>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- BƯỚC 1: ĐỒNG BỘ TỪ KẾ HOẠCH DỰ ĐỊNH SANG & THIẾT LẬP MẶT BẰNG MÁI      -->
    <!-- ===================================================================== -->
    <section id="step-1-plan" class="sol-glass-card" style="padding: 2rem 2.25rem; margin-bottom: 2.75rem; border-left: 4px solid var(--accent-amber);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BƯỚC 1 / 4 • ĐỒNG BỘ KẾ HOẠCH DỰ ĐỊNH</span>
                <h2 style="color: #ffffff; font-size: 1.45rem; font-family: var(--font-heading); margin: 0;">
                    📋 Thiết Bị Trong Kế Hoạch & Thiết Lập Mặt Bằng Mái
                </h2>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="index.php?page=cart" class="sol-btn sol-btn--outline sol-btn--sm">
                    ✏️ Thêm / Chỉnh Sửa Kế Hoạch Dự Định
                </a>
            </div>
        </div>

        <?php if ($isPlanSource): ?>
            <!-- Lưới danh sách thiết bị trong Kế hoạch dự định -->
            <div style="margin-bottom: 1.5rem;">
                <?php if (empty($planItems)): ?>
                    <div style="text-align: center; padding: 2rem 1rem; background: rgba(8, 12, 20, 0.4); border-radius: var(--radius-sm); border: 1px dashed var(--border-subtle);">
                        <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📋</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; margin: 0 0 0.4rem;">Kế hoạch dự định hiện chưa có thiết bị nào</h4>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; max-width: 520px; margin: 0 auto 1.25rem;">
                            Hệ thống đang tự động gợi ý cấu hình mẫu hộ gia đình <strong>5.5 kWp (10 tấm pin 550W + Inverter 5kW)</strong> để mô phỏng. Bạn có thể ghé danh mục sản phẩm để chọn đúng model bạn yêu thích.
                        </p>
                        <a href="index.php?page=products" class="sol-btn sol-btn--solar sol-btn--sm">
                            + Khám Phá & Chọn Thiết Bị Vào Kế Hoạch →
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                        <?php foreach ($planItems as $item): ?>
                            <div style="padding: 1rem 1.15rem; background: rgba(8, 12, 20, 0.65); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(14, 22, 38, 0.8); border: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                                        <?= str_contains(mb_strtolower($item['name']), 'pin') ? '☀️' : (str_contains(mb_strtolower($item['name']), 'biến tần') ? '⚡' : (str_contains(mb_strtolower($item['name']), 'lưu trữ') ? '🔋' : '🔧')) ?>
                                    </div>
                                    <div>
                                        <strong style="color: #ffffff; font-size: 0.9rem; display: block; line-height: 1.3;">
                                            <?= htmlspecialchars($item['name'] ?? 'Thiết bị') ?>
                                        </strong>
                                        <span style="color: var(--text-secondary); font-size: 0.75rem;">
                                            <?= htmlspecialchars($item['category_name'] ?? 'Phụ kiện') ?>
                                            <?= !empty($item['capacity_watts']) ? ' • ' . (int)$item['capacity_watts'] . 'W' : '' ?>
                                        </span>
                                    </div>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span style="display: inline-block; padding: 0.2rem 0.55rem; background: rgba(245, 158, 11, 0.15); border: 1px solid var(--accent-amber); border-radius: 4px; font-weight: 800; font-size: 0.8rem; color: var(--accent-gold);">
                                        x<?= (int)($item['quantity'] ?? 1) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tình trạng ghép đôi & Thông báo tương thích -->
            <div style="background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.35rem;">⚡</span>
                    <div>
                        <strong style="color: #ffffff; font-size: 0.9rem;">
                            Tình Trạng Tương Thích Ghép Đôi: <span style="color: var(--accent-gold);"><?= $compat['completion_score'] ?>%</span>
                        </strong>
                        <div style="font-size: 0.775rem; color: var(--text-secondary);">
                            Giàn pin: <strong style="color: var(--accent-amber);"><?= $compat['total_panel_kwp'] ?> kWp</strong> (<?= $compat['total_panels'] ?> tấm) • Inverter: <strong style="color: var(--accent-cyan);"><?= $compat['total_inverter_kw'] ?> kW</strong> • Pin lưu trữ: <strong style="color: #10b981;"><?= $compat['total_battery_kwh'] ?> kWh</strong>
                        </div>
                    </div>
                </div>
                <?php if (!empty($calc['is_estimated_hardware'])): ?>
                    <span class="sol-badge sol-badge--amber" style="font-size: 0.725rem;">
                        ● Đã tự động ước tính giàn pin & biến tần để hoàn chỉnh bài toán đầu tư
                    </span>
                <?php endif; ?>
            </div>

            <!-- Form tùy biến diện tích mái & kết cấu để cập nhật mô phỏng -->
            <form method="POST" action="index.php?page=calculator&source=plan#step-2-simulation" style="background: rgba(14, 22, 38, 0.75); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1.5rem;">
                <input type="hidden" name="calc_source" value="plan">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; align-items: end;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            Diện tích mái nhà khả dụng (m²):
                        </label>
                        <input 
                            type="number" 
                            name="roof_area" 
                            value="<?= htmlspecialchars((string)($calc['provided_roof_area'] ?? $calc['required_roof_area'] ?? 35)) ?>" 
                            min="10" 
                            max="5000" 
                            step="1"
                            required
                            style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.95rem; font-family: var(--font-mono);"
                        >
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            Kết cấu mặt bằng mái nhà:
                        </label>
                        <select 
                            name="roof_type" 
                            style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.95rem;"
                        >
                            <option value="metal_roof" <?= ($calc['roof_type'] ?? '') === 'metal_roof' ? 'selected' : '' ?>>Mái tôn sóng / Seamlock</option>
                            <option value="tile_roof" <?= ($calc['roof_type'] ?? '') === 'tile_roof' ? 'selected' : '' ?>>Mái ngói Thái / Nhật</option>
                            <option value="concrete_roof" <?= ($calc['roof_type'] ?? '') === 'concrete_roof' ? 'selected' : '' ?>>Sân thượng Bê tông cốt thép</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="sol-btn sol-btn--solar" style="width: 100%; padding: 0.8rem 1.25rem; font-size: 0.95rem; font-weight: 700; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35); display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <span>⚡</span> Cập Nhật Diện Tích Mái & Mô Phỏng
                        </button>
                    </div>
                </div>
            </form>

        <?php else: ?>
            <!-- GIAO DIỆN CHẾ ĐỘ 2: THEO HÓA ĐƠN TIỀN ĐIỆN -->
            <form method="POST" action="index.php?page=calculator&source=bill#step-2-simulation" style="background: rgba(14, 22, 38, 0.75); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1.5rem;">
                <input type="hidden" name="calc_source" value="bill">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; align-items: end;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            Hóa đơn tiền điện trung bình hàng tháng (VNĐ):
                        </label>
                        <input 
                            type="number" 
                            name="monthly_bill" 
                            value="<?= (int)($calc['monthly_bill'] ?? 3000000) ?>" 
                            step="100000" 
                            min="500000"
                            style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.95rem; font-family: var(--font-mono);"
                        >
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            Diện tích mái khả dụng (m²):
                        </label>
                        <input 
                            type="number" 
                            name="roof_area" 
                            value="<?= htmlspecialchars((string)($calc['provided_roof_area'] ?? $calc['required_roof_area'] ?? 35)) ?>" 
                            style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.95rem;"
                        >
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            Gói hệ thống:
                        </label>
                        <select name="package" style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.95rem;">
                            <option value="residential" <?= ($calc['package_key'] ?? '') === 'residential' ? 'selected' : '' ?>>Hòa Lưới Dân Dụng (Hộ Gia Đình)</option>
                            <option value="commercial" <?= ($calc['package_key'] ?? '') === 'commercial' ? 'selected' : '' ?>>Thương Mại & Doanh Nghiệp (C&I)</option>
                            <option value="hybrid" <?= ($calc['package_key'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Lưu Trữ Hybrid ESS (Có Pin)</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="sol-btn sol-btn--solar" style="width: 100%; padding: 0.8rem 1.25rem; font-size: 0.95rem; font-weight: 700;">
                            ⚡ Cập Nhật Tính Toán & Mô Phỏng
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <!-- ===================================================================== -->
    <!-- BƯỚC 2: MÔ PHỎNG BỐ TRÍ CÁC THIẾT BỊ ĐÃ CÀI ĐẶT (SƠ ĐỒ 2D SVG)        -->
    <!-- ===================================================================== -->
    <section id="step-2-simulation" class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 2.75rem; border-left: 4px solid var(--accent-cyan);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BƯỚC 2 / 4 • MÔ PHỎNG BỐ TRÍ THIẾT BỊ</span>
                <h2 style="color: #ffffff; font-size: 1.45rem; font-family: var(--font-heading); margin: 0;">
                    🏗️ Mô Phỏng Bố Trí Mặt Bằng Mái & Toàn Bộ Thiết Bị Đã Cài Đặt (2D Layout)
                </h2>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.8rem; color: var(--text-secondary); flex-wrap: wrap;">
                <span>📐 Góc nghiêng: <strong style="color: var(--accent-amber);"><?= $calc['layout_matrix']['optimal_tilt'] ?? '12° - 15°' ?></strong></span>
                <span>🧭 Hướng: <strong style="color: var(--accent-cyan);">Chính Nam (180°)</strong></span>
                <span>📏 Ma trận: <strong style="color: #ffffff;"><?= $calc['layout_matrix']['rows'] ?? 2 ?> Hàng × <?= $calc['layout_matrix']['columns'] ?? 5 ?> Cột</strong></span>
            </div>
        </div>

        <!-- Khung bản vẽ SVG kỹ thuật bố trí hệ thống -->
        <div style="background: #060911; border: 1.5px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.75rem; position: relative; overflow-x: auto; box-shadow: inset 0 2px 12px rgba(0,0,0,0.6);">
            <!-- Huy hiệu bức xạ góc phải -->
            <div style="position: absolute; top: 20px; right: 25px; background: rgba(14, 22, 38, 0.9); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.65rem 0.95rem; font-size: 0.75rem; color: #cbd5e1; z-index: 10; display: flex; flex-direction: column; gap: 0.25rem;">
                <div style="font-weight: 700; color: var(--accent-gold);">☀️ BỨC XẠ HIỆU DỤNG VIỆT NAM</div>
                <div>Quang năng: <strong>4.5 kWh/m²/ngày</strong></div>
                <div>Tiêu chuẩn an toàn: <strong>PCCC & EVN Tier-1</strong></div>
            </div>

            <?php
            $rows = $calc['layout_matrix']['rows'] ?? 2;
            $cols = $calc['layout_matrix']['columns'] ?? 5;
            $totalPanels = (int)($calc['panel_count'] ?? 10);

            $panelW = 48;
            $panelH = 80;
            $gapX = 9;
            $gapY = 14;

            $roofPaddingX = 50;
            $roofPaddingY = 50;
            $roofArrayW = ($cols * ($panelW + $gapX)) - $gapX;
            $roofArrayH = ($rows * ($panelH + $gapY)) - $gapY;

            // Kích thước tổng thể SVG bao gồm cả khu vực giàn pin trên mái và trạm thiết bị bên dưới
            $svgW = max(860, $roofArrayW + ($roofPaddingX * 2) + 60);
            $svgH = $roofArrayH + 280;

            $startX = ($svgW - $roofArrayW) / 2;
            $startY = $roofPaddingY + 25;
            ?>

            <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width: 100%; max-width: 980px; height: auto; margin: 0 auto; display: block;">
                <defs>
                    <linearGradient id="cellGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0e2a47"/>
                        <stop offset="50%" stop-color="#0a192f"/>
                        <stop offset="100%" stop-color="#040e1c"/>
                    </linearGradient>

                    <linearGradient id="frameGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#64748b"/>
                        <stop offset="100%" stop-color="#1e293b"/>
                    </linearGradient>

                    <linearGradient id="inverterGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1e293b"/>
                        <stop offset="100%" stop-color="#0f172a"/>
                    </linearGradient>

                    <linearGradient id="batteryGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#064e3b"/>
                        <stop offset="100%" stop-color="#022c22"/>
                    </linearGradient>
                </defs>

                <!-- KHU VỰC 1: MẶT BẰNG MÁI NHÀ KHẢ DỤNG -->
                <rect x="20" y="20" width="<?= $svgW - 40 ?>" height="<?= $roofArrayH + 85 ?>" rx="8" fill="rgba(15, 23, 42, 0.45)" stroke="#334155" stroke-dasharray="6,4" stroke-width="1.5"/>
                <text x="35" y="42" fill="#94a3b8" font-size="11" font-weight="bold" font-family="sans-serif">
                    🏠 MẶT BẰNG MÁI LẮP ĐẶT: <?= mb_strtoupper($roofTypeName) ?> (<?= $calc['required_roof_area'] ?> m² KHẢ DỤNG)
                </text>

                <!-- La bàn Hướng Bắc - Nam đón nắng -->
                <g transform="translate(<?= $svgW - 60 ?>, 65)">
                    <circle cx="0" cy="0" r="16" fill="#0e1626" stroke="#475569" stroke-width="1.2"/>
                    <path d="M 0 -12 L 4 0 L -4 0 Z" fill="#f43f5e"/>
                    <path d="M 0 12 L 4 0 L -4 0 Z" fill="#94a3b8"/>
                    <text x="-4" y="-14" fill="#f43f5e" font-size="9" font-weight="bold">N</text>
                    <text x="-3" y="23" fill="#94a3b8" font-size="9" font-weight="bold">S</text>
                </g>

                <!-- Thanh ray nhôm định hình AL6005 đỡ bên dưới giàn pin -->
                <?php for ($r = 0; $r < $rows; $r++): 
                    $railY1 = $startY + ($r * ($panelH + $gapY)) + 16;
                    $railY2 = $startY + ($r * ($panelH + $gapY)) + $panelH - 16;
                ?>
                    <line x1="<?= $startX - 20 ?>" y1="<?= $railY1 ?>" x2="<?= $startX + $roofArrayW + 20 ?>" y2="<?= $railY1 ?>" stroke="#38bdf8" stroke-width="2.5" opacity="0.4"/>
                    <line x1="<?= $startX - 20 ?>" y1="<?= $railY2 ?>" x2="<?= $startX + $roofArrayW + 20 ?>" y2="<?= $railY2 ?>" stroke="#38bdf8" stroke-width="2.5" opacity="0.4"/>
                <?php endfor; ?>

                <!-- Vẽ từng tấm pin mặt trời 550W theo ma trận -->
                <?php 
                $panelIdx = 0;
                for ($r = 0; $r < $rows; $r++):
                    for ($c = 0; $c < $cols; $c++):
                        if ($panelIdx >= $totalPanels) break;
                        $panelIdx++;
                        $px = $startX + ($c * ($panelW + $gapX));
                        $py = $startY + ($r * ($panelH + $gapY));
                ?>
                    <g transform="translate(<?= $px ?>, <?= $py ?>)">
                        <rect x="0" y="0" width="<?= $panelW ?>" height="<?= $panelH ?>" rx="3" fill="url(#frameGrad)" stroke="#475569" stroke-width="0.8"/>
                        <rect x="2" y="2" width="<?= $panelW - 4 ?>" height="<?= $panelH - 4 ?>" rx="2" fill="url(#cellGrad)"/>
                        <line x1="2" y1="<?= $panelH/2 ?>" x2="<?= $panelW - 2 ?>" y2="<?= $panelH/2 ?>" stroke="rgba(255,255,255,0.25)" stroke-width="0.75"/>
                        <line x1="<?= $panelW/3 ?>" y1="2" x2="<?= $panelW/3 ?>" y2="<?= $panelH - 2 ?>" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
                        <line x1="<?= ($panelW/3)*2 ?>" y1="2" x2="<?= ($panelW/3)*2 ?>" y2="<?= $panelH - 2 ?>" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
                        <text x="<?= $panelW/2 ?>" y="<?= $panelH/2 + 3 ?>" fill="#38bdf8" font-size="8" font-family="monospace" text-anchor="middle" font-weight="bold">#<?= $panelIdx ?></text>
                    </g>
                <?php 
                    endfor;
                endfor; 
                ?>

                <!-- KHU VỰC 2: TRẠM THIẾT BỊ BỐ TRÍ DƯỚI MÁI (BOS & INVERTER STATION) -->
                <?php
                $bosY = $roofArrayH + 130;
                ?>
                <!-- Dây cáp DC chuyên dụng màu cam/đỏ dẫn từ mái xuống -->
                <path d="M <?= $startX + $roofArrayW ?> <?= $startY + ($roofArrayH / 2) ?> L <?= $svgW - 120 ?> <?= $startY + ($roofArrayH / 2) ?> L <?= $svgW - 120 ?> <?= $bosY + 40 ?> L <?= $svgW - 160 ?> <?= $bosY + 40 ?>" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-dasharray="5,3"/>
                <text x="<?= $svgW - 105 ?>" y="<?= $startY + ($roofArrayH / 2) + 20 ?>" fill="#f59e0b" font-size="8" font-family="monospace">Cáp DC 4.0mm²</text>

                <!-- KHỐI 1: TỦ ĐIỆN AN TOÀN AC/DC & SPD CHỐNG SÉT -->
                <g transform="translate(<?= $startX ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="130" height="85" rx="6" fill="#111827" stroke="#38bdf8" stroke-width="1.8"/>
                    <rect x="6" y="6" width="118" height="20" rx="3" fill="#1e293b"/>
                    <circle cx="16" cy="16" r="4" fill="#10b981"/>
                    <text x="26" y="19" fill="#ffffff" font-size="9" font-weight="bold">TỦ ĐIỆN AC/DC</text>
                    
                    <rect x="15" y="36" width="30" height="38" rx="2" fill="#1f2937" stroke="#475569" stroke-width="1"/>
                    <text x="30" y="58" fill="#38bdf8" font-size="8" text-anchor="middle" font-weight="bold">SPD</text>

                    <rect x="55" y="36" width="30" height="38" rx="2" fill="#1f2937" stroke="#475569" stroke-width="1"/>
                    <text x="70" y="58" fill="#f59e0b" font-size="8" text-anchor="middle" font-weight="bold">MCB</text>

                    <rect x="95" y="36" width="22" height="38" rx="2" fill="#1f2937" stroke="#475569" stroke-width="1"/>
                    <circle cx="106" cy="55" r="4" fill="#f43f5e"/>

                    <text x="65" y="100" fill="#cbd5e1" font-size="9" text-anchor="middle" font-weight="bold">1. Tủ Điện An Toàn & SPD</text>
                </g>

                <!-- Dây dẫn nối giữa Tủ điện và Biến tần -->
                <line x1="<?= $startX + 130 ?>" y1="<?= $bosY + 42 ?>" x2="<?= $startX + 170 ?>" y2="<?= $bosY + 42 ?>" stroke="#38bdf8" stroke-width="2.5"/>

                <!-- KHỐI 2: BIẾN TẦN INVERTER QUANG ĐIỆN -->
                <g transform="translate(<?= $startX + 170 ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="150" height="85" rx="6" fill="url(#inverterGrad)" stroke="#f59e0b" stroke-width="2"/>
                    <rect x="15" y="15" width="120" height="30" rx="3" fill="#080c14" stroke="#334155" stroke-width="1"/>
                    <text x="25" y="32" fill="#10b981" font-size="10" font-family="monospace" font-weight="bold">● <?= $calc['estimated_kwp'] ?> kW · 50Hz</text>
                    <line x1="20" y1="58" x2="130" y2="58" stroke="#334155" stroke-width="1.5"/>
                    <line x1="20" y1="64" x2="130" y2="64" stroke="#334155" stroke-width="1.5"/>
                    <text x="75" y="100" fill="#fbbf24" font-size="9" text-anchor="middle" font-weight="bold">2. Biến Tần Inverter</text>
                </g>

                <!-- Dây dẫn nối từ Inverter sang Pin lưu trữ hoặc Smart Meter -->
                <line x1="<?= $startX + 320 ?>" y1="<?= $bosY + 42 ?>" x2="<?= $startX + 360 ?>" y2="<?= $bosY + 42 ?>" stroke="#10b981" stroke-width="2.5"/>

                <!-- KHỐI 3: PIN LƯU TRỮ LITHIUM ESS (NẾU CÓ HOẶC HỆ HYBRID) -->
                <g transform="translate(<?= $startX + 360 ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="120" height="85" rx="6" fill="url(#batteryGrad)" stroke="#10b981" stroke-width="1.8"/>
                    <rect x="10" y="12" width="100" height="15" rx="2" fill="#064e3b"/>
                    <text x="60" y="23" fill="#34d399" font-size="8" text-anchor="middle" font-weight="bold">LITHIUM LIFEPO4</text>
                    <rect x="25" y="38" width="70" height="10" rx="2" fill="#042f2e" stroke="#10b981" stroke-width="0.8"/>
                    <rect x="27" y="40" width="55" height="6" rx="1" fill="#10b981"/>
                    <text x="60" y="65" fill="#a7f3d0" font-size="8" text-anchor="middle">SOC: 98%</text>
                    <text x="60" y="100" fill="#10b981" font-size="9" text-anchor="middle" font-weight="bold">3. Pin Lưu Trữ ESS</text>
                </g>

                <!-- Dây dẫn nối sang Smart Meter -->
                <line x1="<?= $startX + 480 ?>" y1="<?= $bosY + 42 ?>" x2="<?= $startX + 520 ?>" y2="<?= $bosY + 42 ?>" stroke="#38bdf8" stroke-width="2.5"/>

                <!-- KHỐI 4: ĐỒNG HỒ SMART METER & ĐẤU NỐI EVN -->
                <g transform="translate(<?= $startX + 520 ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="130" height="85" rx="6" fill="#111827" stroke="#38bdf8" stroke-width="1.8"/>
                    <circle cx="65" cy="35" r="22" fill="#080c14" stroke="#38bdf8" stroke-width="1.2"/>
                    <text x="65" y="32" fill="#38bdf8" font-size="8" font-family="monospace" text-anchor="middle">kWh EVN</text>
                    <text x="65" y="44" fill="#fbbf24" font-size="9" font-family="monospace" font-weight="bold" text-anchor="middle">2 CHIỀU</text>
                    <text x="65" y="72" fill="#94a3b8" font-size="8" text-anchor="middle">Modbus RTU</text>
                    <text x="65" y="100" fill="#38bdf8" font-size="9" text-anchor="middle" font-weight="bold">4. Smart Meter 2 Chiều</text>
                </g>

                <!-- Dây AC nối vào Lưới điện EVN và Phụ tải gia đình -->
                <path d="M <?= $startX + 650 ?> <?= $bosY + 42 ?> L <?= $startX + 690 ?> <?= $bosY + 42 ?>" stroke="#10b981" stroke-width="2.5"/>

                <g transform="translate(<?= $startX + 690 ?>, <?= $bosY + 10 ?>)">
                    <rect x="0" y="0" width="55" height="65" rx="4" fill="#1f2937" stroke="#64748b" stroke-width="1"/>
                    <text x="27" y="28" font-size="18" text-anchor="middle">⚡</text>
                    <text x="27" y="46" fill="#cbd5e1" font-size="7" font-weight="bold" text-anchor="middle">LƯỚI EVN</text>
                    <text x="27" y="56" fill="#10b981" font-size="7" text-anchor="middle">HÒA LƯỚI</text>
                </g>
            </svg>

            <!-- Chú thích bố trí thiết bị hệ thống -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; flex-wrap: wrap; margin-top: 1.5rem; font-size: 0.775rem; color: var(--text-secondary); border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 14px; height: 14px; background: #0a192f; border: 1px solid #475569; border-radius: 2px;"></span>
                    <span><strong><?= $calc['panel_count'] ?> Tấm Pin Tier-1</strong> (<?= $calc['estimated_kwp'] ?> kWp)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 18px; height: 3px; background: #38bdf8;"></span>
                    <span>Ray Nhôm AL6005 & Kẹp Pin Inox</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #111827; border: 1.5px solid #38bdf8; border-radius: 2px;"></span>
                    <span>Tủ Điện AC/DC & SPD Chống Sét</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 14px; height: 12px; background: #1e293b; border: 1.5px solid #f59e0b; border-radius: 2px;"></span>
                    <span>Biến Tần Inverter IP65</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #064e3b; border: 1.5px solid #10b981; border-radius: 2px;"></span>
                    <span>Pin Lưu Trữ Lithium (ESS)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #111827; border: 1.5px solid #38bdf8; border-radius: 50%;"></span>
                    <span>Đồng Hồ Smart Meter 2 Chiều</span>
                </div>
            </div>
        </div>

        <?php if (!empty($calc['is_roof_sufficient']) && !$calc['is_roof_sufficient']): ?>
            <div style="margin-top: 1.25rem; padding: 0.9rem 1.2rem; border-radius: var(--radius-sm); background: rgba(244, 63, 94, 0.15); border: 1px solid var(--accent-rose); font-size: 0.85rem; color: #fecdd3;">
                ⚠️ <strong>Cảnh báo diện tích:</strong> Diện tích mái khai báo (<?= $calc['provided_roof_area'] ?> m²) nhỏ hơn diện tích yêu cầu cho giàn pin (<?= $calc['required_roof_area'] ?> m²). Kỹ sư sẽ tư vấn phương án dựng giàn khung nâng cao khi khảo sát hiện trường.
            </div>
        <?php endif; ?>
    </section>

    <!-- ===================================================================== -->
    <!-- BƯỚC 3: CÁC THÔNG SỐ CŨNG NHƯ THÔNG TIN KHÁCH HÀNG SẼ CẦN BIẾT         -->
    <!-- ===================================================================== -->
    <section id="step-3-metrics" class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 2.75rem; border-left: 4px solid var(--accent-emerald);">
        <div style="margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <span class="sol-badge sol-badge--emerald" style="margin-bottom: 0.35rem;">BƯỚC 3 / 4 • BÁO CÁO THÔNG SỐ & HIỆU QUẢ</span>
            <h2 style="color: #ffffff; font-size: 1.45rem; font-family: var(--font-heading); margin: 0;">
                📊 Các Thông Số Kỹ Thuật & Hiệu Quả Đầu Tư Khách Hàng Cần Biết
            </h2>
        </div>

        <!-- 3 KHỐI CHỈ SỐ VÀNG NỔI BẬT THEO YÊU CẦU -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            
            <!-- CHỈ SỐ 1: SỐ KW ĐIỆN TẠO RA -->
            <div class="sol-glass-card" style="padding: 1.5rem 1.65rem; border-left: 4px solid var(--accent-amber); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(8, 12, 20, 0.85) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="sol-badge sol-badge--amber" style="font-size: 0.725rem; margin-bottom: 0.35rem;">
                            ⚡ NĂNG LƯỢNG QUANG ĐIỆN
                        </span>
                        <div style="font-size: 0.825rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase;">
                            Số KW Điện Tạo Ra Từ Kế Hoạch
                        </div>
                    </div>
                    <span style="font-size: 1.75rem;">☀️</span>
                </div>

                <div style="margin-top: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Công Suất Đỉnh Giàn Pin:</div>
                    <div style="font-family: var(--font-heading); font-size: 2.3rem; font-weight: 800; color: #ffffff; line-height: 1.1;">
                        <?= $calc['estimated_kwp'] ?> <span style="font-size: 1.2rem; color: var(--accent-amber);">kWp</span>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Quy mô: <strong><?= $calc['panel_count'] ?> tấm pin</strong> (Hiệu suất 0.82)
                    </div>
                </div>

                <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid var(--border-subtle);">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Sản Lượng Điện Ước Tính:</div>
                    <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: var(--accent-cyan); line-height: 1.1;">
                        ~<?= number_format($calc['monthly_kwh'] ?? (($calc['yearly_kwh_produced'] ?? 0) / 12), 1, ',', '.') ?> <span style="font-size: 0.95rem; color: #ffffff;">kWh / tháng</span>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                        Tương đương <strong>~<?= number_format($calc['yearly_kwh_produced'], 0, ',', '.') ?> số điện (kWh)/năm</strong>
                    </div>
                </div>
            </div>

            <!-- CHỈ SỐ 2: SỐ TIỀN TẠO RA TRONG 1 THÁNG -->
            <div class="sol-glass-card" style="padding: 1.5rem 1.65rem; border-left: 4px solid var(--accent-emerald); background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(8, 12, 20, 0.85) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="sol-badge sol-badge--emerald" style="font-size: 0.725rem; margin-bottom: 0.35rem;">
                            💰 GIÁ TRỊ TÀI CHÍNH
                        </span>
                        <div style="font-size: 0.825rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase;">
                            Số Tiền Tạo Ra Trong 1 Tháng
                        </div>
                    </div>
                    <span style="font-size: 1.75rem;">💵</span>
                </div>

                <div style="margin-top: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Tiết Kiệm Điện Mỗi Tháng:</div>
                    <div style="font-family: var(--font-heading); font-size: 2.1rem; font-weight: 800; color: var(--accent-gold); line-height: 1.1;">
                        <?= $calc['monthly_money_formatted'] ?? $calc['monthly_savings_formatted'] ?>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Tính trên đơn giá điện EVN: <strong>3.000 đ/kWh</strong>
                    </div>
                </div>

                <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid var(--border-subtle);">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Giá Trị Tạo Ra Cả Năm:</div>
                    <div style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: #10b981; line-height: 1.1;">
                        ~<?= $calc['yearly_savings_formatted'] ?>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                        Sau 25 năm tạo ra: <strong><?= number_format($calc['total_25yr_savings'] / 1000000, 0, ',', '.') ?> Triệu VNĐ</strong>
                    </div>
                </div>
            </div>

            <!-- CHỈ SỐ 3: DỰ TÍNH THỜI GIAN GỠ LẠI SỐ TIỀN (HOÀN VỐN) -->
            <div class="sol-glass-card" style="padding: 1.5rem 1.65rem; border-left: 4px solid var(--accent-cyan); background: linear-gradient(135deg, rgba(56, 189, 248, 0.08) 0%, rgba(8, 12, 20, 0.85) 100%);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span class="sol-badge sol-badge--cyan" style="font-size: 0.725rem; margin-bottom: 0.35rem;">
                            ⏳ THỜI GIAN HOÀN VỐN (ROI)
                        </span>
                        <div style="font-size: 0.825rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase;">
                            Dự Tính Thời Gian Gỡ Lại Vốn
                        </div>
                    </div>
                    <span style="font-size: 1.75rem;">🎯</span>
                </div>

                <div style="margin-top: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Thời Gian Gỡ Lại Toàn Bộ Vốn:</div>
                    <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #38bdf8; line-height: 1.1;">
                        <?= $calc['payback_text'] ?? ($calc['payback_years'] . ' Năm') ?>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Tương đương <strong><?= $calc['payback_years_decimal'] ?? $calc['payback_years'] ?> năm</strong> hoàn vốn 100%
                    </div>
                </div>

                <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid var(--border-subtle);">
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Tổng Vốn Đầu Tư Dự Tính:</div>
                    <div style="font-family: var(--font-heading); font-size: 1.55rem; font-weight: 800; color: #ffffff; line-height: 1.1;">
                        <?= $calc['estimated_total_cost_formatted'] ?>
                    </div>
                    <div style="font-size: 0.775rem; color: #34d399; margin-top: 0.2rem;">
                        Lãi ròng 25 năm: <strong>+<?= $calc['net_25yr_profit_formatted'] ?></strong> (Tỷ suất <?= $calc['roi_percentage'] ?>%)
                    </div>
                </div>
            </div>

        </div>

        <!-- 4 Ô CHỈ SỐ MÔI TRƯỜNG & DIỆN TÍCH -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); text-align: center;">
                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Diện Tích Mái Cần</div>
                <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: #ffffff; margin-top: 0.2rem;">
                    <?= $calc['required_roof_area'] ?> m²
                </div>
                <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.15rem;">Khả dụng: <?= $calc['provided_roof_area'] ?? $calc['required_roof_area'] ?> m²</div>
            </div>

            <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); text-align: center;">
                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Số Lượng Tấm Pin</div>
                <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: var(--accent-amber); margin-top: 0.2rem;">
                    <?= $calc['panel_count'] ?> Tấm
                </div>
                <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.15rem;">Loại Mono Tier-1 550W</div>
            </div>

            <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); text-align: center;">
                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Cắt Giảm CO₂ Hàng Năm</div>
                <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: #38bdf8; margin-top: 0.2rem;">
                    ~<?= number_format(($calc['co2_saved_kg_yearly'] ?? 0) / 1000, 1) ?> Tấn
                </div>
                <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.15rem;">Bảo vệ khí hậu xanh</div>
            </div>

            <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); text-align: center;">
                <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Trồng Cây Tương Đương</div>
                <div style="font-family: var(--font-heading); font-size: 1.3rem; font-weight: 700; color: #10b981; margin-top: 0.2rem;">
                    🌱 <?= $calc['trees_equivalent_yearly'] ?? 0 ?> Cây
                </div>
                <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.15rem;">Đóng góp môi trường</div>
            </div>
        </div>

        <!-- BẢNG BÓC TÁCH VẬT TƯ KỸ THUẬT CHI TIẾT (BOM) -->
        <div style="margin-top: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                <h4 style="color: #ffffff; font-size: 1.05rem; font-family: var(--font-heading); margin: 0;">
                    📋 Bảng Bóc Tách Vật Tư Kỹ Thuật Chi Tiết (BOM - Bill of Materials)
                </h4>
                <span style="color: var(--accent-gold); font-size: 0.85rem; font-weight: 700;">
                    Tổng dự toán: <?= $calc['estimated_total_cost_formatted'] ?>
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; text-align: left; color: #ffffff;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(245, 158, 11, 0.25); background: rgba(8, 12, 20, 0.7); color: var(--accent-gold); font-size: 0.75rem; text-transform: uppercase;">
                            <th style="padding: 0.75rem 0.85rem;">STT</th>
                            <th style="padding: 0.75rem 0.85rem;">Hạng Mục Thiết Bị</th>
                            <th style="padding: 0.75rem 0.85rem;">Quy Cách & Tiêu Chuẩn Kỹ Thuật</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: center;">Số Lượng</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: center;">ĐVT</th>
                            <th style="padding: 0.75rem 0.85rem;">Chính Sách Bảo Hành</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($calc['bom'])): ?>
                            <?php foreach ($calc['bom'] as $index => $b): ?>
                                <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.15s ease;">
                                    <td style="padding: 0.75rem 0.85rem; color: var(--text-muted); font-family: var(--font-mono);"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></td>
                                    <td style="padding: 0.75rem 0.85rem; font-weight: 600; color: #ffffff;"><?= htmlspecialchars($b['item']) ?></td>
                                    <td style="padding: 0.75rem 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars($b['specs']) ?></td>
                                    <td style="padding: 0.75rem 0.85rem; text-align: center; font-weight: 700; color: var(--accent-amber); font-family: var(--font-mono);"><?= $b['quantity'] ?></td>
                                    <td style="padding: 0.75rem 0.85rem; text-align: center; color: var(--text-muted);"><?= htmlspecialchars($b['unit']) ?></td>
                                    <td style="padding: 0.75rem 0.85rem; color: #10b981; font-weight: 600; font-size: 0.78rem;"><?= htmlspecialchars($b['warranty']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- BƯỚC 4: XÁC NHẬN PHƯƠNG ÁN & CHUYỂN SANG CHỨC NĂNG QUẢN LÝ LẮP ĐẶT    -->
    <!-- ===================================================================== -->
    <section id="step-4-confirm" class="sol-glass-card" style="padding: 2.25rem 2.5rem; margin-bottom: 3.5rem; border-left: 4px solid #ec4899; border-color: rgba(236, 72, 153, 0.35);">
        <div style="max-width: 820px; margin: 0 auto; text-align: center; margin-bottom: 2rem;">
            <span class="sol-badge" style="background: rgba(236, 72, 153, 0.15); color: #f472b6; border: 1px solid #ec4899; margin-bottom: 0.45rem;">
                BƯỚC 4 / 4 • XÁC NHẬN CẤU HÌNH
            </span>
            <h2 style="color: #ffffff; font-size: 1.85rem; font-family: var(--font-heading); margin-bottom: 0.65rem;">
                Xác Nhận Cấu Hình Phương Án
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.925rem; line-height: 1.5;">
                Sau khi kiểm tra mô phỏng mặt bằng 2D và hiệu quả tài chính, hãy điền thông tin công trình và nhấn <strong>Xác nhận cấu hình</strong> để lưu dự toán vào CSDL và tự động chuyển sang phân hệ Quản lý lắp đặt.
            </p>
        </div>

        <!-- Hộp tóm tắt phương án chốt trước khi xác nhận -->
        <div style="max-width: 820px; margin: 0 auto 2rem; background: rgba(8, 12, 20, 0.65); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1.25rem 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; text-align: center;">
            <div>
                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; text-transform: uppercase;">Quy mô công suất:</span>
                <strong style="color: var(--accent-amber); font-size: 1.2rem; font-family: var(--font-heading);"><?= $calc['estimated_kwp'] ?> kWp</strong>
                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;"><?= $calc['panel_count'] ?> tấm pin Tier-1</span>
            </div>
            <div>
                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; text-transform: uppercase;">Mặt bằng lắp đặt:</span>
                <strong style="color: #ffffff; font-size: 1.05rem;"><?= htmlspecialchars($roofTypeName) ?></strong>
                <span style="font-size: 0.75rem; color: var(--text-secondary); display: block;"><?= $calc['required_roof_area'] ?> m² diện tích</span>
            </div>
            <div>
                <span style="font-size: 0.72rem; color: var(--text-muted); display: block; text-transform: uppercase;">Dự toán trọn gói:</span>
                <strong style="color: var(--accent-gold); font-size: 1.2rem; font-family: var(--font-heading);"><?= $calc['estimated_total_cost_formatted'] ?></strong>
                <span style="font-size: 0.75rem; color: #10b981; display: block;">Hoàn vốn ~<?= $calc['payback_text'] ?? '3.5 năm' ?></span>
            </div>
        </div>

        <!-- Form xác nhận & gửi yêu cầu khảo sát thi công -->
        <form method="POST" action="index.php?page=calculator<?= $isPlanSource ? '&source=plan' : '' ?>" style="max-width: 820px; margin: 0 auto;">
            <input type="hidden" name="calc_source" value="<?= htmlspecialchars($source ?? 'plan') ?>">
            <input type="hidden" name="monthly_bill" value="<?= (int)($calc['monthly_bill'] ?? $calc['monthly_money'] ?? 3000000) ?>">
            <input type="hidden" name="roof_area" value="<?= $calc['required_roof_area'] ?>">
            <input type="hidden" name="package" value="<?= htmlspecialchars($calc['package_key'] ?? 'residential') ?>">
            <input type="hidden" name="roof_type" value="<?= htmlspecialchars($calc['roof_type'] ?? 'metal_roof') ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;" class="sol-form-two-cols">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                        Họ và tên khách hàng / Chủ đầu tư <span style="color: var(--accent-rose);">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="customer_name" 
                        value="<?= htmlspecialchars($defaultCustomerName) ?>" 
                        required 
                        placeholder="Ví dụ: Nguyễn Văn An"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
                    >
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                        Số điện thoại liên hệ <span style="color: var(--accent-rose);">*</span>
                    </label>
                    <input 
                        type="tel" 
                        name="customer_phone" 
                        value="<?= htmlspecialchars($defaultCustomerPhone) ?>" 
                        required 
                        placeholder="Ví dụ: 0912 345 678"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
                    >
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;" class="sol-form-two-cols">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                        Email nhận thông báo tiến độ & hợp đồng
                    </label>
                    <input 
                        type="email" 
                        name="customer_email" 
                        value="<?= htmlspecialchars($defaultCustomerEmail) ?>" 
                        placeholder="khachhang@email.com"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
                    >
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                        Địa chỉ công trình lắp đặt <span style="color: var(--accent-rose);">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="address" 
                        required 
                        placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/TP..."
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
                    >
                </div>
            </div>

            <div style="margin-bottom: 1.75rem;">
                <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                    Ghi chú yêu cầu khảo sát hoặc thiết bị chỉ định
                </label>
                <textarea 
                    name="notes" 
                    rows="3" 
                    placeholder="Ghi chú thêm về hướng nhà, độ dốc mái tôn hoặc giờ hẹn kỹ sư khảo sát..."
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem; font-family: inherit;"
                ><?= $isPlanSource ? "Cấu hình từ Kế hoạch dự định: {$calc['estimated_kwp']} kWp ({$calc['panel_count']} tấm pin). Dự toán: {$calc['estimated_total_cost_formatted']}." : '' ?></textarea>
            </div>

            <!-- NÚT XÁC NHẬN CHUYỂN SANG QUẢN LÝ LẮP ĐẶT (ĐIỂM NHẤN CHÍNH) -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.85rem;">
                <button 
                    type="submit" 
                    name="action" 
                    value="confirm_to_install" 
                    class="sol-btn sol-btn--solar sol-btn--lg" 
                    style="width: 100%; max-width: 520px; font-size: 1.05rem; font-weight: 800; padding: 1rem 1.85rem; box-shadow: 0 6px 25px rgba(245, 158, 11, 0.45); display: flex; align-items: center; justify-content: center; gap: 0.65rem;"
                >
                    <span>🚀</span> Xác Nhận Cấu Hình
                </button>

                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; justify-content: center;">
                    <button 
                        type="submit" 
                        name="action" 
                        value="save_quote" 
                        class="sol-btn sol-btn--outline sol-btn--sm" 
                        style="color: var(--text-secondary); font-size: 0.825rem;"
                    >
                        💾 Lưu Dự Toán (Trạng Thái Approved)
                    </button>
                    <a 
                        href="index.php?page=installations" 
                        class="sol-btn sol-btn--secondary sol-btn--sm" 
                        style="font-size: 0.825rem; text-decoration: none;"
                    >
                        📋 Xem Trang Quản Lý Lắp Đặt Hiện Tại ➔
                    </a>
                </div>
            </div>
        </form>
    </section>

    <!-- ===================================================================== -->
    <!-- DANH SÁCH LỊCH SỬ BÁO GIÁ ĐÃ LƯU (CSDL MySQL solar_quotes)             -->
    <!-- ===================================================================== -->
    <section class="sol-glass-card" style="padding: 2.25rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.4rem;">QUẢN TRỊ DỮ LIỆU CSDL (THÀNH VIÊN 3)</span>
                <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0;">
                    Danh Sách Yêu Cầu Báo Giá Đã Tiếp Nhận (solar_quotes)
                </h3>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="sol-badge <?= $db_connected ? 'sol-badge--emerald' : 'sol-badge--amber' ?>">
                    <?= $db_connected ? '● MySQL Connected' : '○ Chế độ Offline' ?>
                </span>
            </div>
        </div>

        <?php if (empty($quotes_list)): ?>
            <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📋</div>
                <div style="font-size: 1rem; color: #ffffff; margin-bottom: 0.35rem;">Chưa có yêu cầu báo giá nào trong cơ sở dữ liệu</div>
                <p style="font-size: 0.85rem; max-width: 520px; margin: 0 auto;">
                    Hãy điền form bên trên để đăng ký dự toán đầu tiên, dữ liệu sẽ tự động lưu vào bảng <code>solar_quotes</code> của CSDL MySQL.
                </p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(56, 189, 248, 0.3); color: var(--accent-cyan); font-size: 0.75rem; text-transform: uppercase;">
                            <th style="padding: 0.75rem 0.85rem;">Mã Báo Giá</th>
                            <th style="padding: 0.75rem 0.85rem;">Khách Hàng</th>
                            <th style="padding: 0.75rem 0.85rem;">Số Điện Thoại</th>
                            <th style="padding: 0.75rem 0.85rem;">Địa Chỉ</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: center;">Công Suất</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: center;">Số Pin</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: right;">Dự Toán Chi Phí</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: center;">Trạng Thái</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: center;">Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotes_list as $q): ?>
                            <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.15s ease;">
                                <td style="padding: 0.8rem 0.85rem; font-family: var(--font-mono); color: var(--accent-gold); font-weight: 700;">
                                    #SQ-<?= str_pad((string)$q['id'], 4, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td style="padding: 0.8rem 0.85rem; color: #ffffff; font-weight: 600;">
                                    <?= htmlspecialchars($q['customer_name']) ?>
                                </td>
                                <td style="padding: 0.8rem 0.85rem; color: var(--text-secondary); font-family: var(--font-mono);">
                                    <?= htmlspecialchars($q['customer_phone']) ?>
                                </td>
                                <td style="padding: 0.8rem 0.85rem; color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($q['address']) ?>
                                </td>
                                <td style="padding: 0.8rem 0.85rem; text-align: center; color: var(--accent-amber); font-weight: 700;">
                                    <?= $q['estimated_system_kwp'] ?> kWp
                                </td>
                                <td style="padding: 0.8rem 0.85rem; text-align: center; color: var(--text-primary);">
                                    <?= $q['panel_count'] ?> tấm
                                </td>
                                <td style="padding: 0.8rem 0.85rem; text-align: right; color: var(--accent-gold); font-weight: 600; font-family: var(--font-mono);">
                                    <?= number_format((float)$q['estimated_total_cost'], 0, ',', '.') ?> đ
                                </td>
                                <td style="padding: 0.8rem 0.85rem; text-align: center;">
                                    <span class="sol-badge sol-badge--emerald" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                        ✓ Approved
                                    </span>
                                </td>
                                <td style="padding: 0.8rem 0.85rem; text-align: center; color: var(--text-muted); font-size: 0.75rem;">
                                    <?= !empty($q['created_at']) ? date('d/m/Y H:i', strtotime($q['created_at'])) : 'Vừa xong' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Thanh phân trang tự động qua Hook UsePaginationState (> 20 dòng) -->
            <?= $pagination->render() ?>
        <?php endif; ?>
    </section>
</div>

<script>
    // Tự động cuộn mượt mà đến phần mô phỏng nếu có hash #step-2-simulation
    if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
