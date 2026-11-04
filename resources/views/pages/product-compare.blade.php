<?php
declare(strict_types=1);
use App\Hooks\UseProductCsrf;
use App\Hooks\UseProductSpecifications;
$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="sol-container products-page products-compare-page">
    <header class="products-hero"><div><span class="products-eyebrow">SOLARIS / SO SÁNH THIẾT BỊ</span><h1>Chọn thiết bị qua từng thông số</h1><p>Đối chiếu sản phẩm cùng danh mục trước khi thêm vào kế hoạch.</p></div><a class="sol-btn sol-btn--outline" href="index.php?page=products">← Quay lại catalog</a></header>
    <?php if (!empty($viewData['error'])): ?><div class="products-notice" role="alert"><?= $e($viewData['error']) ?></div><?php else:
        $rows = ['Giá' => static fn (array $p): string => UseProductSpecifications::priceLabel($p), 'Công suất / dung lượng' => static fn (array $p): string => UseProductSpecifications::capacityLabel($p), 'Bảo hành' => static fn (array $p): string => $p['warranty_years'] . ' năm'];
        $specFields = UseProductSpecifications::fields();
        foreach ($specFields as $key => $field) {
            if (!UseProductSpecifications::appliesTo($field, $viewData['products'][0]['category_slug'])) continue;
            foreach ($viewData['products'] as $p) if (array_key_exists($key, UseProductSpecifications::decode($p['technical_specs']))) { $rows[$field['label']] = static fn (array $p): string => UseProductSpecifications::displayValue(UseProductSpecifications::decode($p['technical_specs'])[$key] ?? null); break; }
        }
    ?>
    <div class="products-compare-table sol-glass-card"><table><thead><tr><th>Thông tin</th><?php foreach ($viewData['products'] as $item): $image = UseProductSpecifications::imageUrl($item); ?><th><?php if ($image): ?><img class="compare-product-image" src="<?= $e($image) ?>" alt=""><?php endif; ?><a href="index.php?page=products&amp;product=<?= (int) $item['id'] ?>"><?= $e($item['name']) ?></a><small><?= $e($item['product_code']) ?></small></th><?php endforeach; ?></tr></thead><tbody>
        <?php foreach ($rows as $label => $value): ?><tr><th><?= $e($label) ?></th><?php foreach ($viewData['products'] as $item): ?><td><?= $e($value($item)) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        <tr><th>Thêm vào kế hoạch</th><?php foreach ($viewData['products'] as $item): ?><td><form method="post" action="index.php?page=products" data-add-to-plan><?= UseProductCsrf::field() ?><input type="hidden" name="cart_action" value="add"><input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>"><button class="sol-btn sol-btn--solar" type="submit">Thêm thiết bị</button></form></td><?php endforeach; ?></tr>
    </tbody></table></div>
    <?php endif; ?>
    <div class="products-feedback" role="status" data-products-feedback></div>
</section>
