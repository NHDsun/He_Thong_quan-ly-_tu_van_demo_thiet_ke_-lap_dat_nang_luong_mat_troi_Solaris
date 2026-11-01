<?php
/**
 * Bài 6 (Căn bản 6): Phép tính trên hai số
 * Phụ trách: Thành viên 1 (Lead)
 */

function cong(float $a, float $b): float { return $a + $b; }
function tru(float $a, float $b): float { return $a - $b; }
function nhan(float $a, float $b): float { return $a * $b; }
function chia(float $a, float $b): float { return $a / $b; }

$so1 = isset($_POST['so1']) ? trim($_POST['so1']) : '';
$so2 = isset($_POST['so2']) ? trim($_POST['so2']) : '';
$pheptinh = isset($_POST['pheptinh']) ? $_POST['pheptinh'] : 'cong';
$ketqua = null;
$ten_pheptinh = '';
$ky_hieu = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if ($so1 === '' || $so2 === '') {
        $error = "Vui lòng nhập cả số thứ nhất và số thứ nhì.";
    } elseif (!is_numeric($so1) || !is_numeric($so2)) {
        $error = "Giá trị nhập vào phải là số.";
    } elseif ($pheptinh === 'chia' && (float)$so2 == 0) {
        $error = "Lỗi toán học: Không thể thực hiện phép chia cho số 0.";
    } else {
        $n1 = (float)$so1;
        $n2 = (float)$so2;
        switch ($pheptinh) {
            case 'cong':
                $ketqua = cong($n1, $n2);
                $ten_pheptinh = 'Phép Cộng';
                $ky_hieu = '+';
                break;
            case 'tru':
                $ketqua = tru($n1, $n2);
                $ten_pheptinh = 'Phép Trừ';
                $ky_hieu = '−';
                break;
            case 'nhan':
                $ketqua = nhan($n1, $n2);
                $ten_pheptinh = 'Phép Nhân';
                $ky_hieu = '×';
                break;
            case 'chia':
                $ketqua = chia($n1, $n2);
                $ten_pheptinh = 'Phép Chia';
                $ky_hieu = '÷';
                break;
            default:
                $error = "Phép tính không hợp lệ.";
        }
        if ($ketqua !== null) {
            $success = true;
        }
    }
}
?>

<div class="sol-glass-card" style="padding: 2.25rem; max-width: 780px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
        <div>
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BÀI TẬP 6 • THÀNH VIÊN 1</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading); margin-top: 0.25rem;">
                Phép Tính Số Học Trên Hai Số
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.35); color: #f43f5e; padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.875rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=exercises&exercise=6" style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div>
            <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.65rem; font-weight: 500;">
                Chọn phép tính:
            </label>
            <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; background: rgba(14, 22, 38, 0.6); padding: 0.85rem 1.25rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm);">
                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #ffffff; font-size: 0.9rem;">
                    <input type="radio" name="pheptinh" value="cong" <?= $pheptinh === 'cong' ? 'checked' : '' ?> style="accent-color: var(--accent-amber);">
                    <span>Cộng (+)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #ffffff; font-size: 0.9rem;">
                    <input type="radio" name="pheptinh" value="tru" <?= $pheptinh === 'tru' ? 'checked' : '' ?> style="accent-color: var(--accent-amber);">
                    <span>Trừ (−)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #ffffff; font-size: 0.9rem;">
                    <input type="radio" name="pheptinh" value="nhan" <?= $pheptinh === 'nhan' ? 'checked' : '' ?> style="accent-color: var(--accent-amber);">
                    <span>Nhân (×)</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer; color: #ffffff; font-size: 0.9rem;">
                    <input type="radio" name="pheptinh" value="chia" <?= $pheptinh === 'chia' ? 'checked' : '' ?> style="accent-color: var(--accent-amber);">
                    <span>Chia (÷)</span>
                </label>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Số thứ nhất:
                </label>
                <input type="text" name="so1" value="<?= htmlspecialchars($so1) ?>" required placeholder="Ví dụ: 125" class="products-input" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Số thứ nhì:
                </label>
                <input type="text" name="so2" value="<?= htmlspecialchars($so2) ?>" required placeholder="Ví dụ: 5" class="products-input" style="width: 100%;">
            </div>
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(14, 22, 38, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);"><?= htmlspecialchars($ten_pheptinh) ?>:</span>
                    <div style="font-size: 1.15rem; color: #cbd5e1; margin-top: 0.2rem;">
                        <?= htmlspecialchars($so1) ?> <?= $ky_hieu ?> <?= htmlspecialchars($so2) ?>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Kết quả tính:</span>
                    <div style="font-size: 1.7rem; font-weight: 700; color: var(--accent-gold); margin-top: 0.2rem;">
                        <?= is_float($ketqua) && floor($ketqua) != $ketqua ? number_format($ketqua, 4, ',', '.') : number_format($ketqua, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
            <button type="submit" name="submit" class="sol-btn sol-btn--solar">
                ⚡ Thực Hiện Phép Tính
            </button>
            <a href="index.php?page=exercises&exercise=6" class="sol-btn sol-btn--secondary">
                Làm mới
            </a>
        </div>
    </form>
</div>
