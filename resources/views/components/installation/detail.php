<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;

/** @var array $viewData Dữ liệu được truyền khi installations.blade.php require file này. */
if (!isset($viewData['detail'], $viewData['actor'], $viewData['availability'])) {
    http_response_code(404);
    return;
}

$admin = $viewData['actor']['role'] === 'admin';
$detail = $viewData['detail'];
$project = $detail['project'];
$current = null;
$approvedStages = 0;
foreach ($detail['stages'] as $stage) {
    if ($stage['stage'] === $project['current_stage']) {
        $current = $stage;
    }
    if ($stage['status'] === 'approved') {
        $approvedStages++;
    }
}
$editable = $project['status'] === 'in_progress';

$hasPostpone = false;
foreach ($detail['requests'] as $request) {
    if ($request['request_type'] === 'postpone') {
        $hasPostpone = true;
        break;
    }
}

$documentsByStage = [];
foreach ($detail['documents'] as $document) {
    $documentsByStage[$document['stage_id']][] = $document;
}
$crewIds = [];
foreach ($detail['crew'] as $member) {
    $crewIds[] = (int) $member['technician_id'];
}
?>
<a class="tv4-back" href="index.php?page=installations">
    ← Danh sách dự án
</a>
<article class="sol-glass-card tv4-panel tv4-summary">
    <div>
        <span class="tv4-eyebrow"><?= UI::escape($project['contract_code']) ?></span>
        <h2><?= UI::escape($project['project_name']) ?></h2>
        <p><?= UI::escape($project['installation_address']) ?></p>
    </div>
    <?php UI::component('badge', UI::label($project['status']), ['variant' => $project['status'] === 'completed' ? 'emerald' : 'amber']); ?>
    <div class="tv4-summary-grid">
        <div>
            <span>Công suất</span>
            <strong><?= UI::escape($project['capacity_kwp']) ?> kWp</strong>
        </div>
        <div>
            <span>Giá trị hợp đồng</span>
            <strong><?= number_format((float) $project['total_amount'], 0, ',', '.') ?> đ</strong>
        </div>
        <div>
            <span>Lịch dự kiến</span>
            <strong><?= UI::escape($project['start_date'] ?? 'Chưa xếp lịch') ?> → <?= UI::escape($project['planned_end_date'] ?? '—') ?></strong>
        </div>
        <div>
            <span>Tiến độ đã duyệt</span>
            <strong><?= $approvedStages ?> /6 · <?= (int) round($approvedStages / 6 * 100) ?> %</strong>
        </div>
    </div>
    <?php if ($project['notes']): ?>
        <p><?= UI::escape($project['notes']) ?></p>
    <?php endif; ?>
    <?php if ($detail['crew']): ?>
        <div class="tv4-crew">
            <?php foreach ($detail['crew'] as $member): ?>
                <span><?= UI::escape($member['full_name']) ?> · <?= UI::escape($member['hours_per_day']) ?> h/ngày</span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($project['completed_at']): ?>
        <p>Hoàn tất lúc <?= UI::escape($project['completed_at']) ?></p>
    <?php endif; ?>
</article>
<ol class="tv4-steps" aria-label="Tiến độ sáu giai đoạn">
    <?php foreach ($detail['stages'] as $stage): ?>
        <li class="<?= $stage['status'] === 'approved' ? 'done' : ($stage['stage'] === $project['current_stage'] && $editable ? 'current' : '') ?>">
            <a href="#stage-<?= $stage['id'] ?>">
                <span><?= $stage['status'] === 'approved' ? '✓' : (int) $stage['position'] ?></span>
                <strong><?= UI::escape(Workflow::STAGES[$stage['stage']]) ?></strong>
                <small><?= UI::escape(UI::label($stage['status'])) ?></small>
            </a>
        </li>
    <?php endforeach; ?>
</ol>
<?php if ($admin && $project['status'] === 'new'): ?>
    <?php require __DIR__ . '/staff.php'; ?>
