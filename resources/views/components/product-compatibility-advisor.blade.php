<?php
declare(strict_types=1);

use App\Hooks\UseProductRecommendations;

$advisor = UseProductRecommendations::make($compat);
?>
<?php if ($advisor['actions'] || $advisor['successes']): ?>
<section class="product-compatibility-advisor" aria-labelledby="product-advisor-title">
    <header class="product-advisor-heading">
        <div><h2 id="product-advisor-title">📋 Đánh giá tương thích & Khuyến nghị kỹ thuật</h2><p>Xử lý các mục cần lưu ý trước, sau đó chọn thiết bị bổ sung.</p></div>
        <div class="product-advisor-counts" aria-label="Tổng hợp kết quả kiểm tra">
            <?php if ($advisor['counts']['danger']): ?><span class="product-advisor-count product-advisor-count--danger"><?= $advisor['counts']['danger'] ?> cần xử lý</span><?php endif; ?>
            <?php if ($advisor['counts']['warning']): ?><span class="product-advisor-count product-advisor-count--warning"><?= $advisor['counts']['warning'] ?> cần lưu ý</span><?php endif; ?>
            <?php if ($advisor['counts']['info'] + $advisor['counts']['neutral']): ?><span class="product-advisor-count"><?= $advisor['counts']['info'] + $advisor['counts']['neutral'] ?> đề xuất</span><?php endif; ?>
            <?php if ($advisor['counts']['success']): ?><span class="product-advisor-count product-advisor-count--success"><?= $advisor['counts']['success'] ?> đã đạt</span><?php endif; ?>
        </div>
    </header>
    <div class="product-advice-list">
        <?php foreach ($advisor['actions'] as $rule) include __DIR__ . '/product-recommendation-card.blade.php'; ?>
    </div>
    <?php if ($advisor['successes']): ?>
        <details class="product-advisor-successes" <?= !$advisor['actions'] ? 'open' : '' ?>>
            <summary><?= count($advisor['successes']) ?> mục đã đạt theo kiểm tra hiện có</summary>
            <div class="product-advice-list"><?php foreach ($advisor['successes'] as $rule) include __DIR__ . '/product-recommendation-card.blade.php'; ?></div>
        </details>
    <?php endif; ?>
</section>
<?php endif; ?>
