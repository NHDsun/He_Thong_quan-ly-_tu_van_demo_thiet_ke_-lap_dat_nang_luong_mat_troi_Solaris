<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;
$e = [UI::class, 'escape'];
$v = $installationView;
$actor = $v['actor'];
$admin = $actor && $actor['role'] === 'admin';
$form = static function (string $action): void {
    UI::csrf();
    echo '<input type="hidden" name="action" value="' . UI::escape($action) . '">';
};
?>
<link rel="stylesheet" href="resources/css/installation.css">
<section class="sol-container tv4">
    <div class="tv4-heading">
        <div><span class="tv4-eyebrow">SOLARIS / VẬN HÀNH DỰ ÁN</span><h1>Quản lý lắp đặt</h1>
            <p>Theo dõi từng giai đoạn, phối hợp đội ngũ và quản lý hồ sơ thi công.</p></div>
        <?php if ($actor): ?><div class="tv4-account"><strong><?= $e($actor['full_name']) ?></strong><span><?= $admin ? 'Quản lý' : 'Kỹ thuật viên' ?></span>
            <?php if (!isset($_SESSION['user_id'])): ?><form method="post"><?php $form('logout'); UI::component('button', 'Đăng xuất', ['variant' => 'outline', 'size' => 'sm', 'type' => 'submit']); ?></form><?php endif; ?>
        </div><?php endif; ?>
    </div>
    <?php if ($v['flash']): ?><div class="tv4-notice" role="status"><?= $e($v['flash']) ?></div><?php endif; ?>
    <?php if ($v['error']): ?><div class="tv4-notice tv4-notice--error" role="alert"><?= $e($v['error']) ?></div><?php endif; ?>
    <?php if (!$actor): ?>
        <form method="post" class="sol-glass-card tv4-panel tv4-login">
            <h2>Đăng nhập đội ngũ</h2><p>Dành cho quản lý và kỹ thuật viên được phân công.</p>
            <?php $form('login'); ?>
            <label>Tên đăng nhập<?php UI::component('input', '', [], ['name' => 'username', 'required' => true, 'autocomplete' => 'username', 'maxlength' => 50]); ?></label>
            <label>Mật khẩu<?php UI::component('input', '', ['type' => 'password'], ['name' => 'password', 'required' => true, 'autocomplete' => 'current-password']); ?></label>
            <?php UI::component('button', 'Đăng nhập', ['type' => 'submit']); ?>
        </form>
    <?php elseif ($actor && !$v['error']): ?>
        <?php if (isset($v['detail'])): ?>
            <?php require __DIR__ . '/../components/installation/detail.php'; ?>
        <?php else: ?>
            <nav class="tv4-tabs" aria-label="Lọc dự án">
                <?php $filters = $admin ? ['new' => 'Dự án mới', 'in_progress' => 'Đang thực hiện', 'pending' => 'Chờ phê duyệt', 'completed' => 'Hoàn tất', 'all' => 'Tất cả'] : ['in_progress' => 'Đang thực hiện', 'all' => 'Dự án của tôi']; ?>
                <?php foreach ($filters as $key => $label): ?><a class="<?= $v['filter'] === $key ? 'active' : '' ?>" href="<?= $e(UI::url(['filter' => $key, 'p' => 1])) ?>"><?= $e($label) ?></a><?php endforeach; ?>
            </nav>
            <div class="tv4-list-heading"><h2><?= $e($filters[$v['filter']] ?? 'Danh sách dự án') ?></h2><span><?= $v['listing']['pager']['total'] ?> dự án</span></div>
            <div class="tv4-projects">
                <?php foreach ($v['listing']['projects'] as $p): $progress = (int) round($p['approved_count'] / 6 * 100); ?>
                    <a class="sol-glass-card tv4-project-card" href="<?= $e(UI::url(['project' => $p['id']])) ?>">
                        <div class="tv4-project-symbol" aria-hidden="true">☀</div>
                        <div class="tv4-project-main"><span class="tv4-eyebrow"><?= $e($p['contract_code']) ?></span><h3><?= $e($p['project_name']) ?></h3><p><?= $e($p['installation_address']) ?></p>
                            <div class="tv4-meta"><span><?= $e($p['capacity_kwp']) ?> kWp</span><span><?= $p['crew_count'] ?> kỹ thuật viên</span><span><?= $e($p['start_date'] ?? 'Chưa xếp lịch') ?> → <?= $e($p['planned_end_date'] ?? '—') ?></span></div>
                        </div>
                        <div class="tv4-project-progress"><?php UI::component('badge', UI::label($p['status']), ['variant' => $p['status'] === 'completed' ? 'emerald' : 'amber']); ?>
                            <div class="tv4-meter"><span style="width:<?= $progress ?>%"></span></div><span><?= $progress ?>% · <?= $p['approved_count'] ?>/6 giai đoạn</span>
                            <?php if ($p['request_count']): ?><small><?= $p['request_count'] ?> yêu cầu chờ xử lý</small><?php endif; ?>
                        </div><span class="tv4-card-arrow" aria-hidden="true">↗</span>
                    </a>
                <?php endforeach; ?>
                <?php if (!$v['listing']['projects']): ?><div class="tv4-empty sol-glass-card"><h3>Chưa có dự án trong danh sách này</h3><p><?= $admin ? 'Dự án mới xuất hiện sau khi báo giá được chấp thuận và TV3 khởi tạo dự án.' : 'Dự án sẽ xuất hiện khi quản lý phân công bạn vào nhóm thực hiện.' ?></p></div><?php endif; ?>
            </div>
            <?php UI::pager($v['listing']['pager'], 'p'); ?>
        <?php endif; ?>
    <?php endif; ?>
</section>
<script src="resources/js/installation.js" defer></script>
