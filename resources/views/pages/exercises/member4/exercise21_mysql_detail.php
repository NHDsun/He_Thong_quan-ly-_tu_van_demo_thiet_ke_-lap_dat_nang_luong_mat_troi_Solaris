<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'quanly_ban_sua';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($servername, $username, $password, $dbname);
$error = '';
$items = [];
$numRows = 0;
$rowsPerPage = 10;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$maxPage = 1;
if (!$conn) {
    $error = 'Không kết nối được quanly_ban_sua.';
} else {
    mysqli_set_charset($conn, 'utf8mb4');
    $result = mysqli_query($conn, 'SELECT Ma_sua FROM sua');
    if ($result) {
        $numRows = mysqli_num_rows($result);
        $maxPage = max(1, (int)ceil($numRows / $rowsPerPage));
        $currentPage = min($currentPage, $maxPage);
        $offset = ($currentPage - 1) * $rowsPerPage;
        $result = mysqli_query($conn, "SELECT Ma_sua,Ten_sua,Trong_luong,Don_gia,Hinh FROM sua ORDER BY Ma_sua LIMIT $offset, $rowsPerPage");
        if ($result) $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
        else $error = 'Không đọc được danh sách sữa.';
    } else $error = 'Không đọc được bảng sua.';
}
?>
<link rel="stylesheet" href="resources/css/milk-exercises.css">
<section class="sol-glass-card milk-exercises milk-product-page">
    <header class="milk-heading">
        <div><span class="sol-badge sol-badge--amber">BÀI TẬP 21 (MYSQL 6) • THÀNH VIÊN 4</span><h2>THÔNG TIN CÁC SẢN PHẨM</h2></div>
        <a class="sol-btn sol-btn--secondary sol-btn--sm" href="index.php?page=exercises">⬅ Quay Lại Portal</a>
    </header>
    <?php if ($error): ?><p class="milk-notice" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <div class="milk-product-list">
        <?php foreach ($items as $row): ?>
            <?php $image = basename(str_replace('\\', '/', (string)$row['Hinh'])); ?>
            <article class="milk-product-cell">
                <a class="milk-product-name" href="index.php?page=exercises&amp;exercise=21&amp;milk=<?= rawurlencode($row['Ma_sua']) ?>&amp;p=<?= $currentPage ?>"><?= htmlspecialchars($row['Ten_sua']) ?></a>
                <p><?= (int)$row['Trong_luong'] ?> g/ml - <?= number_format((float)$row['Don_gia'],0,',','.') ?> VNĐ</p>
                <a href="index.php?page=exercises&amp;exercise=21&amp;milk=<?= rawurlencode($row['Ma_sua']) ?>&amp;p=<?= $currentPage ?>">
                    <?php if ($image !== '' && is_file(dirname(__DIR__,5).'/assets/Hinh_sua/'.$image)): ?>
                        <img src="assets/Hinh_sua/<?= rawurlencode($image) ?>" alt="<?= htmlspecialchars($row['Ten_sua']) ?>" loading="lazy">
                    <?php else: ?><span class="milk-no-photo">Chưa có ảnh</span><?php endif; ?>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (!$items): ?><p>Không có sản phẩm.</p><?php endif; ?>
    <nav class="milk-actions milk-pager" aria-label="Phân trang">
        <?php if ($currentPage > 1): ?><a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?page=exercises&amp;exercise=21&amp;p=<?= $currentPage - 1 ?>">Back</a><?php endif; ?>
        <?php for ($i = 1; $i <= $maxPage; $i++): ?>
            <?php if ($i === $currentPage): ?><strong aria-current="page"><?= $i ?></strong>
            <?php else: ?><a href="index.php?page=exercises&amp;exercise=21&amp;p=<?= $i ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($currentPage < $maxPage): ?><a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?page=exercises&amp;exercise=21&amp;p=<?= $currentPage + 1 ?>">Next</a><?php endif; ?>
    </nav>
</section>
<?php if ($conn) mysqli_close($conn); ?>
