<?php
use App\Modules\Member4_Installation\View as UI;

if (!isset($detail['billing'], $project, $viewData['actor'])
    || !in_array($viewData['actor']['role'], ['admin', 'technician'], true)) {
    return;
}
$materialBilling = $detail['billing'];
?>
<dialog id="tv4-material-dialog" class="tv4-material-dialog" aria-labelledby="tv4-material-title" data-auto-open="<?= ($_GET['material_popup'] ?? '') === '1' ? '1' : '0' ?>">
    <div class="tv4-material-heading">
        <h3 id="tv4-material-title">Phát sinh vật tư</h3>
        <?php UI::component('button', 'Đóng', ['variant' => 'outline', 'size' => 'sm'], ['data-material-close' => true]); ?>
    </div>
    <p><strong><?= UI::escape($project['contract_code']) ?></strong> · <?= UI::escape($project['project_name']) ?></p>
    <p>Vật tư bổ sung vào kế hoạch của dự án này sẽ được cộng vào chi phí phát sinh.</p>
    <?php if ($viewData['flash']): ?>
        <div class="tv4-notice" role="status"><?= UI::escape($viewData['flash']) ?></div>
    <?php endif; ?>
    <h4>Vật tư phát sinh hiện tại</h4>
    <?php if (!$materialBilling['materials']): ?><p class="tv4-muted">Chưa có vật tư phát sinh.</p><?php endif; ?>
    <?php foreach ($materialBilling['materials'] as $material): ?>
        <div class="tv4-request">
            <strong><?= UI::escape($material['product_name']) ?></strong>
            <p><?= (int) $material['quantity'] ?> × <?= number_format((float) $material['unit_price'], 0, ',', '.') ?> đ</p>
            <small><?= UI::escape($material['reason']) ?></small>
        </div>
    <?php endforeach; ?>
    <?php UI::pager($materialBilling['pager'], 'material_page', ['material_popup' => 1]); ?>
    <?php if (!$materialBilling['canAdd']): ?>
        <p class="tv4-notice"><?= $project['status'] === 'new' ? 'Dự án đang chờ phân công. Hãy phân công kỹ thuật viên và bắt đầu khảo sát để cập nhật vật tư.' : 'Hiện chưa thể cập nhật vật tư. Chỉ cập nhật khi khảo sát, thiết kế hoặc mua sắm đang làm/cần bổ sung.' ?></p>
    <?php elseif (!$materialBilling['products']): ?>
        <p class="tv4-notice">Danh mục chưa có vật tư đang kinh doanh. Cần thêm sản phẩm vào danh mục trước khi chọn vật tư phát sinh.</p>
    <?php else: ?>
        <form class="tv4-material-form" method="post" action="index.php?page=installations&amp;project=<?= (int) $project['id'] ?>">
            <?php UI::form('add_material'); ?>
            <?php UI::component('input', '', ['type' => 'search'], ['data-material-search' => true, 'placeholder' => 'Tìm tên vật tư...', 'aria-label' => 'Tìm tên vật tư', 'autocomplete' => 'off']); ?>
            <div class="tv4-material-selects">
                <select data-material-category aria-label="Lọc theo loại vật tư">
                    <option value="">Tất cả loại vật tư</option>
                    <?php foreach ($materialBilling['categories'] as $materialCategory): ?>
                        <option value="<?= (int) $materialCategory['id'] ?>"><?= UI::escape($materialCategory['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="product_id" required data-material-products aria-label="Chọn vật tư">
                    <option value="">Chọn vật tư</option>
                    <?php foreach ($materialBilling['products'] as $materialProduct): ?>
                        <option value="<?= (int) $materialProduct['id'] ?>" data-name="<?= UI::escape($materialProduct['name']) ?>" data-category="<?= (int) $materialProduct['category_id'] ?>"><?= UI::escape($materialProduct['name']) ?> — <?= number_format((float) $materialProduct['price'], 0, ',', '.') ?> đ</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="tv4-muted" data-material-count aria-live="polite"><?= count($materialBilling['products']) ?> vật tư</p>
            <?php UI::component('input', '', ['type' => 'number'], ['name' => 'quantity', 'aria-label' => 'Số lượng', 'placeholder' => 'Số lượng', 'min' => 1, 'max' => 10000, 'value' => 1, 'required' => true]); ?>
            <textarea name="material_reason" aria-label="Lý do phát sinh" placeholder="Lý do phát sinh" maxlength="5000" required></textarea>
            <small class="tv4-muted">Đơn giá theo danh mục · Vật tư dự toán, chưa xuất kho.</small>
            <?php UI::component('button', 'Cập nhật vật tư', ['type' => 'submit']); ?>
        </form>
    <?php endif; ?>
</dialog>
