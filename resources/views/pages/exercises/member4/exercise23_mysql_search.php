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
$filters = ['q' => ''];
$brands = $types = [];
$where = '1=1';
$params = [];
$numRows = 0;
$rowsPerPage = 10;
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$maxPage = 1;
$offset = 0;
foreach ($filters as $key => $value) {
    $filters[$key] = is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
}
if ($filters['q'] !== '') {
    $where .= " AND s.Ten_sua LIKE ? ESCAPE '='";
    $params[] = '%' . str_replace(['=', '%', '_'], ['==', '=%', '=_'], $filters['q']) . '%';
}
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
<section class="sol-glass-card milk-exercises milk-search">
    <header class="milk-heading">
        <div>
            <span class="sol-badge sol-badge--amber">BÀI TẬP 23 (MYSQL 8) • THÀNH VIÊN 4</span>
            <h2>TÌM KIẾM THÔNG TIN SỮA</h2>
        </div>
        <a class="sol-btn sol-btn--secondary sol-btn--sm" href="index.php?page=exercises">⬅ Quay Lại Portal</a>
    </header>
    <?php if ($error): ?>
        <p class="milk-notice" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="get" class="milk-form milk-search-form">
        <input type="hidden" name="page" value="exercises">
        <input type="hidden" name="exercise" value="23">
        <div class="milk-search-name">
            <label for="milk-search-name">Tên sữa:</label>
            <input id="milk-search-name" name="q" maxlength="100" value="<?= htmlspecialchars($filters['q']) ?>">
            <button class="sol-btn sol-btn--solar sol-btn--sm" type="submit">Tìm kiếm</button>
        </div>
    </form>
    <p class="milk-search-count">Có <?= $numRows ?> sản phẩm được tìm thấy</p>

    <?php if ($items): ?>
        <div class="milk-search-results">
            <?php foreach ($items as $row): ?>
                <?php $image = basename(str_replace('\\', '/', (string)$row['Hinh'])); ?>
                <article class="milk-search-result">
                    <h3><?= htmlspecialchars($row['Ten_sua'].' - '.$row['Ten_hang_sua']) ?></h3>
                    <div class="milk-search-product">
                        <div class="milk-search-image">
                            <?php if ($image !== '' && is_file(dirname(__DIR__,5).'/assets/Hinh_sua/'.$image)): ?>
                                <img src="assets/Hinh_sua/<?= rawurlencode($image) ?>" alt="<?= htmlspecialchars($row['Ten_sua']) ?>" loading="lazy">
                            <?php else: ?>
                                <span>Chưa có ảnh</span>
                            <?php endif; ?>
                        </div>
                        <div class="milk-search-info">
                            <p><strong><em>Thành phần dinh dưỡng:</em></strong><br><span class="milk-text"><?= htmlspecialchars($row['TP_Dinh_Duong'] ?: 'Chưa cập nhật.') ?></span></p>
                            <p><strong><em>Lợi ích:</em></strong><br><span class="milk-text"><?= htmlspecialchars($row['Loi_ich'] ?: 'Chưa cập nhật.') ?></span></p>
                            <p><strong><em>Trọng lượng:</em></strong> <?= (int)$row['Trong_luong'] ?> g/ml - <strong><em>Đơn giá:</em></strong> <span class="milk-search-price"><?= number_format((float)$row['Don_gia'],0,',','.') ?> VNĐ</span></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php elseif ($error === ''): ?>
        <p class="milk-search-empty">Không tìm thấy sản phẩm phù hợp.</p>
    <?php endif; ?>

    <nav class="milk-actions milk-pager" aria-label="Phân trang">
        <?php if ($currentPage > 1): ?>
            <a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?<?= htmlspecialchars(http_build_query(['page'=>'exercises', 'exercise'=>23, 'p'=>$currentPage - 1] + $filters)) ?>">Back</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $maxPage; $i++): ?>
            <?php if ($i === $currentPage): ?>
                <strong aria-current="page"><?= $i ?></strong>
            <?php else: ?>
                <a href="index.php?<?= htmlspecialchars(http_build_query(['page'=>'exercises', 'exercise'=>23, 'p'=>$i] + $filters)) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($currentPage < $maxPage): ?>
            <a class="sol-btn sol-btn--outline sol-btn--sm" href="index.php?<?= htmlspecialchars(http_build_query(['page'=>'exercises', 'exercise'=>23, 'p'=>$currentPage + 1] + $filters)) ?>">Next</a>
        <?php endif; ?>
    </nav>
</section>
<?php if ($conn) mysqli_close($conn); ?>
