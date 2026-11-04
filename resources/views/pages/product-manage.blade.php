<?php
declare(strict_types=1);
use App\Hooks\UseProductCsrf;
use App\Hooks\UseProductSpecifications;
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$managementFilters = $viewData['filters'] ?? ['q' => '', 'category' => '', 'visibility' => 'all'];
$managementUrl = static fn (array $changes = []): string => $e('index.php?' . http_build_query(array_merge(['page' => 'products', 'action' => 'manage'], $managementFilters, ['p' => $viewData['pager']['page'] ?? 1], $changes)));
?>
<section class="sol-container products-page products-management-page">
    <header class="products-hero"><div><span class="products-eyebrow">SOLARIS / QUẢN TRỊ</span><h1>Quản lý sản phẩm</h1><p>Theo dõi danh mục, chỉnh sửa thông tin và khôi phục thiết bị đã ẩn.</p></div><div class="products-hero-actions"><a class="sol-btn sol-btn--outline" href="index.php?page=products">Xem catalog</a><?php if (!empty($viewData['allowed'])): ?><a class="sol-btn sol-btn--solar" href="index.php?page=products&amp;action=create">＋ Thêm sản phẩm</a><?php endif; ?></div></header>
    <?php if (!empty($viewData['flash'])): ?><div class="products-notice" role="status"><?= $e($viewData['flash']) ?></div><?php endif; ?>
    <?php if (!empty($viewData['error'])): ?><div class="products-notice" role="alert"><?= $e($viewData['error']) ?></div><?php endif; ?>
    <?php if (empty($viewData['allowed'])) return; ?>
    <div class="products-management-stats"><?php foreach (['total' => 'Tất cả sản phẩm', 'active' => 'Đang hiển thị', 'hidden' => 'Đã ẩn'] as $key => $label): ?><div class="sol-glass-card"><span><?= $label ?></span><strong><?= (int) $viewData['counts'][$key] ?></strong></div><?php endforeach; ?></div>
    <form class="products-management-filter sol-glass-card" method="get">
        <input type="hidden" name="page" value="products"><input type="hidden" name="action" value="manage">
        <label>Tên hoặc mã sản phẩm<input class="products-input" name="q" value="<?= $e($managementFilters['q']) ?>" placeholder="Tìm trong toàn bộ danh mục..."></label>
        <label>Danh mục<select class="products-input" name="category"><option value="">Tất cả danh mục</option><?php foreach ($viewData['categories'] as $category): ?><option value="<?= $e($category['slug']) ?>" <?= $managementFilters['category'] === $category['slug'] ? 'selected' : '' ?>><?= $e($category['name']) ?></option><?php endforeach; ?></select></label>
        <label>Trạng thái<select class="products-input" name="visibility"><?php foreach (['all' => 'Tất cả trạng thái', 'active' => 'Đang hiển thị', 'hidden' => 'Đã ẩn'] as $key => $label): ?><option value="<?= $key ?>" <?= $managementFilters['visibility'] === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label>
        <button class="sol-btn sol-btn--solar" type="submit">Lọc danh sách</button>
    </form>
    <div class="products-management-table sol-glass-card"><table><thead><tr><th>Sản phẩm</th><th>Danh mục</th><th>Đơn giá</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
        <?php foreach ($viewData['products'] as $item): $image = UseProductSpecifications::imageUrl($item); ?>
        <tr><td><div class="management-product"><?php if ($image): ?><img src="<?= $e($image) ?>" alt="" loading="lazy"><?php endif; ?><div><a href="index.php?page=products&amp;product=<?= (int) $item['id'] ?>"><?= $e($item['name']) ?></a><small><?= $e($item['product_code']) ?></small></div></div></td><td><?= $e($item['category_name']) ?></td><td><?= $e(UseProductSpecifications::priceLabel($item)) ?></td><td><span class="management-status <?= $item['is_active'] ? 'is-active' : 'is-hidden' ?>"><?= $item['is_active'] ? 'Đang hiển thị' : 'Đã ẩn' ?></span></td><td><div class="management-actions"><a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?page=products&amp;product=<?= (int) $item['id'] ?>">Chỉnh sửa</a><form method="post" action="<?= $managementUrl() ?>"><?= UseProductCsrf::field() ?><input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>"><button class="sol-btn sol-btn--outline sol-btn--sm" type="submit" name="cart_action" value="<?= $item['is_active'] ? 'admin_delete' : 'admin_restore' ?>" <?= $item['is_active'] ? 'onclick="return confirm(\'Ẩn sản phẩm này khỏi catalog?\')"' : '' ?>><?= $item['is_active'] ? 'Ẩn' : 'Khôi phục' ?></button></form></div></td></tr>
        <?php endforeach; ?>
        <?php if (!$viewData['products']): ?><tr><td colspan="5" class="products-empty">Không có sản phẩm phù hợp với bộ lọc.</td></tr><?php endif; ?>
    </tbody></table></div>
    <?php if (isset($viewData['pager'])): $managementPager = $viewData['pager']; ?><nav class="products-pager" aria-label="Phân trang quản trị"><?php if ($managementPager['page'] > 1): ?><a href="<?= $managementUrl(['p' => $managementPager['page'] - 1]) ?>">← Trước</a><?php endif; ?><span>Trang <?= $managementPager['page'] ?> / <?= $managementPager['pages'] ?></span><?php if ($managementPager['page'] < $managementPager['pages']): ?><a href="<?= $managementUrl(['p' => $managementPager['page'] + 1]) ?>">Sau →</a><?php endif; ?></nav><?php endif; ?>
</section>
