<?php
/**
 * Bài 3 (Căn bản 3): Tính tiền điện thanh toán
 * Phụ trách: Thành viên 1 (Lead)
 */

$ten_chu_ho = isset($_POST['ten_chu_ho']) ? trim($_POST['ten_chu_ho']) : 'Nguyễn Văn An';
$chi_so_cu = isset($_POST['chi_so_cu']) ? trim($_POST['chi_so_cu']) : '';
$chi_so_moi = isset($_POST['chi_so_moi']) ? trim($_POST['chi_so_moi']) : '';
$don_gia = isset($_POST['don_gia']) ? trim($_POST['don_gia']) : '2500';
$so_kwh = '';
$thanh_tien = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if ($ten_chu_ho === '' || $chi_so_cu === '' || $chi_so_moi === '' || $don_gia === '') {
        $error = "Vui lòng nhập đầy đủ các thông tin yêu cầu.";
    } elseif (!is_numeric($chi_so_cu) || !is_numeric($chi_so_moi) || !is_numeric($don_gia)) {
        $error = "Chỉ số và đơn giá phải là giá trị số.";
    } elseif ((float)$chi_so_cu < 0 || (float)$chi_so_moi < 0 || (float)$don_gia <= 0) {
        $error = "Chỉ số điện phải không âm (≥ 0) và đơn giá phải lớn hơn 0.";
    } elseif ((float)$chi_so_moi < (float)$chi_so_cu) {
        $error = "Chỉ số mới phải lớn hơn hoặc bằng chỉ số cũ.";
    } else {
        $cu = (float)$chi_so_cu;
        $moi = (float)$chi_so_moi;
        $gia = (float)$don_gia;
        $so_kwh = $moi - $cu;
        $thanh_tien = $so_kwh * $gia;
        $success = true;
    }
}
?>

<div class="sol-glass-card" style="padding: 2.25rem; max-width: 780px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
        <div>
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BÀI TẬP 3 • THÀNH VIÊN 1</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading); margin-top: 0.25rem;">
                Tính Hóa Đơn Thanh Toán Tiền Điện
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.35); color: #f43f5e; padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.875rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=exercises&exercise=3" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div>
            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                Tên chủ hộ:
            </label>
            <input type="text" name="ten_chu_ho" value="<?= htmlspecialchars($ten_chu_ho) ?>" required class="products-input" style="width: 100%;">
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Chỉ số cũ (kWh):
                </label>
                <input type="text" name="chi_so_cu" value="<?= htmlspecialchars($chi_so_cu) ?>" required placeholder="Ví dụ: 120" class="products-input" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Chỉ số mới (kWh):
                </label>
                <input type="text" name="chi_so_moi" value="<?= htmlspecialchars($chi_so_moi) ?>" required placeholder="Ví dụ: 450" class="products-input" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Đơn giá điện (VNĐ/kWh):
                </label>
                <input type="text" name="don_gia" value="<?= htmlspecialchars($don_gia) ?>" required class="products-input" style="width: 100%;">
            </div>
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(14, 22, 38, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 0.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Chủ hộ tiêu thụ:</span>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #ffffff; margin-top: 0.2rem;">
                        <?= htmlspecialchars($ten_chu_ho) ?>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Điện năng tiêu thụ:</span>
                    <div style="font-size: 1.3rem; font-weight: 700; color: #38bdf8; margin-top: 0.2rem;">
                        <?= number_format($so_kwh, 0, ',', '.') ?> <span style="font-size: 0.85rem; font-weight: 500;">kWh</span>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Tổng tiền thanh toán:</span>
                    <div style="font-size: 1.35rem; font-weight: 700; color: var(--accent-gold); margin-top: 0.2rem;">
                        <?= number_format($thanh_tien, 0, ',', '.') ?> <span style="font-size: 0.85rem; font-weight: 500;">VNĐ</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="submit" name="submit" class="sol-btn sol-btn--solar">
                ⚡ Tính Tiền Điện
            </button>
            <a href="index.php?page=exercises&exercise=3" class="sol-btn sol-btn--secondary">
                Làm mới
            </a>
        </div>
    </form>
</div>
