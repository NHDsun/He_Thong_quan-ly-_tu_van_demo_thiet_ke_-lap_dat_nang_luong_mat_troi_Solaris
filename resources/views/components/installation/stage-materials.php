<?php
use App\Modules\Member4_Installation\View as UI;

if (!isset($detail['billing']) || !$detail['billing']['materials']) {
    return;
}
?>
<section class="tv4-stage-materials" aria-label="Vật tư phát sinh của dự án">
    <h4>Vật tư phát sinh đã cập nhật</h4>
    <?php foreach ($detail['billing']['materials'] as $stageMaterial): ?>
        <div class="tv4-stage-material-row">
            <div>
                <strong><?= UI::escape($stageMaterial['product_name']) ?></strong>
                <p><?= (int) $stageMaterial['quantity'] ?> × <?= number_format((float) $stageMaterial['unit_price'], 0, ',', '.') ?> đ</p>
                <small><?= UI::escape($stageMaterial['reason']) ?> · <?= UI::escape($stageMaterial['created_at']) ?></small>
            </div>
            <strong><?= number_format((int) $stageMaterial['quantity'] * (float) $stageMaterial['unit_price'], 0, ',', '.') ?> đ</strong>
        </div>
    <?php endforeach; ?>
    <?php UI::pager($detail['billing']['pager'], 'material_page'); ?>
    <p class="tv4-stage-material-total">Tổng phát sinh: <strong><?= number_format($detail['billing']['summary']['extra'] / 100, 0, ',', '.') ?> đ</strong></p>
</section>
