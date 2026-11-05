<?php
declare(strict_types=1);

use App\Hooks\UseProductFilters;

$returnUrl = UseProductFilters::url(UseProductFilters::read($_GET)['filters'], ['p' => $_GET['p'] ?? 1]);
?>
<section class="sol-container products-page products-create-page">
    <header class="products-hero">
        <div><span class="sol-badge sol-badge--amber">Quản trị sản phẩm</span><h1>Thêm sản phẩm</h1><p>Chọn danh mục để nhập đúng thông số kỹ thuật của thiết bị.</p></div>
        <a class="sol-btn sol-btn--outline" href="<?= htmlspecialchars($returnUrl, ENT_QUOTES, 'UTF-8') ?>">← Quay lại sản phẩm</a>
    </header>
    <?php if (!empty($viewData['flash'])): ?><div class="products-notice" role="status"><?= htmlspecialchars($viewData['flash'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($viewData['error'])): ?><div class="products-notice" role="alert"><?= htmlspecialchars($viewData['error'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($viewData['allowed']) && !empty($viewData['categories'])):
        $editorProduct = [];
        $editorCategories = $viewData['categories'];
        $selectedCategory = UseProductFilters::read($_GET)['filters']['category'];
        foreach ($editorCategories as $category) {
            if ($category['slug'] === $selectedCategory) $editorProduct['category_id'] = $category['id'];
        }
        include dirname(__DIR__) . '/components/product-admin-form.blade.php';
    endif; ?>
</section>
