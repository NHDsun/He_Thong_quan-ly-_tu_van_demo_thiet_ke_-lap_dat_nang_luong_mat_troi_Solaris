<?php
use App\Modules\Member4_Installation\View as UI;
$viewData = $installationView;
$actor = $viewData['actor'];
$admin = $actor && $actor['role'] === 'admin';
$customer = $actor && $actor['role'] === 'customer';

if ($admin) {
    $filters = [
        'new' => 'Dự án mới',
        'in_progress' => 'Đang thực hiện',
        'pending' => 'Chờ phê duyệt',
        'completed' => 'Hoàn tất',
        'all' => 'Tất cả',
    ];
} elseif ($customer) {
    $filters = ['all' => 'Tất cả dự án của tôi', 'new' => 'Chờ triển khai',
        'in_progress' => 'Đang thực hiện', 'completed' => 'Đã hoàn tất'];
} else {
    $filters = [
        'in_progress' => 'Đang thực hiện',
        'all' => 'Dự án của tôi',
    ];
}
?>
<link rel="stylesheet" href="resources/css/installation.css">
<section class="sol-container tv4">
    <div class="tv4-heading">
        <div>
            <span class="tv4-eyebrow">SOLARIS / VẬN HÀNH DỰ ÁN</span>
            <h1><?= !$actor ? 'Đồng hành cùng dự án điện mặt trời' : ($customer ? 'Dự án của tôi' : 'Quản lý lắp đặt') ?></h1>
            <p><?= !$actor ? 'Tìm hiểu quy trình lắp đặt và cách theo dõi dự án cùng SOLARIS.' : ($customer ? 'Theo dõi lịch triển khai và kết quả từng giai đoạn của dự án thuộc tài khoản của bạn.' : 'Theo dõi từng giai đoạn, phối hợp đội ngũ và quản lý hồ sơ thi công.') ?></p>
        </div>
        <?php if ($actor): ?>
            <div class="tv4-account">
                <strong><?= UI::escape($actor['full_name']) ?></strong>
                <span><?= $admin ? 'Quản lý' : ($customer ? 'Khách hàng' : 'Kỹ thuật viên') ?></span>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <form method="post">
                        <?php UI::form('logout'); UI::component('button', 'Đăng xuất', ['variant' => 'outline', 'size' => 'sm', 'type' => 'submit']); ?>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($viewData['flash']): ?>
        <div class="tv4-notice" role="status">
            <?= UI::escape($viewData['flash']) ?>
        </div>
    <?php endif; ?>
    <?php if ($viewData['error']): ?>
        <div class="tv4-notice tv4-notice--error" role="alert">
            <?= UI::escape($viewData['error']) ?>
        </div>
    <?php endif; ?>
    <?php if (!$actor): ?>
        <?php require __DIR__ . '/../components/installation/guest.php'; ?>
        <form method="post" class="sol-glass-card tv4-panel tv4-login" id="installation-login">
            <h2>Đăng nhập theo dõi dự án</h2>
            <p>Dành cho khách hàng, quản lý và kỹ thuật viên.</p>
            <?php UI::form('login'); ?>
            <label>
                Tên đăng nhập
                <?php UI::component('input', '', [], ['name' => 'username', 'required' => true, 'autocomplete' => 'username', 'maxlength' => 50]); ?>
            </label>
            <label>
                Mật khẩu
                <?php UI::component('input', '', ['type' => 'password'], ['name' => 'password', 'required' => true, 'autocomplete' => 'current-password']); ?>
            </label>
            <?php UI::component('button', 'Đăng nhập', ['type' => 'submit']); ?>
        </form>
    <?php elseif ($actor && !$viewData['error']): ?>
        <?php if (isset($viewData['detail'])): ?>
            <?php if ($customer): ?>
                <?php require __DIR__ . '/../components/installation/customer-detail.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/../components/installation/detail.php'; ?>
            <?php endif; ?>
        <?php else: ?>
            <nav class="tv4-tabs" aria-label="Lọc dự án">
                <?php foreach ($filters as $key => $label): ?>
                    <a class="<?= $viewData['filter'] === $key ? 'active' : '' ?>" href="<?= UI::escape(UI::url(['filter' => $key, 'p' => 1])) ?>">
                        <?= UI::escape($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="tv4-list-heading">
                <h2><?= UI::escape($filters[$viewData['filter']] ?? 'Danh sách dự án') ?></h2>
                <span><?= $viewData['listing']['pager']['total'] ?> dự án</span>
            </div>
            <div class="tv4-projects">
                <?php foreach ($viewData['listing']['projects'] as $project): $progress = (int) round($project['approved_count'] / 6 * 100); ?>
                    <a class="sol-glass-card tv4-project-card" href="<?= UI::escape(UI::url(['project' => $project['id']])) ?>">
                        <div class="tv4-project-symbol" aria-hidden="true">
                            ☀
                        </div>
                        <div class="tv4-project-main">
                            <span class="tv4-eyebrow"><?= UI::escape($project['contract_code']) ?></span>
                            <h3><?= UI::escape($project['project_name']) ?></h3>
                            <p><?= UI::escape($project['installation_address']) ?></p>
                            <div class="tv4-meta">
                                <span><?= UI::escape($project['capacity_kwp']) ?> kWp</span>
                                <span><?= $project['crew_count'] ?> kỹ thuật viên</span>
                                <span><?= UI::escape($project['start_date'] ?? 'Chưa xếp lịch') ?> → <?= UI::escape($project['planned_end_date'] ?? '—') ?></span>
                            </div>
                        </div>
                        <div class="tv4-project-progress">
                            <?php UI::component('badge', UI::label($project['status']), ['variant' => $project['status'] === 'completed' ? 'emerald' : 'amber']); ?>
                            <div class="tv4-meter">
                                <span style="width:<?= $progress ?>%"></span>
                            </div>
                            <span><?= $progress ?> % · <?= $project['approved_count'] ?> /6 giai đoạn</span>
                            <?php if (!$customer && $project['request_count']): ?>
                                <small><?= $project['request_count'] ?> yêu cầu chờ xử lý</small>
                            <?php endif; ?>
                        </div>
                        <span class="tv4-card-arrow" aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?>
                <?php if (!$viewData['listing']['projects']): ?>
                    <div class="tv4-empty sol-glass-card">
                        <h3>Chưa có dự án trong danh sách này</h3>
                        <p>
                            <?= $customer ? 'Dự án sẽ xuất hiện khi báo giá được chấp thuận và dự án được tạo gắn với tài khoản của bạn.' : ($admin ? 'Dự án mới xuất hiện sau khi báo giá được chấp thuận và TV3 khởi tạo dự án.' : 'Dự án sẽ xuất hiện khi quản lý phân công bạn vào nhóm thực hiện.') ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            <?php UI::pager($viewData['listing']['pager'], 'p'); ?>
        <?php endif; ?>
    <?php endif; ?>
</section>
<script src="resources/js/installation.js" defer>
</script>
