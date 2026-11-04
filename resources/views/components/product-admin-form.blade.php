<?php
declare(strict_types=1);

use App\Hooks\UseProductSpecifications;
use App\Hooks\UseProductCsrf;

$editorProduct = $editorProduct ?? [];
$editing = !empty($editorProduct['id']);
$editorSpecs = UseProductSpecifications::decode($editorProduct['technical_specs'] ?? null);
$editorInput = $viewData['formInput'] ?? null;
if (is_array($editorInput)
    && ($editorInput['cart_action'] ?? '') === ($editing ? 'admin_update' : 'admin_create')
    && (int) ($editorInput['product_id'] ?? 0) === (int) ($editorProduct['id'] ?? 0)) {
    foreach (['name', 'product_code', 'category_id', 'price', 'capacity_watts', 'warranty_years', 'image_url'] as $inputKey) {
        if (isset($editorInput[$inputKey]) && is_scalar($editorInput[$inputKey])) $editorProduct[$inputKey] = $editorInput[$inputKey];
    }
    foreach (['specs', 'spec_metadata'] as $inputGroup) {
        foreach ((array) ($editorInput[$inputGroup] ?? []) as $inputKey => $inputValue) {
            if (is_scalar($inputValue)) $editorSpecs[$inputKey] = $inputValue;
        }
    }
}
$editorFields = UseProductSpecifications::fields();
$editorCategoryId = (int) ($editorProduct['category_id'] ?? $editorCategories[0]['id'] ?? 0);
$editorCategorySlug = '';
$editorCategoryName = '';
foreach ($editorCategories as $editorCategory) {
    if ((int) $editorCategory['id'] === $editorCategoryId) {
        $editorCategorySlug = $editorCategory['slug'];
        $editorCategoryName = $editorCategory['name'];
    }
}
if ($editorCategorySlug === 'tam-pin' && !array_key_exists('power_w', $editorSpecs)) $editorSpecs['power_w'] = $editorProduct['capacity_watts'] ?? '';
if ($editorCategorySlug === 'inverter' && !array_key_exists('rated_kw', $editorSpecs)) $editorSpecs['rated_kw'] = isset($editorProduct['capacity_watts']) ? (float) $editorProduct['capacity_watts'] / 1000 : '';
foreach ($editorSpecs as $specKey => $specValue) {
    if (!isset($editorFields[$specKey]) && !isset(UseProductSpecifications::METADATA[$specKey]) && is_scalar($specValue)) {
        $editorFields[$specKey] = ['label' => UseProductSpecifications::label((string) $specKey)];
    }
}
$escapeEditor = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$editorErrors = $viewData['fieldErrors'] ?? [];
$editorError = static fn (string $key): string => isset($editorErrors[$key]) ? '<small class="admin-field-error" role="alert">' . $escapeEditor($editorErrors[$key]) . '</small>' : '';
$editorImageName = basename((string) ($editorProduct['image_url'] ?? ''));
$editorPreviewName = $editorImageName;
$editorImageRoot = dirname(__DIR__, 3) . '/assets/products/';
if ($editorPreviewName !== '' && !is_file($editorImageRoot . $editorPreviewName)) $editorPreviewName = pathinfo($editorPreviewName, PATHINFO_FILENAME) . '.jpg';
$editorImagePath = $editorImageName !== '' && is_file($editorImageRoot . $editorPreviewName) ? 'assets/products/' . rawurlencode($editorPreviewName) : '';
?>
<form class="admin-product-editor sol-glass-card" method="post" enctype="multipart/form-data" data-product-editor>
    <?= UseProductCsrf::field() ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
    <input type="hidden" name="cart_action" value="<?= $editing ? 'admin_update' : 'admin_create' ?>">
    <?php if ($editing): ?><input type="hidden" name="product_id" value="<?= (int) $editorProduct['id'] ?>"><?php endif; ?>
    <div class="admin-editor-heading"><div><span class="products-eyebrow"><?= $editing ? 'CẬP NHẬT THIẾT BỊ' : 'HỒ SƠ SẢN PHẨM MỚI' ?></span><h2>Thông tin sản phẩm</h2><p class="admin-editor-hint">Nhập thông tin cơ bản, chọn ảnh và hoàn thiện thông số theo danh mục.</p></div><span class="admin-editor-required-note">* Thông tin bắt buộc</span></div>
    <?php if ($editorErrors): ?><div class="admin-validation-summary" role="alert"><strong>Vui lòng kiểm tra lại thông tin</strong><ul><?php foreach ($editorErrors as $message): ?><li><?= $escapeEditor($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <fieldset class="admin-editor-basic">
        <legend><span class="admin-section-number">01</span> Thông tin cơ bản & hình ảnh</legend>
        <div class="admin-editor-grid">
            <label class="admin-editor-name">Tên sản phẩm *<input class="products-input" name="name" maxlength="255" required placeholder="Ví dụ: Inverter Deye Hybrid 5 kW" aria-invalid="<?= isset($editorErrors['name']) ? 'true' : 'false' ?>" value="<?= $escapeEditor($editorProduct['name'] ?? '') ?>"><?= $editorError('name') ?></label>
            <label>Mã sản phẩm *<input class="products-input" name="product_code" maxlength="50" required placeholder="Mã duy nhất, ví dụ INV-DEYE-5K" aria-invalid="<?= isset($editorErrors['product_code']) ? 'true' : 'false' ?>" value="<?= $escapeEditor($editorProduct['product_code'] ?? '') ?>"><?= $editorError('product_code') ?></label>
            <label>Danh mục *<select class="products-input" name="category_id" required><?php foreach ($editorCategories as $editorCategory): ?><option value="<?= (int) $editorCategory['id'] ?>" data-slug="<?= $escapeEditor($editorCategory['slug']) ?>" <?= $editorCategoryId === (int) $editorCategory['id'] ? 'selected' : '' ?>><?= $escapeEditor($editorCategory['name']) ?></option><?php endforeach; ?></select><?= $editorError('category_id') ?></label>
            <?php
            $priceCategorySlug = $editorCategorySlug;
            $priceInputName = 'price';
            $priceInputLabel = 'Giá sản phẩm (VNĐ)';
            $priceInputValue = $editorProduct['price'] ?? '';
            $priceInputRequired = true;
            $priceInputInvalid = isset($editorErrors['price']);
            $pricePlaceholder = 'Nhập giá sản phẩm';
            ?>
            <label for="product-price-price"><?= $priceInputLabel ?> *<?php include __DIR__ . '/product-price-input.blade.php'; ?><?= $editorError('price') ?></label>
            <label>Bảo hành (năm)<input class="products-input" type="number" name="warranty_years" min="0" max="100" step="1" value="<?= $escapeEditor($editorProduct['warranty_years'] ?? 5) ?>"><?= $editorError('warranty_years') ?></label>
            <div class="admin-editor-image admin-image-upload">
                <label for="product-image-file">Ảnh sản phẩm từ máy tính<input id="product-image-file" class="products-input" type="file" name="product_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png" aria-describedby="product-image-help product-image-status"></label>
                <input type="hidden" name="image_url" value="<?= $escapeEditor($editorImageName) ?>">
                <small id="product-image-help">JPG, JPEG hoặc PNG, tối đa 5 MB. Không chọn ảnh mới sẽ giữ ảnh hiện tại.</small>
                <?php if (is_array($editorInput)): ?><small>Nếu lần lưu trước bị lỗi, hãy chọn lại ảnh mới trước khi lưu.</small><?php endif; ?>
                <div class="admin-image-preview">
                    <img data-product-image-preview data-existing-src="<?= $escapeEditor($editorImagePath) ?>" <?= $editorImagePath !== '' ? 'src="' . $escapeEditor($editorImagePath) . '"' : 'hidden' ?> alt="Xem trước ảnh sản phẩm">
                    <span data-product-image-empty <?= $editorImagePath !== '' ? 'hidden' : '' ?>>Chưa có ảnh sản phẩm</span>
                </div>
                <div id="product-image-status" class="admin-image-status" role="status"></div>
                <button class="sol-btn sol-btn--outline sol-btn--sm" type="button" data-cancel-product-image hidden>Bỏ ảnh vừa chọn</button>
            </div>
        </div>
    </fieldset>
    <fieldset class="admin-editor-specifications">
        <legend data-editor-spec-title>Thông số kỹ thuật · <?= $escapeEditor($editorCategoryName) ?></legend>
        <p class="admin-editor-hint">Tấm pin: W · Inverter: kW · Pin lưu trữ: kWh. Thông số cần cho tính toán phải lớn hơn 0.</p>
        <div class="admin-editor-grid">
            <?php foreach ($editorFields as $specKey => $specField): $specValue = $editorSpecs[$specKey] ?? ''; $specVisible = UseProductSpecifications::appliesTo($specField, $editorCategorySlug); ?>
                <?php if (is_array($specValue)) continue; ?>
                <label data-spec-categories="<?= $escapeEditor($specField['categories'] ?? '') ?>" <?= $specVisible ? '' : 'hidden' ?>>
                    <?= $escapeEditor($specField['label']) ?>
                    <?php if (is_bool($specValue)): ?>
                        <select class="products-input" name="specs[<?= $escapeEditor($specKey) ?>]" <?= $specVisible ? '' : 'disabled' ?>><option value="1" <?= $specValue ? 'selected' : '' ?>>Có</option><option value="0" <?= !$specValue ? 'selected' : '' ?>>Không</option></select>
                    <?php else: ?>
                        <input class="products-input" name="specs[<?= $escapeEditor($specKey) ?>]" <?= $specVisible ? '' : 'disabled' ?> type="<?= !empty($specField['numeric']) ? 'number' : 'text' ?>" <?= !empty($specField['numeric']) ? 'min="0" step="any"' : 'maxlength="1000"' ?> value="<?= $escapeEditor($specValue) ?>" placeholder="<?= $escapeEditor($specField['placeholder'] ?? 'Chưa cập nhật') ?>">
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <datalist id="product-system-types"><option value="Hybrid"><option value="On-grid"><option value="Off-grid"></datalist>
    </fieldset>
    <details class="admin-editor-extra"><summary>Nguồn dữ liệu & cách trình bày giá, hình ảnh</summary><fieldset class="admin-editor-metadata">
        <legend>Nguồn dữ liệu, giá và hình ảnh</legend>
        <p class="admin-editor-hint">Chỉ chọn dữ liệu thực tế khi đã kiểm chứng. Các mục này không phải thông số kỹ thuật của thiết bị.</p>
        <div class="admin-editor-grid">
            <?php foreach (UseProductSpecifications::METADATA as $metaKey => $metaField):
                $metaValue = $editorSpecs[$metaKey] ?? '';
                $metaValue = is_bool($metaValue) ? ($metaValue ? '1' : '0') : (is_scalar($metaValue) ? (string) $metaValue : '');
                $metaOptions = $metaField['options'];
                if ($metaValue !== '' && !array_key_exists($metaValue, $metaOptions)) $metaOptions[$metaValue] = 'Giữ giá trị hiện tại: ' . $metaValue;
            ?>
                <label><?= $escapeEditor($metaField['label']) ?><select class="products-input" name="spec_metadata[<?= $escapeEditor($metaKey) ?>]"><?php foreach ($metaOptions as $optionValue => $optionLabel): ?><option value="<?= $escapeEditor($optionValue) ?>" <?= (string) $optionValue === $metaValue ? 'selected' : '' ?>><?= $escapeEditor($optionLabel) ?></option><?php endforeach; ?></select></label>
            <?php endforeach; ?>
        </div>
    </fieldset></details>
    <div class="admin-editor-actions">
        <a class="sol-btn sol-btn--outline" href="<?= $escapeEditor($returnUrl ?? 'index.php?page=products&action=manage') ?>">Hủy / Quay lại</a>
        <?php if ($editing): ?><button class="sol-btn sol-btn--outline" type="submit" name="cart_action" value="<?= !empty($editorProduct['is_active']) ? 'admin_delete' : 'admin_restore' ?>" formnovalidate onclick="return confirm('Thay đổi trạng thái hiển thị sản phẩm này?')"><?= !empty($editorProduct['is_active']) ? 'Ẩn sản phẩm' : 'Khôi phục sản phẩm' ?></button><?php endif; ?>
        <button class="sol-btn sol-btn--solar" type="submit"><?= $editing ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button>
    </div>
</form>
<script>
(() => {
    const form = document.querySelector('[data-product-editor]');
    if (!form) return;
    const category = form.elements.category_id;
    const type = form.querySelector('[name="specs[type]"]');
    const updateFields = () => {
        const slug = category.selectedOptions[0]?.dataset.slug || '';
        form.querySelector('[data-editor-spec-title]').textContent = 'Thông số kỹ thuật · ' + (category.selectedOptions[0]?.textContent || '');
        if (type) {
            if (slug === 'inverter') type.setAttribute('list', 'product-system-types');
            else type.removeAttribute('list');
            type.placeholder = slug === 'inverter' ? 'Hybrid, On-grid, Off-grid' : 'Ví dụ: CB DC 2P, SPD DC Type 2';
        }
        form.querySelectorAll('[data-spec-categories]').forEach(label => {
            const groups = label.dataset.specCategories.split(' ').filter(Boolean);
            const control = label.querySelector('input, select');
            const visible = !groups.length || groups.includes(slug);
            label.hidden = !visible;
            control.disabled = !visible;
            if (['specs[power_w]', 'specs[rated_kw]', 'specs[energy_kwh]'].includes(control.name)) {
                control.required = visible;
                control.min = '0.001';
            }
        });
    };
    category.addEventListener('change', updateFields);
    updateFields();
})();
</script>
