<?php
use App\Modules\Member4_Installation\View as UI;
use App\Hooks\UseInstallationWorkflow as Workflow;

/** @var array $viewData Dữ liệu trang lắp đặt. */
if (!isset($viewData)) {
    http_response_code(404);
    return;
}
$descriptions = [
    'survey' => 'Khảo sát hiện trạng mái, vị trí lắp đặt và nhu cầu sử dụng điện.',
    'design' => 'Lập phương án kỹ thuật, bố trí thiết bị và hồ sơ triển khai.',
    'procurement' => 'Xác nhận phương án và chuẩn bị vật tư cho công trình.',
    'installation' => 'Đội kỹ thuật thi công hệ thống theo thiết kế đã được duyệt.',
    'grid_connection' => 'Kiểm tra vận hành, an toàn và thực hiện các công việc nối lưới liên quan.',
    'handover' => 'Nghiệm thu, bàn giao hệ thống và hướng dẫn sử dụng.',
];
$images = [
    'survey' => 'giai_doan_1_khao_sat.png',
    'design' => 'giai_doan_2_thiet_ke.png',
    'procurement' => 'giai_doan_3_mua_sam.png',
    'installation' => 'giai_doan_4_thi_cong.png',
    'grid_connection' => 'giai_doan_5_kiem_thu.png',
    'handover' => 'giai_doan_6_hoa_luoi.png',
];
?>
<section class="sol-glass-card tv4-panel tv4-public-intro">
    <div>
        <span class="tv4-eyebrow">MINH BẠCH TRONG TỪNG GIAI ĐOẠN</span>
        <h2>Từ khảo sát đến bàn giao, bạn luôn nắm được tiến độ</h2>
        <p>SOLARIS tổ chức dự án theo sáu bước. Mỗi bước được đội kỹ thuật thực hiện và quản lý xác nhận trước khi chuyển sang bước tiếp theo.</p>
    </div>
    <a href="#installation-login" class="sol-btn sol-btn--solar">Đăng nhập xem dự án của tôi</a>
</section>
<section class="tv4-public-section" aria-labelledby="installation-process-title">
    <div class="tv4-coverflow" data-coverflow role="region" aria-roledescription="carousel" aria-labelledby="installation-process-title" tabindex="0">
    <div class="tv4-process-heading">
        <h2 id="installation-process-title">Quy trình thực hiện dự án</h2>
        <div aria-live="polite" aria-atomic="true">
            <p class="tv4-coverflow-status" data-coverflow-status>Giai đoạn 1 / 6 · Khảo sát hiện trường</p>
            <p class="tv4-process-description" data-coverflow-description><?= UI::escape($descriptions['survey']) ?></p>
        </div>
    </div>
    <div class="tv4-coverflow-viewport">
    <ol class="tv4-process-cards" id="installation-process-slides">
        <?php $step = 0; foreach (Workflow::STAGES as $code => $label): $step++; ?>
            <li class="sol-glass-card tv4-process-card">
                <div class="tv4-process-media">
                    <img class="tv4-process-backdrop" src="assets/uploads/QuyTrinh_Image/<?= UI::escape($images[$code]) ?>"
                         alt="" aria-hidden="true" draggable="false"
                         width="1376" height="768" loading="lazy" decoding="async">
                    <div class="tv4-process-image">
                        <img src="assets/uploads/QuyTrinh_Image/<?= UI::escape($images[$code]) ?>"
                             alt="Minh họa <?= UI::escape($label) ?>"
                             width="1376" height="768" loading="lazy" decoding="async" draggable="false">
                        <span class="tv4-stage-number"><?= $step ?></span>
                    </div>
                </div>
                <div class="tv4-process-content">
                    <h3><?= UI::escape($label) ?></h3>
                    <p><?= UI::escape($descriptions[$code]) ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
        <div class="tv4-coverflow-controls" hidden>
            <button type="button" class="tv4-coverflow-arrow" data-coverflow-prev aria-label="Giai đoạn trước" aria-controls="installation-process-slides">&#8592;</button>
            <button type="button" class="tv4-coverflow-arrow" data-coverflow-next aria-label="Giai đoạn tiếp theo" aria-controls="installation-process-slides">&#8594;</button>
        </div>
    </div>
    <div class="tv4-coverflow-dots" aria-label="Chọn giai đoạn" hidden>
        <?php $step = 0; foreach (Workflow::STAGES as $label): $step++; ?>
            <button type="button" data-coverflow-step="<?= $step - 1 ?>" aria-label="Giai đoạn <?= $step ?>: <?= UI::escape($label) ?>" aria-controls="installation-process-slides"><span></span></button>
        <?php endforeach; ?>
    </div>
    </div>
</section>
<section class="tv4-public-section" aria-labelledby="customer-benefits-title">
    <h2 id="customer-benefits-title">Quyền lợi khi theo dõi dự án</h2>
    <div class="tv4-benefits">
        <article class="sol-glass-card tv4-panel tv4-benefit">
            <span class="tv4-benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18m-13 5 2 2 4-4"/>
                </svg>
            </span>
            <div><h3>Nắm rõ lịch triển khai</h3><p>Lịch dự kiến và các mốc bắt đầu, hoàn thành của từng giai đoạn.</p></div>
        </article>
        <article class="sol-glass-card tv4-panel tv4-benefit">
            <span class="tv4-benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="m3 6 1.5 1.5L7 4m-4 8 1.5 1.5L7 10m-4 8 1.5 1.5L7 16M11 6h10m-10 6h10m-10 6h10"/>
                </svg>
            </span>
            <div><h3>Theo dõi kết quả từng bước</h3><p>Cập nhật trạng thái và kết quả triển khai của từng giai đoạn.</p></div>
        </article>
        <article class="sol-glass-card tv4-panel tv4-benefit">
            <span class="tv4-benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2m2-16a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4v2"/>
                </svg>
            </span>
            <div><h3>Biết đội ngũ phụ trách</h3><p>Danh sách kỹ thuật viên phụ trách dự án.</p></div>
        </article>
        <article class="sol-glass-card tv4-panel tv4-benefit">
            <span class="tv4-benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <rect x="3" y="3" width="18" height="5" rx="1"/><path d="M5 8v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8m-10 6 2 2 4-4"/>
                </svg>
            </span>
            <div><h3>Xem lại dự án đã bàn giao</h3><p>Xem tiến độ và các mốc hoàn thành sau khi dự án được bàn giao.</p></div>
        </article>
    </div>
</section>
