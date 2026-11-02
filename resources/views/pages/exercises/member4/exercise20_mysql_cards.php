<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'quanly_ban_sua';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($servername, $username, $password, $dbname);
$error = '';
if (!$conn) {
    $error = 'Không kết nối được quanly_ban_sua. Hãy kiểm tra MySQL và thông tin kết nối.';
} else {
    mysqli_set_charset($conn, 'utf8mb4');
}
$items = [];
$filters = ['q' => '', 'brand' => '', 'type' => '', 'min' => '', 'max' => ''];
$brands = $types = [];
$where = '1=1';
$params = [];
$numRows = 0;
$rowsPerPage = 10;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$maxPage = 1;
$offset = 0;
if ($conn && $error === '') {
    $stmt = mysqli_prepare($conn, 'SELECT s.Ma_sua FROM sua s WHERE ' . $where);
    if ($stmt) {
        if ($params)
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
        if (mysqli_stmt_execute($stmt))
            $numRows = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        else $error = 'Không đọc được dữ liệu sữa.';
        mysqli_stmt_close($stmt);
    } else
        $error = 'Không đọc được bảng sua.';
    $maxPage = max(1, (int)ceil($numRows / $rowsPerPage));
    $currentPage = min($currentPage, $maxPage);
    $offset = ($currentPage - 1) * $rowsPerPage;
    $sql = 'SELECT s.*,h.Ten_hang_sua,l.Ten_loai FROM sua s JOIN hang_sua h ON h.Ma_hang_sua=s.Ma_hang_sua JOIN loai_sua l ON l.Ma_loai_sua=s.Ma_loai_sua WHERE ' . $where . ' ORDER BY s.Ma_sua LIMIT ' . $offset . ', ' . $rowsPerPage;
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        if ($params)
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
        if (mysqli_stmt_execute($stmt))
            $items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        else $error = 'Không đọc được danh sách sữa.';
        mysqli_stmt_close($stmt);
    } else
        $error = 'Không đọc được danh sách sữa.';
}
?>
<link rel="stylesheet" href="resources/css/milk-exercises.css">
<section class="sol-glass-card milk-exercises">
    <header class="milk-heading">
        <div>
            <span class="sol-badge sol-badge--amber">BÀI TẬP 20 (MYSQL 5) • THÀNH VIÊN 4</span>
            <h2>Sản phẩm sữa kèm ảnh</h2>
        </div>
        <a class="sol-btn sol-btn--secondary sol-btn--sm" href="index.php?page=exercises">⬅ Quay Lại Portal</a>
    </header>
    <?php if ($error): ?>
        <p class="milk-notice" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <p><?= $numRows ?> sản phẩm</p>
    <div class="milk-cards">
        <?php foreach ($items as $row): $image = basename(str_replace('\\','/',(string)$row['Hinh'])); ?>
            <article class="sol-glass-card milk-panel">
                <?php if ($image !== '' && is_file(dirname(__DIR__,5).'/assets/Hinh_sua/'.$image)): ?>
                    <img class="milk-photo" src="assets/Hinh_sua/<?= rawurlencode($image) ?>" alt="<?= htmlspecialchars($row['Ten_sua']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="milk-no-photo">
                        Chưa có ảnh
                    </div>
                <?php endif; ?>
                <h2><?= htmlspecialchars($row['Ten_sua']) ?></h2>
                <p><?= htmlspecialchars($row['Ten_hang_sua'].' · '.$row['Ten_loai']) ?></p>
                <p>
                    <?= (int)$row['Trong_luong'] ?>
                    g/ml ·
                    <strong>
                        <?= number_format((float)$row['Don_gia'],0,',','.') ?>
                        đ
                    </strong>
                </p>
                <a class="sol-btn sol-btn--outline" href="index.php?page=exercises&amp;exercise=21&amp;milk=<?= rawurlencode($row['Ma_sua']) ?>">Xem chi tiết</a>
            </article>
        <?php endforeach; ?>
    </div>
    <nav class="milk-actions milk-pager" aria-label="Phân trang">
        <?php if ($currentPage > 1): ?>
            <a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?<?= htmlspecialchars(http_build_query(['page'=>'exercises', 'exercise'=>20, 'p'=>$currentPage - 1] + $filters)) ?>">Back</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $maxPage; $i++): ?>
            <?php if ($i === $currentPage): ?>
                <strong aria-current="page"><?= $i ?></strong>
            <?php else: ?>
                <a href="index.php?<?= htmlspecialchars(http_build_query(['page'=>'exercises', 'exercise'=>20, 'p'=>$i] + $filters)) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($currentPage < $maxPage): ?>
            <a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?<?= htmlspecialchars(http_build_query(['page'=>'exercises', 'exercise'=>20, 'p'=>$currentPage + 1] + $filters)) ?>">Next</a>
        <?php endif; ?>
    </nav>
</section>
<?php if ($conn) mysqli_close($conn); ?>
