<?php
declare(strict_types=1);

use App\Hooks\UseProductFilters;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$inputValue = static fn (string $key): string => $escape(UseProductFilters::text($_GET[$key] ?? $filters[$key] ?? ''));
?>
<form id="product-filter-form" class="sol-glass-card products-filter products-filter-bar" method="get" action="index.php">
    <input type="hidden" name="page" value="products">
    <h2>Bộ lọc thiết bị</h2>
    <label class="products-filter-wide">Tìm kiếm thiết bị
        <input class="products-input" type="search" name="q" value="<?= $escape($filters['q']) ?>" placeholder="Tên hoặc mã thiết bị...">
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
        <select class="products-input" name="brand">
            <option value="">Tất cả thương hiệu</option>
            <?php foreach ($filterOptions['brands'] as $brand): ?>
                <option value="<?= $escape($brand) ?>" <?= $filters['brand'] === $brand ? 'selected' : '' ?>><?= $escape($brand) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <fieldset class="products-filter-range">
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
        <label>Loại hệ thống
            <select class="products-input" name="system_type">
                <option value="">Tất cả loại</option>
                <?php foreach ($filterOptions['system_types'] as $type): ?>
                    <option value="<?= $escape($type) ?>" <?= $filters['system_type'] === $type ? 'selected' : '' ?>><?= $escape($type) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </fieldset>
    <p class="products-filter-hint" id="product-filter-hint" <?= isset(UseProductFilters::RANGES[$filters['category']]) ? 'hidden' : '' ?>>Chọn tấm pin, inverter hoặc pin lưu trữ để lọc theo công suất / dung lượng.</p>
    <label>Bảo hành tối thiểu (năm)
        <input class="products-input" type="number" name="min_warranty" min="0" step="any" value="<?= $inputValue('min_warranty') ?>" placeholder="Không giới hạn">
    </label>
    <div class="products-filter-actions">
        <button class="sol-btn sol-btn--solar" type="submit">Áp dụng bộ lọc</button>
        <a class="sol-btn sol-btn--outline" href="<?= $query(['q' => '', 'category' => '', 'brand' => '', 'system_type' => '', 'min_price' => null, 'max_price' => null, 'min_warranty' => null, 'p' => 1]) ?>">Xóa tất cả bộ lọc</a>
    </div>
</form>
