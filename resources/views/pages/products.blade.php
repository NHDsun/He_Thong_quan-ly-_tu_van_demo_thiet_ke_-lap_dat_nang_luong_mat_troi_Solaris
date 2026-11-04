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
<link rel="stylesheet" href="resources/css/products-workspace.css">
<script src="resources/js/products-catalog.js" defer></script>
<?php
$viewData = $productView;
if (in_array($viewData['mode'] ?? '', ['create', 'manage', 'compare'], true)) {
    include __DIR__ . '/product-' . $viewData['mode'] . '.blade.php';
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
                    <strong class="product-price"><?= htmlspecialchars(UseProductSpecifications::priceLabel($product), ENT_QUOTES, 'UTF-8') ?></strong>
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
<section class="sol-container products-page products-page--catalog">
    <header class="products-hero catalog-header">
        <div class="catalog-header-copy">
            <span class="products-eyebrow">SOLARIS / DANH MỤC THIẾT BỊ</span>
            <h1>Giải pháp & Sản phẩm Solar</h1>
            <p>Thiết bị được chọn theo công suất, nhu cầu lưu trữ và điều kiện lắp đặt thực tế.</p>
        </div>
        <div class="catalog-header-actions" role="group" aria-label="Thao tác sản phẩm và kế hoạch">
            <?php if (!empty($viewData['isAdmin'])): ?>
                <a class="sol-btn catalog-create-button" href="<?= $query() ?>&amp;action=create">
                    <svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                    Thêm sản phẩm
                </a>
            <?php endif; ?>
            <a class="sol-btn cart-page-button catalog-plan-button" href="index.php?page=cart" title="Xem các thiết bị đã chọn">
                <svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3" transform="translate(0 -1)"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="M8 11h8M8 15h5" stroke-linecap="round"/></svg>
                Kế hoạch dự định
                <span class="catalog-plan-count" aria-live="polite" aria-atomic="true"><?= \App\Modules\Member2_Products\ProductController::cartCount() ?></span>
                <svg class="catalog-ui-icon catalog-plan-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m9 6 6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </header>
    <?php if ($viewData['error']): ?><div class="products-status" role="status"><span><?= htmlspecialchars($viewData['error'], ENT_QUOTES, 'UTF-8') ?></span><a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?page=products">Thử lại</a></div><?php endif; ?>
<?php if (!empty($viewData['flash'])): ?><div class="products-notice" role="status"><?= htmlspecialchars($viewData['flash'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
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
        <?php if (!$viewData['products']): ?><div class="sol-glass-card products-empty"><strong>Không có sản phẩm phù hợp</strong><p>Thử bỏ bớt điều kiện hoặc mở rộng khoảng giá.</p></div><?php else: ?><div class="products-grid"><?php foreach ($viewData['products'] as $product) include dirname(__DIR__) . '/components/product-card.blade.php'; ?></div><?php endif; ?>
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
<div class="products-feedback" role="status" data-products-feedback></div><div class="products-compare-tray sol-glass-card" data-compare-tray hidden></div>
</section>
<script src="resources/js/products-filter.js" defer></script>
