<?php
declare(strict_types=1);
$items = $cartItems;
$resolveCartImage = static function (array $item): ?string {
    $name = basename(trim((string) ($item['image_url'] ?? '')));
    $root = dirname(__DIR__, 3) . '/assets/products/';
    if ($name !== '' && is_file($root . $name)) return 'assets/products/' . $name;
    $jpg = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
    return $name !== '' && is_file($root . $jpg) ? 'assets/products/' . $jpg : null;
};

// Đảm bảo thông tin tương thích hệ thống luôn có sẵn
$compat = $compatibility ?? (class_exists(\App\Hooks\UseSolarCalculator::class) 
    ? \App\Hooks\UseSolarCalculator::analyzeCompatibility($items) 
    : ['total_panels' => 0, 'total_panel_kwp' => 0, 'total_inverters' => 0, 'total_inverter_kw' => 0, 'total_batteries' => 0, 'total_battery_kwh' => 0, 'completion_score' => 0, 'rules' => []]);
?>
<link rel="stylesheet" href="resources/css/products.css">
<link rel="stylesheet" href="resources/css/cart.css">
<link rel="stylesheet" href="resources/css/cart-grid.css">
<link rel="stylesheet" href="resources/css/cart-polish.css">

<section class="sol-container products-page cart-page" style="padding-top: 2rem; padding-bottom: 5rem;">
    <header class="products-hero" style="margin-bottom: 2rem;">
        <div>
            <span class="sol-badge sol-badge--emerald">Solaris · Kế Hoạch Dự Định</span>
            <h1 style="font-size: 2.2rem; margin: 0.5rem 0 0.4rem;">Danh Sách Thiết Bị Dự Định Lắp Đặt</h1>
            <p style="color: var(--text-secondary); max-width: 780px;">
                Quản lý các thiết bị trong kế hoạch, kiểm tra <strong>tính tương thích và quy tắc ghép đôi thiết bị</strong>, sau đó đồng bộ sang bộ mô phỏng để tính <strong>số KW điện tạo ra, số tiền tạo ra trong 1 tháng và thời gian hoàn vốn</strong>.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <a class="sol-btn sol-btn--outline" href="index.php?page=products">← Tiếp tục chọn thiết bị</a>
            <?php if (!empty($items)): ?>
                <a class="sol-btn sol-btn--solar" href="index.php?page=calculator&source=plan">⚡ Tính Toán & Demo Thiết Kế →</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!$items): ?>
        <div class="sol-glass-card products-empty" style="text-align: center; padding: 3.5rem 2rem;">
            <div>
                <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                <strong style="font-size: 1.25rem; color: #ffffff; display: block; margin-bottom: 0.5rem;">Kế hoạch dự định chưa có thiết bị nào</strong>
                <p style="color: var(--text-secondary); margin: 0 auto 1.5rem; max-width: 520px; font-size: 0.95rem;">
                    Hãy khám phá danh mục để chọn các tấm pin năng lượng mặt trời, biến tần inverter và phụ kiện tủ điện cần thiết để tạo thành hệ thống hoàn chỉnh.
                </p>
                <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                    <a class="sol-btn sol-btn--solar" href="index.php?page=products">Xem danh mục thiết bị</a>
                    <a class="sol-btn sol-btn--outline" href="index.php?page=calculator">Tính toán theo hóa đơn điện</a>
                </div>
            </div>
        </div>
    <?php else: ?>

        <!-- ================================================================= -->
        <!-- KHỐI NÂNG CẤP: BẢNG KIỂM TRA TƯƠNG THÍCH & GHÉP ĐÔI THIẾT BỊ     -->
        <!-- (HIỂN THỊ CẦN THIẾT BỊ NÀO VỚI THIẾT BỊ NÀO THEO YÊU CẦU CỦA USER) -->
        <!-- ================================================================= -->
        <section class="sol-glass-card" style="padding: 1.75rem; margin-bottom: 2rem; border-color: rgba(245, 158, 11, 0.4); background: linear-gradient(180deg, rgba(14, 22, 38, 0.85) 0%, rgba(8, 12, 20, 0.95) 100%);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                <div>
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">
                        ⚡ System Pairing & Compatibility Advisor
                    </span>
                    <h2 style="font-size: 1.35rem; color: #ffffff; font-family: var(--font-heading); margin: 0;">
                        Bảng Kiểm Tra Tương Thích & Quy Tắc Ghép Đôi Thiết Bị
                    </h2>
                    <p style="color: var(--text-secondary); font-size: 0.825rem; margin: 0.3rem 0 0;">
                        Một hệ thống điện mặt trời tiêu chuẩn cần sự phối ghép đồng bộ giữa 4 nhóm thiết bị chính dưới đây:
                    </p>
                </div>
                <!-- Thanh điểm hoàn thiện hệ thống -->
                <div style="text-align: right; min-width: 200px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; font-size: 0.8rem;">
                        <span style="color: var(--text-secondary);">Mức độ hoàn thiện:</span>
                        <strong style="color: <?= $compat['completion_score'] >= 80 ? '#10b981' : ($compat['completion_score'] >= 50 ? 'var(--accent-amber)' : '#f43f5e') ?>; font-size: 0.95rem; font-family: var(--font-mono);">
                            <?= $compat['completion_score'] ?>%
                        </strong>
                    </div>
                    <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
                        <div style="width: <?= $compat['completion_score'] ?>%; height: 100%; background: <?= $compat['completion_score'] >= 80 ? 'linear-gradient(90deg, #10b981, #34d399)' : ($compat['completion_score'] >= 50 ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #f43f5e, #fb7185)') ?>; transition: width 0.4s ease;"></div>
                    </div>
                </div>
            </div>

            <!-- Grid 4 Nhóm Thiết Bị Phối Ghép -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                
                <!-- 1. Tấm Pin Mặt Trời -->
                <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid <?= $compat['total_panels'] > 0 ? 'rgba(16, 185, 129, 0.4)' : 'rgba(244, 63, 94, 0.4)' ?>; border-radius: var(--radius-sm); position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 0.4rem;">
                            <span>☀️</span> 1. Tấm Pin Mặt Trời
                        </span>
                        <span class="sol-badge <?= $compat['total_panels'] > 0 ? 'sol-badge--emerald' : 'sol-badge--rose' ?>" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;">
                            <?= $compat['total_panels'] > 0 ? 'Đã có' : 'Bắt buộc' ?>
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.35rem;">
                        <?php if ($compat['total_panels'] > 0): ?>
                            <strong style="color: var(--accent-amber);"><?= $compat['total_panels'] ?> tấm</strong> (~ <strong style="color: #ffffff;"><?= $compat['total_panel_kwp'] ?> kWp</strong>)
                        <?php else: ?>
                            <span style="color: #fca5a5;">Chưa có tấm pin nào</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.35;">
                        Tạo dòng điện DC. <strong>Cần ghép đôi với:</strong> Biến tần Inverter có công suất tương đương.
                    </div>
                    <?php if ($compat['total_panels'] === 0): ?>
                        <a href="index.php?page=products&category=tam-pin-mat-troi" style="display: inline-block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--accent-gold); text-decoration: underline;">+ Chọn tấm pin phù hợp</a>
                    <?php endif; ?>
                </div>

                <!-- 2. Biến Tần Inverter -->
                <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid <?= $compat['total_inverters'] > 0 ? 'rgba(16, 185, 129, 0.4)' : 'rgba(244, 63, 94, 0.4)' ?>; border-radius: var(--radius-sm);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 0.4rem;">
                            <span>⚡</span> 2. Biến Tần Inverter
                        </span>
                        <span class="sol-badge <?= $compat['total_inverters'] > 0 ? 'sol-badge--emerald' : 'sol-badge--rose' ?>" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;">
                            <?= $compat['total_inverters'] > 0 ? 'Đã có' : 'Bắt buộc' ?>
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.35rem;">
                        <?php if ($compat['total_inverters'] > 0): ?>
                            <strong style="color: var(--accent-cyan);"><?= $compat['total_inverters'] ?> bộ</strong> (Công suất: <strong style="color: #ffffff;"><?= $compat['total_inverter_kw'] ?> kW</strong> <?= $compat['has_hybrid_inverter'] ? '• Hybrid' : '• Hòa lưới' ?>)
                        <?php else: ?>
                            <span style="color: #fca5a5;">Chưa có biến tần</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.35;">
                        Biến đổi DC $\to$ AC. <strong>Cần ghép đôi với:</strong> Giàn pin tỷ lệ DC/AC từ 1.0 - 1.25.
                    </div>
                    <?php if ($compat['total_inverters'] === 0): ?>
                        <a href="index.php?page=products&category=bien-tan-inverter" style="display: inline-block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--accent-gold); text-decoration: underline;">+ Chọn Inverter tương thích</a>
                    <?php endif; ?>
                </div>

                <!-- 3. Tủ Điện & Khung Giá Cơ Khí -->
                <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid <?= ($compat['has_protection_box'] || $compat['has_mounting_rail']) ? 'rgba(16, 185, 129, 0.4)' : 'rgba(245, 158, 11, 0.4)' ?>; border-radius: var(--radius-sm);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 0.4rem;">
                            <span>🛡️</span> 3. Tủ Điện & Khung Giá
                        </span>
                        <span class="sol-badge <?= $compat['has_protection_box'] ? 'sol-badge--emerald' : 'sol-badge--amber' ?>" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;">
                            <?= $compat['has_protection_box'] ? 'Đã có tủ' : 'Cần an toàn' ?>
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.35rem;">
                        Tủ AC/DC: <?= $compat['has_protection_box'] ? '✅ Đã chọn' : '<span style="color: var(--accent-gold);">Chưa chọn</span>' ?> | Ray nhôm: <?= $compat['has_mounting_rail'] ? '✅ Đã chọn' : '<span style="color: var(--text-muted);">Tùy chọn</span>' ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.35;">
                        Bảo vệ chống sét SPD & chống quá tải. <strong>Cần ghép đôi với:</strong> Toàn bộ giàn pin & Inverter.
                    </div>
                    <?php if (!$compat['has_protection_box']): ?>
                        <a href="index.php?page=products&category=phu-kien-lap-dat" style="display: inline-block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--accent-gold); text-decoration: underline;">+ Chọn tủ điện đóng cắt</a>
                    <?php endif; ?>
                </div>

                <!-- 4. Pin Lưu Trữ Lithium (BESS) -->
                <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border: 1px solid <?= $compat['total_batteries'] > 0 ? ($compat['has_hybrid_inverter'] ? 'rgba(16, 185, 129, 0.4)' : 'rgba(244, 63, 94, 0.4)') : 'rgba(255,255,255,0.1)' ?>; border-radius: var(--radius-sm);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 0.4rem;">
                            <span>🔋</span> 4. Pin Lưu Trữ Lithium
                        </span>
                        <span class="sol-badge <?= $compat['total_batteries'] > 0 ? ($compat['has_hybrid_inverter'] ? 'sol-badge--emerald' : 'sol-badge--rose') : 'sol-badge--cyan' ?>" style="font-size: 0.7rem; padding: 0.1rem 0.4rem;">
                            <?= $compat['total_batteries'] > 0 ? ($compat['has_hybrid_inverter'] ? 'Khớp Hybrid' : 'Xung đột Inverter') : 'Hệ On-Grid' ?>
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.35rem;">
                        <?php if ($compat['total_batteries'] > 0): ?>
                            <strong style="color: #10b981;"><?= $compat['total_batteries'] ?> bộ ESS</strong> (~ <?= $compat['total_battery_kwh'] ?> kWh)
                        <?php else: ?>
                            <span style="color: var(--text-muted);">Không dùng lưu trữ (Hòa lưới trực tiếp)</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.35;">
                        Lưu trữ điện ban đêm. <strong>Cần ghép đôi với:</strong> Bắt buộc dùng <strong>Biến tần Inverter HYBRID</strong>.
                    </div>
                    <?php if ($compat['total_batteries'] === 0): ?>
                        <a href="index.php?page=products&category=pin-luu-tru-lithium" style="display: inline-block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--accent-gold); text-decoration: underline;">+ Thêm pin lưu trữ nếu cần</a>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Danh sách Cảnh báo & Đề xuất ghép đôi kỹ thuật -->
            <?php if (!empty($compat['rules'])): ?>
                <div style="display: flex; flex-direction: column; gap: 0.65rem; border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--accent-gold); text-transform: uppercase;">
                        📋 Đánh giá tương thích & Khuyến nghị kỹ thuật từ Kỹ Sư Solaris:
                    </div>
                    <?php foreach ($compat['rules'] as $rule): ?>
                        <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); background: <?= $rule['type'] === 'success' ? 'rgba(16, 185, 129, 0.1)' : ($rule['type'] === 'warning' ? 'rgba(245, 158, 11, 0.1)' : ($rule['type'] === 'danger' ? 'rgba(244, 63, 94, 0.1)' : 'rgba(56, 189, 248, 0.1)')) ?>; border-left: 3px solid <?= $rule['type'] === 'success' ? '#10b981' : ($rule['type'] === 'warning' ? '#f59e0b' : ($rule['type'] === 'danger' ? '#f43f5e' : '#38bdf8')) ?>;">
                            <span style="font-size: 1.1rem; line-height: 1;"><?= $rule['icon'] ?></span>
                            <div>
                                <strong style="color: #ffffff; font-size: 0.85rem; display: block; margin-bottom: 0.15rem;">
                                    <?= htmlspecialchars($rule['title']) ?>
                                </strong>
                                <span style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.4;">
                                    <?= htmlspecialchars($rule['detail']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ================================================================= -->
        <!-- DANH SÁCH THIẾT BỊ ĐÃ CHỌN TRONG KẾ HOẠCH DỰ ĐỊNH                -->
        <!-- ================================================================= -->
        <form method="post" class="sol-glass-card cart-table" id="cart-form">
            <input type="hidden" name="cart_action" value="demo_selected">
            <div class="cart-toolbar" style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border-subtle);">
                <label class="cart-select-all" style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #ffffff;">
                    <input type="checkbox" id="select-all"> <span>Chọn tất cả thiết bị</span>
                </label>
                <span id="selected-count" style="color: var(--accent-gold); font-weight: 600;">0 thiết bị được chọn</span>
            </div>

            <div class="cart-items" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem;">
                <?php foreach ($items as $item): $image = $resolveCartImage($item); ?>
                    <article class="cart-item" data-id="<?= (int) $item['id'] ?>" style="display: grid; grid-template-columns: auto 80px 1fr auto auto; align-items: center; gap: 1rem; padding: 0.85rem; background: rgba(14, 22, 38, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">
                        <label class="cart-check-wrap">
                            <input class="cart-check" type="checkbox" name="selected[]" value="<?= (int) $item['id'] ?>" checked style="width: 18px; height: 18px; accent-color: var(--accent-amber);">
                        </label>
                        <div class="cart-product-image" style="width: 72px; height: 72px; border-radius: var(--radius-sm); overflow: hidden; background: #080c14; display: flex; align-items: center; justify-content: center;">
                            <?php if ($image): ?>
                                <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <span style="font-size: 1.5rem;">☀️</span>
                            <?php endif; ?>
                        </div>
                        <div class="cart-product-info">
                            <strong style="color: #ffffff; font-size: 0.95rem;"><?= htmlspecialchars($item['name']) ?></strong>
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem; font-size: 0.775rem; flex-wrap: wrap;">
                                <span class="sol-badge sol-badge--amber" style="padding: 0.1rem 0.45rem;"><?= htmlspecialchars($item['product_code']) ?></span>
                                <span class="sol-badge sol-badge--emerald" style="padding: 0.1rem 0.45rem;"><?= htmlspecialchars($item['category_name']) ?></span>
                                <?php if (!empty($item['capacity_watts'])): ?>
                                    <span style="color: var(--accent-cyan); font-weight: 600;"><?= (int)$item['capacity_watts'] ?> W/món</span>
                                    <span style="color: var(--accent-gold); font-weight: 600;">(Tổng: <?= round(((int)$item['capacity_watts'] * (int)$item['quantity']) / 1000, 2) ?> kW)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="quantity-control" style="display: flex; align-items: center; gap: 0.25rem;">
                            <button type="button" class="quantity-button qty-minus" aria-label="Giảm số lượng" style="width: 28px; height: 28px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-subtle); border-radius: 4px; color: #fff; cursor: pointer;">−</button>
                            <input class="cart-quantity" type="number" name="quantity[<?= (int) $item['id'] ?>]" value="<?= (int) $item['quantity'] ?>" min="1" max="99" aria-label="Số lượng" style="width: 48px; height: 28px; text-align: center; background: #080c14; border: 1px solid var(--border-subtle); border-radius: 4px; color: #fff; font-weight: 600;">
                            <button type="button" class="quantity-button qty-plus" aria-label="Tăng số lượng" style="width: 28px; height: 28px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-subtle); border-radius: 4px; color: #fff; cursor: pointer;">+</button>
                        </div>

                        <button class="cart-remove" type="submit" name="cart_action" value="remove" formaction="index.php?page=cart" onclick="this.form.product_id.value='<?= (int) $item['id'] ?>'" aria-label="Xóa thiết bị" style="background: none; border: none; color: #f43f5e; font-size: 1.25rem; cursor: pointer; padding: 0.25rem 0.5rem;" title="Xóa khỏi kế hoạch">×</button>
                    </article>
                <?php endforeach; ?>
            </div>

            <input type="hidden" name="product_id">
            <footer class="cart-summary" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding: 1.25rem; border-top: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.6);">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">Thiết bị trong kế hoạch:</span>
                    <strong id="selected-total-items" style="color: var(--accent-gold); font-size: 1.15rem;">0 thiết bị được chọn</strong>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="sol-btn sol-btn--secondary" type="submit" formaction="index.php?page=cart" name="cart_action" value="update">
                        Cập nhật số lượng
                    </button>
                    <button class="sol-btn sol-btn--solar" type="submit">
                        ⚡ Đồng bộ sang Tư Vấn & Demo Thiết Kế →
                    </button>
                </div>
            </footer>
        </form>
    <?php endif; ?>
