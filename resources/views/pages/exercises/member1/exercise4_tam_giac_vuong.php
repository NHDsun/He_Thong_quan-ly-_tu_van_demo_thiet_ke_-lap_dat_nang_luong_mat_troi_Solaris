<?php
/**
 * Bài 4 (Căn bản 4): Tính cạnh huyền tam giác vuông
 * Phụ trách: Thành viên 1 (Lead)
 */

$canh_a = isset($_POST['canh_a']) ? trim($_POST['canh_a']) : '';
$canh_b = isset($_POST['canh_b']) ? trim($_POST['canh_b']) : '';
$canh_huyen = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if ($canh_a === '' || $canh_b === '') {
        $error = "Vui lòng nhập độ dài hai cạnh góc vuông A và B.";
    } elseif (!is_numeric($canh_a) || !is_numeric($canh_b)) {
        $error = "Độ dài các cạnh phải là giá trị số.";
    } elseif ((float)$canh_a <= 0 || (float)$canh_b <= 0) {
        $error = "Độ dài hai cạnh góc vuông phải lớn hơn 0.";
    } else {
        $a = (float)$canh_a;
        $b = (float)$canh_b;
        $canh_huyen = sqrt(pow($a, 2) + pow($b, 2));
        $success = true;
    }
}
?>

<div class="sol-glass-card" style="padding: 2.25rem; max-width: 780px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
        <div>
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BÀI TẬP 4 • THÀNH VIÊN 1</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading); margin-top: 0.25rem;">
                Định Lý Pythagoras - Tính Cạnh Huyền
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.35); color: #f43f5e; padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.875rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=exercises&exercise=4" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Cạnh góc vuông A:
                </label>
                <input type="text" name="canh_a" value="<?= htmlspecialchars($canh_a) ?>" required placeholder="Ví dụ: 3" class="products-input" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Cạnh góc vuông B:
                </label>
                <input type="text" name="canh_b" value="<?= htmlspecialchars($canh_b) ?>" required placeholder="Ví dụ: 4" class="products-input" style="width: 100%;">
            </div>
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(14, 22, 38, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Công thức: c = √(a² + b²)</span>
                    <div style="font-size: 0.95rem; color: #94a3b8; margin-top: 0.25rem;">
                        c = √((<?= htmlspecialchars($canh_a) ?>)² + (<?= htmlspecialchars($canh_b) ?>)²)
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Độ dài cạnh huyền (c):</span>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--accent-gold); margin-top: 0.2rem;">
                        <?= number_format($canh_huyen, 2, ',', '.') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="submit" name="submit" class="sol-btn sol-btn--solar">
                ⚡ Tính Cạnh Huyền
            </button>
            <a href="index.php?page=exercises&exercise=4" class="sol-btn sol-btn--secondary">
                Làm mới
            </a>
        </div>
    </form>
</div>
