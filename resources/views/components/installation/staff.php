<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;
$availability = $v['availability'];
?>
<section class="sol-glass-card tv4-panel tv4-staff" id="assignment">
    <div class="tv4-list-heading"><div><span class="tv4-eyebrow">PHÂN CÔNG ĐỘI NGŨ</span><h2>Chọn kỹ thuật viên</h2></div><span>Lịch làm việc: T2–T6 · 8h/ngày</span></div>
    <form method="get" class="tv4-calendar-filter"><input type="hidden" name="page" value="installations"><input type="hidden" name="project" value="<?= $p['id'] ?>"><label>Từ ngày<input type="date" name="from" value="<?= $e($availability['start']) ?>" required></label><label>Đến ngày<input type="date" name="to" value="<?= $e($availability['end']) ?>" required></label><?php UI::component('button', 'Xem mức rảnh', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?></form>
    <p class="tv4-muted">% rảnh = thời gian còn lại / thời gian làm việc trong khoảng đang xem. Số dự án là toàn bộ dự án đang thực hiện. Lịch chưa phản ánh ngày nghỉ cá nhân; có thể phân công chồng lịch nếu vẫn đủ năng lực.</p>
    <form method="post" id="tv4-assignment-form" data-project="<?= $p['id'] ?>">
        <?php $form('assign'); ?><input type="hidden" name="start_date" value="<?= $e($availability['start']) ?>"><input type="hidden" name="end_date" value="<?= $e($availability['end']) ?>">
        <div class="tv4-roster-scroll"><div class="tv4-roster">
            <div class="tv4-roster-head"><strong>Nhân viên / tải công việc</strong><div><span><?= $e($availability['start']) ?></span><span>Lịch phân công đang có</span><span><?= $e($availability['end']) ?></span></div></div>
            <?php $spanDays = (int) Workflow::date($availability['start'])->diff(Workflow::date($availability['end']))->days + 1; ?>
            <?php foreach ($availability['technicians'] as $tech): ?><div class="tv4-roster-row">
                <label class="tv4-person"><input type="checkbox" name="technicians[]" value="<?= $tech['id'] ?>" data-person="<?= $e($tech['full_name']) ?>"><span><strong><?= $e($tech['full_name']) ?></strong><small><?= $tech['free_percent'] === null ? 'Không có ngày làm việc' : $tech['free_percent'] . '% rảnh' ?> · <?= $tech['project_count'] ?> dự án</small></span></label>
                <div class="tv4-bookings"><?php foreach ($tech['bookings'] as $booking):
                    $a = max($availability['start'], $booking['planned_start']); $b = min($availability['end'], $booking['planned_end']);
                    $offset = (int) Workflow::date($availability['start'])->diff(Workflow::date($a))->days;
                    $length = (int) Workflow::date($a)->diff(Workflow::date($b))->days + 1;
                ?><div class="tv4-booking" style="margin-left:<?= round($offset / $spanDays * 100, 3) ?>%;width:<?= round($length / $spanDays * 100, 3) ?>%" title="<?= $e($booking['project_name'] . ' | ' . $booking['planned_start'] . ' → ' . $booking['planned_end'] . ' | ' . $booking['hours_per_day'] . 'h/ngày') ?>"><?= $e($booking['contract_code']) ?> · <?= $e($booking['hours_per_day']) ?>h</div><?php endforeach; ?>
                    <?php if (!$tech['bookings']): ?><span class="tv4-calendar-empty">Chưa có lịch trong khoảng này</span><?php endif; ?>
                </div>
            </div><?php endforeach; ?>
        </div></div>
        <?php UI::pager($availability['pager'], 'staff_page'); ?>
        <p id="tv4-selected" class="tv4-notice" aria-live="polite">Chưa chọn kỹ thuật viên.</p>
        <div class="tv4-actions"><label>Dự kiến mỗi người (giờ/ngày)<input type="number" name="hours_per_day" min="0.25" max="8" step="0.25" value="4" required></label><?php UI::component('button', 'Phân công & bắt đầu dự án', ['type' => 'submit']); ?></div>
        <p class="tv4-muted">Khoảng ngày trên sẽ dùng làm lịch dự án, tối thiểu 6 ngày làm việc. Sáu giai đoạn được chia đều trên ngày làm việc; nhóm giữ nguyên cho toàn bộ dự án.</p>
    </form>
</section>
