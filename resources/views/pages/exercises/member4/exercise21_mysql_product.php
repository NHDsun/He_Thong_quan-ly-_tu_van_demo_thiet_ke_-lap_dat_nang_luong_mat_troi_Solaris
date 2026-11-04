<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'quanly_ban_sua';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($servername, $username, $password, $dbname);
$error = '';
$product = null;
$code = is_string($_GET['milk'] ?? null) ? trim($_GET['milk']) : '';
$returnPage = max(1, (int)($_GET['p'] ?? 1));
if (!$conn) {
    $error = 'Không kết nối được quanly_ban_sua.';
} else {
    mysqli_set_charset($conn, 'utf8mb4');
    $stmt = mysqli_prepare($conn, 'SELECT s.*,h.Ten_hang_sua FROM sua s JOIN hang_sua h ON h.Ma_hang_sua=s.Ma_hang_sua WHERE s.Ma_sua=?');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $code);
        if (mysqli_stmt_execute($stmt)) $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
    }
    if (!$product) $error = 'Không tìm thấy sản phẩm sữa.';
}
?>
<link rel="stylesheet" href="resources/css/milk-exercises.css">
<section class="sol-glass-card milk-exercises milk-search">
    <header class="milk-heading">
        <div><span class="sol-badge sol-badge--amber">BÀI TẬP 21 (MYSQL 6) • THÀNH VIÊN 4</span><h2>CHI TIẾT SẢN PHẨM SỮA</h2></div>
        <a class="sol-btn sol-btn--secondary sol-btn--sm" href="index.php?page=exercises">⬅ Quay Lại Portal</a>
    </header>
    <?php if ($error): ?><p class="milk-notice" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($product): ?>
        <?php $row = $product; ?>
                <?php $image = basename(str_replace('\\', '/', (string)$row['Hinh'])); ?>
        <div class="milk-search-results">
            <article class="milk-search-result">
                <h3><?= htmlspecialchars($row['Ten_sua'].' - '.$row['Ten_hang_sua']) ?></h3>
                <div class="milk-search-product">
                    <div class="milk-search-image">
                        <?php if ($image !== '' && is_file(dirname(__DIR__,5).'/assets/Hinh_sua/'.$image)): ?>
                            <img src="assets/Hinh_sua/<?= rawurlencode($image) ?>" alt="<?= htmlspecialchars($row['Ten_sua']) ?>">
                        <?php else: ?><span>Chưa có ảnh</span><?php endif; ?>
                    </div>
                    <div class="milk-search-info">
                        <p><strong><em>Thành phần dinh dưỡng:</em></strong><br><span class="milk-text"><?= htmlspecialchars($row['TP_Dinh_Duong'] ?: 'Chưa cập nhật.') ?></span></p>
                        <p><strong><em>Lợi ích:</em></strong><br><span class="milk-text"><?= htmlspecialchars($row['Loi_ich'] ?: 'Chưa cập nhật.') ?></span></p>
                        <p><strong><em>Trọng lượng:</em></strong> <?= (int)$row['Trong_luong'] ?> g/ml - <strong><em>Đơn giá:</em></strong> <span class="milk-search-price"><?= number_format((float)$row['Don_gia'],0,',','.') ?> VNĐ</span></p>
                    </div>
                </div>
            </article>
        </div>

    <?php endif; ?>
    <p class="milk-product-back"><a href="index.php?page=exercises&amp;exercise=21&amp;p=<?= $returnPage ?>">Quay về</a></p>
</section>
<?php if ($conn) mysqli_close($conn); ?>
