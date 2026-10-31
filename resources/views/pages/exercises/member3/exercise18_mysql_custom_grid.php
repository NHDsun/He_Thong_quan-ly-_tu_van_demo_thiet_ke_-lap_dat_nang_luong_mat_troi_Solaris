<?php
/**
 * Bài 18 (MySQL 3): Lưới tùy biến kẻ sọc (Custom Zebra Grid)
 * Phụ trách: Thành viên 3
 */

use App\Config\Database;

$pdo = Database::getPdo();
$data_list = [];
$is_live_data = false;
$db_error = Database::getLastError();
$selected_view = $_GET['view'] ?? 'khach_hang';

if ($pdo !== null) {
    try {
        if ($selected_view === 'sua') {
            $stmt = $pdo->query("SELECT s.Ma_sua, s.Ten_sua, h.Ten_hang_sua, s.Trong_luong, s.Don_gia 
                                 FROM sua s 
                                 LEFT JOIN hang_sua h ON s.Ma_hang_sua = h.Ma_hang_sua 
                                 LIMIT 15");
            $data_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $is_live_data = true;
        } else {
            $stmt = $pdo->query("SELECT Ma_khach_hang, Ten_khach_hang, Phai, Dia_chi, Dien_thoai FROM khach_hang LIMIT 20");
            $data_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $is_live_data = true;
        }
    } catch (\Exception $e) {
        $db_error = $e->getMessage();
    }
}

// Dữ liệu mẫu dự phòng
if (empty($data_list)) {
    if ($selected_view === 'sua') {
        $data_list = [
            ['Ma_sua' => 'VNM01', 'Ten_sua' => 'Sữa tươi tiệt trùng 100%', 'Ten_hang_sua' => 'Vinamilk', 'Trong_luong' => 180, 'Don_gia' => 32000],
            ['Ma_sua' => 'VNM02', 'Ten_sua' => 'Sữa chua men sống Probi', 'Ten_hang_sua' => 'Vinamilk', 'Trong_luong' => 65, 'Don_gia' => 24000],
            ['Ma_sua' => 'NTF01', 'Ten_sua' => 'Sữa bột Grow Plus+ Đỏ', 'Ten_hang_sua' => 'Nutifood', 'Trong_luong' => 900, 'Don_gia' => 385000],
            ['Ma_sua' => 'THM01', 'Ten_sua' => 'Sữa tươi thanh trùng ít đường', 'Ten_hang_sua' => 'TH True Milk', 'Trong_luong' => 1000, 'Don_gia' => 45000],
            ['Ma_sua' => 'DLM01', 'Ten_sua' => 'Sữa tiệt trùng Cô Gái Hà Lan Canxi', 'Ten_hang_sua' => 'Cô Gái Hà Lan', 'Trong_luong' => 220, 'Don_gia' => 28000],
        ];
    } else {
        $data_list = [
            ['Ma_khach_hang' => 'KH001', 'Ten_khach_hang' => 'Khuất Thùy Phương', 'Phai' => 1, 'Dia_chi' => 'A21 Nguyễn Oanh, Gò Vấp', 'Dien_thoai' => '0987412585'],
            ['Ma_khach_hang' => 'KH002', 'Ten_khach_hang' => 'Đỗ Hải Triều', 'Phai' => 0, 'Dia_chi' => '84/1 Bùi Thị Xuân, Tân Bình', 'Dien_thoai' => '0918234567'],
            ['Ma_khach_hang' => 'KH003', 'Ten_khach_hang' => 'Trần Thị Mai Anh', 'Phai' => 1, 'Dia_chi' => '12 Phan Đình Phùng, Nha Trang', 'Dien_thoai' => '0905123456'],
            ['Ma_khach_hang' => 'KH004', 'Ten_khach_hang' => 'Nguyễn Đình Hoàng', 'Phai' => 0, 'Dia_chi' => '45 Lê Hồng Phong, Nha Trang', 'Dien_thoai' => '0935678910'],
            ['Ma_khach_hang' => 'KH005', 'Ten_khach_hang' => 'Võ Thị Hồng Gấm', 'Phai' => 1, 'Dia_chi' => '88 Trần Phú, Vĩnh Nguyên', 'Dien_thoai' => '0908765432'],
        ];
    }
}
?>

<div class="sol-glass-card" style="padding: 2rem; max-width: 980px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
        <div>
            <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">BÀI TẬP 18 (MYSQL 3) • THÀNH VIÊN 3</span>
            <h2 style="color: #ffffff; font-size: 1.5rem; font-family: var(--font-heading);">
                Lưới Dữ Liệu Tùy Biến Kẻ Sọc Nâng Cao
            </h2>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="index.php?page=exercises&exercise=18&view=khach_hang" class="sol-btn <?= $selected_view === 'khach_hang' ? 'sol-btn--solar' : 'sol-btn--secondary' ?> sol-btn--sm">
                👤 Lưới Khách Hàng
            </a>
            <a href="index.php?page=exercises&exercise=18&view=sua" class="sol-btn <?= $selected_view === 'sua' ? 'sol-btn--solar' : 'sol-btn--secondary' ?> sol-btn--sm">
                🥛 Lưới Sản Phẩm Sữa
            </a>
            <a href="index.php?page=exercises" class="sol-btn sol-btn--secondary sol-btn--sm">⬅ Quay Lại</a>
        </div>
    </div>

    <?php if (!$is_live_data): ?>
        <div style="padding: 0.85rem 1.15rem; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-sm); color: #fde68a; font-size: 0.825rem; margin-bottom: 1.5rem;">
            💡 <strong>Ghi chú dữ liệu:</strong> Chưa kết nối được CSDL MySQL (<?= htmlspecialchars($db_error ?? 'Chưa khởi động MySQL') ?>). Hệ thống đang hiển thị <strong>dữ liệu mẫu minh họa</strong> để kiểm thử giao diện lưới kẻ sọc. Khi bạn import file <code>database.sql</code> vào CSDL <code>solaris_db</code> hoặc <code>quanly_ban_sua</code> và khởi động MySQL trong XAMPP, dữ liệu thực sẽ tự động nạp!
        </div>
    <?php endif; ?>

    <?php if ($selected_view === 'sua'): ?>
        <!-- Hiển thị lưới sản phẩm sữa kẻ sọc -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--accent-amber); color: var(--accent-gold); font-size: 0.775rem; text-transform: uppercase;">
                        <th style="padding: 0.85rem 1rem; width: 90px; text-align: center;">Mã Sữa</th>
                        <th style="padding: 0.85rem 1rem;">Tên Sản Phẩm Sữa</th>
                        <th style="padding: 0.85rem 1rem;">Hãng Sản Xuất</th>
                        <th style="padding: 0.85rem 1rem; text-align: center; width: 120px;">Trọng Lượng</th>
                        <th style="padding: 0.85rem 1rem; text-align: right; width: 140px;">Đơn Giá</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_list as $i => $row): 
                        $isStripe = $i % 2 === 1;
                        $bg = $isStripe ? 'rgba(56, 189, 248, 0.06)' : 'rgba(14, 22, 38, 0.4)';
                    ?>
                        <tr style="border-bottom: 1px solid var(--border-subtle); background: <?= $bg ?>; transition: background 0.15s ease;">
                            <td style="padding: 0.85rem 1rem; text-align: center; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 700;">
                                <?= htmlspecialchars($row['Ma_sua'] ?? $row[0] ?? '') ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: #ffffff; font-weight: 600;">
                                <?= htmlspecialchars($row['Ten_sua'] ?? $row[1] ?? '') ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: var(--accent-amber);">
                                <?= htmlspecialchars($row['Ten_hang_sua'] ?? $row[2] ?? '') ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center; color: var(--text-secondary); font-family: var(--font-mono);">
                                <?= $row['Trong_luong'] ?? $row[3] ?? '' ?> gr/ml
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; color: var(--accent-gold); font-weight: 700; font-family: var(--font-mono);">
                                <?= number_format((float)($row['Don_gia'] ?? $row[4] ?? 0), 0, ',', '.') ?> đ
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Hiển thị lưới khách hàng kẻ sọc -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--accent-amber); color: var(--accent-gold); font-size: 0.775rem; text-transform: uppercase;">
                        <th style="padding: 0.85rem 1rem; width: 90px; text-align: center;">Mã KH</th>
                        <th style="padding: 0.85rem 1rem;">Tên Khách Hàng</th>
                        <th style="padding: 0.85rem 1rem; width: 110px; text-align: center;">Phái (Giới Tính)</th>
                        <th style="padding: 0.85rem 1rem;">Địa Chỉ Khách Hàng</th>
                        <th style="padding: 0.85rem 1rem; width: 140px;">Số Điện Thoại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_list as $i => $row): 
                        $isNu = ((int)($row['Phai'] ?? $row[2] ?? 0)) === 1;
                        $isStripe = $i % 2 === 1;
                        $bg = $isStripe ? 'rgba(245, 158, 11, 0.06)' : 'rgba(14, 22, 38, 0.4)';
                    ?>
                        <tr style="border-bottom: 1px solid var(--border-subtle); background: <?= $bg ?>; transition: background 0.15s ease;">
                            <td style="padding: 0.85rem 1rem; text-align: center; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 700;">
                                <?= htmlspecialchars($row['Ma_khach_hang'] ?? $row[0] ?? '') ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: #ffffff; font-weight: 600;">
                                <?= htmlspecialchars($row['Ten_khach_hang'] ?? $row[1] ?? '') ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: center; font-size: 1.15rem;">
                                <?php if ($isNu): ?>
                                    <span title="Nữ" style="color: #fb7185;">👩 <small style="font-size: 0.75rem; font-weight: 600;">Nữ</small></span>
                                <?php else: ?>
                                    <span title="Nam" style="color: #38bdf8;">👨 <small style="font-size: 0.75rem; font-weight: 600;">Nam</small></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: var(--text-secondary);">
                                <?= htmlspecialchars($row['Dia_chi'] ?? $row[3] ?? '') ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: var(--accent-amber); font-family: var(--font-mono);">
                                <?= htmlspecialchars($row['Dien_thoai'] ?? $row[4] ?? '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top: 1.5rem; text-align: right; font-size: 0.8rem; color: var(--text-muted);">
        Tổng cộng: <strong style="color: var(--accent-gold);"><?= count($data_list) ?></strong> bản ghi
    </div>
</div>
