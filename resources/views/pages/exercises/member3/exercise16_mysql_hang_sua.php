<?php
/**
 * Bài 16 (MySQL 1): Hiển thị lưới thông tin hãng sữa
 * Phụ trách: Thành viên 3
 */

use App\Config\Database;

$pdo = Database::getPdo();
$hang_sua_list = [];
$is_live_data = false;
$db_error = Database::getLastError();

if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT Ma_hang_sua, Ten_hang_sua, Dia_chi, Dien_thoai, Email FROM hang_sua");
        $hang_sua_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $is_live_data = true;
    } catch (\Exception $e) {
        $db_error = $e->getMessage();
    }
}

// Dữ liệu mẫu dự phòng nếu CSDL chưa được import
if (empty($hang_sua_list)) {
    $hang_sua_list = [
        ['Ma_hang_sua' => 'VNM', 'Ten_hang_sua' => 'Vinamilk', 'Dia_chi' => '10 Tân Trào, P. Tân Phú, Q.7, TP.HCM', 'Dien_thoai' => '028 5415 5555', 'Email' => 'vinamilk@vinamilk.com.vn'],
        ['Ma_hang_sua' => 'NTF', 'Ten_hang_sua' => 'Nutifood', 'Dia_chi' => '281-283 Hoàng Diệu, P.6, Q.4, TP.HCM', 'Dien_thoai' => '028 3826 7999', 'Email' => 'nutifood@nutifood.com.vn'],
        ['Ma_hang_sua' => 'THM', 'Ten_hang_sua' => 'TH True Milk', 'Dia_chi' => 'Nghĩa Đàn, Nghệ An', 'Dien_thoai' => '1800 54 54 40', 'Email' => 'cskh@thmilk.vn'],
        ['Ma_hang_sua' => 'DLM', 'Ten_hang_sua' => 'Cô Gái Hà Lan (Dutch Lady)', 'Dia_chi' => 'Bình Hòa, Thuận An, Bình Dương', 'Dien_thoai' => '1800 1545', 'Email' => 'dutchlady@frieslandcampina.com'],
        ['Ma_hang_sua' => 'ABB', 'Ten_hang_sua' => 'Abbott Nutrition', 'Dia_chi' => 'Mê Linh Point Tower, 2 Ngô Đức Kế, Q.1, TP.HCM', 'Dien_thoai' => '028 3825 6551', 'Email' => 'abbott_vietnam@abbott.com'],
        ['Ma_hang_sua' => 'MJM', 'Ten_hang_sua' => 'Mead Johnson', 'Dia_chi' => 'Saigon Tower, 29 Lê Duẩn, Q.1, TP.HCM', 'Dien_thoai' => '028 3824 5022', 'Email' => 'meadjohnson@mjn.com'],
    ];
}
?>

<div class="sol-glass-card" style="padding: 2rem; max-width: 980px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
        <div>
            <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BÀI TẬP 16 (MYSQL 1) • THÀNH VIÊN 3</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading);">
                Hiển Thị Lưới Thông Tin Hãng Sữa (Bảng <code>hang_sua</code>)
            </h2>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span class="sol-badge <?= $is_live_data ? 'sol-badge--emerald' : 'sol-badge--amber' ?>">
                <?= $is_live_data ? '● Dữ liệu MySQL Trực tiếp' : '○ Dữ liệu mẫu (Cần import SQL)' ?>
            </span>
            <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại Portal</a>
        </div>
    </div>

    <?php if (!$is_live_data): ?>
        <div style="padding: 0.85rem 1.15rem; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-sm); color: #fde68a; font-size: 0.825rem; margin-bottom: 1.5rem;">
            💡 <strong>Ghi chú dữ liệu:</strong> Chưa kết nối được bảng <code>hang_sua</code> trong MySQL (<?= htmlspecialchars($db_error ?? 'Chưa khởi động MySQL') ?>). Hệ thống đang hiển thị <strong>dữ liệu mẫu minh họa</strong> để bạn trải nghiệm giao diện. Khi bạn import file <code>database.sql</code> vào CSDL <code>solaris_db</code> hoặc <code>quanly_ban_sua</code> và khởi động MySQL trong XAMPP, dữ liệu thực sẽ tự động cập nhật!
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--accent-amber); color: var(--accent-gold); font-size: 0.775rem; text-transform: uppercase;">
                    <th style="padding: 0.85rem 1rem; width: 90px; text-align: center;">Mã HS</th>
                    <th style="padding: 0.85rem 1rem;">Tên Hãng Sữa</th>
                    <th style="padding: 0.85rem 1rem;">Địa Chỉ</th>
                    <th style="padding: 0.85rem 1rem; width: 140px;">Điện Thoại</th>
                    <th style="padding: 0.85rem 1rem; width: 190px;">Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hang_sua_list as $i => $row): ?>
                    <tr style="border-bottom: 1px solid var(--border-subtle); background: <?= $i % 2 === 1 ? 'rgba(255, 255, 255, 0.02)' : 'transparent' ?>; transition: background 0.15s ease;">
                        <td style="padding: 0.85rem 1rem; text-align: center; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 700;">
                            <?= htmlspecialchars($row['Ma_hang_sua'] ?? $row[0] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: #ffffff; font-weight: 600;">
                            <?= htmlspecialchars($row['Ten_hang_sua'] ?? $row[1] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: var(--text-secondary);">
                            <?= htmlspecialchars($row['Dia_chi'] ?? $row[2] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: var(--accent-amber); font-family: var(--font-mono);">
                            <?= htmlspecialchars($row['Dien_thoai'] ?? $row[3] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: #38bdf8; font-size: 0.8rem;">
                            <?= htmlspecialchars($row['Email'] ?? $row[4] ?? '') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem; text-align: right; font-size: 0.8rem; color: var(--text-muted);">
        Tổng cộng: <strong style="color: var(--accent-gold);"><?= count($hang_sua_list) ?></strong> hãng sữa
    </div>
</div>
