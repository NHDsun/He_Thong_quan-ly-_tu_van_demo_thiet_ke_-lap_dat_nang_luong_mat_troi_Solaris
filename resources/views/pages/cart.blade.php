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
?>
<link rel="stylesheet" href="resources/css/products.css">
<link rel="stylesheet" href="resources/css/cart.css">
<link rel="stylesheet" href="resources/css/cart-grid.css">
<link rel="stylesheet" href="resources/css/cart-polish.css">

<section class="sol-container products-page cart-page">
    <header class="products-hero">
        <div>
            <span class="sol-badge sol-badge--emerald">Solaris · Kế Hoạch Dự Định</span>
            <h1>Danh Sách Thiết Bị Dự Định Lắp Đặt</h1>
            <p>Chọn các thiết bị trong kế hoạch, điều chỉnh số lượng và chuyển cấu hình sang phần Demo thiết kế & tính toán.</p>
        </div>
        <a class="sol-btn sol-btn--outline" href="index.php?page=products">← Tiếp tục chọn thiết bị</a>
    </header>

    <?php if (!$items): ?>
        <div class="sol-glass-card products-empty">
            <div>
                <strong style="font-size: 1.15rem; color: #ffffff;">Kế hoạch dự định chưa có thiết bị nào</strong>
                <p style="color: var(--text-secondary); margin: 0.5rem 0 1.25rem;">Hãy khám phá catalog để chọn các tấm pin, inverter và phụ kiện dự định lắp đặt.</p>
                <a class="sol-btn sol-btn--solar" href="index.php?page=products">Xem danh mục thiết bị</a>
            </div>
        </div>
    <?php else: ?>
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
                            <input class="cart-check" type="checkbox" name="selected[]" value="<?= (int) $item['id'] ?>" style="width: 18px; height: 18px; accent-color: var(--accent-amber);">
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
                            <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem; font-size: 0.775rem;">
                                <span class="sol-badge sol-badge--amber" style="padding: 0.1rem 0.45rem;"><?= htmlspecialchars($item['product_code']) ?></span>
                                <span class="sol-badge sol-badge--emerald" style="padding: 0.1rem 0.45rem;"><?= htmlspecialchars($item['category_name']) ?></span>
                                <?php if (!empty($item['capacity_watts'])): ?>
                                    <span style="color: var(--accent-cyan); font-weight: 600;"><?= (int)$item['capacity_watts'] ?> W</span>
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
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">Thiết bị trong kế hoạch dự định:</span>
                    <strong id="selected-total-items" style="color: var(--accent-gold); font-size: 1.15rem;">0 thiết bị được chọn</strong>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <button class="sol-btn sol-btn--secondary" type="submit" formaction="index.php?page=cart" name="cart_action" value="update">
                        Cập nhật số lượng
                    </button>
                    <button class="sol-btn sol-btn--solar" type="submit">
                        ⚡ Chuyển sang Demo Thiết Kế & Báo Giá →
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
