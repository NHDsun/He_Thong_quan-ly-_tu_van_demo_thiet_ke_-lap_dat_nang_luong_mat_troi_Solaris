<?php
use App\Modules\Member4_Installation\View as UI;

if (!isset($detail['billing'], $viewData['actor'])) {
    return;
}
$billing = $detail['billing'];
$money = $billing['summary'];
$isCustomer = $viewData['actor']['role'] === 'customer';
$formatMoney = static fn (int $cents): string => number_format($cents / 100, $cents % 100 ? 2 : 0, ',', '.') . ' đ';
?>
<section class="sol-glass-card tv4-panel tv4-billing">
    <h3>Chi phí & thanh toán dự án</h3>
    <div class="tv4-summary-grid">
        <div><span>Giá trị ban đầu</span><strong><?= $formatMoney($money['base']) ?></strong></div>
        <div><span>Vật tư phát sinh</span><strong><?= $formatMoney($money['extra']) ?></strong></div>
        <div><span>Tổng phải thanh toán</span><strong><?= $formatMoney($money['total']) ?></strong></div>
        <div><span>Đã thanh toán mô phỏng</span><strong><?= $formatMoney($money['paid']) ?></strong></div>
        <div><span>Còn phải trả</span><strong><?= $formatMoney($money['remaining']) ?></strong></div>
    </div>
    <p class="tv4-notice">Thanh toán mô phỏng phục vụ bài tập, không thu tiền thật.</p>
    <?php if ($isCustomer && in_array($project['status'], ['in_progress', 'completed'], true) && $money['remaining'] > 0): ?>
        <form method="post">
            <?php UI::form('pay_project'); ?>
            <input type="hidden" name="expected_remaining" value="<?= $money['remaining'] ?>">
            <label class="tv4-check"><input type="checkbox" name="confirm_simulation" value="1" required> Tôi xác nhận thanh toán mô phỏng <?= $formatMoney($money['remaining']) ?>.</label>
            <?php UI::component('button', 'Thanh toán mô phỏng', ['type' => 'submit']); ?>
        </form>
    <?php elseif ($money['remaining'] === 0): ?>
        <p>Đã thanh toán đủ chi phí hiện tại.</p>
    <?php endif; ?>
    <?php if ($billing['canPrint']): ?>
        <p><a class="sol-btn sol-btn--outline sol-btn--sm" target="_blank" rel="noopener" href="index.php?page=installations&amp;project=<?= (int) $project['id'] ?>&amp;invoice=1">In hóa đơn</a></p>
    <?php endif; ?>
    <h4>Vật tư phát sinh sau khảo sát</h4>
    <?php if (!$billing['materials']): ?><p class="tv4-muted">Chưa có vật tư phát sinh.</p><?php endif; ?>
    <?php foreach ($billing['materials'] as $material): ?>
        <article class="tv4-request">
            <strong><?= UI::escape($material['product_name']) ?></strong>
            <p><?= (int) $material['quantity'] ?> × <?= number_format((float) $material['unit_price'], 0, ',', '.') ?> đ = <?= number_format((float) $material['unit_price'] * (int) $material['quantity'], 0, ',', '.') ?> đ</p>
            <p><?= UI::escape($material['reason']) ?></p>
            <small><?= UI::escape($material['created_at']) ?></small>
        </article>
    <?php endforeach; ?>
    <?php UI::pager($billing['pager'], 'material_page'); ?>
    <h4>Lịch sử thanh toán mô phỏng</h4>
    <?php if (!$billing['payments']): ?><p class="tv4-muted">Chưa có thanh toán.</p><?php endif; ?>
    <?php foreach ($billing['payments'] as $payment): ?>
        <p><?= UI::escape($payment['payment_code']) ?> · <?= number_format((float) $payment['amount'], 0, ',', '.') ?> đ · <?= UI::escape($payment['created_at']) ?></p>
    <?php endforeach; ?>
    <?php UI::pager($billing['paymentPager'], 'payment_page'); ?>
</section>
