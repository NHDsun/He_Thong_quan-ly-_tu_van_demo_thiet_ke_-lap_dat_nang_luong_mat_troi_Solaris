<?php
/**
 * Bài 5 (Căn bản 5): Tính tiền phòng Karaoke theo khung giờ
 * Phụ trách: Thành viên 1 (Lead)
 */

$gio_bat_dau = isset($_POST['gio_bat_dau']) ? trim($_POST['gio_bat_dau']) : '';
$gio_ket_thuc = isset($_POST['gio_ket_thuc']) ? trim($_POST['gio_ket_thuc']) : '';
$tien = '';
$chi_tiet = '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if ($gio_bat_dau === '' || $gio_ket_thuc === '') {
        $error = "Vui lòng nhập giờ bắt đầu và giờ kết thúc.";
    } elseif (!is_numeric($gio_bat_dau) || !is_numeric($gio_ket_thuc)) {
        $error = "Giờ bắt đầu và kết thúc phải là giá trị số.";
    } else {
        $start = (float)$gio_bat_dau;
        $end = (float)$gio_ket_thuc;
        if ($end <= $start) {
            $error = "Giờ kết thúc phải lớn hơn giờ bắt đầu.";
        } elseif ($start < 10 || $end > 24) {
            $error = "Giờ phục vụ hợp lệ trong khoảng từ 10:00 đến 24:00.";
        } else {
            if ($end <= 17) {
                $hours = $end - $start;
                $tien = $hours * 20000;
                $chi_tiet = "{$hours} giờ × 20.000 VNĐ (Khung ban ngày 10h-17h)";
            } elseif ($start >= 17) {
                $hours = $end - $start;
                $tien = $hours * 45000;
                $chi_tiet = "{$hours} giờ × 45.000 VNĐ (Khung giờ cao điểm 17h-24h)";
            } else {
                $h1 = 17 - $start;
                $h2 = $end - 17;
                $tien = ($h1 * 20000) + ($h2 * 45000);
                $chi_tiet = "{$h1}h ban ngày (20k) + {$h2}h cao điểm (45k)";
            }
            $success = true;
        }
    }
}
?>

<div class="sol-glass-card" style="padding: 2.25rem; max-width: 780px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
        <div>
            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BÀI TẬP 5 • THÀNH VIÊN 1</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading); margin-top: 0.25rem;">
                Tính Tiền Giờ Hát Karaoke Theo Khung Giờ
            </h2>
        </div>
        <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.35); color: #f43f5e; padding: 0.85rem 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-size: 0.875rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: var(--radius-sm); padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.825rem; color: var(--text-secondary);">
        ℹ️ <strong>Bảng giá áp dụng:</strong> Khung 10:00 - 17:00: <strong style="color: #38bdf8;">20.000 VNĐ/giờ</strong> | Khung 17:00 - 24:00: <strong style="color: var(--accent-gold);">45.000 VNĐ/giờ</strong>.
    </div>

    <form method="post" action="index.php?page=exercises&exercise=5" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Giờ bắt đầu (10h - 24h):
                </label>
                <input type="text" name="gio_bat_dau" value="<?= htmlspecialchars($gio_bat_dau) ?>" required placeholder="Ví dụ: 14" class="products-input" style="width: 100%;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.45rem; font-weight: 500;">
                    Giờ kết thúc (10h - 24h):
                </label>
                <input type="text" name="gio_ket_thuc" value="<?= htmlspecialchars($gio_ket_thuc) ?>" required placeholder="Ví dụ: 19" class="products-input" style="width: 100%;">
            </div>
        </div>

        <?php if ($success): ?>
            <div style="background: rgba(14, 22, 38, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Chi tiết khung giờ:</span>
                    <div style="font-size: 0.95rem; color: #cbd5e1; margin-top: 0.25rem;">
                        <?= htmlspecialchars($chi_tiet) ?>
                    </div>
                </div>
                <div>
                    <span style="font-size: 0.825rem; color: var(--text-secondary);">Tiền thanh toán:</span>
                    <div style="font-size: 1.6rem; font-weight: 700; color: var(--accent-gold); margin-top: 0.2rem;">
                        <?= number_format($tien, 0, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: 500;">VNĐ</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="submit" name="submit" class="sol-btn sol-btn--solar">
                ⚡ Tính Tiền Phòng
            </button>
            <a href="index.php?page=exercises&exercise=5" class="sol-btn sol-btn--secondary">
                Làm mới
            </a>
        </div>
    </form>
</div>
