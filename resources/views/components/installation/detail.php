<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;
$d = $v['detail'];
$p = $d['project'];
$current = null;
$approved = 0;
foreach ($d['stages'] as $s) {
    if ($s['stage'] === $p['current_stage']) { $current = $s; }
    $approved += (int) ($s['status'] === 'approved');
}
$editable = $p['status'] === 'in_progress';
$hasPostpone = (bool) array_filter($d['requests'], fn($r) => $r['request_type'] === 'postpone');
?>
<a class="tv4-back" href="index.php?page=installations">← Danh sách dự án</a>
<article class="sol-glass-card tv4-panel tv4-summary">
    <div><span class="tv4-eyebrow"><?= $e($p['contract_code']) ?></span><h2><?= $e($p['project_name']) ?></h2><p><?= $e($p['installation_address']) ?></p></div>
    <?php UI::component('badge', UI::label($p['status']), ['variant' => $p['status'] === 'completed' ? 'emerald' : 'amber']); ?>
    <div class="tv4-summary-grid"><div><span>Công suất</span><strong><?= $e($p['capacity_kwp']) ?> kWp</strong></div><div><span>Giá trị hợp đồng</span><strong><?= number_format((float) $p['total_amount'], 0, ',', '.') ?> đ</strong></div><div><span>Lịch dự kiến</span><strong><?= $e($p['start_date'] ?? 'Chưa xếp lịch') ?> → <?= $e($p['planned_end_date'] ?? '—') ?></strong></div><div><span>Tiến độ đã duyệt</span><strong><?= $approved ?>/6 · <?= (int) round($approved / 6 * 100) ?>%</strong></div></div>
    <?php if ($p['notes']): ?><p><?= $e($p['notes']) ?></p><?php endif; ?>
    <?php if ($d['crew']): ?><div class="tv4-crew"><?php foreach ($d['crew'] as $member): ?><span><?= $e($member['full_name']) ?> · <?= $e($member['hours_per_day']) ?>h/ngày</span><?php endforeach; ?></div><?php endif; ?>
    <?php if ($p['completed_at']): ?><p>Hoàn tất lúc <?= $e($p['completed_at']) ?></p><?php endif; ?>
</article>
<ol class="tv4-steps" aria-label="Tiến độ sáu giai đoạn">
    <?php foreach ($d['stages'] as $s): ?><li class="<?= $s['status'] === 'approved' ? 'done' : ($s['stage'] === $p['current_stage'] && $editable ? 'current' : '') ?>"><a href="#stage-<?= $s['id'] ?>"><span><?= $s['status'] === 'approved' ? '✓' : (int) $s['position'] ?></span><strong><?= $e(Workflow::STAGES[$s['stage']]) ?></strong><small><?= $e(UI::label($s['status'])) ?></small></a></li><?php endforeach; ?>
</ol>
<?php if ($admin && $p['status'] === 'new'): ?>
    <?php require __DIR__ . '/staff.php'; ?>
