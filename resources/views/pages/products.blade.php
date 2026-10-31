<?php
declare(strict_types=1);

$resolveImage = static function (array $product): ?string {
    $name = basename(trim((string) ($product['image_url'] ?? '')));
    if ($name === '') return null;
    $root = dirname(__DIR__, 3) . '/assets/products/';
    if (is_file($root . $name)) return 'assets/products/' . $name;
    $jpg = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
    return is_file($root . $jpg) ? 'assets/products/' . $jpg : null;
};
?>
<link rel="stylesheet" href="resources/css/products.css"><link rel="stylesheet" href="resources/css/products-polish.css"><link rel="stylesheet" href="resources/css/products-final.css"><link rel="stylesheet" href="resources/css/products-card-fix.css">
<?php
$viewData = $productView;
if (isset($viewData['product'])):
    $product = $viewData['product'];
    $image = $product ? $resolveImage($product) : null;
    ?>
    <section class="sol-container products-page">
        <?php if (!$product): ?><div class="products-empty sol-glass-card">Không tìm thấy sản phẩm.</div><?php else: ?>
            <article class="sol-glass-card product-detail">
                <div class="product-visual"><?php if ($image): ?><img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"><?php else: ?><span>☀</span><?php endif; ?></div>
                <div><span class="sol-badge sol-badge--emerald"><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?></span><h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1><p class="product-code"><?= htmlspecialchars($product['product_code'], ENT_QUOTES, 'UTF-8') ?></p><strong class="product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?> VNĐ</strong><p>Bảo hành <?= htmlspecialchars((string) $product['warranty_years']) ?> năm · <?= $product['capacity_watts'] ? htmlspecialchars((string) $product['capacity_watts']) . ' W' : 'Thiết bị phụ trợ' ?></p><h2>Thông số kỹ thuật</h2><pre><?= htmlspecialchars((string) ($product['technical_specs'] ?? 'Chưa cập nhật'), ENT_QUOTES, 'UTF-8') ?></pre></div>
            </article>
        <?php endif; ?>
        <div class="product-back"><a class="sol-btn sol-btn--outline" href="index.php?page=products">← Quay lại catalog</a></div>
    </section>
    <script>
    (() => { const raw = document.querySelector('.product-detail pre'); if (!raw) return; try { const specs = JSON.parse(raw.textContent || '{}'); const list = document.createElement('dl'); list.className = 'product-spec-list'; const label = key => key.replaceAll('_', ' ').replace(/\b\w/g, char => char.toUpperCase()); Object.entries(specs).forEach(([key, item]) => { const term = document.createElement('dt'); term.textContent = label(key); const detail = document.createElement('dd'); detail.textContent = typeof item === 'object' && item !== null ? JSON.stringify(item, null, 2) : String(item); list.append(term, detail); }); raw.replaceWith(list); } catch (error) { raw.classList.add('product-spec-raw'); } })();
    </script>
    <?php return; endif;