<?php endif; ?>
<div class="tv4-detail-grid">
    <div class="tv4-stage-list">
        <?php foreach ($detail['stages'] as $stage): $active = $editable && $stage['stage'] === $project['current_stage']; ?>
            <article class="sol-glass-card tv4-panel tv4-stage <?= $active ? 'tv4-stage--active' : '' ?>" id="stage-<?= $stage['id'] ?>">
                <div class="tv4-stage-heading">
                    <span class="tv4-stage-number"><?= (int) $stage['position'] ?></span>
                    <div>
                        <h3><?= UI::escape(Workflow::STAGES[$stage['stage']]) ?></h3>
                        <p><?= UI::escape($stage['planned_start'] ?? 'Chưa xếp lịch') ?> → <?= UI::escape($stage['planned_end'] ?? '—') ?></p>
                    </div>
                    <?php UI::component('badge', UI::label($stage['status']), ['variant' => $stage['status'] === 'approved' ? 'emerald' : 'amber']); ?>
                </div>
                <?php $stageDocuments = $documentsByStage[$stage['id']] ?? []; ?>
                <?php if ($stageDocuments): ?>
                    <div class="tv4-stage-docs">
                        <?php foreach ($stageDocuments as $document): ?>
                            <a class="tv4-evidence" href="index.php?page=installations&amp;document=<?= $document['id'] ?>">
                                <?php if (in_array($document['file_type'], ['image/jpeg','image/png'], true)): ?>
                                    <img loading="lazy" src="index.php?page=installations&amp;document=<?= $document['id'] ?>&amp;preview=1" alt="<?= UI::escape($document['file_name']) ?>">
                                <?php endif; ?>
                                <span><?= UI::escape($document['file_name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($stage['review_note']): ?>
                    <div class="tv4-notice">
                        Phản hồi quản lý:
                        <?= UI::escape($stage['review_note']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($stage['submitted_at']): ?>
                    <p class="tv4-muted">Gửi duyệt: <?= UI::escape($stage['submitted_at']) ?></p>
                <?php endif; ?>
                <?php if ($stage['completed_at']): ?>
                    <p class="tv4-muted">Hoàn thành: <?= UI::escape($stage['completed_at']) ?></p>
                <?php endif; ?>
                <?php if ($active && !$admin && in_array($stage['status'], ['in_progress','revision_required'], true)): ?>
                    <form method="post" enctype="multipart/form-data" class="tv4-upload">
                        <?php UI::form('upload'); ?>
                        <label>
                            Loại tài liệu
                            <select name="document_type">
                                <option value="stage_photo">
                                    Ảnh minh chứng giai đoạn
                                </option>
                                <option value="survey_photo">
                                    Ảnh khảo sát
                                </option>
                                <option value="technical_drawing">
                                    Bản vẽ kỹ thuật
                                </option>
                                <option value="procurement_invoice">
                                    Hóa đơn vật tư
                                </option>
                                <option value="installation_photo">
                                    Ảnh thi công
                                </option>
                                <option value="testing_photo">
                                    Ảnh kiểm thử
                                </option>
                                <option value="evn_permit">
                                    Hồ sơ EVN
                                </option>
                                <option value="handover_report">
                                    Biên bản bàn giao
                                </option>
                            </select>
                        </label>
                        <label>
                            Tài liệu (tối đa 5MB/file)
                            <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf,.dwg" required>
                        </label>
                        <?php UI::component('button', 'Tải minh chứng', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?>
                    </form>
                    <form method="post" class="tv4-submit-stage">
                        <?php UI::form('submit_stage'); ?>
                        <?php if ($hasPostpone): ?>
                            <div class="tv4-notice">
                                <strong>Đang có yêu cầu hoãn chờ xử lý.</strong>
                                <p>Khi giai đoạn được duyệt hoàn thành, yêu cầu hoãn sẽ được đóng.</p>
                                <label class="tv4-check">
                                    <input type="checkbox" name="acknowledge_postpone" value="1" required>
                                    Tôi đã tiếp tục công việc và hoàn thành giai đoạn.
                                </label>
                            </div>
                        <?php endif; ?>
                        <p class="tv4-muted">Cần ít nhất một ảnh JPG/PNG của giai đoạn để gửi duyệt.</p>
                        <?php UI::component('button', 'Yêu cầu duyệt giai đoạn', ['type' => 'submit']); ?>
                    </form>
                <?php elseif ($active && $admin && $stage['status'] === 'pending_approval'): ?>
                    <form method="post" class="tv4-review">
                        <?php UI::csrf(); ?>
                        <label>
                            Nhận xét (bắt buộc khi yêu cầu bổ sung)
                            <textarea name="review_note" maxlength="5000"></textarea>
                        </label>
                        <div class="tv4-actions">
                            <?php UI::component('button', 'Duyệt & mở bước tiếp theo', ['type' => 'submit'], ['name' => 'action', 'value' => 'approve_stage']); UI::component('button', 'Yêu cầu bổ sung', ['type' => 'submit', 'variant' => 'outline'], ['name' => 'action', 'value' => 'reject_stage']); ?>
                        </div>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <section class="sol-glass-card tv4-panel">
            <h3>Hồ sơ & ảnh minh chứng</h3>
            <div class="tv4-documents">
                <?php foreach ($detail['documents'] as $document): ?>
                    <a class="tv4-document" href="index.php?page=installations&amp;document=<?= $document['id'] ?>">
                        <?php if (in_array($document['file_type'], ['image/jpeg','image/png'], true)): ?>
                            <img loading="lazy" src="index.php?page=installations&amp;document=<?= $document['id'] ?>&amp;preview=1" alt="<?= UI::escape($document['file_name']) ?>">
                        <?php endif; ?>
                        <strong><?= UI::escape($document['file_name']) ?></strong>
                        <span><?= UI::escape($document['full_name']) ?> · <?= $document['file_size_kb'] ?> KB</span>
                        <small>
                            <?= UI::escape(UI::documentLabel($document['document_type'])) ?>
                            · Giai đoạn
                            <?= array_search((int) $document['stage_id'], array_map('intval', array_column($detail['stages'], 'id')), true) + 1 ?>
                        </small>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (!$detail['documents']): ?>
                <p class="tv4-muted">Chưa có minh chứng.</p>
            <?php endif; ?>
            <?php UI::pager($detail['docPager'], 'doc_page'); ?>
        </section>
        <section class="sol-glass-card tv4-panel">
            <h3>Nhật ký dự án</h3>
            <div class="tv4-logs">
                <?php foreach ($detail['logs'] as $log): ?>
                    <div>
                        <time><?= UI::escape($log['created_at']) ?></time>
                        <strong><?= UI::escape($log['full_name'] ?? 'Hệ thống') ?></strong>
                        <p><?= UI::escape(UI::actionLabel($log['action'])) ?> · <?= UI::escape(Workflow::STAGES[$log['stage']]) ?></p>
                        <p><?= UI::escape($log['note']) ?></p>
                        <?php if ($log['from_status'] || $log['to_status']): ?>
                            <small><?= UI::escape(UI::label($log['from_status'] ?? '')) ?> → <?= UI::escape(UI::label($log['to_status'] ?? '')) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php UI::pager($detail['logPager'], 'log_page'); ?>
        </section>
    </div>
    <aside class="tv4-side">
        <section class="sol-glass-card tv4-panel">
            <h3>
                Yêu cầu chờ xử lý
                <span class="tv4-count"><?= count($detail['requests']) ?></span>
            </h3>
            <?php if (!$detail['requests']): ?>
                <p class="tv4-muted">Không có yêu cầu đang chờ.</p>
            <?php endif; ?>
            <?php foreach ($detail['requests'] as $request): ?>
                <article class="tv4-request">
                    <h4><?= $request['request_type'] === 'postpone' ? 'Hoãn lịch giai đoạn' : 'Thay kỹ thuật viên' ?></h4>
                    <small># <?= $request['id'] ?> · <?= UI::escape($request['full_name']) ?> · <?= UI::escape($request['requested_at']) ?></small>
                    <p><?= UI::escape($request['reason']) ?></p>
                    <?php if ($request['request_type'] === 'postpone'): ?>
                        <p>Lịch đề xuất: <?= UI::escape($request['proposed_start']) ?> → <?= UI::escape($request['proposed_end']) ?></p>
                        <small>Các giai đoạn sau sẽ được dời theo ngày kết thúc mới.</small>
                    <?php else: ?>
                        <p><?= UI::escape($request['old_name']) ?> → <?= UI::escape($request['next_name']) ?></p>
                    <?php endif; ?>
                    <?php if ($admin && $editable): ?>
                        <form method="post">
                            <?php UI::csrf(); ?>
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <label>
                                Phản hồi
                                <textarea name="review_note" maxlength="5000"></textarea>
                            </label>
                            <div class="tv4-actions">
                                <?php UI::component('button', 'Duyệt', ['type' => 'submit', 'size' => 'sm'], ['name' => 'action', 'value' => 'approve_request']); UI::component('button', 'Từ chối', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm'], ['name' => 'action', 'value' => 'reject_request']); ?>
                            </div>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
        <?php if (!$admin && $editable && $current && in_array($current['status'], ['in_progress','revision_required'], true)): ?>
            <section class="sol-glass-card tv4-panel">
                <h3>Đề xuất hoãn lịch</h3>
                <form method="post">
                    <?php UI::form('postpone'); ?>
                    <label>
                        Bắt đầu mới
                        <input type="date" name="proposed_start" min="<?= UI::escape($current['planned_start']) ?>" value="<?= UI::escape($current['planned_start']) ?>" required>
                    </label>
                    <label>
                        Kết thúc mới
                        <input type="date" name="proposed_end" min="<?= UI::escape($current['planned_end']) ?>" value="<?= UI::escape($current['planned_end']) ?>" required>
                    </label>
                    <label>
                        Lý do
                        <textarea name="reason" required maxlength="5000" placeholder="Thời tiết, điều kiện công trường..."></textarea>
                    </label>
                    <?php UI::component('button', 'Gửi yêu cầu hoãn', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?>
                </form>
            </section>
            <section class="sol-glass-card tv4-panel">
                <h3>Đề xuất thay người</h3>
                <form method="post">
                    <?php UI::form('replacement'); ?>
                    <label>
                        Người cần thay
                        <select name="old_technician_id" required>
                            <?php foreach ($detail['crew'] as $member): ?>
                                <option value="<?= $member['technician_id'] ?>">
                                    <?= UI::escape($member['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Nhân viên thay thế
                        <select name="proposed_technician_id" required>
                            <option value="">
                                Chọn kỹ thuật viên
                            </option>
                            <?php foreach ($viewData['availability']['technicians'] as $technician): if (in_array((int) $technician['id'], $crewIds, true)) { continue; } ?>
                                <option value="<?= $technician['id'] ?>">
                                    <?= UI::escape($technician['full_name']) ?>
                                    ·
                                    <?= $technician['project_count'] ?>
                                    DA ·
                                    <?= $technician['free_percent'] === null ? '—' : $technician['free_percent'] . '% rảnh' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <?php UI::pager($viewData['availability']['pager'], 'staff_page'); ?>
                    <small class="tv4-muted">% rảnh theo lịch dự án · Tổng số dự án đang làm</small>
                    <label>
                        Lý do
                        <textarea name="reason" required maxlength="5000"></textarea>
                    </label>
                    <?php UI::component('button', 'Gửi yêu cầu thay người', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?>
                </form>
            </section>
        <?php endif; ?>
        <?php if ($detail['requestHistory']): ?>
            <section class="sol-glass-card tv4-panel">
                <h3>Lịch sử yêu cầu</h3>
                <?php foreach ($detail['requestHistory'] as $request): ?>
                    <article class="tv4-request">
                        <h4># <?= $request['id'] ?> · <?= $request['request_type'] === 'postpone' ? 'Hoãn lịch' : 'Thay nhân viên' ?></h4>
                        <?php UI::component('badge', UI::label($request['status']), ['variant' => $request['status'] === 'approved' ? 'emerald' : 'amber']); ?>
                        <p><?= UI::escape($request['reason']) ?></p>
                        <small>Gửi bởi <?= UI::escape($request['full_name']) ?> · <?= UI::escape($request['requested_at']) ?></small>
                        <?php if ($request['request_type'] === 'postpone'): ?>
                            <p><?= UI::escape($request['proposed_start']) ?> → <?= UI::escape($request['proposed_end']) ?></p>
                        <?php else: ?>
                            <p><?= UI::escape($request['old_name']) ?> → <?= UI::escape($request['next_name']) ?></p>
                        <?php endif; ?>
                        <p><?= UI::escape($request['review_note']) ?></p>
                        <small>Xử lý: <?= UI::escape($request['reviewer_name']) ?> · <?= UI::escape($request['reviewed_at']) ?></small>
                    </article>
                <?php endforeach; ?>
                <?php UI::pager($detail['requestPager'], 'request_page'); ?>
            </section>
        <?php endif; ?>
    </aside>
</div>