<?php endif; ?>
<div class="tv4-detail-grid">
    <div class="tv4-stage-list">
        <?php foreach ($d['stages'] as $s): $active = $editable && $s['stage'] === $p['current_stage']; ?>
            <article class="sol-glass-card tv4-panel tv4-stage <?= $active ? 'tv4-stage--active' : '' ?>" id="stage-<?= $s['id'] ?>">
                <div class="tv4-stage-heading"><span class="tv4-stage-number"><?= (int) $s['position'] ?></span><div><h3><?= $e(Workflow::STAGES[$s['stage']]) ?></h3><p><?= $e($s['planned_start'] ?? 'Chưa xếp lịch') ?> → <?= $e($s['planned_end'] ?? '—') ?></p></div><?php UI::component('badge', UI::label($s['status']), ['variant' => $s['status'] === 'approved' ? 'emerald' : 'amber']); ?></div>
                <?php $stageDocuments = array_filter($d['documents'], fn($doc) => (int) $doc['stage_id'] === (int) $s['id']); ?>
                <?php if ($stageDocuments): ?><div class="tv4-stage-docs"><?php foreach ($stageDocuments as $doc): ?><a class="tv4-evidence" href="index.php?page=installations&amp;document=<?= $doc['id'] ?>"><?php if (in_array($doc['file_type'], ['image/jpeg','image/png'], true)): ?><img loading="lazy" src="index.php?page=installations&amp;document=<?= $doc['id'] ?>&amp;preview=1" alt="<?= $e($doc['file_name']) ?>"><?php endif; ?><span><?= $e($doc['file_name']) ?></span></a><?php endforeach; ?></div><?php endif; ?>
                <?php if ($s['review_note']): ?><div class="tv4-notice">Phản hồi quản lý: <?= $e($s['review_note']) ?></div><?php endif; ?>
                <?php if ($s['submitted_at']): ?><p class="tv4-muted">Gửi duyệt: <?= $e($s['submitted_at']) ?></p><?php endif; ?>
                <?php if ($s['completed_at']): ?><p class="tv4-muted">Hoàn thành: <?= $e($s['completed_at']) ?></p><?php endif; ?>
                <?php if ($active && !$admin && in_array($s['status'], ['in_progress','revision_required'], true)): ?>
                    <form method="post" enctype="multipart/form-data" class="tv4-upload"><?php $form('upload'); ?>
                        <label>Loại tài liệu<select name="document_type"><option value="stage_photo">Ảnh minh chứng giai đoạn</option><option value="survey_photo">Ảnh khảo sát</option><option value="technical_drawing">Bản vẽ kỹ thuật</option><option value="procurement_invoice">Hóa đơn vật tư</option><option value="installation_photo">Ảnh thi công</option><option value="testing_photo">Ảnh kiểm thử</option><option value="evn_permit">Hồ sơ EVN</option><option value="handover_report">Biên bản bàn giao</option></select></label>
                        <label>Tài liệu (tối đa 5MB/file)<input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf,.dwg" required></label>
                        <?php UI::component('button', 'Tải minh chứng', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?>
                    </form>
                    <form method="post" class="tv4-submit-stage"><?php $form('submit_stage'); ?>
                        <?php if ($hasPostpone): ?><div class="tv4-notice"><strong>Đang có yêu cầu hoãn chờ xử lý.</strong><p>Khi giai đoạn được duyệt hoàn thành, yêu cầu hoãn sẽ được đóng.</p><label class="tv4-check"><input type="checkbox" name="acknowledge_postpone" value="1" required> Tôi đã tiếp tục công việc và hoàn thành giai đoạn.</label></div><?php endif; ?>
                        <p class="tv4-muted">Cần ít nhất một ảnh JPG/PNG của giai đoạn để gửi duyệt.</p>
                        <?php UI::component('button', 'Yêu cầu duyệt giai đoạn', ['type' => 'submit']); ?>
                    </form>
                <?php elseif ($active && $admin && $s['status'] === 'pending_approval'): ?>
                    <form method="post" class="tv4-review"><?php UI::csrf(); ?><label>Nhận xét (bắt buộc khi yêu cầu bổ sung)<textarea name="review_note" maxlength="5000"></textarea></label><div class="tv4-actions"><?php UI::component('button', 'Duyệt & mở bước tiếp theo', ['type' => 'submit'], ['name' => 'action', 'value' => 'approve_stage']); UI::component('button', 'Yêu cầu bổ sung', ['type' => 'submit', 'variant' => 'outline'], ['name' => 'action', 'value' => 'reject_stage']); ?></div></form>
                <?php elseif ($s['status'] === 'locked'): ?><p class="tv4-muted">Giai đoạn mở sau khi bước trước được quản lý duyệt.</p>
                <?php elseif ($s['status'] === 'pending_approval'): ?><p class="tv4-muted">Đã gửi minh chứng, đang chờ quản lý phê duyệt.</p><?php endif; ?>
            </article>
        <?php endforeach; ?>
        <section class="sol-glass-card tv4-panel"><h3>Hồ sơ & ảnh minh chứng</h3>
            <div class="tv4-documents"><?php foreach ($d['documents'] as $doc): ?>
                <a class="tv4-document" href="index.php?page=installations&amp;document=<?= $doc['id'] ?>">
                    <?php if (in_array($doc['file_type'], ['image/jpeg','image/png'], true)): ?><img loading="lazy" src="index.php?page=installations&amp;document=<?= $doc['id'] ?>&amp;preview=1" alt="<?= $e($doc['file_name']) ?>"><?php endif; ?>
                    <strong><?= $e($doc['file_name']) ?></strong><span><?= $e($doc['full_name']) ?> · <?= $doc['file_size_kb'] ?>KB</span><small><?= $e(UI::documentLabel($doc['document_type'])) ?> · Giai đoạn <?= array_search((int) $doc['stage_id'], array_map('intval', array_column($d['stages'], 'id')), true) + 1 ?></small>
                </a>
            <?php endforeach; ?></div><?php if (!$d['documents']): ?><p class="tv4-muted">Chưa có minh chứng.</p><?php endif; ?>
            <?php UI::pager($d['docPager'], 'doc_page'); ?>
        </section>
        <section class="sol-glass-card tv4-panel"><h3>Nhật ký dự án</h3><div class="tv4-logs">
            <?php foreach ($d['logs'] as $log): ?><div><time><?= $e($log['created_at']) ?></time><strong><?= $e($log['full_name'] ?? 'Hệ thống') ?></strong><p><?= $e(UI::actionLabel($log['action'])) ?> · <?= $e(Workflow::STAGES[$log['stage']]) ?></p><p><?= $e($log['note']) ?></p><?php if ($log['from_status'] || $log['to_status']): ?><small><?= $e(UI::label($log['from_status'] ?? '')) ?> → <?= $e(UI::label($log['to_status'] ?? '')) ?></small><?php endif; ?></div><?php endforeach; ?>
        </div><?php UI::pager($d['logPager'], 'log_page'); ?></section>
    </div>
    <aside class="tv4-side">
        <section class="sol-glass-card tv4-panel"><h3>Yêu cầu chờ xử lý <span class="tv4-count"><?= count($d['requests']) ?></span></h3>
            <?php if (!$d['requests']): ?><p class="tv4-muted">Không có yêu cầu đang chờ.</p><?php endif; ?>
            <?php foreach ($d['requests'] as $r): ?><article class="tv4-request"><h4><?= $r['request_type'] === 'postpone' ? 'Hoãn lịch giai đoạn' : 'Thay kỹ thuật viên' ?></h4><small>#<?= $r['id'] ?> · <?= $e($r['full_name']) ?> · <?= $e($r['requested_at']) ?></small><p><?= $e($r['reason']) ?></p>
                <?php if ($r['request_type'] === 'postpone'): ?><p>Lịch đề xuất: <?= $e($r['proposed_start']) ?> → <?= $e($r['proposed_end']) ?></p><small>Các giai đoạn sau sẽ được dời theo ngày kết thúc mới.</small><?php else: ?><p><?= $e($r['old_name']) ?> → <?= $e($r['next_name']) ?></p><?php endif; ?>
                <?php if ($admin && $editable): ?><form method="post"><?php UI::csrf(); ?><input type="hidden" name="request_id" value="<?= $r['id'] ?>"><label>Phản hồi<textarea name="review_note" maxlength="5000"></textarea></label><div class="tv4-actions"><?php UI::component('button', 'Duyệt', ['type' => 'submit', 'size' => 'sm'], ['name' => 'action', 'value' => 'approve_request']); UI::component('button', 'Từ chối', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm'], ['name' => 'action', 'value' => 'reject_request']); ?></div></form><?php endif; ?>
            </article><?php endforeach; ?>
        </section>
        <?php if (!$admin && $editable && $current && in_array($current['status'], ['in_progress','revision_required'], true)): ?>
            <section class="sol-glass-card tv4-panel"><h3>Đề xuất hoãn lịch</h3><p class="tv4-muted">Áp dụng cho giai đoạn đang làm. Bạn vẫn có thể tiếp tục công việc và gửi duyệt khi hoàn thành.</p>
                <form method="post"><?php $form('postpone'); ?><label>Bắt đầu mới<input type="date" name="proposed_start" min="<?= $e($current['planned_start']) ?>" value="<?= $e($current['planned_start']) ?>" required></label><label>Kết thúc mới<input type="date" name="proposed_end" min="<?= $e($current['planned_end']) ?>" value="<?= $e($current['planned_end']) ?>" required></label><label>Lý do<textarea name="reason" required maxlength="5000" placeholder="Thời tiết, điều kiện công trường..."></textarea></label><?php UI::component('button', 'Gửi yêu cầu hoãn', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?></form>
            </section>
            <section class="sol-glass-card tv4-panel"><h3>Đề xuất thay người</h3><form method="post"><?php $form('replacement'); ?>
                <label>Người cần thay<select name="old_technician_id" required><?php foreach ($d['crew'] as $member): ?><option value="<?= $member['technician_id'] ?>"><?= $e($member['full_name']) ?></option><?php endforeach; ?></select></label>
                <label>Nhân viên thay thế<select name="proposed_technician_id" required><option value="">Chọn kỹ thuật viên</option><?php foreach ($v['availability']['technicians'] as $tech): if (in_array((int) $tech['id'], array_map('intval', array_column($d['crew'], 'technician_id')), true)) { continue; } ?><option value="<?= $tech['id'] ?>"><?= $e($tech['full_name']) ?> · <?= $tech['project_count'] ?> DA · <?= $tech['free_percent'] === null ? '—' : $tech['free_percent'] . '% rảnh' ?></option><?php endforeach; ?></select></label>
                <?php UI::pager($v['availability']['pager'], 'staff_page'); ?>
                <small class="tv4-muted">Mức rảnh trong lịch dự kiến dự án; số dự án tính toàn bộ dự án đang làm.</small>
                <label>Lý do<textarea name="reason" required maxlength="5000"></textarea></label><?php UI::component('button', 'Gửi yêu cầu thay người', ['type' => 'submit', 'variant' => 'outline', 'size' => 'sm']); ?>
            </form></section>
        <?php endif; ?>
        <?php if ($d['requestHistory']): ?><section class="sol-glass-card tv4-panel"><h3>Lịch sử yêu cầu</h3>
            <?php foreach ($d['requestHistory'] as $r): ?><article class="tv4-request"><h4>#<?= $r['id'] ?> · <?= $r['request_type'] === 'postpone' ? 'Hoãn lịch' : 'Thay nhân viên' ?></h4>
                <?php UI::component('badge', UI::label($r['status']), ['variant' => $r['status'] === 'approved' ? 'emerald' : 'amber']); ?>
                <p><?= $e($r['reason']) ?></p><small>Gửi bởi <?= $e($r['full_name']) ?> · <?= $e($r['requested_at']) ?></small>
                <?php if ($r['request_type'] === 'postpone'): ?><p><?= $e($r['proposed_start']) ?> → <?= $e($r['proposed_end']) ?></p><?php else: ?><p><?= $e($r['old_name']) ?> → <?= $e($r['next_name']) ?></p><?php endif; ?>
                <p><?= $e($r['review_note']) ?></p><small>Xử lý: <?= $e($r['reviewer_name']) ?> · <?= $e($r['reviewed_at']) ?></small>
            </article><?php endforeach; ?><?php UI::pager($d['requestPager'], 'request_page'); ?>
        </section><?php endif; ?>
    </aside>
</div>
