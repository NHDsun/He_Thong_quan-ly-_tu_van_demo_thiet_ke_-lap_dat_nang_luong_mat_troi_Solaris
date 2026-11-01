<?php
/**
 * View: resources/views/pages/calculator.blade.php
 * Phân hệ 3: Tư Vấn & Demo Thiết Kế (Solar Calculator & Simulator)
 * Phụ trách: Thành viên 3 (Fullstack / Solar Sizing Engine & Data)
 */

$isPlanSource = ($source ?? 'bill') === 'plan';
$hasCart = !empty($cart_products);
$planItems = $cart_products ?? [];
$compat = $compatibility ?? ['total_panels' => 0, 'total_panel_kwp' => 0, 'total_inverters' => 0, 'total_inverter_kw' => 0, 'completion_score' => 0, 'rules' => []];
?>

<div class="sol-container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <!-- Tiêu đề phân hệ -->
    <div class="sol-section-header" style="margin-bottom: 1.75rem;">
        <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.65rem;">
            ⚡ Phân Hệ 3 • Solar Sizing Engine & Simulator
        </span>
        <h1 class="sol-section-header__title" style="font-size: 2.3rem;">
            Tư Vấn Kỹ Thuật & Mô Phỏng Cấu Hình Điện Mặt Trời
        </h1>
        <p class="sol-section-header__desc">
            Tính toán chính xác <strong>số KW điện tạo ra</strong>, <strong>số tiền tạo ra trong 1 tháng</strong> và <strong>dự tính thời gian gỡ lại vốn</strong> trực tiếp từ thiết bị trong kế hoạch dự định hoặc ước tính theo hóa đơn tiền điện.
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

    <!-- ===================================================================== -->
    <!-- BỘ CHUYỂN ĐỔI CHẾ ĐỘ TÍNH TOÁN: TỪ KẾ HOẠCH DỰ ĐỊNH vs THEO HÓA ĐƠN   -->
    <!-- ===================================================================== -->
    <div class="sol-glass-card" style="padding: 1.25rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-color: rgba(245, 158, 11, 0.35);">
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

    <!-- ===================================================================== -->
    <!-- BỘ ĐIỀU KHIỂN ĐẦU VÀO VÀ KẾT QUẢ TÍNH TOÁN 3 CHỈ SỐ CỐT LÕI           -->
    <!-- ===================================================================== -->
    <div style="display: grid; grid-template-columns: 1fr 1.35fr; gap: 2rem; margin-bottom: 3rem;" class="sol-calc-grid-layout">
        
        <!-- CỘT TRÁI: ĐIỀU KHIỂN ĐẦU VÀO -->
        <div class="sol-glass-card" style="padding: 2rem 1.85rem;">
            <?php if ($isPlanSource): ?>
                <!-- GIAO DIỆN CHẾ ĐỘ 1: TỪ KẾ HOẠCH DỰ ĐỊNH -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
                    <h3 style="color: #ffffff; font-size: 1.2rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                        <span>📋</span> Thiết Bị Trong Kế Hoạch
                    </h3>
                    <a href="index.php?page=cart" class="sol-btn sol-btn--outline sol-btn--sm" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                        ✏️ Sửa Kế Hoạch
                    </a>
                </div>

                <?php if (!$hasCart): ?>
                    <div style="text-align: center; padding: 2rem 1rem; background: rgba(8, 12, 20, 0.4); border-radius: var(--radius-sm); border: 1px dashed var(--border-subtle); margin-bottom: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1rem;">
                            Kế hoạch dự định của bạn hiện chưa có thiết bị nào. Hệ thống đang tính toán mô phỏng mẫu <strong>5.5 kWp (10 tấm pin 550W)</strong>.
                        </p>
                        <a href="index.php?page=products" class="sol-btn sol-btn--solar sol-btn--sm">
                            + Thêm thiết bị vào kế hoạch
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Danh sách thiết bị tóm tắt -->
                    <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-bottom: 1.25rem; max-height: 260px; overflow-y: auto; padding-right: 4px;">
                        <?php foreach ($planItems as $pItem): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); font-size: 0.825rem;">
                                <div>
                                    <strong style="color: #ffffff; display: block;"><?= htmlspecialchars($pItem['name']) ?></strong>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">
                                        <?= htmlspecialchars($pItem['category_name']) ?>
                                        <?php if (!empty($pItem['capacity_watts'])): ?>
                                            • <span style="color: var(--accent-cyan); font-weight: 600;"><?= (int)$pItem['capacity_watts'] ?>W</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="text-align: right;">
                                    <span class="sol-badge sol-badge--amber" style="padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                        x<?= (int)$pItem['quantity'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Trạng thái tương thích ghép đôi -->
                    <div style="padding: 0.85rem 1rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.8rem;">
                        <div style="font-weight: 700; color: #10b981; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">
                            <span>⚡</span> Tương Thích Ghép Đôi: <?= $compat['completion_score'] ?>%
                        </div>
                        <div style="color: var(--text-secondary); line-height: 1.4;">
                            Giàn pin: <strong><?= $calc['estimated_kwp'] ?> kWp</strong> • Inverter: <strong><?= $compat['total_inverter_kw'] ?> kW</strong>
                            <?php if ($compat['dc_ac_ratio'] !== null): ?>
                                • Tỷ lệ DC/AC: <strong><?= $compat['dc_ac_ratio'] ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form tùy biến diện tích mái & kết cấu -->
                <form method="GET" action="index.php">
                    <input type="hidden" name="page" value="calculator">
                    <input type="hidden" name="source" value="plan">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                                Diện tích mái nhà (m²):
                            </label>
                            <input 
                                type="number" 
                                name="roof_area" 
                                value="<?= $calc['provided_roof_area'] ?? $calc['required_roof_area'] ?>" 
                                min="10" 
                                max="5000"
                                style="width: 100%; padding: 0.6rem 0.8rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-family: var(--font-mono); font-size: 0.9rem;"
                            >
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.8rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.4rem;">
                                Kết cấu mặt bằng mái:
                            </label>
                            <select 
                                name="roof_type" 
                                onchange="this.form.submit()"
                                style="width: 100%; padding: 0.6rem 0.8rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.825rem;"
                            >
                                <option value="metal_roof" <?= $calc['roof_type'] === 'metal_roof' ? 'selected' : '' ?>>Mái tôn sóng / Seamlock</option>
                                <option value="tile_roof" <?= $calc['roof_type'] === 'tile_roof' ? 'selected' : '' ?>>Mái ngói Thái / Nhật</option>
                                <option value="concrete_roof" <?= $calc['roof_type'] === 'concrete_roof' ? 'selected' : '' ?>>Sân thượng Bê tông</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="sol-btn sol-btn--solar" style="width: 100%;">
                        ⚡ Cập Nhật Diện Tích Mái & Mô Phỏng
                    </button>
                </form>

            <?php else: ?>
                <!-- GIAO DIỆN CHẾ ĐỘ 2: THEO HÓA ĐƠN TIỀN ĐIỆN -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                    <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                        <span>⚙️</span> Thông Số Đầu Vào
                    </h3>
                    <span class="sol-badge sol-badge--amber">Thuật toán v2.1</span>
                </div>

                <form method="GET" action="index.php" id="solarCalcForm">
                    <input type="hidden" name="page" value="calculator">
                    <input type="hidden" name="source" value="bill">

                    <!-- 1. Chọn Gói Hệ Thống -->
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.85rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.65rem;">
                            1. Phân loại giải pháp hệ thống:
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 0.55rem;">
                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid <?= $calc['package_key'] === 'residential' ? 'var(--accent-amber)' : 'var(--border-subtle)' ?>; background: <?= $calc['package_key'] === 'residential' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(8, 12, 20, 0.4)' ?>; cursor: pointer;">
                                <input type="radio" name="package" value="residential" <?= $calc['package_key'] === 'residential' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--accent-amber);">
                                <div>
                                    <div style="color: #ffffff; font-weight: 600; font-size: 0.875rem;">Hòa Lưới Dân Dụng (Hộ Gia Đình)</div>
                                    <div style="color: var(--text-muted); font-size: 0.75rem;">14.000.000 đ/kWp • Giảm tiền điện bậc thang cao</div>
                                </div>
                            </label>

                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid <?= $calc['package_key'] === 'commercial' ? 'var(--accent-emerald)' : 'var(--border-subtle)' ?>; background: <?= $calc['package_key'] === 'commercial' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(8, 12, 20, 0.4)' ?>; cursor: pointer;">
                                <input type="radio" name="package" value="commercial" <?= $calc['package_key'] === 'commercial' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--accent-emerald);">
                                <div>
                                    <div style="color: #ffffff; font-weight: 600; font-size: 0.875rem;">Doanh Nghiệp & Nhà Xưởng (C&I)</div>
                                    <div style="color: var(--text-muted); font-size: 0.75rem;">13.000.000 đ/kWp • Giảm chi phí điện giờ cao điểm</div>
                                </div>
                            </label>

                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid <?= $calc['package_key'] === 'hybrid' ? 'var(--accent-cyan)' : 'var(--border-subtle)' ?>; background: <?= $calc['package_key'] === 'hybrid' ? 'rgba(56, 189, 248, 0.1)' : 'rgba(8, 12, 20, 0.4)' ?>; cursor: pointer;">
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
            <?php endif; ?>
        </div>

        <!-- CỘT PHẢI: HIỂN THỊ 3 CHỈ SỐ CỐT LÕI THEO ĐÚNG YÊU CẦU NGƯỜI DÙNG -->
        <div>
            <!-- KHỐI 3 CHỈ SỐ VÀNG NỔI BẬT THEO YÊU CẦU -->
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem;">
                
                <!-- CHỈ SỐ 1: SỐ KW ĐIỆN TẠO RA -->
                <div class="sol-glass-card" style="padding: 1.4rem 1.6rem; border-left: 4px solid var(--accent-amber); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(8, 12, 20, 0.8) 100%);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span class="sol-badge sol-badge--amber" style="font-size: 0.725rem; margin-bottom: 0.35rem;">
                                ⚡ CHỈ SỐ NĂNG LƯỢNG QUANG ĐIỆN
                            </span>
                            <div style="font-size: 0.85rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase;">
                                Số KW Điện Tạo Ra Từ Kế Hoạch Thiết Bị
                            </div>
                        </div>
                        <span style="font-size: 1.75rem;">☀️</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 1.25rem; margin-top: 0.85rem; align-items: center;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Công Suất Đỉnh Giàn Pin:</div>
                            <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #ffffff; line-height: 1.1;">
                                <?= $calc['estimated_kwp'] ?> <span style="font-size: 1.15rem; color: var(--accent-amber);">kWp</span>
                            </div>
                            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                                Quy mô: <strong><?= $calc['panel_count'] ?> tấm pin</strong> (Hiệu suất 0.82)
                            </div>
                        </div>

                        <div style="padding-left: 1rem; border-left: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Sản Lượng Điện Ước Tính:</div>
                            <div style="font-family: var(--font-heading); font-size: 1.85rem; font-weight: 800; color: var(--accent-cyan); line-height: 1.1;">
                                ~<?= number_format($calc['monthly_kwh'] ?? (($calc['yearly_kwh_produced'] ?? 0) / 12), 1, ',', '.') ?> <span style="font-size: 1rem; color: #ffffff;">kWh / tháng</span>
                            </div>
                            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                                Tương đương <strong>~<?= number_format($calc['yearly_kwh_produced'], 0, ',', '.') ?> số điện (kWh)/năm</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHỈ SỐ 2: SỐ TIỀN TẠO RA TRONG 1 THÁNG -->
                <div class="sol-glass-card" style="padding: 1.4rem 1.6rem; border-left: 4px solid var(--accent-emerald); background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(8, 12, 20, 0.8) 100%);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span class="sol-badge sol-badge--emerald" style="font-size: 0.725rem; margin-bottom: 0.35rem;">
                                💰 GIÁ TRỊ TÀI CHÍNH SINH LỢI
                            </span>
                            <div style="font-size: 0.85rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase;">
                                Số Tiền Tạo Ra Trong 1 Tháng (Tiết Kiệm Hàng Tháng)
                            </div>
                        </div>
                        <span style="font-size: 1.75rem;">💵</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 1.25rem; margin-top: 0.85rem; align-items: center;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Giá Trị Điện Sinh Ra / Tháng:</div>
                            <div style="font-family: var(--font-heading); font-size: 2.1rem; font-weight: 800; color: var(--accent-gold); line-height: 1.1;">
                                <?= $calc['monthly_money_formatted'] ?? $calc['monthly_savings_formatted'] ?>
                            </div>
                            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                                Dựa trên đơn giá điện EVN: <strong>3.000 đ/kWh</strong>
                            </div>
                        </div>

                        <div style="padding-left: 1rem; border-left: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Giá Trị Tạo Ra Cả Năm:</div>
                            <div style="font-family: var(--font-heading); font-size: 1.85rem; font-weight: 800; color: #10b981; line-height: 1.1;">
                                ~<?= $calc['yearly_savings_formatted'] ?>
                            </div>
                            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                                Sau 25 năm tạo ra: <strong><?= number_format($calc['total_25yr_savings'] / 1000000, 0, ',', '.') ?> Triệu VNĐ</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHỈ SỐ 3: DỰ TÍNH THỜI GIAN GỠ LẠI SỐ TIỀN (HOÀN VỐN) -->
                <div class="sol-glass-card" style="padding: 1.4rem 1.6rem; border-left: 4px solid var(--accent-cyan); background: linear-gradient(135deg, rgba(56, 189, 248, 0.08) 0%, rgba(8, 12, 20, 0.8) 100%);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span class="sol-badge sol-badge--cyan" style="font-size: 0.725rem; margin-bottom: 0.35rem;">
                                ⏳ DỰ TOÁN HOÀN VỐN ĐẦU TƯ (ROI)
                            </span>
                            <div style="font-size: 0.85rem; color: #cbd5e1; font-weight: 700; text-transform: uppercase;">
                                Dự Tính Bao Lâu Thì Gỡ Lại Được Số Tiền Đã Đầu Tư
                            </div>
                        </div>
                        <span style="font-size: 1.75rem;">🎯</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 1.25rem; margin-top: 0.85rem; align-items: center;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Thời Gian Gỡ Lại Vốn:</div>
                            <div style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: #38bdf8; line-height: 1.1;">
                                <?= $calc['payback_text'] ?? ($calc['payback_years'] . ' Năm') ?>
                            </div>
                            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 0.2rem;">
                                Tương đương <strong><?= $calc['payback_years_decimal'] ?? $calc['payback_years'] ?> năm</strong> hoàn vốn 100%
                            </div>
                        </div>

                        <div style="padding-left: 1rem; border-left: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Tổng Vốn Đầu Tư Dự Tính:</div>
                            <div style="font-family: var(--font-heading); font-size: 1.55rem; font-weight: 800; color: #ffffff; line-height: 1.1;">
                                <?= $calc['estimated_total_cost_formatted'] ?>
                            </div>
                            <div style="font-size: 0.775rem; color: #34d399; margin-top: 0.3rem;">
                                Lãi ròng 25 năm: <strong>+<?= $calc['net_25yr_profit_formatted'] ?></strong> (Tỷ suất <?= $calc['roi_percentage'] ?>%)
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Grid 4 thẻ nhỏ Bổ sung (Diện tích mái, Số pin, Khí thải, Cây xanh) -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;">
                <div class="sol-glass-card" style="padding: 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.2rem;">Diện Tích Mái</div>
                    <div style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: #ffffff;">
                        <?= $calc['required_roof_area'] ?> m²
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.2rem;">Số Tấm Pin</div>
                    <div style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: var(--accent-amber);">
                        <?= $calc['panel_count'] ?> Tấm
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.2rem;">Cắt Giảm CO₂</div>
                    <div style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: #38bdf8;">
                        ~<?= number_format($calc['co2_saved_kg_yearly'] / 1000, 1) ?> Tấn/năm
                    </div>
                </div>

                <div class="sol-glass-card" style="padding: 0.85rem; text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.2rem;">Trồng Cây Xanh</div>
                    <div style="font-family: var(--font-heading); font-size: 1.1rem; font-weight: 700; color: #10b981;">
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

    <!-- ===================================================================== -->
    <!-- SƠ ĐỒ MẶT BẰNG KỸ THUẬT 2D (SOLAR ROOF SIMULATOR 2D SVG)              -->
    <!-- ===================================================================== -->
    <section class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.4rem;">Sơ Đồ Kỹ Thuật 2D Mặt Bằng Mái</span>
                <h3 style="color: #ffffff; font-size: 1.4rem; font-family: var(--font-heading); margin: 0;">
                    Mô Phỏng Bố Trí <?= $calc['panel_count'] ?> Tấm Pin (<?= $calc['estimated_kwp'] ?> kWp) & Góc Đón Nắng
                </h3>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.8rem; color: var(--text-secondary); flex-wrap: wrap;">
                <span>📐 Góc nghiêng: <strong style="color: var(--accent-amber);"><?= $calc['layout_matrix']['optimal_tilt'] ?></strong></span>
                <span>🧭 Hướng: <strong style="color: var(--accent-cyan);"><?= $calc['layout_matrix']['azimuth'] ?></strong></span>
                <span>📏 Bố trí: <strong style="color: #ffffff;"><?= $calc['layout_matrix']['rows'] ?> Hàng × <?= $calc['layout_matrix']['columns'] ?> Cột</strong></span>
            </div>
        </div>

        <div style="background: #060911; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.5rem; position: relative; overflow-x: auto;">
            <!-- La bàn & Chỉ dẫn bức xạ -->
            <div style="position: absolute; top: 20px; right: 25px; background: rgba(14, 22, 38, 0.85); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.6rem 0.85rem; font-size: 0.75rem; color: #cbd5e1; z-index: 10; display: flex; flex-direction: column; gap: 0.25rem;">
                <div style="font-weight: 700; color: var(--accent-gold);">☀️ BỨC XẠ HIỆU DỤNG</div>
                <div>Quang năng: 4.5 kWh/m²/ngày</div>
                <div>Gió bão: Tiêu chuẩn cấp 12</div>
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
                    <linearGradient id="solarCellGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0f2b46"/>
                        <stop offset="50%" stop-color="#0a192f"/>
                        <stop offset="100%" stop-color="#05101e"/>
                    </linearGradient>

                    <linearGradient id="frameGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#475569"/>
                        <stop offset="100%" stop-color="#1e293b"/>
                    </linearGradient>
                </defs>

                <!-- Viền Mái Nhà Khả Dụng -->
                <rect x="15" y="15" width="<?= $svgW - 30 ?>" height="<?= $svgH - 30 ?>" rx="8" fill="rgba(15, 23, 42, 0.4)" stroke="#334155" stroke-dasharray="6,4" stroke-width="1.5"/>
                <text x="30" y="35" fill="#64748b" font-size="11" font-family="monospace">MẶT BẰNG MÁI: <?= strtoupper($calc['roof_type']) ?> (<?= $calc['required_roof_area'] ?> m² KHẢ DỤNG)</text>

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
                        <rect x="0" y="0" width="<?= $panelW ?>" height="<?= $panelH ?>" rx="3" fill="url(#frameGrad)" stroke="#64748b" stroke-width="0.8"/>
                        <rect x="2" y="2" width="<?= $panelW - 4 ?>" height="<?= $panelH - 4 ?>" rx="2" fill="url(#solarCellGrad)"/>
                        <line x1="2" y1="<?= $panelH/2 ?>" x2="<?= $panelW - 2 ?>" y2="<?= $panelH/2 ?>" stroke="rgba(255,255,255,0.2)" stroke-width="0.75"/>
                        <line x1="<?= $panelW/3 ?>" y1="2" x2="<?= $panelW/3 ?>" y2="<?= $panelH - 2 ?>" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
                        <line x1="<?= ($panelW/3)*2 ?>" y1="2" x2="<?= ($panelW/3)*2 ?>" y2="<?= $panelH - 2 ?>" stroke="rgba(255,255,255,0.15)" stroke-width="0.5"/>
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
                    <span>Tấm Pin Mono Half-cell (Tier-1)</span>
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

    <!-- ===================================================================== -->
    <!-- BẢNG BÓC TÁCH VẬT TƯ KỸ THUẬT (BOM - BILL OF MATERIALS)               -->
    <!-- ===================================================================== -->
    <section class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 3.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.4rem;">BẢNG TIÊU CHUẨN THIẾT BỊ</span>
                <h3 style="color: #ffffff; font-size: 1.4rem; font-family: var(--font-heading); margin: 0;">
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

    <!-- ===================================================================== -->
    <!-- FORM ĐĂNG KÝ TƯ VẤN & LƯU BÁO GIÁ VÀO BẢNG SOLAR_QUOTES              -->
    <!-- ===================================================================== -->
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

        <form method="POST" action="index.php?page=calculator<?= $isPlanSource ? '&source=plan' : '' ?>" style="max-width: 760px; margin: 0 auto;">
            <input type="hidden" name="action" value="save_quote">
            <input type="hidden" name="calc_source" value="<?= htmlspecialchars($source ?? 'bill') ?>">
            <input type="hidden" name="monthly_bill" value="<?= (int)($calc['monthly_bill'] ?? $calc['monthly_money'] ?? 3000000) ?>">
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
                    Ghi chú yêu cầu đặc biệt hoặc cấu hình đã chọn
                </label>
                <textarea 
                    name="notes" 
                    rows="3" 
                    placeholder="Ví dụ: Đã cấu hình theo Kế hoạch dự định (<?= $calc['estimated_kwp'] ?> kWp, <?= $calc['panel_count'] ?> tấm pin)..."
                    style="width: 100%; padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.9rem; font-family: inherit;"
                ><?= $isPlanSource ? "Cấu hình từ Kế hoạch dự định: {$calc['estimated_kwp']} kWp ({$calc['panel_count']} tấm pin). Dự tính hoàn vốn: " . ($calc['payback_text'] ?? '') : '' ?></textarea>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="sol-btn sol-btn--solar sol-btn--lg" style="width: 100%; max-width: 440px; font-size: 1rem;">
                    🚀 Xác Nhận Gửi Yêu Cầu & Lưu Dự Toán #<?= $calc['estimated_kwp'] ?>kWp
                </button>
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
