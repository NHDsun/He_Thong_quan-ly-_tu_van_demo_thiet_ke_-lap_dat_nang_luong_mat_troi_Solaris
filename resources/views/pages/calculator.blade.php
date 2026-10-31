<?php
/**
 * View: resources/views/pages/calculator.blade.php
 * Phân hệ 3: Tư Vấn & Demo Thiết Kế (Solar Calculator & Simulator)
 * Phụ trách: Thành viên 3 (Fullstack / Solar Sizing Engine & Data)
 */
?>

<div class="sol-container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <!-- Tiêu đề phân hệ -->
    <div class="sol-section-header" style="margin-bottom: 2rem;">
        <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.65rem;">
            ⚡ Phân Hệ 3 • Solar Sizing Engine & Simulator
        </span>
        <h1 class="sol-section-header__title" style="font-size: 2.4rem;">
            Tư Vấn Kỹ Thuật & Mô Phỏng Cấu Hình Điện Mặt Trời
        </h1>
        <p class="sol-section-header__desc">
            Tính toán công suất tối ưu, diện tích mái khả dụng, bóc tách vật tư kỹ thuật và lập bảng dự toán hoàn vốn 25 năm dựa trên thuật toán quang năng độc quyền Solaris.
        </p>
    </div>

    <!-- Thông báo Alert nếu có -->
    <?php if (!empty($alert)): ?>
        <div style="margin-bottom: 2rem; padding: 1.15rem 1.4rem; border-radius: var(--radius-md); background: <?= $alert['type'] === 'success' ? 'rgba(16, 185, 129, 0.15)' : ($alert['type'] === 'warning' ? 'rgba(245, 158, 11, 0.15)' : 'rgba(244, 63, 94, 0.15)') ?>; border: 1px solid <?= $alert['type'] === 'success' ? '#10b981' : ($alert['type'] === 'warning' ? '#f59e0b' : '#f43f5e') ?>; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #ffffff; font-size: 0.95rem;">
                <span style="font-size: 1.35rem;"><?= $alert['type'] === 'success' ? '✅' : ($alert['type'] === 'warning' ? '⚠️' : '❌') ?></span>
                <span><?= htmlspecialchars($alert['message']) ?></span>
            </div>
            <a href="index.php?page=calculator" style="color: var(--text-muted); text-decoration: none; font-size: 1.25rem;">&times;</a>
        </div>
    <?php endif; ?>

    <!-- Bộ điều khiển tính toán & Kết quả tổng quan -->
    <div style="display: grid; grid-template-columns: 1fr 1.35fr; gap: 2rem; margin-bottom: 3rem;" class="sol-calc-grid-layout">
        
        <!-- Cột trái: Bộ điều khiển nhập liệu (Calculator Form) -->
        <div class="sol-glass-card" style="padding: 2rem 1.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem;">
                    <span>⚙️</span> Thông Số Đầu Vào
                </h3>
                <span class="sol-badge sol-badge--amber">Thuật toán v2.1</span>
            </div>

            <form method="GET" action="index.php" id="solarCalcForm">
                <input type="hidden" name="page" value="calculator">

                <!-- 1. Chọn Gói Hệ Thống -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.65rem;">
                        1. Phân loại giải pháp hệ thống:
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 0.55rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid <?= $calc['package_key'] === 'residential' ? 'var(--accent-amber)' : 'var(--border-subtle)' ?>; background: <?= $calc['package_key'] === 'residential' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(8, 12, 20, 0.4)' ?>; cursor: pointer; transition: var(--transition);">
                            <input type="radio" name="package" value="residential" <?= $calc['package_key'] === 'residential' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--accent-amber);">
                            <div>
                                <div style="color: #ffffff; font-weight: 600; font-size: 0.875rem;">Hòa Lưới Dân Dụng (Hộ Gia Đình)</div>
                                <div style="color: var(--text-muted); font-size: 0.75rem;">14.000.000 đ/kWp • Giảm tiền điện bậc thang cao</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid <?= $calc['package_key'] === 'commercial' ? 'var(--accent-emerald)' : 'var(--border-subtle)' ?>; background: <?= $calc['package_key'] === 'commercial' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(8, 12, 20, 0.4)' ?>; cursor: pointer; transition: var(--transition);">
                            <input type="radio" name="package" value="commercial" <?= $calc['package_key'] === 'commercial' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--accent-emerald);">
                            <div>
                                <div style="color: #ffffff; font-weight: 600; font-size: 0.875rem;">Doanh Nghiệp & Nhà Xưởng (C&I)</div>
                                <div style="color: var(--text-muted); font-size: 0.75rem;">13.000.000 đ/kWp • Giảm chi phí điện giờ cao điểm</div>
                            </div>
                        </label>

                        <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid <?= $calc['package_key'] === 'hybrid' ? 'var(--accent-cyan)' : 'var(--border-subtle)' ?>; background: <?= $calc['package_key'] === 'hybrid' ? 'rgba(56, 189, 248, 0.1)' : 'rgba(8, 12, 20, 0.4)' ?>; cursor: pointer; transition: var(--transition);">
                            <input type="radio" name="package" value="hybrid" <?= $calc['package_key'] === 'hybrid' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--accent-cyan);">
                            <div>
                                <div style="color: #ffffff; font-weight: 600; font-size: 0.875rem;">Hệ Thống Lưu Trữ ESS Hybrid</div>
                                <div style="color: var(--text-muted); font-size: 0.75rem;">22.000.000 đ/kWp • Kèm pin Lithium 24/7 không lo mất điện</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2. Hóa đơn tiền điện hàng tháng -->
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="font-size: 0.85rem; color: #cbd5e1; font-weight: 600;">
                            2. Tiền điện bình quân / tháng:
                        </label>
                        <span id="billValDisplay" style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--accent-gold);">
                            <?= $calc['monthly_bill_formatted'] ?>
                        </span>
                    </div>

                    <input 
                        type="range" 
                        name="bill" 
                        id="billInputRange"
                        class="sol-slider-track" 
                        min="1000000" 
                        max="30000000" 
                        step="500000" 
                        value="<?= (int)$calc['monthly_bill'] ?>"
                        oninput="updateCalcPreview(this.value)"
                        onchange="this.form.submit()"
                    >
                    <div style="display: flex; justify-content: space-between; font-size: 0.725rem; color: var(--text-muted); margin-top: 0.35rem;">
                        <span>1 Triệu</span>
                        <span>10 Triệu</span>
                        <span>20 Triệu</span>
                        <span>30 Triệu VNĐ</span>
                    </div>
                </div>

                <!-- 3. Diện tích mái khả dụng & Loại mái -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            3. Diện tích mái khả dụng (m²):
                        </label>
                        <input 
                            type="number" 
                            name="roof_area" 
                            value="<?= $calc['provided_roof_area'] ?? $calc['required_roof_area'] ?>" 
                            min="10" 
                            max="5000"
                            style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
                        >
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.45rem;">
                            4. Kết cấu mặt bằng mái:
                        </label>
                        <select 
                            name="roof_type" 
                            onchange="this.form.submit()"
                            style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.85rem;"
                        >
                            <option value="metal_roof" <?= $calc['roof_type'] === 'metal_roof' ? 'selected' : '' ?>>Mái tôn sóng / Seamlock</option>
                            <option value="tile_roof" <?= $calc['roof_type'] === 'tile_roof' ? 'selected' : '' ?>>Mái ngói Thái / Nhật</option>
                            <option value="concrete_roof" <?= $calc['roof_type'] === 'concrete_roof' ? 'selected' : '' ?>>Sân thượng Bê tông</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <button type="submit" class="sol-btn sol-btn--solar" style="flex: 1;">
                        ⚡ Cập Nhật Dự Toán Mô Phỏng
                    </button>
                    <a href="index.php?page=calculator&bill=3000000&package=residential" class="sol-btn sol-btn--secondary" title="Đặt lại mặc định">
                        🔄
                    </a>
                </div>
            </form>
        </div>

        <!-- Cột phải: 8 Chỉ Số Kỹ Thuật & Tài Chính Nổi Bật -->
        <div>
            <!-- Grid 4 thẻ Kỹ thuật -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                <div class="sol-glass-card" style="padding: 1.35rem 1.25rem; border-left: 3px solid var(--accent-amber);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.35rem;">
                        CÔNG SUẤT ĐỈNH KHUYẾN NGHỊ
                    </div>
                    <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #ffffff;">
                        <?= $calc['estimated_kwp'] ?> <span style="font-size: 1.1rem; color: var(--accent-amber);">kWp</span>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Sản lượng: ~<?= number_format($calc['yearly_kwh_produced'], 0, ',', '.') ?> kWh/năm
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 1.35rem 1.25rem; border-left: 3px solid var(--accent-cyan);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.35rem;">
                        SỐ TẤM PIN MONO 550W
                    </div>
                    <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #ffffff;">
                        <?= $calc['panel_count'] ?> <span style="font-size: 1.1rem; color: var(--accent-cyan);">Tấm</span>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Hiệu suất Tier-1 Mono Perc Half-cell
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 1.35rem 1.25rem; border-left: 3px solid var(--accent-emerald);">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.35rem;">
                        DIỆN TÍCH MÁI YÊU CẦU
                    </div>
                    <div style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #ffffff;">
                        <?= $calc['required_roof_area'] ?> <span style="font-size: 1.1rem; color: var(--accent-emerald);">m²</span>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Định mức 2.5 m²/tấm kèm lối kỹ thuật
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 1.35rem 1.25rem; border-left: 3px solid #e11d48;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 0.35rem;">
                        DỰ TOÁN ĐẦU TƯ TRỌN GÓI
                    </div>
                    <div style="font-family: var(--font-heading); font-size: 1.65rem; font-weight: 800; color: var(--accent-gold);">
                        <?= $calc['estimated_total_cost_formatted'] ?>
                    </div>
                    <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        Chìa khóa trao tay (VAT + Thi công trọn gói)
                    </div>
                </div>
            </div>

            <!-- Grid 4 thẻ Tài chính & Môi trường -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;">
                <div class="sol-glass-card" style="padding: 1rem 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.25rem;">Tiết kiệm/Tháng</div>
                    <div style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--accent-gold);">
                        ~<?= number_format($calc['monthly_savings'] / 1000000, 1) ?> Tr
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 1rem 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.25rem;">Tiết kiệm/Năm</div>
                    <div style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--accent-emerald);">
                        ~<?= number_format($calc['yearly_savings'] / 1000000, 1) ?> Tr
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 1rem 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.25rem;">Thời gian hoàn vốn</div>
                    <div style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--accent-cyan);">
                        <?= $calc['payback_years'] ?> Năm
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 1rem 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.25rem;">Cây xanh / Năm</div>
                    <div style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: #10b981;">
                        🌱 <?= $calc['trees_equivalent_yearly'] ?> Cây
                    </div>
                </div>
            </div>

            <!-- Cảnh báo nếu diện tích mái không đủ -->
            <?php if (!$calc['is_roof_sufficient']): ?>
                <div style="margin-top: 1rem; padding: 0.85rem 1rem; border-radius: var(--radius-sm); background: rgba(244, 63, 94, 0.15); border: 1px solid var(--accent-rose); font-size: 0.825rem; color: #fecdd3;">
                    ⚠️ <strong>Lưu ý mặt bằng:</strong> Diện tích mái khai báo (<?= $calc['provided_roof_area'] ?> m²) nhỏ hơn diện tích yêu cầu (<?= $calc['required_roof_area'] ?> m²). Mái của bạn chỉ lắp tối đa khoảng <strong><?= $calc['max_panels_by_roof'] ?> tấm pin (~<?= round($calc['max_panels_by_roof'] * 0.55, 1) ?> kWp)</strong>.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SƠ ĐỒ MẶT BẰNG KỸ THUẬT 2D (SOLAR ROOF SIMULATOR 2D SVG) -->
    <section class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.4rem;">Sơ Đồ Kỹ Thuật 2D Mặt Bằng Mái</span>
                <h3 style="color: #ffffff; font-size: 1.4rem; font-family: var(--font-heading);">
                    Mô Phỏng Bố Trí Giàn Pin & Góc Đón Nắng
                </h3>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.8rem; color: var(--text-secondary);">
                <span>📐 Góc nghiêng tối ưu: <strong style="color: var(--accent-amber);"><?= $calc['layout_matrix']['optimal_tilt'] ?></strong></span>
                <span>🧭 Hướng lắp: <strong style="color: var(--accent-cyan);"><?= $calc['layout_matrix']['azimuth'] ?></strong></span>
                <span>📏 Ma trận: <strong style="color: #ffffff;"><?= $calc['layout_matrix']['rows'] ?> Hàng × <?= $calc['layout_matrix']['columns'] ?> Cột</strong></span>
            </div>
        </div>

        <div style="background: #060911; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.5rem; position: relative; overflow-x: auto;">
            <!-- La bàn & Chỉ dẫn bức xạ -->
            <div style="position: absolute; top: 20px; right: 25px; background: rgba(14, 22, 38, 0.85); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.6rem 0.85rem; font-size: 0.75rem; color: #cbd5e1; z-index: 10; display: flex; flex-direction: column; gap: 0.25rem;">
                <div style="font-weight: 700; color: var(--accent-gold);">☀️ BỨC XẠ HIỆU DỤNG</div>
                <div>Quang năng: 4.5 kWh/m²/ngày</div>
                <div>Gió bão: Thiết kế chịu bão cấp 12</div>
            </div>

            <!-- Vẽ sơ đồ SVG động theo số tấm pin -->
            <?php
            $rows = $calc['layout_matrix']['rows'];
            $cols = $calc['layout_matrix']['columns'];
            $totalPanels = $calc['panel_count'];

            $panelW = 46;
            $panelH = 78;
            $gapX = 8;
            $gapY = 12;

            $roofPadding = 45;
            $svgW = max(700, ($cols * ($panelW + $gapX)) + ($roofPadding * 2));
            $svgH = ($rows * ($panelH + $gapY)) + ($roofPadding * 2) + 20;

            $startX = ($svgW - (($cols * ($panelW + $gapX)) - $gapX)) / 2;
            $startY = $roofPadding;
            ?>

            <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width: 100%; max-width: 960px; height: auto; margin: 0 auto; display: block;">
                <defs>
                    <!-- Gradient Tấm Pin Solar Mono -->
                    <linearGradient id="solarCellGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0f2b46"/>
                        <stop offset="50%" stop-color="#0a192f"/>
                        <stop offset="100%" stop-color="#05101e"/>
                    </linearGradient>

                    <!-- Khung Nhôm Anode -->
                    <linearGradient id="frameGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#475569"/>
                        <stop offset="100%" stop-color="#1e293b"/>
                    </linearGradient>

                    <!-- Mặt bằng mái nhà -->
                    <pattern id="roofPattern" width="20" height="20" patternUnits="userSpaceOnUse">
                        <line x1="0" y1="0" x2="20" y2="0" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                    </pattern>
                </defs>

                <!-- Viền Mái Nhà Khả Dụng -->
                <rect x="15" y="15" width="<?= $svgW - 30 ?>" height="<?= $svgH - 30 ?>" rx="8" fill="rgba(15, 23, 42, 0.4)" stroke="#334155" stroke-dasharray="6,4" stroke-width="1.5"/>
                <text x="30" y="35" fill="#64748b" font-size="11" font-family="monospace">BOUNDARY: <?= strtoupper($calc['roof_type']) ?> (<?= $calc['required_roof_area'] ?> m² KHẢ DỤNG)</text>

                <!-- Chỉ báo Hướng Bắc - Nam -->
                <g transform="translate(<?= $svgW - 50 ?>, 65)">
                    <circle cx="0" cy="0" r="16" fill="#0e1626" stroke="#475569" stroke-width="1"/>
                    <path d="M 0 -12 L 4 0 L -4 0 Z" fill="#f43f5e"/>
                    <path d="M 0 12 L 4 0 L -4 0 Z" fill="#94a3b8"/>
                    <text x="-4" y="-14" fill="#f43f5e" font-size="9" font-weight="bold">N</text>
                    <text x="-3" y="22" fill="#94a3b8" font-size="9" font-weight="bold">S</text>
                </g>

                <!-- Vẽ ray nhôm cơ khí bên dưới giàn pin -->
                <?php for ($r = 0; $r < $rows; $r++): 
                    $railY1 = $startY + ($r * ($panelH + $gapY)) + 15;
                    $railY2 = $startY + ($r * ($panelH + $gapY)) + $panelH - 15;
                ?>
                    <line x1="<?= $startX - 15 ?>" y1="<?= $railY1 ?>" x2="<?= $startX + ($cols * ($panelW + $gapX)) + 5 ?>" y2="<?= $railY1 ?>" stroke="#38bdf8" stroke-width="2.5" opacity="0.45"/>
                    <line x1="<?= $startX - 15 ?>" y1="<?= $railY2 ?>" x2="<?= $startX + ($cols * ($panelW + $gapX)) + 5 ?>" y2="<?= $railY2 ?>" stroke="#38bdf8" stroke-width="2.5" opacity="0.45"/>
                <?php endfor; ?>

                <!-- Vẽ từng tấm pin mặt trời 550W -->
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
                        <!-- Khung nhôm ngoài -->
                        <rect x="0" y="0" width="<?= $panelW ?>" height="<?= $panelH ?>" rx="3" fill="url(#frameGrad)" stroke="#64748b" stroke-width="0.8"/>
                        <!-- Cell silicon bên trong -->
                        <rect x="2" y="2" width="<?= $panelW - 4 ?>" height="<?= $panelH - 4 ?>" rx="2" fill="url(#solarCellGrad)"/>
                        
                        <!-- Đường kẻ busbar silicon -->
                        <line x1="2" y1="<?= $panelH/2 ?>" x2="<?= $panelW - 2 ?>" y2="<?= $panelH/2 ?>" stroke="rgba(255,255,255,0.2)" stroke-width="0.75"/>
                        <line x1="<?= $panelW/3 ?>" y1="2" x2="<?= $panelW/3 ?>" y2="<?= $panelH - 2 ?>" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
                        <line x1="<?= ($panelW/3)*2 ?>" y1="2" x2="<?= ($panelW/3)*2 ?>" y2="<?= $panelH - 2 ?>" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>

                        <!-- Số thứ tự tấm pin -->
                        <text x="<?= $panelW/2 ?>" y="<?= $panelH/2 + 3 ?>" fill="#38bdf8" font-size="8" font-family="monospace" text-anchor="middle" opacity="0.85">#<?= $panelIdx ?></text>
                    </g>
                <?php 
                    endfor;
                endfor; 
                ?>

                <!-- Module Biến Tần Inverter gắn cạnh giàn pin -->
                <g transform="translate(<?= $startX - 38 ?>, <?= $startY + 15 ?>)">
                    <rect x="0" y="0" width="22" height="42" rx="3" fill="#1e293b" stroke="#f59e0b" stroke-width="1.5"/>
                    <circle cx="11" cy="12" r="3" fill="#10b981"/>
                    <text x="11" y="28" fill="#f59e0b" font-size="6" text-anchor="middle" font-weight="bold">INV</text>
                </g>
            </svg>

            <!-- Chú thích ký hiệu sơ đồ kỹ thuật -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 1.75rem; flex-wrap: wrap; margin-top: 1.25rem; font-size: 0.775rem; color: var(--text-secondary); border-top: 1px solid var(--border-subtle); padding-top: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 14px; height: 14px; background: #0f2b46; border: 1px solid #64748b; border-radius: 2px;"></span>
                    <span>Tấm Pin Mono Half-cell 550W (Tier-1)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 22px; height: 3px; background: #38bdf8;"></span>
                    <span>Ray nhôm định hình Anode chịu lực</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 10px; height: 16px; background: #1e293b; border: 1px solid #f59e0b; border-radius: 2px;"></span>
                    <span>Biến tần Inverter IP65</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="display: inline-block; width: 14px; height: 14px; border: 1px dashed #334155; border-radius: 2px;"></span>
                    <span>Hành lang kỹ thuật an toàn</span>
                </div>
            </div>
        </div>
    </section>

    <!-- BẢNG BÓC TÁCH VẬT TƯ KỸ THUẬT (BOM - BILL OF MATERIALS) -->
    <section class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.4rem;">BẢNG TIÊU CHUẨN THIẾT BỊ</span>
                <h3 style="color: #ffffff; font-size: 1.4rem; font-family: var(--font-heading);">
                    Bóc Tách Vật Tư Kỹ Thuật (BOM) Cấu Hình <?= $calc['estimated_kwp'] ?> kWp
                </h3>
            </div>
            <div style="color: var(--accent-gold); font-weight: 700; font-size: 1.15rem;">
                Tổng dự toán: <?= $calc['estimated_total_cost_formatted'] ?>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(245, 158, 11, 0.25); color: var(--accent-gold); font-size: 0.775rem; text-transform: uppercase;">
                        <th style="padding: 0.75rem 1rem;">STT</th>
                        <th style="padding: 0.75rem 1rem;">Hạng Mục Thiết Bị</th>
                        <th style="padding: 0.75rem 1rem;">Mô Tả Tiêu Chuẩn Kỹ Thuật</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Số Lượng</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">ĐVT</th>
                        <th style="padding: 0.75rem 1rem;">Chính Sách Bảo Hành</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calc['bom'] as $index => $item): ?>
                        <tr style="border-bottom: 1px solid var(--border-subtle); transition: background 0.15s ease;">
                            <td style="padding: 0.85rem 1rem; color: var(--text-muted); font-family: var(--font-mono);"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></td>
                            <td style="padding: 0.85rem 1rem; color: #ffffff; font-weight: 600;"><?= htmlspecialchars($item['item']) ?></td>
                            <td style="padding: 0.85rem 1rem; color: var(--text-secondary);"><?= htmlspecialchars($item['specs']) ?></td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 700; color: var(--accent-amber); font-family: var(--font-mono);"><?= $item['quantity'] ?></td>
                            <td style="padding: 0.85rem 1rem; text-align: center; color: var(--text-muted);"><?= htmlspecialchars($item['unit']) ?></td>
                            <td style="padding: 0.85rem 1rem; color: #10b981; font-size: 0.8rem;"><?= htmlspecialchars($item['warranty']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- FORM ĐĂNG KÝ TƯ VẤN & LƯU BÁO GIÁ VÀO BẢNG SOLAR_QUOTES -->
    <section class="sol-glass-card" style="padding: 2.5rem; margin-bottom: 3.5rem; border-color: rgba(245, 158, 11, 0.35);">
        <div style="max-width: 720px; margin: 0 auto; text-align: center; margin-bottom: 2rem;">
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.5rem;">Đăng Ký Khảo Sát Hiện Trường</span>
            <h3 style="color: #ffffff; font-size: 1.85rem; font-family: var(--font-heading); margin-bottom: 0.65rem;">
                Nhận Báo Giá Kỹ Thuật Chi Tiết & Lịch Khảo Sát Mái Miễn Phí
            </h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Dữ liệu sẽ được lưu tự động vào hệ thống cơ sở dữ liệu Solaris (bảng <code>solar_quotes</code>). Kỹ sư trưởng sẽ liên hệ trực tiếp để đo đạc và lập phương án đấu nối EVN.
            </p>
        </div>

        <form method="POST" action="index.php?page=calculator" style="max-width: 760px; margin: 0 auto;">
            <input type="hidden" name="action" value="save_quote">
            <input type="hidden" name="monthly_bill" value="<?= (int)$calc['monthly_bill'] ?>">
            <input type="hidden" name="roof_area" value="<?= $calc['required_roof_area'] ?>">
            <input type="hidden" name="package" value="<?= htmlspecialchars($calc['package_key']) ?>">
            <input type="hidden" name="roof_type" value="<?= htmlspecialchars($calc['roof_type']) ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                        Họ và tên khách hàng <span style="color: var(--accent-rose);">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="customer_name" 
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
                        required 
                        placeholder="Ví dụ: 0912 345 678"
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
                    >
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                        Địa chỉ Email nhận hồ sơ
                    </label>
                    <input 
                        type="email" 
                        name="customer_email" 
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
                        placeholder="Số nhà, đường, phường/xã, TP..."
                        style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem;"
                    >
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                    Ghi chú yêu cầu đặc biệt hoặc khung giờ khảo sát mong muốn
                </label>
                <textarea 
                    name="notes" 
                    rows="3" 
                    placeholder="Ví dụ: Cần khảo sát vào sáng thứ Bảy, nhà có sẵn nguồn 3 pha..."
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem; font-family: inherit;"
                ></textarea>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="sol-btn sol-btn--solar sol-btn--lg" style="width: 100%; max-width: 420px; font-size: 1rem;">
                    🚀 Xác Nhận Gửi Yêu Cầu & Lưu Dự Toán #<?= $calc['estimated_kwp'] ?>kWp
                </button>
            </div>
        </form>
    </section>

    <!-- DANH SÁCH LỊCH SỬ BÁO GIÁ ĐÃ LƯU (KÈM PHÂN TRANG USEPAGINATIONSTATE KHI > 20 DÒNG) -->
    <section class="sol-glass-card" style="padding: 2.25rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.4rem;">QUẢN TRỊ DỮ LIỆU CSDL (THÀNH VIÊN 3)</span>
                <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading);">
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
                                    <?php 
                                    $statusClass = match ($q['status'] ?? 'pending') {
                                        'approved' => 'sol-badge--emerald',
                                        'contacted', 'survey_scheduled' => 'sol-badge--cyan',
                                        'rejected' => 'sol-badge--rose',
                                        default => 'sol-badge--amber'
                                    };
                                    $statusText = match ($q['status'] ?? 'pending') {
                                        'approved' => 'Đã phê duyệt',
                                        'contacted' => 'Đã liên hệ',
                                        'survey_scheduled' => 'Lên lịch khảo sát',
                                        'rejected' => 'Từ chối',
                                        default => 'Chờ xử lý'
                                    };
                                    ?>
                                    <span class="sol-badge <?= $statusClass ?>" style="font-size: 0.7rem;">
                                        <?= $statusText ?>
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
    function updateCalcPreview(val) {
        const bill = parseFloat(val);
        const billDisplay = document.getElementById('billValDisplay');
        if (billDisplay) {
            billDisplay.innerText = new Intl.NumberFormat('vi-VN').format(bill) + ' VNĐ';
        }
    }
</script>
