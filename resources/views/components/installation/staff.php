<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;

/** @var array $viewData Dữ liệu trang lắp đặt, được truyền qua require từ detail.php. */
if (!isset($viewData['detail'], $viewData['availability'])) {
    http_response_code(404);
    return;
}

$project = $viewData['detail']['project'];
$availability = $viewData['availability'];
?>
<section class="sol-glass-card tv4-panel tv4-staff" id="assignment">
    <div class="tv4-list-heading">
        <div>
            <span class="tv4-eyebrow">PHÂN CÔNG ĐỘI NGŨ</span>
            <h2>Chọn kỹ thuật viên</h2>
        </div>
        <span>Lịch làm việc: T2–T6 · 8h/ngày</span>
    </div>
    <form method="get" class="tv4-calendar-filter">
        <input type="hidden" name="page" value="installations">
        <input type="hidden" name="project" value="<?= $project['id'] ?>">
        <label>
            Từ ngày
            <input type="date" name="from" value="<?= UI::escape($availability['start']) ?>" required>
        </label>
        <label>
            Đến ngày
            <input type="date" name="to" value="<?= UI::escape($availability['end']) ?>" required>
        </label>
        <?php UI::component('button', 'Xem mức rảnh', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?>
    </form>
    <p class="tv4-muted">
        % rảnh = thời gian còn lại / thời gian làm việc trong khoảng đang xem. Số dự án là toàn bộ dự án đang thực hiện. Lịch chưa phản ánh ngày nghỉ cá nhân; có thể phân công chồng lịch nếu vẫn đủ năng lực.
    </p>
    <form method="post" id="tv4-assignment-form" data-project="<?= $project['id'] ?>">
        <?php UI::form('assign'); ?>
        <input type="hidden" name="start_date" value="<?= UI::escape($availability['start']) ?>">
        <input type="hidden" name="end_date" value="<?= UI::escape($availability['end']) ?>">
        <div class="tv4-roster-scroll">
            <div class="tv4-roster">
                <div class="tv4-roster-head">
                    <strong>Nhân viên / tải công việc</strong>
                    <div>
                        <span><?= UI::escape($availability['start']) ?></span>
                        <span>Lịch phân công đang có</span>
                        <span><?= UI::escape($availability['end']) ?></span>
                    </div>
                </div>
                <?php $calendarDays = (int) Workflow::date($availability['start'])->diff(Workflow::date($availability['end']))->days + 1; ?>
                <?php foreach ($availability['technicians'] as $technician): ?>
                    <div class="tv4-roster-row">
                        <label class="tv4-person">
                            <input type="checkbox" name="technicians[]" value="<?= $technician['id'] ?>" data-person="<?= UI::escape($technician['full_name']) ?>">
                            <span>
                                <strong><?= UI::escape($technician['full_name']) ?></strong>
                                <small>
                                    <?= $technician['free_percent'] === null ? 'Không có ngày làm việc' : $technician['free_percent'] . '% rảnh' ?>
                                    ·
                                    <?= $technician['project_count'] ?>
                                    dự án
                                </small>
                            </span>
                        </label>
                        <div class="tv4-bookings">
                            <?php foreach ($technician['bookings'] as $booking):
                                $bookingStart = max($availability['start'], $booking['planned_start']);
                                $bookingEnd = min($availability['end'], $booking['planned_end']);
                                $offsetDays = (int) Workflow::date($availability['start'])->diff(Workflow::date($bookingStart))->days;
                                $bookingDays = (int) Workflow::date($bookingStart)->diff(Workflow::date($bookingEnd))->days + 1;
                            ?>
                                <div class="tv4-booking" style="margin-left:<?= round($offsetDays / $calendarDays * 100, 3) ?>%;width:<?= round($bookingDays / $calendarDays * 100, 3) ?>%" title="<?= UI::escape($booking['project_name'] . ' | ' . $booking['planned_start'] . ' → ' . $booking['planned_end'] . ' | ' . $booking['hours_per_day'] . 'h/ngày') ?>">
                                    <?= UI::escape($booking['contract_code']) ?>
                                    ·
                                    <?= UI::escape($booking['hours_per_day']) ?>
                                    h
                                </div>
                            <?php endforeach; ?>
                            <?php if (!$technician['bookings']): ?>
                                <span class="tv4-calendar-empty">Chưa có lịch trong khoảng này</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php UI::pager($availability['pager'], 'staff_page'); ?>
        <p id="tv4-selected" class="tv4-notice" aria-live="polite">Chưa chọn kỹ thuật viên.</p>
        <div class="tv4-actions">
            <label>
                Dự kiến mỗi người (giờ/ngày)
                <input type="number" name="hours_per_day" min="0.25" max="8" step="0.25" value="4" required>
            </label>
            <?php UI::component('button', 'Phân công & bắt đầu dự án', ['type' => 'submit']); ?>
        </div>
        <p class="tv4-muted">
            Khoảng ngày trên sẽ dùng làm lịch dự án, tối thiểu 6 ngày làm việc. Sáu giai đoạn được chia đều trên ngày làm việc; nhóm giữ nguyên cho toàn bộ dự án.
        </p>
    </form>
</section>
