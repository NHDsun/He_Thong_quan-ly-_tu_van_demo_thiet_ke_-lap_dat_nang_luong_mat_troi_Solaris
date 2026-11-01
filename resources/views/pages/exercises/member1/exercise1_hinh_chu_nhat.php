<?php
/**
 * Bài 1 (Căn bản 1): Tính diện tích và chu vi hình chữ nhật
 * Phụ trách: Thành viên 1 (Lead)
 */

$chieu_dai = isset($_POST['chieu_dai']) ? trim($_POST['chieu_dai']) : '';
$chieu_rong = isset($_POST['chieu_rong']) ? trim($_POST['chieu_rong']) : '';
$dien_tich = '';
$chu_vi = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if ($chieu_dai === '' || $chieu_rong === '') {
        $error = "Vui lòng nhập đầy đủ chiều dài và chiều rộng.";
    } elseif (!is_numeric($chieu_dai) || !is_numeric($chieu_rong)) {
        $error = "Chiều dài và chiều rộng phải là giá trị số.";
    } elseif ((float)$chieu_dai <= 0 || (float)$chieu_rong <= 0) {
        $error = "Chiều dài và chiều rộng phải lớn hơn 0.";
    } else {
        $cd = (float)$chieu_dai;
        $cr = (float)$chieu_rong;
        $dien_tich = $cd * $cr;
        $chu_vi = ($cd + $cr) * 2;
        $success = true;
    }
}
?>

<div class="sol-glass-card" style="padding: 2.25rem; max-width: 780px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
        <div>
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BÀI TẬP 1 • THÀNH VIÊN 1</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading); margin-top: 0.25rem;">
                Diện Tích & Chu Vi Hình Chữ Nhật
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.35); color: #f43f5e; padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.875rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=exercises&exercise=1" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Chiều dài (m):
                </label>
                <input type="text" name="chieu_dai" value="<?= htmlspecialchars($chieu_dai) ?>" required placeholder="Ví dụ: 15" class="products-input" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Chiều rộng (m):
                </label>
                <input type="text" name="chieu_rong" value="<?= htmlspecialchars($chieu_rong) ?>" required placeholder="Ví dụ: 8" class="products-input" style="width: 100%;">
            </div>
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(14, 22, 38, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 0.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Diện tích:</span>
                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--accent-gold); margin-top: 0.2rem;">
                        <?= number_format($dien_tich, 2, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: 500;">m²</span>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Chu vi:</span>
                    <div style="font-size: 1.4rem; font-weight: 700; color: #38bdf8; margin-top: 0.2rem;">
                        <?= number_format($chu_vi, 2, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: 500;">m</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="submit" name="submit" class="sol-btn sol-btn--solar">
                ⚡ Tính Toán Ngay
            </button>
            <a href="index.php?page=exercises&exercise=1" class="sol-btn sol-btn--secondary">
                Làm mới
            </a>
        </div>
    </form>
</div>
