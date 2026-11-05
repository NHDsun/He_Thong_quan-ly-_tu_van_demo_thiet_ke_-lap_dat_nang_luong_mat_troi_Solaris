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

$showStepsFrom2 = $show_steps_from_2 ?? false;
$isHardwareComplete = $is_hardware_complete ?? false;
$missingRequired = $missing_required ?? [];

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
            Quy trình khép kín: Đồng bộ thiết bị từ <strong>Kế hoạch dự định</strong> → Mô phỏng <strong>bố trí thiết bị trên mặt bằng 2D</strong> → Tính toán <strong>3 chỉ số cốt lõi</strong> (Số KW, Tiền tạo ra/tháng, Thời gian gỡ vốn) → <strong>Gửi yêu cầu để quản lý xem xét</strong>.
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
            <?php if (!empty($showStepsFrom2)): ?>
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
            <?php else: ?>
                <div style="display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-sm); color: var(--text-muted); opacity: 0.7;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #334155; color: #94a3b8; font-weight: 800; font-size: 0.75rem;">2</span>
                    <span style="font-size: 0.8rem; font-weight: 600;">2. Mô Phỏng Bố Trí 2D</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-sm); color: var(--text-muted); opacity: 0.7;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #334155; color: #94a3b8; font-weight: 800; font-size: 0.75rem;">3</span>
                    <span style="font-size: 0.8rem; font-weight: 600;">3. Thông Số &amp; Tài Chính</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.85rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-sm); color: var(--text-muted); opacity: 0.7;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #334155; color: #94a3b8; font-weight: 800; font-size: 0.75rem;">4</span>
                    <span style="font-size: 0.8rem; font-weight: 600;">4. Xác Nhận Cấu Hình</span>
                </div>
            <?php endif; ?>
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
            <style>
            .sol-sys-matrix {
                margin-bottom: 2rem;
                padding: 1.5rem;
                border-radius: var(--radius-lg, 16px);
                background: linear-gradient(135deg, rgba(14, 22, 38, 0.85) 0%, rgba(8, 12, 20, 0.95) 100%);
                border: 1px solid rgba(245, 158, 11, 0.25);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            }
            .sol-sys-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
                padding-bottom: 1.25rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }
            .sol-sys-title-group {
                max-width: 620px;
            }
            .sol-sys-title {
                font-size: 1.25rem;
                color: #ffffff;
                font-family: var(--font-heading);
                margin: 0.35rem 0 0.25rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .sol-sys-desc {
                font-size: 0.825rem;
                color: var(--text-secondary, #94a3b8);
                margin: 0;
                line-height: 1.5;
            }
            .sol-sys-progress-meta {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                flex-wrap: wrap;
            }
            .sol-sys-track {
                width: 100%;
                height: 6px;
                background: rgba(255, 255, 255, 0.06);
                border-radius: 999px;
                overflow: hidden;
                margin-top: 1rem;
                position: relative;
            }
            .sol-sys-bar {
                height: 100%;
                border-radius: 999px;
                transition: width 0.4s ease;
            }
            .sol-sys-cards {
                display: grid;
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 0.75rem;
                margin-top: 1.25rem;
            }
            @media (max-width: 1200px) {
                .sol-sys-cards {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
            }
            @media (max-width: 768px) {
                .sol-sys-cards {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }
            @media (max-width: 480px) {
                .sol-sys-cards {
                    grid-template-columns: 1fr;
                }
            }
            .sol-card-node {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 1.1rem 0.85rem;
                border-radius: var(--radius-md, 12px);
                background: rgba(10, 15, 26, 0.7);
                border: 1.5px solid rgba(255, 255, 255, 0.08);
                transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
                position: relative;
                min-height: 220px;
            }
            .sol-card-node:hover {
                transform: translateY(-3px);
            }
            .sol-card-node.is-selected {
                border-color: rgba(16, 185, 129, 0.55);
                background: linear-gradient(180deg, rgba(16, 185, 129, 0.12) 0%, rgba(10, 15, 26, 0.85) 100%);
                box-shadow: 0 4px 18px rgba(16, 185, 129, 0.15);
            }
            .sol-card-node.is-missing-required {
                border-color: rgba(244, 63, 94, 0.45);
                border-style: dashed;
                background: linear-gradient(180deg, rgba(244, 63, 94, 0.08) 0%, rgba(10, 15, 26, 0.85) 100%);
            }
            .sol-card-node.is-missing-optional {
                border-color: rgba(148, 163, 184, 0.25);
                border-style: dashed;
                background: rgba(10, 15, 26, 0.5);
            }
            .sol-node-tag {
                font-size: 0.65rem;
                font-weight: 800;
                letter-spacing: 0.5px;
                padding: 0.18rem 0.55rem;
                border-radius: 999px;
                margin-bottom: 0.75rem;
                text-transform: uppercase;
            }
            .sol-node-icon {
                width: 46px;
                height: 46px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.4rem;
                margin-bottom: 0.55rem;
            }
            .sol-node-icon.has-product-image {
                width: 64px;
                height: 64px;
                overflow: hidden;
                border-radius: 12px;
            }
            .sol-node-icon.has-product-image img {
                display: block;
                width: 100%;
                height: 100%;
                padding: 4px;
                object-fit: contain;
            }
            .sol-node-label {
                font-size: 0.875rem;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 0.25rem;
                line-height: 1.25;
            }
            .sol-node-sub {
                font-size: 0.7rem;
                color: var(--text-muted, #94a3b8);
                margin-bottom: 0.65rem;
            }
            .sol-node-product-name {
                font-size: 0.72rem;
                font-weight: 600;
                color: #38bdf8;
                line-height: 1.3;
                max-height: 2.6em;
                overflow: hidden;
                text-overflow: ellipsis;
                margin-bottom: 0.35rem;
            }
            .sol-node-qty {
                font-size: 0.75rem;
                font-weight: 700;
                color: #10b981;
                margin-bottom: 0.5rem;
            }
            .sol-node-action {
                margin-top: auto;
                width: 100%;
            }
            .sol-node-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.35rem;
                width: 100%;
                padding: 0.4rem 0.5rem;
                font-size: 0.72rem;
                font-weight: 700;
                border-radius: 6px;
                text-decoration: none;
                transition: all 0.15s ease;
            }
            </style>
            <?php
            // Sơ đồ đồng bộ 7 nhóm thiết bị liên kết với phân hệ Giải Pháp & Sản Phẩm (TV2)
            $diagramCategories = [
                ['slug' => 'tam-pin', 'label' => 'Tấm Pin Solar', 'sub' => 'Thu quang năng', 'icon' => '☀️', 'required' => true, 'keys' => ['tấm pin', 'tam pin', 'solar panel']],
                ['slug' => 'inverter', 'label' => 'Biến Tần Inverter', 'sub' => 'Đổi điện DC sang AC', 'icon' => '⚡', 'required' => true, 'keys' => ['inverter', 'biến tần']],
                ['slug' => 'tu-dien', 'label' => 'Tủ Điện AC/DC', 'sub' => 'Đóng cắt & chống sét', 'icon' => '🛡️', 'required' => true, 'keys' => ['tủ điện', 'đóng cắt', 'box']],
                ['slug' => 'khung-gia', 'label' => 'Khung Giá Ray', 'sub' => 'Ray nhôm AL6005 & kẹp', 'icon' => '🔩', 'required' => true, 'keys' => ['khung giá', 'khung nhôm', 'ray', 'kẹp']],
                ['slug' => 'day-cap', 'label' => 'Cáp Điện & MC4', 'sub' => 'Cáp DC Solar 4.0mm²', 'icon' => '🔌', 'required' => true, 'keys' => ['cáp', 'mc4', 'dây']],
                ['slug' => 'dong-ho', 'label' => 'Công Tơ 2 Chiều', 'sub' => 'Smart Meter đo đếm', 'icon' => '📟', 'required' => true, 'keys' => ['đồng hồ', 'công tơ', 'meter']],
                ['slug' => 'pin-luu-tru', 'label' => 'Pin Lưu Trữ ESS', 'sub' => 'Lưu trữ điện ban đêm', 'icon' => '🔋', 'required' => false, 'keys' => ['pin lưu trữ', 'lithium', 'battery', 'bess']],
            ];
            $diagramState = array_fill(0, count($diagramCategories), null);
            foreach ($planItems as $pItem) {
                $categoryName = mb_strtolower((string) ($pItem['category_name'] ?? ''));
                $itemName = mb_strtolower((string) ($pItem['name'] ?? ''));
                $itemCode = strtoupper((string) ($pItem['product_code'] ?? ''));
                foreach ($diagramCategories as $dIndex => $dCat) {
                    if ($diagramState[$dIndex] !== null) continue;
                    foreach ($dCat['keys'] as $dKey) {
                        if (str_contains($categoryName, $dKey) || str_contains($itemName, $dKey) || str_contains($itemCode, strtoupper($dCat['slug']))) {
                            $diagramState[$dIndex] = $pItem;
                            break;
                        }
                    }
                }
            }
            $syncedGroupsCount = count(array_filter($diagramState));
            $syncedRequiredCount = 0;
            foreach ($diagramCategories as $idx => $cat) {
                if ($cat['required'] && $diagramState[$idx] !== null) {
                    $syncedRequiredCount++;
                }
            }
            $progressPercent = min(100, (int) round(($syncedRequiredCount / 6) * 100));
            ?>

            <!-- SƠ ĐỒ ĐỒNG BỘ 7 NHÓM THIẾT BỊ HỆ THỐNG (REMAKE MODERN TECH DASHBOARD) -->
            <section class="sol-glass-card sol-sys-matrix">
                <div class="sol-sys-header">
                    <div class="sol-sys-title-group">
                        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <span>🔗</span> Đồng Bộ Module: TV2 Giải Pháp ⇄ TV3 Thiết Kế
                        </span>
                        <h3 class="sol-sys-title">
                            Sơ Đồ Phối Ghép 7 Nhóm Thiết Bị Hệ Thống
                        </h3>
                        <p class="sol-sys-desc">
                            Hệ thống yêu cầu phải có đủ <strong>6 nhóm thiết bị kỹ thuật bắt buộc</strong> (Tấm pin, Inverter, Tủ điện, Khung giá, Cáp điện, Công tơ) để bắt đầu tính toán mô phỏng mặt bằng 2D.
                        </p>
                    </div>
                    <div class="sol-sys-progress-meta">
                        <div style="text-align: right;">
                            <span class="sol-badge sol-badge--cyan" style="font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                                <?= $syncedGroupsCount ?> / 7 Nhóm Đã Chọn
                            </span>
                            <div style="margin-top: 0.4rem;">
                                <?php if (!empty($missingRequired)): ?>
                                    <span class="sol-badge sol-badge--rose" style="font-size: 0.76rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                                        ⚠️ Thiếu <?= count($missingRequired) ?> nhóm bắt buộc
                                    </span>
                                <?php else: ?>
                                    <span class="sol-badge sol-badge--emerald" style="font-size: 0.76rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                                        ✅ Đã đủ 6 nhóm kỹ thuật chuẩn
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thanh chỉ báo tiến độ đồng bộ -->
                <div class="sol-sys-track">
                    <div class="sol-sys-bar" style="width: <?= $progressPercent ?>%; background: <?= $progressPercent === 100 ? 'linear-gradient(90deg, #10b981, #059669)' : 'linear-gradient(90deg, #f59e0b, #ef4444)' ?>; box-shadow: 0 0 10px <?= $progressPercent === 100 ? 'rgba(16,185,129,0.5)' : 'rgba(245,158,11,0.5)' ?>;"></div>
                </div>

                <!-- Lưới 7 thẻ nhóm thiết bị chuyên nghiệp -->
                <div class="sol-sys-cards">
                    <?php foreach ($diagramCategories as $dIndex => $dCat):
                        $dSelected = $diagramState[$dIndex];
                        $dImage = $dSelected ? \App\Hooks\UseProductSpecifications::imageUrl($dSelected) : null;
                        $isRequired = $dCat['required'];
                        $cardClass = $dSelected 
                            ? 'is-selected' 
                            : ($isRequired ? 'is-missing-required' : 'is-missing-optional');
                    ?>
                        <div class="sol-card-node <?= $cardClass ?>">
                            <?php if ($dSelected): ?>
                                <span class="sol-node-tag" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.45);">
                                    ✓ Đã Chọn
                                </span>
                            <?php elseif ($isRequired): ?>
                                <span class="sol-node-tag" style="background: rgba(244, 63, 94, 0.18); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.4);">
                                    Bắt Buộc
                                </span>
                            <?php else: ?>
                                <span class="sol-node-tag" style="background: rgba(148, 163, 184, 0.12); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.25);">
                                    Tùy Chọn
                                </span>
                            <?php endif; ?>

                            <div class="sol-node-icon<?= $dImage ? ' has-product-image' : '' ?>" style="<?= $dImage ? 'background: #f1f5f9; border: 1.5px solid rgba(16, 185, 129, 0.65); box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);' : ($dSelected ? 'background: rgba(16, 185, 129, 0.15); border: 1.5px solid rgba(16, 185, 129, 0.5); box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);' : ($isRequired ? 'background: rgba(244, 63, 94, 0.1); border: 1.5px dashed rgba(244, 63, 94, 0.4);' : 'background: rgba(255, 255, 255, 0.04); border: 1.5px dashed rgba(255, 255, 255, 0.15);')) ?>">
                                <?php if ($dImage): ?>
                                    <img src="<?= htmlspecialchars($dImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $dSelected['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                                <?php else: ?><?= $dCat['icon'] ?><?php endif; ?>
                            </div>

                            <div class="sol-node-label"><?= $dCat['label'] ?></div>
                            <div class="sol-node-sub"><?= $dCat['sub'] ?></div>

                            <?php if ($dSelected): ?>
                                <div class="sol-node-product-name" title="<?= htmlspecialchars($dSelected['name']) ?>">
                                    <?= htmlspecialchars($dSelected['name']) ?>
                                </div>
                                <div class="sol-node-qty">
                                    Số lượng: <strong>x<?= (int)($dSelected['quantity'] ?? 1) ?></strong>
                                </div>
                                <div class="sol-node-action">
                                    <a href="index.php?page=cart" class="sol-node-btn" style="background: rgba(16, 185, 129, 0.15); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35);">
                                        <span>✏️</span> Xem Trong Giỏ
                                    </a>
                                </div>
                            <?php else: ?>
                                <div style="font-size: 0.72rem; color: var(--text-muted); margin: 0.4rem 0 0.6rem;">
                                    <?= $isRequired ? 'Chưa có trong kế hoạch' : 'Chưa chọn lưu trữ' ?>
                                </div>
                                <div class="sol-node-action">
                                    <a href="index.php?page=products&category=<?= $dCat['slug'] ?>" class="sol-node-btn" style="<?= $isRequired ? 'background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.5);' : 'background: rgba(255, 255, 255, 0.06); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.18);' ?>">
                                        <span>+</span> <?= $isRequired ? 'Chọn Ngay' : 'Thêm Pin' ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Thanh thông báo thiếu thiết bị kèm nút bổ sung nhanh 1-Click tích hợp ngay bên dưới -->
                <?php if (!empty($missingRequired)): ?>
                    <div style="margin-top: 1.25rem; background: rgba(244, 63, 94, 0.12); border: 1.5px solid rgba(244, 63, 94, 0.45); border-radius: var(--radius-md, 12px); padding: 1.15rem 1.35rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 4px 15px rgba(244, 63, 94, 0.15);">
                        <div style="display: flex; align-items: flex-start; gap: 0.85rem; max-width: 680px;">
                            <span style="font-size: 1.6rem; line-height: 1;">⚠️</span>
                            <div>
                                <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.25rem;">
                                    Kế hoạch dự định chưa đủ các thiết bị bắt buộc (Đang thiếu <?= count($missingRequired) ?> nhóm kỹ thuật):
                                </strong>
                                <div style="font-size: 0.825rem; color: #fecdd3; line-height: 1.5;">
                                    Đang thiếu: <strong style="color: #ffffff;"><?= implode(', ', $missingRequired) ?></strong>. Bạn có thể chọn từng nhóm ở trên hoặc nhấn nút bên phải để tự động bổ sung nhanh.
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="index.php?page=calculator&source=plan" style="margin: 0;">
                            <input type="hidden" name="action" value="fill_full_system">
                            <button type="submit" class="sol-btn sol-btn--solar" style="font-size: 0.9rem; font-weight: 800; padding: 0.75rem 1.4rem; box-shadow: 0 4px 18px rgba(245, 158, 11, 0.45); display: inline-flex; align-items: center; gap: 0.5rem;">
                                <span>⚡</span> Bổ Sung Đầy Đủ Trọn Bộ 6 Nhóm Chuẩn (1-Click)
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </section>

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
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1rem;">
                        <?php foreach ($planItems as $item): ?>
                            <div style="padding: 1rem 1.15rem; background: rgba(8, 12, 20, 0.65); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem; min-width: 0; flex: 1;">
                                    <?php include dirname(__DIR__) . '/components/product-device-thumbnail.blade.php'; ?>
                                    <div style="min-width: 0; overflow-wrap: anywhere;">
                                        <strong style="color: #ffffff; font-size: 0.9rem; display: block; line-height: 1.3;">
                                            <?= htmlspecialchars($item['name'] ?? 'Thiết bị') ?>
                                        </strong>
                                        <div style="display: flex; gap: 0.4rem; margin-top: 0.2rem; flex-wrap: wrap;">
                                            <?php if (!empty($item['product_code'])): ?>
                                                <span class="sol-badge sol-badge--amber" style="padding: 0.05rem 0.35rem; font-size: 0.7rem;"><?= htmlspecialchars($item['product_code']) ?></span>
                                            <?php endif; ?>
                                            <span style="color: var(--text-secondary); font-size: 0.74rem;">
                                                <?= htmlspecialchars($item['category_name'] ?? 'Phụ kiện') ?>
                                                <?= !empty($item['capacity_watts']) ? ' • ' . (int)$item['capacity_watts'] . 'W' : '' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span style="display: inline-block; padding: 0.2rem 0.55rem; background: rgba(245, 158, 11, 0.15); border: 1px solid var(--accent-amber); border-radius: 4px; font-weight: 800; font-size: 0.8rem; color: var(--accent-gold);">
                                        x<?= (int)($item['quantity'] ?? 1) ?>
                                    </span>
                                    <?php if (!empty($item['price'])): ?>
                                        <div style="font-size: 0.72rem; color: #10b981; margin-top: 0.2rem; font-weight: 600;">
                                            <?= number_format(((float)$item['price']) * max(1, (int)($item['quantity'] ?? 1)), 0, ',', '.') ?> đ
                                        </div>
                                    <?php endif; ?>
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
                <input type="hidden" name="action" value="update_simulation">
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
                            <span>⚡</span> Cập Nhật Diện Tích Mái &amp; Mô Phỏng
                        </button>
                    </div>
                </div>
            </form>

        <?php else: ?>
            <!-- GIAO DIỆN CHẾ ĐỘ 2: THEO HÓA ĐƠN TIỀN ĐIỆN -->
            <form method="POST" action="index.php?page=calculator&source=bill#step-2-simulation" style="background: rgba(14, 22, 38, 0.75); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1.5rem;">
                <input type="hidden" name="calc_source" value="bill">
                <input type="hidden" name="action" value="update_simulation">
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
                            ⚡ Cập Nhật Tính Toán &amp; Mô Phỏng
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <?php if (!empty($showStepsFrom2)): ?>
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
            <?php
            $rows = $calc['layout_matrix']['rows'] ?? 2;
            $cols = $calc['layout_matrix']['columns'] ?? 5;
            $totalPanels = (int)($calc['panel_count'] ?? 10);

            $panelW = 48;
            $panelH = 80;
            $gapX = 9;
            $gapY = 14;

            $roofArrayW = ($cols * ($panelW + $gapX)) - $gapX;
            $roofArrayH = ($rows * ($panelH + $gapY)) - $gapY;

            // Kích thước các khối trạm thiết bị dưới mái
            $b1W = 125; // Khối 1: Tủ Điện AC/DC
            $b2W = 145; // Khối 2: Biến Tần Inverter
            $b3W = 125; // Khối 3: Pin ESS
            $b4W = 125; // Khối 4: Smart Meter
            $b5W = 70;  // Khối 5: Lưới EVN
            $wireLen = 30; // Khoảng cách dây nối giữa các khối

            // Tổng chiều rộng hàng thiết bị 5 khối
            $stationW = $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen + $b4W + $wireLen + $b5W;

            // Chiều rộng tổng thể SVG: đảm bảo cả giàn pin và trạm thiết bị có khoảng thở 2 bên, không bị cắt mép phải
            $svgW = max(960, $roofArrayW + 280, $stationW + 180);
            $roofBoxH = $roofArrayH + 95;
            $bosY = $roofBoxH + 58; // Y đỉnh các khối thiết bị bên dưới
            $svgH = $bosY + 145;

            // Căn giữa giàn pin trên mái
            $startX = ($svgW - $roofArrayW) / 2;
            $startY = 58;

            // Căn giữa trạm thiết bị 5 khối (độc lập với kích thước giàn pin)
            $eqStartX = ($svgW - $stationW) / 2;
            $yCorridor = $roofBoxH + 34; // Máng dẫn cáp DC giữa khu vực mái và trạm thiết bị
            ?>

            <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width: 100%; min-width: 880px; max-width: 1020px; height: auto; margin: 0 auto; display: block;">
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

                    <!-- Mũi tên định hướng luồng điện tử -->
                    <marker id="dcArrow" markerWidth="6" markerHeight="6" refX="5" refY="3" orient="auto">
                        <path d="M 0 0 L 6 3 L 0 6 Z" fill="#f59e0b" />
                    </marker>
                    <marker id="acArrow" markerWidth="6" markerHeight="6" refX="5" refY="3" orient="auto">
                        <path d="M 0 0 L 6 3 L 0 6 Z" fill="#38bdf8" />
                    </marker>
                    <marker id="greenArrow" markerWidth="6" markerHeight="6" refX="5" refY="3" orient="auto">
                        <path d="M 0 0 L 6 3 L 0 6 Z" fill="#10b981" />
                    </marker>
                </defs>

                <!-- KHU VỰC 1: MẶT BẰNG MÁI NHÀ KHẢ DỤNG -->
                <rect x="20" y="16" width="<?= $svgW - 40 ?>" height="<?= $roofBoxH ?>" rx="8" fill="rgba(15, 23, 42, 0.45)" stroke="#334155" stroke-dasharray="6,4" stroke-width="1.5"/>
                <text x="35" y="38" fill="#94a3b8" font-size="11" font-weight="bold" font-family="sans-serif">
                    🏠 MẶT BẰNG MÁI LẮP ĐẶT: <?= mb_strtoupper($roofTypeName) ?> (<?= $calc['required_roof_area'] ?> m² KHẢ DỤNG)
                </text>

                <!-- Thẻ thông số bức xạ hiệu dụng (tích hợp trực tiếp bên trong SVG, không bị che khuất) -->
                <g transform="translate(<?= $svgW - 275 ?>, 24)">
                    <rect width="195" height="52" rx="6" fill="#0b1329" stroke="#334155" stroke-width="1"/>
                    <text x="12" y="17" fill="#fbbf24" font-size="8.5" font-weight="bold">☀️ BỨC XẠ HIỆU DỤNG VIỆT NAM</text>
                    <text x="12" y="31" fill="#cbd5e1" font-size="8">Quang năng: 4.5 kWh/m²/ngày</text>
                    <text x="12" y="43" fill="#94a3b8" font-size="7.5">Tiêu chuẩn: PCCC &amp; EVN Tier-1</text>
                </g>

                <!-- La bàn Hướng Bắc - Nam đón nắng (đặt góc phải thoáng đãng, không bị đè) -->
                <g transform="translate(<?= $svgW - 52 ?>, 50)">
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

                <!-- Điểm ra của chuỗi pin quang điện (DC Terminals) -->
                <circle cx="<?= $startX + $roofArrayW ?>" cy="<?= $startY + ($roofArrayH / 2) ?>" r="4" fill="#f59e0b" stroke="#ffffff" stroke-width="1.2"/>
                <text x="<?= $startX + $roofArrayW + 8 ?>" y="<?= $startY + ($roofArrayH / 2) - 6 ?>" fill="#f59e0b" font-size="8" font-family="monospace" font-weight="bold">DC (+/-)</text>

                <!-- TUYẾN DÂY CÁP DC CHUYÊN DỤNG 4.0mm² ĐI TỪ GIÀN PIN VÀO TRỰC TIẾP KHỐI 1 (TỦ ĐIỆN AC/DC) -->
                <!-- Đi từ giàn pin -> sang phải -> xuống máng cáp kỹ thuật -> rẽ trái vào đầu vào Khối 1 -->
                <path d="M <?= $startX + $roofArrayW ?> <?= $startY + ($roofArrayH / 2) ?> 
                         L <?= $svgW - 65 ?> <?= $startY + ($roofArrayH / 2) ?> 
                         L <?= $svgW - 65 ?> <?= $yCorridor ?> 
                         L <?= $eqStartX - 28 ?> <?= $yCorridor ?> 
                         L <?= $eqStartX - 28 ?> <?= $bosY + 42 ?> 
                         L <?= $eqStartX ?> <?= $bosY + 42 ?>" 
                      fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-dasharray="6,3" marker-end="url(#dcArrow)"/>
                
                <text x="<?= $svgW - 55 ?>" y="<?= $startY + ($roofArrayH / 2) + 26 ?>" fill="#f59e0b" font-size="8.5" font-family="monospace" font-weight="bold">Cáp DC 4.0mm²</text>
                <text x="<?= $svgW / 2 ?>" y="<?= $yCorridor - 5 ?>" fill="#f59e0b" font-size="7.5" font-family="monospace" text-anchor="middle">═════ Máng Cáp Kỹ Thuật DC Solar 1000V (Chống Cháy &amp; Tia Cực Tím UV) ═════►</text>

                <?php
                $dispPanelName = !empty($calc['primary_panel']['name']) ? $calc['primary_panel']['name'] : 'Tấm Pin Tier-1 Mono 550W';
                $dispInverterName = !empty($calc['primary_inverter']['name']) ? $calc['primary_inverter']['name'] : ('Inverter ' . $calc['estimated_kwp'] . 'kW');
                $dispBoxName = !empty($calc['primary_box']['name']) ? $calc['primary_box']['name'] : 'Tủ Điện AC/DC & Chống Sét';
                $dispBatteryName = !empty($calc['primary_battery']['name']) ? $calc['primary_battery']['name'] : null;
                $hasBatteryActive = ($dispBatteryName !== null && ($calc['primary_battery']['quantity'] ?? 0) > 0);
                $dispMeterName = !empty($calc['primary_meter']['name']) ? $calc['primary_meter']['name'] : 'Smart Meter 2 Chiều';
                ?>

                <!-- KHỐI 1: TỦ ĐIỆN AN TOÀN AC/DC & SPD CHỐNG SÉT LAN TRUYỀN -->
                <g transform="translate(<?= $eqStartX ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="<?= $b1W ?>" height="85" rx="6" fill="#111827" stroke="<?= !empty($calc['primary_box']) ? '#10b981' : '#38bdf8' ?>" stroke-width="1.8"/>
                    
                    <!-- Header tiêu đề tủ điện -->
                    <rect x="5" y="5" width="<?= $b1W - 10 ?>" height="20" rx="3" fill="#1e293b"/>
                    <circle cx="15" cy="15" r="4" fill="<?= !empty($calc['primary_box']) ? '#10b981' : '#38bdf8' ?>"/>
                    <text x="25" y="18" fill="#ffffff" font-size="8" font-weight="bold"><?= htmlspecialchars(mb_strimwidth($dispBoxName, 0, 18, '..')) ?></text>
                    
                    <!-- Cổng vào DC Input từ giàn pin -->
                    <circle cx="0" cy="42" r="3.5" fill="#f59e0b"/>
                    <text x="4" y="38" fill="#f59e0b" font-size="6.5" font-family="monospace">DC IN</text>

                    <!-- Thiết bị chống sét lan truyền SPD DC -->
                    <rect x="12" y="32" width="28" height="40" rx="2" fill="#1f2937" stroke="#38bdf8" stroke-width="1"/>
                    <text x="26" y="52" fill="#38bdf8" font-size="7.5" text-anchor="middle" font-weight="bold">SPD</text>
                    <text x="26" y="63" fill="#94a3b8" font-size="6" text-anchor="middle">1000V</text>

                    <!-- Aptomat ngắt mạch bảo vệ MCB -->
                    <rect x="46" y="32" width="30" height="40" rx="2" fill="#1f2937" stroke="#f59e0b" stroke-width="1"/>
                    <text x="61" y="52" fill="#f59e0b" font-size="7.5" text-anchor="middle" font-weight="bold">MCB</text>
                    <text x="61" y="63" fill="#94a3b8" font-size="6" text-anchor="middle">DC/AC</text>

                    <!-- Cụm đèn chỉ thị trạng thái an toàn -->
                    <rect x="82" y="32" width="32" height="40" rx="2" fill="#1f2937" stroke="#475569" stroke-width="1"/>
                    <circle cx="98" cy="46" r="4" fill="#10b981"/>
                    <text x="98" y="62" fill="#10b981" font-size="6" text-anchor="middle" font-weight="bold">SAFE</text>

                    <!-- Cổng xuất sang Inverter -->
                    <circle cx="<?= $b1W ?>" cy="42" r="3" fill="#38bdf8"/>

                    <text x="<?= $b1W / 2 ?>" y="100" fill="#cbd5e1" font-size="8.5" text-anchor="middle" font-weight="bold">1. Tủ Điện AC/DC</text>
                    <text x="<?= $b1W / 2 ?>" y="112" fill="<?= !empty($calc['primary_box']) ? '#10b981' : '#64748b' ?>" font-size="7" text-anchor="middle"><?= !empty($calc['primary_box']) ? '● Đã chọn trong kế hoạch' : 'Tiêu chuẩn IEC 60364' ?></text>
                </g>

                <!-- Dây dẫn nối giữa Tủ điện và Biến tần (DC Bus) -->
                <line x1="<?= $eqStartX + $b1W ?>" y1="<?= $bosY + 42 ?>" x2="<?= $eqStartX + $b1W + $wireLen ?>" y2="<?= $bosY + 42 ?>" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#acArrow)"/>
                <text x="<?= $eqStartX + $b1W + ($wireLen / 2) ?>" y="<?= $bosY + 36 ?>" fill="#38bdf8" font-size="7" font-family="monospace" text-anchor="middle">DC Bus</text>

                <!-- KHỐI 2: BIẾN TẦN INVERTER QUANG ĐIỆN -->
                <g transform="translate(<?= $eqStartX + $b1W + $wireLen ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="<?= $b2W ?>" height="85" rx="6" fill="url(#inverterGrad)" stroke="#f59e0b" stroke-width="2"/>
                    
                    <!-- Màn hình LED thông số kỹ thuật Inverter -->
                    <rect x="8" y="10" width="<?= $b2W - 16 ?>" height="36" rx="3" fill="#080c14" stroke="#334155" stroke-width="1"/>
                    <text x="16" y="23" fill="#10b981" font-size="8" font-family="monospace" font-weight="bold">● <?= $calc['estimated_kwp'] ?> kW · 50Hz</text>
                    <text x="16" y="34" fill="#fbbf24" font-size="7.5" font-weight="bold"><?= htmlspecialchars(mb_strimwidth($dispInverterName, 0, 20, '..')) ?></text>
                    <text x="16" y="43" fill="#94a3b8" font-size="6.5">Sóng Sin Chuẩn EVN · IP65</text>
                    
                    <!-- Khe tản nhiệt công nghiệp của Inverter -->
                    <line x1="16" y1="56" x2="<?= $b2W - 16 ?>" y2="56" stroke="#334155" stroke-width="1.5"/>
                    <line x1="16" y1="62" x2="<?= $b2W - 16 ?>" y2="62" stroke="#334155" stroke-width="1.5"/>
                    <line x1="16" y1="68" x2="<?= $b2W - 16 ?>" y2="68" stroke="#334155" stroke-width="1.5"/>

                    <text x="<?= $b2W / 2 ?>" y="100" fill="#fbbf24" font-size="8.5" text-anchor="middle" font-weight="bold">2. Biến Tần Inverter</text>
                    <text x="<?= $b2W / 2 ?>" y="112" fill="<?= !empty($calc['primary_inverter']) ? '#10b981' : '#f59e0b' ?>" font-size="7" text-anchor="middle"><?= !empty($calc['primary_inverter']) ? '● Model từ kế hoạch' : 'Ước tính hòa lưới' ?></text>
                </g>

                <!-- Dây dẫn nối từ Inverter sang Pin lưu trữ hoặc Smart Meter -->
                <line x1="<?= $eqStartX + $b1W + $wireLen + $b2W ?>" y1="<?= $bosY + 42 ?>" x2="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen ?>" y2="<?= $bosY + 42 ?>" 
                      stroke="<?= $hasBatteryActive ? '#10b981' : '#475569' ?>" 
                      stroke-width="<?= $hasBatteryActive ? '2.5' : '1.5' ?>" 
                      stroke-dasharray="<?= $hasBatteryActive ? 'none' : '4,3' ?>"
                      <?= $hasBatteryActive ? 'marker-end="url(#greenArrow)"' : '' ?>/>
                <text x="<?= $eqStartX + $b1W + $wireLen + $b2W + ($wireLen / 2) ?>" y="<?= $bosY + 36 ?>" fill="<?= $hasBatteryActive ? '#10b981' : '#64748b' ?>" font-size="6.5" font-family="monospace" text-anchor="middle"><?= $hasBatteryActive ? 'DC ESS' : 'Hybrid' ?></text>

                <!-- KHỐI 3: PIN LƯU TRỮ LITHIUM ESS (NẾU CÓ HOẶC HỆ HYBRID) -->
                <g transform="translate(<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen ?>, <?= $bosY ?>)">
                    <?php if ($hasBatteryActive): ?>
                        <rect x="0" y="0" width="<?= $b3W ?>" height="85" rx="6" fill="url(#batteryGrad)" stroke="#10b981" stroke-width="2"/>
                        <rect x="8" y="8" width="<?= $b3W - 16 ?>" height="15" rx="2" fill="#064e3b"/>
                        <text x="<?= $b3W / 2 ?>" y="19" fill="#34d399" font-size="7.5" text-anchor="middle" font-weight="bold">LITHIUM LIFEPO4</text>
                        <rect x="18" y="28" width="<?= $b3W - 36 ?>" height="10" rx="2" fill="#042f2e" stroke="#10b981" stroke-width="0.8"/>
                        <rect x="20" y="30" width="<?= $b3W - 55 ?>" height="6" rx="1" fill="#10b981"/>
                        <text x="<?= $b3W / 2 ?>" y="52" fill="#a7f3d0" font-size="7.5" text-anchor="middle" font-weight="bold"><?= htmlspecialchars(mb_strimwidth($dispBatteryName, 0, 16, '..')) ?></text>
                        <text x="<?= $b3W / 2 ?>" y="65" fill="#34d399" font-size="7" text-anchor="middle">SOC: 98% · Đã chọn</text>
                        <text x="<?= $b3W / 2 ?>" y="100" fill="#10b981" font-size="8.5" text-anchor="middle" font-weight="bold">3. Pin Lưu Trữ ESS</text>
                        <text x="<?= $b3W / 2 ?>" y="112" fill="#10b981" font-size="7" text-anchor="middle">● Đã đồng bộ từ kế hoạch</text>
                    <?php else: ?>
                        <rect x="0" y="0" width="<?= $b3W ?>" height="85" rx="6" fill="#081412" stroke="#475569" stroke-dasharray="4,3" stroke-width="1.5"/>
                        <rect x="8" y="10" width="<?= $b3W - 16 ?>" height="15" rx="2" fill="#0f172a"/>
                        <text x="<?= $b3W / 2 ?>" y="21" fill="#64748b" font-size="7.5" text-anchor="middle">DỰ PHÒNG ESS</text>
                        <text x="<?= $b3W / 2 ?>" y="48" fill="#64748b" font-size="8" text-anchor="middle">Chưa chọn lưu trữ</text>
                        <text x="<?= $b3W / 2 ?>" y="62" fill="#475569" font-size="7" text-anchor="middle">(Tùy chọn bổ sung)</text>
                        <text x="<?= $b3W / 2 ?>" y="100" fill="#64748b" font-size="8.5" text-anchor="middle" font-weight="bold">3. Pin Lưu Trữ ESS</text>
                        <text x="<?= $b3W / 2 ?>" y="112" fill="#64748b" font-size="7" text-anchor="middle">Hòa lưới On-Grid</text>
                    <?php endif; ?>
                </g>

                <!-- Dây dẫn nối sang Smart Meter (AC 220V) -->
                <line x1="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W ?>" y1="<?= $bosY + 42 ?>" x2="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen ?>" y2="<?= $bosY + 42 ?>" stroke="#38bdf8" stroke-width="2.5" marker-end="url(#acArrow)"/>
                <text x="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + ($wireLen / 2) ?>" y="<?= $bosY + 36 ?>" fill="#38bdf8" font-size="7" font-family="monospace" text-anchor="middle">AC 220V</text>

                <!-- KHỐI 4: ĐỒNG HỒ SMART METER & ĐO ĐẾM 2 CHIỀU -->
                <g transform="translate(<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen ?>, <?= $bosY ?>)">
                    <rect x="0" y="0" width="<?= $b4W ?>" height="85" rx="6" fill="#111827" stroke="<?= !empty($calc['primary_meter']) ? '#10b981' : '#38bdf8' ?>" stroke-width="1.8"/>
                    <circle cx="<?= $b4W / 2 ?>" cy="34" r="21" fill="#080c14" stroke="<?= !empty($calc['primary_meter']) ? '#10b981' : '#38bdf8' ?>" stroke-width="1.2"/>
                    <text x="<?= $b4W / 2 ?>" y="31" fill="#38bdf8" font-size="7.5" font-family="monospace" text-anchor="middle">kWh EVN</text>
                    <text x="<?= $b4W / 2 ?>" y="43" fill="#fbbf24" font-size="8" font-family="monospace" font-weight="bold" text-anchor="middle">2 CHIỀU</text>
                    <text x="<?= $b4W / 2 ?>" y="69" fill="#94a3b8" font-size="7" text-anchor="middle"><?= htmlspecialchars(mb_strimwidth($dispMeterName, 0, 16, '..')) ?></text>
                    <text x="<?= $b4W / 2 ?>" y="100" fill="#38bdf8" font-size="8.5" text-anchor="middle" font-weight="bold">4. Smart Meter</text>
                    <text x="<?= $b4W / 2 ?>" y="112" fill="<?= !empty($calc['primary_meter']) ? '#10b981' : '#38bdf8' ?>" font-size="7" text-anchor="middle"><?= !empty($calc['primary_meter']) ? '● Đã chọn trong kế hoạch' : 'Đo đếm 2 chiều EVN' ?></text>
                </g>

                <!-- Dây AC nối vào Lưới điện EVN và Phụ tải gia đình -->
                <line x1="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen + $b4W ?>" y1="<?= $bosY + 42 ?>" x2="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen + $b4W + $wireLen ?>" y2="<?= $bosY + 42 ?>" stroke="#10b981" stroke-width="2.5" marker-end="url(#greenArrow)"/>
                <text x="<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen + $b4W + ($wireLen / 2) ?>" y="<?= $bosY + 36 ?>" fill="#10b981" font-size="6.5" font-family="monospace" text-anchor="middle">Grid</text>

                <!-- KHỐI 5: LƯỚI ĐIỆN QUỐC GIA EVN & PHỤ TẢI GIA ĐÌNH -->
                <g transform="translate(<?= $eqStartX + $b1W + $wireLen + $b2W + $wireLen + $b3W + $wireLen + $b4W + $wireLen ?>, <?= $bosY + 5 ?>)">
                    <rect x="0" y="0" width="<?= $b5W ?>" height="75" rx="5" fill="#1f2937" stroke="#10b981" stroke-width="1.5"/>
                    <text x="<?= $b5W / 2 ?>" y="26" font-size="18" text-anchor="middle">⚡</text>
                    <text x="<?= $b5W / 2 ?>" y="44" fill="#cbd5e1" font-size="7.5" font-weight="bold" text-anchor="middle">LƯỚI EVN</text>
                    <text x="<?= $b5W / 2 ?>" y="56" fill="#10b981" font-size="7" font-weight="bold" text-anchor="middle">&amp; TẢI NHÀ</text>
                    <text x="<?= $b5W / 2 ?>" y="95" fill="#10b981" font-size="8.5" text-anchor="middle" font-weight="bold">5. Lưới EVN</text>
                    <text x="<?= $b5W / 2 ?>" y="107" fill="#64748b" font-size="7" text-anchor="middle">Hòa đồng bộ 220V</text>
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
                    <span>Ray Nhôm AL6005 &amp; Kẹp Pin Inox</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 22px; height: 2px; border-top: 2.5px dashed #f59e0b;"></span>
                    <span>Cáp DC 4.0mm² (1000V)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #111827; border: 1.5px solid #38bdf8; border-radius: 2px;"></span>
                    <span>1. Tủ Điện AC/DC &amp; SPD Chống Sét</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 14px; height: 12px; background: #1e293b; border: 1.5px solid #f59e0b; border-radius: 2px;"></span>
                    <span>2. Biến Tần Inverter IP65</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #064e3b; border: 1.5px solid #10b981; border-radius: 2px;"></span>
                    <span>3. Pin Lưu Trữ Lithium (ESS)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #111827; border: 1.5px solid #38bdf8; border-radius: 50%;"></span>
                    <span>4. Đồng Hồ Smart Meter 2 Chiều</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 12px; height: 12px; background: #1f2937; border: 1.5px solid #10b981; border-radius: 2px;"></span>
                    <span>5. Lưới Điện Quốc Gia EVN</span>
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
                            <th style="padding: 0.75rem 0.85rem;">Nguồn Vật Tư</th>
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
                                    <td style="padding: 0.75rem 0.85rem; font-weight: 600; color: #ffffff;">
                                        <?= htmlspecialchars($b['item']) ?>
                                        <?php if (!empty($b['total_price'])): ?>
                                            <div style="font-size: 0.72rem; color: #10b981; margin-top: 2px;">
                                                <?= number_format((float)$b['total_price'], 0, ',', '.') ?> đ
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem 0.85rem;">
                                        <?php if (!empty($b['is_from_cart'])): ?>
                                            <span class="sol-badge sol-badge--emerald" style="font-size: 0.7rem; padding: 2px 7px;">● Từ Kế Hoạch</span>
                                        <?php else: ?>
                                            <span class="sol-badge sol-badge--amber" style="font-size: 0.7rem; padding: 2px 7px;">○ Bổ Sung Chuẩn</span>
                                        <?php endif; ?>
                                    </td>
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
            <h2 style="color: #ffffff; font-size: 1.85rem; font-family: var(--font-heading); margin-bottom: 0;">
                Xác Nhận Cấu Hình Phương Án
            </h2>
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

        <?php if (($currentUser['role'] ?? '') !== 'customer'): ?>
            <p class="sol-notice">Đăng nhập bằng tài khoản khách hàng để lưu dự toán hoặc đăng ký khảo sát. <a href="index.php?page=auth">Đăng nhập</a></p>
        <?php endif; ?>
        <!-- Form xác nhận & gửi yêu cầu khảo sát thi công -->
        <form method="POST" action="index.php?page=calculator<?= $isPlanSource ? '&source=plan' : '' ?>" style="max-width: 820px; margin: 0 auto;">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['solar_csrf'], ENT_QUOTES) ?>">
            <input type="hidden" name="request_key" value="<?= htmlspecialchars($_POST['request_key'] ?? bin2hex(random_bytes(32)), ENT_QUOTES) ?>">
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
                    <span>🚀</span> <?= $isPlanSource ? "Gửi Phương Án Cho Quản Lý Duyệt" : "Đăng Ký Khảo Sát" ?>
                </button>

                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; justify-content: center;">
                    <button 
                        type="submit" 
                        name="action" 
                        value="save_quote" 
                        class="sol-btn sol-btn--outline sol-btn--sm" 
                        style="color: var(--text-secondary); font-size: 0.825rem;"
                    >
                        💾 Lưu Dự Toán Nháp
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
    <?php endif; ?>

    <!-- ===================================================================== -->
    <!-- DANH SÁCH LỊCH SỬ BÁO GIÁ ĐÃ LƯU (CSDL MySQL solar_quotes)             -->
    <!-- ===================================================================== -->
    <section id="quote-history" class="sol-glass-card" style="padding: 2.25rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.4rem;">PHƯƠNG ÁN & YÊU CẦU</span>
                <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0;">
                    <?= ($currentUser['role'] ?? '') === 'admin' ? 'Phương Án Cần Xem Xét' : 'Dự Toán & Yêu Cầu Của Tôi' ?>
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
                <div style="font-size: 1rem; color: #ffffff; margin-bottom: 0.35rem;">Chưa có phương án trong danh sách</div>
                <p style="font-size: 0.85rem; max-width: 520px; margin: 0 auto;">
                    Đăng nhập bằng tài khoản khách hàng để lưu dự toán và theo dõi yêu cầu của bạn.
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
                            <th style="padding: 0.75rem 0.85rem;">Xử Lý</th>
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
                                    <span class="sol-badge <?= $q['status'] === 'approved' ? 'sol-badge--emerald' : 'sol-badge--amber' ?>">
                                        <?= htmlspecialchars(\App\Modules\Member4_Installation\View::label($q['status'])) ?>
                                    </span>
                                    <?php if ($q['review_note']): ?><p><?= htmlspecialchars($q['review_note']) ?></p><?php endif; ?>
                                    <?php if ($q['project_id']): ?><p>Dự án: <?= htmlspecialchars(\App\Modules\Member4_Installation\View::label($q['project_status'])) ?></p><?php endif; ?>
                                    <details><summary>Thông tin phương án</summary>
                                        <p>Nguồn: <?= ($q['source'] ?? '') === 'plan' ? 'Kế hoạch dự định' : 'Hóa đơn tiền điện' ?></p>
                                        <p>Diện tích: <?= htmlspecialchars((string) $q['roof_area_sqm']) ?> m² · Tiền điện: <?= number_format((float) $q['monthly_electric_bill'], 0, ',', '.') ?> đ/tháng</p>
                                        <p><?= htmlspecialchars($q['customer_email'] ?? '') ?></p>
                                        <p><?= nl2br(htmlspecialchars($q['notes'] ?? '')) ?></p>
                                        <?php foreach ($q['items'] as $item): ?>
                                            <p><?= htmlspecialchars($item['product_name']) ?> · <?= (int) $item['quantity'] ?> × <?= number_format((float) $item['unit_price'], 0, ',', '.') ?> đ</p>
                                        <?php endforeach; ?>
                                        <?php if (($q['source'] ?? '') === 'plan' && empty($q['items'])): ?><p>Phương án cũ chưa lưu chi tiết sản phẩm. Cần gửi lại từ kế hoạch.</p><?php endif; ?>
                                    </details>
                                </td>
                                <td style="padding: 0.8rem 0.85rem; text-align: center; color: var(--text-muted); font-size: 0.75rem;">
                                    <?= !empty($q['created_at']) ? date('d/m/Y H:i', strtotime($q['created_at'])) : 'Vừa xong' ?>
                                </td>
                                <td style="padding: 0.8rem; min-width: 220px;">
                                    <?php if ($q['project_id']): ?>
                                        <a class="sol-btn sol-btn--secondary sol-btn--sm" href="index.php?page=installations&amp;project=<?= (int) $q['project_id'] ?>">Xem dự án</a>
                                    <?php elseif (($currentUser['role'] ?? '') === 'customer' && $q['status'] === 'draft'): ?>
                                        <form method="post" action="index.php?page=calculator">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['solar_csrf'], ENT_QUOTES) ?>">
                                            <input type="hidden" name="quote_id" value="<?= (int) $q['id'] ?>">
                                            <button class="sol-btn sol-btn--solar sol-btn--sm" name="action" value="submit_quote"><?= ($q['source'] ?? '') === 'plan' ? 'Gửi duyệt phương án' : 'Đăng ký khảo sát' ?></button>
                                        </form>
                                    <?php elseif (($currentUser['role'] ?? '') === 'admin' && $q['status'] === 'pending_approval'): ?>
                                        <form method="post" action="index.php?page=calculator">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['solar_csrf'], ENT_QUOTES) ?>">
                                            <input type="hidden" name="quote_id" value="<?= (int) $q['id'] ?>">
                                            <textarea name="review_note" maxlength="5000" placeholder="Nhận xét; bắt buộc khi từ chối" aria-label="Nhận xét phương án"></textarea>
                                            <button class="sol-btn sol-btn--solar sol-btn--sm" name="action" value="approve_quote">Duyệt phương án & tạo yêu cầu dự án</button>
                                            <button class="sol-btn sol-btn--outline sol-btn--sm" name="action" value="reject_quote">Từ chối</button>
                                        </form>
                                    <?php endif; ?>
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
    // Tự động cuộn mượt mà đến phần mô phỏng nếu có hash #step-2-simulation và không có vị trí reload cần giữ
    if (window.location.hash && !sessionStorage.getItem('solaris_scroll_pos')) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