$filters = $viewData['filters'];
$query = static function (array $changes = []) use ($filters): string { $values = array_filter(array_merge($filters, $changes), static fn ($value): bool => $value !== null && $value !== ''); return htmlspecialchars('index.php?' . http_build_query(array_merge(['page' => 'products'], $values)), ENT_QUOTES, 'UTF-8'); };
$pager = $viewData['pager']; $visiblePages = [];
for ($pageNumber = 1; $pageNumber <= $pager['pages']; $pageNumber++) if ($pageNumber <= 4 || $pageNumber >= $pager['pages'] - 1 || abs($pageNumber - $pager['page']) <= 1) $visiblePages[] = $pageNumber;
$visiblePages = array_values(array_unique($visiblePages));
?>
<section class="sol-container products-page">
    <header class="products-hero"><div><span class="sol-badge sol-badge--emerald">Khu vực 2 · Catalog thiết bị</span><h1>Giải pháp & Sản phẩm Solar</h1><p>Thiết bị được chọn theo công suất, nhu cầu lưu trữ và điều kiện lắp đặt thực tế.</p></div><a class="sol-btn sol-btn--solar cart-page-button" href="index.php?page=cart">🛒 Giỏ hàng <span><?= \App\Modules\Member2_Products\ProductController::cartCount() ?></span></a></header>
    <?php if ($viewData['error']): ?><div class="products-status" role="status"><span><?= htmlspecialchars($viewData['error'], ENT_QUOTES, 'UTF-8') ?></span><a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?page=products">Thử lại</a></div><?php endif; ?>
    <form class="sol-glass-card products-filter" method="get"><input type="hidden" name="page" value="products"><label>Tìm kiếm<input class="products-input" type="search" name="q" value="<?= htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Tên hoặc mã sản phẩm"></label><label>Giá từ<input class="products-input" type="number" name="min_price" min="0" value="<?= $filters['min_price'] ?? '' ?>" placeholder="VNĐ"></label><label>Giá đến<input class="products-input" type="number" name="max_price" min="0" value="<?= $filters['max_price'] ?? '' ?>" placeholder="VNĐ"></label><button class="sol-btn sol-btn--secondary products-search-button" type="submit">Tìm kiếm <span>⌕</span></button><button class="sol-btn sol-btn--solar" type="submit">Lọc sản phẩm</button><a class="sol-btn sol-btn--outline" href="index.php?page=products">Xóa lọc</a></form>
    <div class="products-category-dropdown"><button type="button" class="products-category-trigger">Danh mục sản phẩm <span>⌄</span></button><div class="products-category-menu"><a href="<?= $query(['category' => '', 'p' => 1]) ?>">Tất cả danh mục</a><?php foreach ($viewData['categories'] as $category): ?><a class="<?= $filters['category'] === $category['slug'] ? 'active' : '' ?>" href="<?= $query(['category' => $category['slug'], 'p' => 1]) ?>"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?></div></div>
    <div class="products-results"><div class="products-results-head"><span><?= $pager['total'] ?> sản phẩm</span><span>Trang <?= $pager['page'] ?> / <?= $pager['pages'] ?></span></div>
        <?php if (!$viewData['products']): ?><div class="sol-glass-card products-empty">Không tìm thấy sản phẩm phù hợp.</div><?php else: ?><div class="products-grid"><?php foreach ($viewData['products'] as $product): $image = $resolveImage($product); ?><article id="product-<?= (int) $product['id'] ?>" class="sol-glass-card product-card"><div class="product-visual"><?php if ($image): ?><img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"><?php else: ?><span>☀</span><?php endif; ?></div><div class="product-card-body"><span class="sol-badge sol-badge--emerald"><?= htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8') ?></span><h2><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h2><p class="product-code"><?= htmlspecialchars($product['product_code'], ENT_QUOTES, 'UTF-8') ?></p><div class="product-meta"><span><?= $product['capacity_watts'] ? htmlspecialchars((string) $product['capacity_watts']) . ' W' : 'Thiết bị phụ trợ' ?></span><span><?= htmlspecialchars((string) $product['warranty_years']) ?> năm</span></div><strong class="product-price"><?= number_format((float) $product['price'], 0, ',', '.') ?> VNĐ</strong><div class="product-actions"><a class="sol-btn sol-btn--outline" href="<?= $query(['product' => (int) $product['id']]) ?>">Chi tiết</a><form method="post" action="index.php?page=products#product-<?= (int) $product['id'] ?>"><input type="hidden" name="cart_action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><button class="sol-btn sol-btn--solar" type="submit">Thêm vào giỏ</button></form></div></div></article><?php endforeach; ?></div><?php endif; ?>
        <nav class="products-pager" aria-label="Phân trang"><?php $previous = 0; foreach ($visiblePages as $pageNumber): ?><?php if ($previous && $pageNumber > $previous + 1): ?><span>…</span><?php endif; ?><a class="<?= $pageNumber === $pager['page'] ? 'active' : '' ?>" href="<?= $query(['p' => $pageNumber]) ?>"><?= $pageNumber ?></a><?php $previous = $pageNumber; endforeach; ?></nav>
    </div>
</section>
