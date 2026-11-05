<?php
use App\Modules\Member4_Installation\View as UI;

if (!isset($viewData['dashboard'], $viewData['actor']) || !in_array($viewData['actor']['role'], ['admin', 'technician'], true)) {
    return;
}
$isAdmin = $viewData['actor']['role'] === 'admin';
$stats = $viewData['dashboard'];
$cards = $isAdmin ? [
    'total_projects' => 'Tổng dự án',
    'pending_projects' => 'Yêu cầu chờ duyệt',
    'new_projects' => 'Chờ phân công',
    'scheduled_projects' => 'Dự án chờ bắt đầu',
    'active_projects' => 'Dự án đang thực hiện',
    'completed_projects' => 'Dự án hoàn tất',
    'pending_stages' => 'Giai đoạn chờ duyệt',
    'pending_requests' => 'Yêu cầu hoãn / đổi người',
] : [
    'total_projects' => 'Dự án được phân công',
    'ready_stages' => 'Giai đoạn chờ bắt đầu',
    'active_stages' => 'Giai đoạn đang thực hiện',
    'revision_stages' => 'Cần bổ sung hồ sơ',
    'pending_stages' => 'Giai đoạn chờ duyệt',
    'pending_requests' => 'Yêu cầu của tôi chờ xử lý',
];
?>
<section class="tv4-dashboard" aria-labelledby="installation-dashboard-title">
    <div class="tv4-dashboard-heading">
        <h2 id="installation-dashboard-title">Tổng quan lắp đặt</h2>
        <p><?= $isAdmin ? 'Số liệu toàn hệ thống' : 'Số liệu trong các dự án bạn đang được phân công' ?></p>
    </div>
    <dl class="tv4-dashboard-grid <?= $isAdmin ? 'tv4-dashboard-grid--admin' : 'tv4-dashboard-grid--technician' ?>">
        <?php foreach ($cards as $key => $title): ?>
            <div class="sol-glass-card tv4-dashboard-card">
                <dt><?= UI::escape($title) ?></dt>
                <dd><?= number_format($stats[$key], 0, ',', '.') ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>
