<?php
declare(strict_types=1);

use App\Hooks\UseProductFilters;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$inputValue = static fn (string $key): string => $escape(UseProductFilters::text($_GET[$key] ?? $filters[$key] ?? ''));
$advancedFilterCount = count(array_filter(UseProductFilters::NUMBERS, static fn (string $key): bool => ($filters[$key] ?? null) !== null));
if ($filters['system_type'] !== '') $advancedFilterCount++;
?>
<form id="product-filter-form" class="sol-glass-card products-filter products-filter-bar" method="get" action="index.php">
    <input type="hidden" name="page" value="products">
    <div class="products-filter-heading">
        <div class="catalog-filter-title">
            <span class="catalog-filter-symbol" aria-hidden="true"><svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h9m4 0h3M4 17h3m4 0h9" stroke-linecap="round"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/></svg></span>
            <div><h2>Bộ lọc sản phẩm</h2><p>Tìm đúng thiết bị cho cấu hình của bạn.</p></div>
        </div>
        <div class="catalog-filter-heading-actions">
            <?php if (!empty($viewData['isAdmin'])): ?><a class="catalog-manage-link" href="index.php?page=products&amp;action=manage">Quản lý sản phẩm <span aria-hidden="true">↗</span></a><?php endif; ?>
            <span class="products-filter-result-count"><strong><?= (int) $pager['total'] ?></strong> sản phẩm</span>
        </div>
    </div>
    <div class="catalog-filter-main">
    <label class="products-filter-wide">Tìm kiếm thiết bị
        <span class="catalog-search-field">
            <svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4" stroke-linecap="round"/></svg>
            <input class="products-input" type="search" name="q" value="<?= $escape($filters['q']) ?>" placeholder="Nhập tên hoặc mã sản phẩm...">
        </span>
    </label>
    <label class="products-filter-wide">Danh mục sản phẩm
        <select class="products-input" name="category" id="product-filter-category">
            <option value="">Tất cả danh mục</option>
            <?php foreach ($viewData['categories'] as $category): ?>
                <option value="<?= $escape($category['slug']) ?>" <?= $filters['category'] === $category['slug'] ? 'selected' : '' ?>><?= $escape($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="products-filter-wide">Thương hiệu
        <select class="products-input" name="brand" data-brands-by-category="<?= $escape(json_encode($filterOptions['brands_by_category'] ?? [], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>">
            <option value="">Tất cả thương hiệu</option>
            <?php if ($filters['brand'] !== '' && !in_array($filters['brand'], $filterOptions['brands'], true)): ?><option value="<?= $escape($filters['brand']) ?>" selected><?= $escape($filters['brand']) ?> · chưa có trong danh mục này</option><?php endif; ?>
            <?php foreach ($filterOptions['brands'] as $brand): ?>
                <option value="<?= $escape($brand) ?>" <?= $filters['brand'] === $brand ? 'selected' : '' ?>><?= $escape($brand) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    </div>
    <div class="catalog-filter-bottom">
    <details class="catalog-filter-advanced" <?= $advancedFilterCount > 0 || !empty($viewData['filterErrors']) ? 'open' : '' ?>>
        <summary><svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5h16l-6 7v6l-4 2v-8z" stroke-linejoin="round"/></svg><strong>Lọc nâng cao</strong><span class="catalog-advanced-description">Giá, công suất & bảo hành</span><?php if ($advancedFilterCount > 0): ?><span class="catalog-advanced-count"><?= $advancedFilterCount ?></span><?php endif; ?><svg class="catalog-ui-icon catalog-advanced-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
        <div class="catalog-filter-details">
    <fieldset class="products-filter-range catalog-price-range">
        <legend>Khoảng giá (VNĐ)</legend>
        <?php foreach (['min_price' => 'Giá từ', 'max_price' => 'Giá đến'] as $priceInputName => $priceInputLabel):
            $priceCategorySlug = $filters['category'];
            $priceInputValue = UseProductFilters::text($_GET[$priceInputName] ?? $filters[$priceInputName] ?? '');
            $priceInputRequired = false;
            $pricePlaceholder = 'Không giới hạn';
            ?>
            <label for="product-price-<?= $priceInputName ?>"><?= $priceInputLabel ?><?php include __DIR__ . '/product-price-input.blade.php'; ?></label>
        <?php endforeach; ?>
    </fieldset>
    <?php foreach (UseProductFilters::RANGES as $slug => [$minimum, $maximum, $label, $unit]): $enabled = $filters['category'] === $slug; ?>
        <fieldset class="products-filter-range" data-product-category="<?= $escape($slug) ?>" <?= $enabled ? '' : 'hidden disabled' ?>>
            <legend><?= $escape($label . ' (' . $unit . ')') ?></legend>
            <label>Từ<input class="products-input" type="number" name="<?= $minimum ?>" min="0" step="any" value="<?= $enabled ? $inputValue($minimum) : '' ?>" placeholder="<?= $unit ?>"></label>
            <label>Đến<input class="products-input" type="number" name="<?= $maximum ?>" min="0" step="any" value="<?= $enabled ? $inputValue($maximum) : '' ?>" placeholder="<?= $unit ?>"></label>
        </fieldset>
    <?php endforeach; ?>
    <fieldset class="products-filter-system" data-product-category="inverter" <?= $filters['category'] === 'inverter' ? '' : 'hidden disabled' ?>>
        <legend>Loại hệ thống</legend>
        <label>Chế độ vận hành
            <select class="products-input" name="system_type">
                <option value="">Tất cả loại</option>
                <?php foreach ($filterOptions['system_types'] as $type): ?>
                    <option value="<?= $escape($type) ?>" <?= $filters['system_type'] === $type ? 'selected' : '' ?>><?= $escape($type) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </fieldset>
    <fieldset class="catalog-filter-warranty"><legend>Bảo hành</legend><label>Số năm tối thiểu
        <input class="products-input" type="number" name="min_warranty" min="0" step="any" value="<?= $inputValue('min_warranty') ?>" placeholder="Không giới hạn">
    </label></fieldset>
    <p class="products-filter-hint" id="product-filter-hint" <?= isset(UseProductFilters::RANGES[$filters['category']]) ? 'hidden' : '' ?>>Chọn danh mục để lọc thêm theo công suất hoặc dung lượng thiết bị.</p>
        </div>
    </details>
    <div class="products-filter-actions">
        <a class="catalog-filter-reset" aria-label="Xóa tất cả bộ lọc" href="<?= $query(['q' => '', 'category' => '', 'brand' => '', 'system_type' => '', 'min_price' => null, 'max_price' => null, 'min_warranty' => null, 'p' => 1]) ?>"><svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 10a8 8 0 1 1 1.2 7M4 4v6h6" stroke-linecap="round" stroke-linejoin="round"/></svg>Đặt lại</a>
        <button class="sol-btn catalog-filter-submit" type="submit"><svg class="catalog-ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4" stroke-linecap="round"/></svg>Áp dụng bộ lọc</button>
    </div>
    </div>
</form>
