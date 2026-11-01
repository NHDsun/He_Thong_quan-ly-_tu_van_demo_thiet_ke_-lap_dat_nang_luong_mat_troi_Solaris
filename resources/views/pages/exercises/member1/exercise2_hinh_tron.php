<?php
/**
 * Bài 2 (Căn bản 2): Tính diện tích và chu vi hình tròn
 * Phụ trách: Thành viên 1 (Lead)
 */

define('PI', 3.14159265359);

$ban_kinh = isset($_POST['ban_kinh']) ? trim($_POST['ban_kinh']) : '';
$dien_tich = '';
$chu_vi = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if ($ban_kinh === '') {
        $error = "Vui lòng nhập bán kính hình tròn.";
    } elseif (!is_numeric($ban_kinh)) {
        $error = "Bán kính phải là giá trị số.";
    } elseif ((float)$ban_kinh <= 0) {
        $error = "Bán kính phải lớn hơn 0.";
    } else {
        $r = (float)$ban_kinh;
        $dien_tich = PI * pow($r, 2);
        $chu_vi = 2 * PI * $r;
        $success = true;
    }
}
?>

<div class="sol-glass-card" style="padding: 2.25rem; max-width: 780px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
        <div>
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BÀI TẬP 2 • THÀNH VIÊN 1</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading); margin-top: 0.25rem;">
                Diện Tích & Chu Vi Hình Tròn
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.35); color: #f43f5e; padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.875rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=exercises&exercise=2" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div>
            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                Bán kính hình tròn (r):
            </label>
            <input type="text" name="ban_kinh" value="<?= htmlspecialchars($ban_kinh) ?>" required placeholder="Ví dụ: 7.5" class="products-input" style="width: 100%; max-width: 380px;">
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(14, 22, 38, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 0.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Diện tích (S = π × r²):</span>
                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--accent-gold); margin-top: 0.2rem;">
                        <?= number_format($dien_tich, 2, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: 500;">đvdt</span>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Chu vi (C = 2 × π × r):</span>
                    <div style="font-size: 1.4rem; font-weight: 700; color: #38bdf8; margin-top: 0.2rem;">
                        <?= number_format($chu_vi, 2, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: 500;">đvđd</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="submit" name="submit" class="sol-btn sol-btn--solar">
                ⚡ Tính Toán Ngay
            </button>
            <a href="index.php?page=exercises&exercise=2" class="sol-btn sol-btn--secondary">
                Làm mới
            </a>
        </div>
    </form>
</div>
