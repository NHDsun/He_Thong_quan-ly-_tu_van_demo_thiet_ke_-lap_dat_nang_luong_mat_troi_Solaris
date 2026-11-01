<?php
/**
 * Bài 17 (MySQL 2): Hiển thị lưới khách hàng kèm phân biệt giới tính
 * Phụ trách: Thành viên 3
 */

use App\Config\Database;

$pdo = Database::getPdo();
$khach_hang_list = [];
$is_live_data = false;
$db_error = Database::getLastError();

if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT Ma_khach_hang, Ten_khach_hang, Phai, Dia_chi, Dien_thoai, Email FROM khach_hang");
        $khach_hang_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $is_live_data = true;
    } catch (\Exception $e) {
        $db_error = $e->getMessage();
    }
}

// Dữ liệu mẫu dự phòng nếu CSDL chưa có dữ liệu
if (empty($khach_hang_list)) {
    $khach_hang_list = [
        ['Ma_khach_hang' => 'kh001', 'Ten_khach_hang' => 'Khuất Thùy Phương', 'Phai' => 1, 'Dia_chi' => 'A21 Nguyễn Oanh, P.17, Q.Gò Vấp', 'Dien_thoai' => '0987412585', 'Email' => 'ktphuong@gmail.com'],
        ['Ma_khach_hang' => 'kh002', 'Ten_khach_hang' => 'Đỗ Hải Triều', 'Phai' => 0, 'Dia_chi' => '84/1 Bùi Thị Xuân, Q.Tân Bình', 'Dien_thoai' => '0918234567', 'Email' => 'haitrieu@yahoo.com'],
        ['Ma_khach_hang' => 'kh003', 'Ten_khach_hang' => 'Trần Thị Mai Anh', 'Phai' => 1, 'Dia_chi' => '12 Phan Đình Phùng, TP. Nha Trang', 'Dien_thoai' => '0905123456', 'Email' => 'maianh_tran@gmail.com'],
        ['Ma_khach_hang' => 'kh004', 'Ten_khach_hang' => 'Nguyễn Đình Hoàng', 'Phai' => 0, 'Dia_chi' => '45 Lê Hồng Phong, TP. Nha Trang', 'Dien_thoai' => '0935678910', 'Email' => 'dinhoang.solaris@gmail.com'],
        ['Ma_khach_hang' => 'kh005', 'Ten_khach_hang' => 'Phạm Thị Bích Ngọc', 'Phai' => 1, 'Dia_chi' => '68 Hùng Vương, Lộc Thọ, Nha Trang', 'Dien_thoai' => '0978999888', 'Email' => 'bichngoc.ntu@gmail.com'],
        ['Ma_khach_hang' => 'kh006', 'Ten_khach_hang' => 'Lê Thanh Tùng', 'Phai' => 0, 'Dia_chi' => '102 2/4, Vĩnh Phước, Nha Trang', 'Dien_thoai' => '0945112233', 'Email' => 'tunglt@gmail.com'],
    ];
}
?>

<div class="sol-glass-card" style="padding: 2rem; max-width: 980px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
        <div>
            <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BÀI TẬP 17 (MYSQL 2) • THÀNH VIÊN 3</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading);">
                Hiển Thị Lưới Khách Hàng (Tô Màu So Le & Giới Tính)
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
            💡 <strong>Ghi chú dữ liệu:</strong> Chưa kết nối được bảng <code>khach_hang</code> trong MySQL (<?= htmlspecialchars($db_error ?? 'Chưa khởi động MySQL') ?>). Hệ thống đang hiển thị <strong>dữ liệu mẫu minh họa</strong> để bạn trải nghiệm giao diện. Khi bạn import file <code>database.sql</code> vào CSDL <code>solaris_db</code> hoặc <code>quanly_ban_sua</code> và khởi động MySQL trong XAMPP, dữ liệu thực sẽ tự động cập nhật!
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--accent-amber); color: var(--accent-gold); font-size: 0.775rem; text-transform: uppercase;">
                    <th style="padding: 0.85rem 1rem; width: 90px; text-align: center;">Mã KH</th>
                    <th style="padding: 0.85rem 1rem;">Tên Khách Hàng</th>
                    <th style="padding: 0.85rem 1rem; width: 100px; text-align: center;">Giới Tính</th>
                    <th style="padding: 0.85rem 1rem;">Địa Chỉ</th>
                    <th style="padding: 0.85rem 1rem; width: 130px;">Điện Thoại</th>
                    <th style="padding: 0.85rem 1rem; width: 180px;">Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($khach_hang_list as $i => $row): 
                    $isNu = ((int)($row['Phai'] ?? $row[2] ?? 0)) === 1;
                    // Màu so le giữa các dòng (Zebra striping)
                    $rowBg = $i % 2 === 1 ? 'rgba(245, 158, 11, 0.05)' : 'rgba(14, 22, 38, 0.4)';
                ?>
                    <tr style="border-bottom: 1px solid var(--border-subtle); background: <?= $rowBg ?>; transition: background 0.15s ease;">
                        <td style="padding: 0.85rem 1rem; text-align: center; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 700;">
                            <?= htmlspecialchars($row['Ma_khach_hang'] ?? $row[0] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: #ffffff; font-weight: 600;">
                            <?= htmlspecialchars($row['Ten_khach_hang'] ?? $row[1] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center;">
                            <?php if ($isNu): ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.55rem; background: rgba(244, 63, 94, 0.12); border: 1px solid rgba(244, 63, 94, 0.3); border-radius: 999px; color: #fb7185; font-size: 0.775rem; font-weight: 600;">
                                    👩 Nữ
                                </span>
                            <?php else: ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.55rem; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 999px; color: #38bdf8; font-size: 0.775rem; font-weight: 600;">
                                    👨 Nam
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: var(--text-secondary);">
                            <?= htmlspecialchars($row['Dia_chi'] ?? $row[3] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: var(--accent-amber); font-family: var(--font-mono);">
                            <?= htmlspecialchars($row['Dien_thoai'] ?? $row[4] ?? '') ?>
                        </td>
                        <td style="padding: 0.85rem 1rem; color: #cbd5e1; font-size: 0.8rem;">
                            <?= htmlspecialchars($row['Email'] ?? $row[5] ?? '') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--text-muted);">
        <div>
            Mẹo: Các dòng được tô màu so le tự động theo thuật toán <code>$i % 2 === 1</code>.
        </div>
        <div>
            Tổng số: <strong style="color: var(--accent-gold);"><?= count($khach_hang_list) ?></strong> khách hàng
        </div>
    </div>
</div>
