<?php
declare(strict_types=1);
use App\Hooks\UseProductCsrf;
use App\Hooks\UseProductSpecifications;
$cardImage = UseProductSpecifications::imageUrl($product);
$cardEscape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<article id="product-<?= (int) $product['id'] ?>" class="sol-glass-card product-card">
    <div class="product-visual"><?php if ($cardImage): ?><img src="<?= $cardEscape($cardImage) ?>" alt="<?= $cardEscape($product['name']) ?>" loading="lazy"><?php else: ?><span>☀</span><?php endif; ?></div>
    <div class="product-card-body">
        <span class="sol-badge sol-badge--emerald"><?= $cardEscape($product['category_name']) ?></span>
        <h2><?= $cardEscape($product['name']) ?></h2><p class="product-code"><?= $cardEscape($product['product_code']) ?></p>
        <div class="product-meta"><span><?= $cardEscape(UseProductSpecifications::capacityLabel($product)) ?></span><span><?= (int) $product['warranty_years'] ?> năm bảo hành</span></div>
        <strong class="product-price"><?= $cardEscape(UseProductSpecifications::priceLabel($product)) ?></strong>
        <button class="product-compare-toggle" type="button" data-compare-product="<?= $cardEscape(json_encode(['id' => (int) $product['id'], 'name' => $product['name'], 'category' => $product['category_slug']], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) ?>" aria-pressed="false">＋ So sánh sản phẩm</button>
        <div class="product-actions"><a class="sol-btn sol-btn--outline" href="<?= $query(['product' => (int) $product['id']]) ?>">Chi tiết</a><form method="post" action="<?= $query(['p' => $pager['page']]) ?>#product-<?= (int) $product['id'] ?>" data-add-to-plan><?= UseProductCsrf::field() ?><input type="hidden" name="cart_action" value="add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><button class="sol-btn sol-btn--solar" type="submit">Thêm vào kế hoạch</button></form></div>
    </div>
</article>
