<?php
declare(strict_types=1);

use App\Hooks\UseProductSpecifications;
use App\Hooks\UseProductFilters;

$resolveImage = static function (array $product): ?string {
    $name = basename(trim((string) ($product['image_url'] ?? '')));
    if ($name === '') return null;
    $root = dirname(__DIR__, 3) . '/assets/products/';
    if (is_file($root . $name)) return 'assets/products/' . $name;
    $jpg = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
    return is_file($root . $jpg) ? 'assets/products/' . $jpg : null;
};
?>
<link rel="stylesheet" href="resources/css/products.css"><link rel="stylesheet" href="resources/css/products-polish.css"><link rel="stylesheet" href="resources/css/products-final.css"><link rel="stylesheet" href="resources/css/products-card-fix.css"><link rel="stylesheet" href="resources/css/products-filter.css"><link rel="stylesheet" href="resources/css/products-admin.css">
<link rel="stylesheet" href="resources/css/products-price.css">
<script src="resources/js/products-price.js" defer></script>
<script src="resources/js/products-image.js" defer></script>
<?php
$viewData = $productView;
if (($viewData['mode'] ?? '') === 'create') {
    include __DIR__ . '/product-create.blade.php';
    return;
}
if (array_key_exists('product', $viewData)):
    $product = $viewData['product'];
    $image = $product ? $resolveImage($product) : null;
    $detailCategories = $viewData['categories'] ?? [];
    $returnUrl = UseProductFilters::url(UseProductFilters::read($_GET)['filters'], ['p' => $_GET['p'] ?? 1]);
    ?>
    <section class="sol-container products-page">
        <?php if (!empty($viewData['flash'])): ?><div class="products-notice" role="status"><?= htmlspecialchars($viewData['flash'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if (!$product): ?><div class="products-empty sol-glass-card">Không tìm thấy sản phẩm.</div><?php else: ?>
            <article class="sol-glass-card product-detail">
                <div class="product-visual"><?php if ($image): ?><img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span>☀</span><?php endif; ?></div>
                <div>
                    <span class="sol-badge sol-badge--emerald"><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="product-code"><?= htmlspecialchars($product['product_code'], ENT_QUOTES, 'UTF-8') ?></p>
                    <strong class="product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?> VNĐ</strong>
                    <p>Bảo hành <?= (int) $product['warranty_years'] ?> năm · <?= htmlspecialchars(UseProductFilters::capacityLabel($product), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php
                    $detailSpecs = UseProductSpecifications::decode($product['technical_specs'] ?? null);
                    $publicSpecs = array_diff_key($detailSpecs, UseProductSpecifications::METADATA);
                    if (!empty($product['category_slug'])) {
                        foreach (UseProductSpecifications::fields() as $specKey => $specField) {
                            if (!UseProductSpecifications::appliesTo($specField, $product['category_slug'])) unset($publicSpecs[$specKey]);
                        }
                    }
                    $sourceNotes = [];
                    if (!empty($detailSpecs['demo_data'])) $sourceNotes[] = 'Thông tin sản phẩm đang dùng dữ liệu demo.';
                    $priceKind = $detailSpecs['price_type'] ?? '';
                    if ($priceKind === 'gia_mo_phong_vnd') $sourceNotes[] = 'Giá mô phỏng, không phải báo giá chính thức.';
                    elseif ($priceKind === 'gia_tham_khao_vnd') $sourceNotes[] = 'Giá tham khảo; giá cuối cùng được xác nhận khi tư vấn.';
                    elseif ($priceKind === 'lien_he_bao_gia') $sourceNotes[] = 'Vui lòng liên hệ để nhận báo giá.';
                    ?>
                    <h2>Thông số kỹ thuật</h2>
                    <?php if ($publicSpecs): ?>
                        <dl class="product-spec-list">
                            <?php foreach ($publicSpecs as $specKey => $specValue): ?>
                                <dt><?= htmlspecialchars(UseProductSpecifications::label((string) $specKey), ENT_QUOTES, 'UTF-8') ?></dt>
                                <dd><?= htmlspecialchars(UseProductSpecifications::displayValue($specValue), ENT_QUOTES, 'UTF-8') ?></dd>
                            <?php endforeach; ?>
                        </dl>
                    <?php else: ?><p class="admin-editor-hint">Thông số kỹ thuật đang được cập nhật.</p><?php endif; ?>
                    <?php if ($sourceNotes): ?><p class="product-data-note"><?= htmlspecialchars(implode(' ', $sourceNotes), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                </div>
            </article>
        <?php endif; ?>
        <?php if (!empty($viewData['isAdmin']) && $product): ?>
            <?php
            $editorProduct = $product;
            $editorCategories = $detailCategories;
            include dirname(__DIR__) . '/components/product-admin-form.blade.php';
            ?>
        <?php endif; ?>
        <div class="product-back"><a class="sol-btn sol-btn--outline" href="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">← Quay lại catalog</a></div>
    </section>
    <?php return; endif;

$filters = $viewData['filters'];
$filterOptions = $viewData['filterOptions'] ?? ['brands' => [], 'system_types' => []];
$query = static fn (array $changes = []): string => htmlspecialchars(UseProductFilters::url($filters, array_merge(['p' => $viewData['pager']['page']], $changes)), ENT_QUOTES, 'UTF-8');
$pager = $viewData['pager']; $visiblePages = [];
for ($pageNumber = 1; $pageNumber <= $pager['pages']; $pageNumber++) if ($pageNumber <= 4 || $pageNumber >= $pager['pages'] - 1 || abs($pageNumber - $pager['page']) <= 1) $visiblePages[] = $pageNumber;
$visiblePages = array_values(array_unique($visiblePages));
?>
<section class="sol-container products-page">
    <header class="products-hero"><div><span class="sol-badge sol-badge--emerald">Khu vực 2 · Catalog thiết bị</span><h1>Giải pháp & Sản phẩm Solar</h1><p>Thiết bị được chọn theo công suất, nhu cầu lưu trữ và điều kiện lắp đặt thực tế.</p></div><a class="sol-btn sol-btn--solar cart-page-button" href="index.php?page=cart">📋 Kế hoạch dự định <span><?= \App\Modules\Member2_Products\ProductController::cartCount() ?></span></a></header>
    <?php if ($viewData['error']): ?><div class="products-status" role="status"><span><?= htmlspecialchars($viewData['error'], ENT_QUOTES, 'UTF-8') ?></span><a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?page=products">Thử lại</a></div><?php endif; ?>
<?php if (!empty($viewData['flash'])): ?><div class="products-notice" role="status"><?= htmlspecialchars($viewData['flash'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if (!empty($viewData['isAdmin'])): ?>
    <div class="products-admin-toolbar"><a class="sol-btn sol-btn--solar" href="<?= $query() ?>&amp;action=create">＋ Thêm sản phẩm</a></div>
<?php endif; ?>
    <?php if (!empty($viewData['filterErrors'])): ?>
        <div class="products-notice" role="alert"><?php foreach ($viewData['filterErrors'] as $message): ?><p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="products-catalog-layout">
        <?php include dirname(__DIR__) . '/components/product-catalog-filter.blade.php'; ?>
        <div class="products-results">
            <div class="products-results-head">
                <div><strong><?= $pager['total'] ?> sản phẩm</strong><span>Trang <?= $pager['page'] ?> / <?= $pager['pages'] ?></span></div>
                <label class="products-sort">Sắp xếp
                    <select id="product-filter-sort" class="products-input" name="sort" form="product-filter-form">
                        <?php foreach (UseProductFilters::SORTS as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $filters['sort'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <?php
            $labels = ['q' => 'Tìm kiếm', 'category' => 'Danh mục', 'brand' => 'Hãng', 'system_type' => 'Loại hệ thống', 'min_price' => 'Giá từ', 'max_price' => 'Giá đến', 'min_capacity' => 'Công suất từ', 'max_capacity' => 'Công suất đến', 'min_power_kw' => 'Công suất từ', 'max_power_kw' => 'Công suất đến', 'min_energy' => 'Dung lượng từ', 'max_energy' => 'Dung lượng đến', 'min_warranty' => 'Bảo hành từ'];
            $categoryNames = array_column($viewData['categories'], 'name', 'slug');
            ?>
            <div class="products-active-filters" aria-label="Bộ lọc đang áp dụng">
                <?php foreach ($labels as $key => $label): if ($filters[$key] === '' || $filters[$key] === null) continue;
                    $value = $filters[$key];
                    if ($key === 'category') $value = $categoryNames[$value] ?? $value;
                    if (in_array($key, ['min_price', 'max_price'], true)) $value = number_format((float) $value, 0, ',', '.') . ' VNĐ';
                    if (in_array($key, ['min_capacity', 'max_capacity'], true)) $value .= ' W';
                    if (in_array($key, ['min_power_kw', 'max_power_kw'], true)) $value .= ' kW';
                    if (in_array($key, ['min_energy', 'max_energy'], true)) $value .= ' kWh';
                    if ($key === 'min_warranty') $value .= ' năm';
                    $chipText = $label . ': ' . $value;
                    ?>
                    <a class="products-filter-chip" href="<?= $query([$key => '', 'p' => 1]) ?>" aria-label="<?= htmlspecialchars('Bỏ lọc ' . $chipText, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($chipText, ENT_QUOTES, 'UTF-8') ?><span aria-hidden="true">×</span></a>
                <?php endforeach; ?>
            </div>
        <?php if (!$viewData['products']): ?><div class="sol-glass-card products-empty">Không tìm thấy sản phẩm phù hợp.</div><?php else: ?><div class="products-grid"><?php foreach ($viewData['products'] as $product): $image = $resolveImage($product); ?><article id="product-<?= (int) $product['id'] ?>" class="sol-glass-card product-card"><div class="product-visual"><?php if ($image): ?><img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"><?php else: ?><span>☀</span><?php endif; ?></div><div class="product-card-body"><span class="sol-badge sol-badge--emerald"><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?></span><h2><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h2><p class="product-code"><?= htmlspecialchars($product['product_code'], ENT_QUOTES, 'UTF-8') ?></p><div class="product-meta"><span><?= htmlspecialchars(UseProductFilters::capacityLabel($product), ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars((string) $product['warranty_years']) ?> năm</span></div><strong class="product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?> VNĐ</strong><div class="product-actions"><a class="sol-btn sol-btn--outline" href="<?= $query(['product' => (int) $product['id']]) ?>">Chi tiết</a><form method="post" action="<?= $query(['p' => $pager['page']]) ?>#product-<?= (int) $product['id'] ?>"><input type="hidden" name="cart_action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><button class="sol-btn sol-btn--solar" type="submit">Thêm vào kế hoạch</button></form></div></div></article><?php endforeach; ?></div><?php endif; ?>
        <nav class="products-pager" aria-label="Phân trang">
            <?php if ($pager['page'] > 1): ?><a href="<?= $query(['p' => $pager['page'] - 1]) ?>" rel="prev">← Trước</a><?php endif; ?>
            <?php $previous = 0; foreach ($visiblePages as $pageNumber): ?>
                <?php if ($previous && $pageNumber > $previous + 1): ?><span>…</span><?php endif; ?>
                <a class="<?= $pageNumber === $pager['page'] ? 'active' : '' ?>" <?= $pageNumber === $pager['page'] ? 'aria-current="page"' : '' ?> href="<?= $query(['p' => $pageNumber]) ?>"><?= $pageNumber ?></a>
                <?php $previous = $pageNumber; ?>
            <?php endforeach; ?>
            <?php if ($pager['page'] < $pager['pages']): ?><a href="<?= $query(['p' => $pager['page'] + 1]) ?>" rel="next">Sau →</a><?php endif; ?>
        </nav>
        </div>
    </div>
</section>
<script src="resources/js/products-filter.js" defer></script>
<script>
(() => {
    const key = 'solaris-products-return-position';
    document.querySelectorAll('form[action*="#product-"]').forEach(form => {
        form.addEventListener('submit', () => {
            const id = form.querySelector('input[name="product_id"]')?.value;
            if (id) sessionStorage.setItem(key, JSON.stringify({ id, top: window.scrollY }));
        });
    });
    const saved = sessionStorage.getItem(key);
    if (!saved) return;
    try {
        const position = JSON.parse(saved);
        const product = document.getElementById('product-' + position.id);
        if (product) {
            window.setTimeout(() => {
                product.scrollIntoView({ behavior: 'instant', block: 'center' });
                sessionStorage.removeItem(key);
            }, 120);
        }
    } catch (error) {
        sessionStorage.removeItem(key);
    }
})();
</script>
<script>
(() => {
    const forms = document.querySelectorAll('form[action*="#product-"]');
    const count = document.querySelector('.cart-page-button span');
    forms.forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (!button || form.dataset.submitting === '1') return;
        form.dataset.submitting = '1';
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Đang thêm...';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
            if (!response.ok) throw new Error('Không thể thêm sản phẩm');
            if (count) count.textContent = String((Number.parseInt(count.textContent, 10) || 0) + 1);
            button.textContent = 'Đã thêm ✓';
            sessionStorage.removeItem('solaris-products-return-position');
            window.setTimeout(() => { button.textContent = originalText; button.disabled = false; form.dataset.submitting = '0'; }, 900);
        } catch (error) {
            button.textContent = 'Thử lại';
            button.disabled = false;
            form.dataset.submitting = '0';
        }
    }));
})();
</script>
