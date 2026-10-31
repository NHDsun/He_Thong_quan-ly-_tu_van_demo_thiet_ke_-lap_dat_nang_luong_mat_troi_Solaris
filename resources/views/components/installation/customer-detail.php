<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;

/** @var array $viewData Dữ liệu chi tiết chỉ xem của khách hàng. */
if (!isset($viewData['detail'], $viewData['actor']) || $viewData['actor']['role'] !== 'customer') {
    http_response_code(404);
    return;
}
$detail = $viewData['detail'];
$project = $detail['project'];
$approved = 0;
foreach ($detail['stages'] as $stage) {
    if ($stage['status'] === 'approved') {
        $approved++;
    }
}
$stageLabels = ['locked' => 'Chưa triển khai', 'in_progress' => 'Đang thực hiện',
    'revision_required' => 'Đang bổ sung hồ sơ', 'pending_approval' => 'Chờ xác nhận hoàn thành', 'approved' => 'Đã hoàn thành'];
?>
<a class="tv4-back" href="index.php?page=installations">← Dự án của tôi</a>
<article class="sol-glass-card tv4-panel tv4-summary">
    <div>
        <span class="tv4-eyebrow"><?= UI::escape($project['contract_code']) ?></span>
        <h2><?= UI::escape($project['project_name']) ?></h2>
        <p><?= UI::escape($project['installation_address']) ?></p>
    </div>
    <?php UI::component('badge', UI::label($project['status']), ['variant' => $project['status'] === 'completed' ? 'emerald' : 'amber']); ?>
    <div class="tv4-summary-grid">
        <div><span>Công suất hệ thống</span><strong><?= UI::escape($project['capacity_kwp']) ?> kWp</strong></div>
        <div><span>Giá trị dự án</span><strong><?= number_format((float) $project['total_amount'], 0, ',', '.') ?> đ</strong></div>
        <div><span>Lịch dự kiến</span><strong><?= UI::escape($project['start_date'] ?? 'Đang sắp xếp') ?> → <?= UI::escape($project['planned_end_date'] ?? 'Đang sắp xếp') ?></strong></div>
        <div><span>Tiến độ đã xác nhận</span><strong><?= $approved ?>/6 bước · <?= (int) round($approved / 6 * 100) ?>%</strong></div>
    </div>
    <?php if ($detail['crew']): ?>
        <div><p>Đội kỹ thuật đang phụ trách</p><div class="tv4-crew">
            <?php foreach ($detail['crew'] as $member): ?><span><?= UI::escape($member['full_name']) ?></span><?php endforeach; ?>
        </div></div>
    <?php endif; ?>
    <?php if ($project['completed_at']): ?><p>Hoàn tất dự án: <?= UI::escape($project['completed_at']) ?></p><?php endif; ?>
</article>
<ol class="tv4-steps" aria-label="Quy trình dự án của bạn">
    <?php foreach ($detail['stages'] as $stage): ?>
        <li class="<?= $stage['status'] === 'approved' ? 'done' : ($stage['stage'] === $project['current_stage'] && $project['status'] === 'in_progress' ? 'current' : '') ?>">
            <a href="#customer-stage-<?= (int) $stage['position'] ?>">
                <span><?= $stage['status'] === 'approved' ? '✓' : (int) $stage['position'] ?></span>
                <strong><?= UI::escape(Workflow::STAGES[$stage['stage']]) ?></strong>
                <small><?= UI::escape($stageLabels[$stage['status']]) ?></small>
            </a>
        </li>
    <?php endforeach; ?>
</ol>
<div class="tv4-stage-list">
    <?php foreach ($detail['stages'] as $stage): ?>
        <article class="sol-glass-card tv4-panel" id="customer-stage-<?= (int) $stage['position'] ?>">
            <div class="tv4-stage-heading">
                <span class="tv4-stage-number"><?= (int) $stage['position'] ?></span>
                <div><h3><?= UI::escape(Workflow::STAGES[$stage['stage']]) ?></h3></div>
                <?php UI::component('badge', $stageLabels[$stage['status']], ['variant' => $stage['status'] === 'approved' ? 'emerald' : 'amber']); ?>
            </div>
            <div class="tv4-meta">
                <span>Dự kiến: <?= UI::escape($stage['planned_start'] ?? 'Chưa xếp lịch') ?> → <?= UI::escape($stage['planned_end'] ?? 'Chưa xếp lịch') ?></span>
                <span>Bắt đầu thực tế: <?= UI::escape($stage['started_at'] ?? 'Chưa bắt đầu') ?></span>
                <span>Hoàn thành: <?= UI::escape($stage['completed_at'] ?? 'Chưa hoàn thành') ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</div>