</section>

<script>
(() => {
    const form = document.getElementById('cart-form');
    if (!form) return;
    const all = document.getElementById('select-all');
    const checks = [...form.querySelectorAll('.cart-check')];
    const refresh = () => {
        let totalQty = 0;
        let selectedCount = 0;
        checks.forEach(check => {
            const row = check.closest('.cart-item');
            const input = row.querySelector('.cart-quantity');
            const qty = Math.max(1, Math.min(99, Number(input.value) || 1));
            input.value = qty;
            if (check.checked) {
                totalQty += qty;
                selectedCount++;
            }
        });
        const summary = document.getElementById('selected-total-items');
        if (summary) summary.textContent = selectedCount + ' loại (' + totalQty + ' món)';
        const count = document.getElementById('selected-count');
        if (count) count.textContent = selectedCount + ' thiết bị được chọn';
        if (all) all.checked = checks.length > 0 && selectedCount === checks.length;
    };
    if (all) all.addEventListener('change', () => {
        checks.forEach(check => check.checked = all.checked);
        refresh();
    });
    checks.forEach(check => check.addEventListener('change', refresh));
    form.querySelectorAll('.cart-quantity').forEach(input => input.addEventListener('input', refresh));
    form.querySelectorAll('.qty-minus').forEach(button => button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('.cart-quantity');
        input.value = Math.max(1, (Number(input.value) || 1) - 1);
        refresh();
    }));
    form.querySelectorAll('.qty-plus').forEach(button => button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('.cart-quantity');
        input.value = Math.min(99, (Number(input.value) || 1) + 1);
        refresh();
    }));
    refresh();
})();
</script>
