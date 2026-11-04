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
if (session_status() !== PHP_SESSION_ACTIVE)
    session_start();
$_SESSION['milk_csrf'] ??= bin2hex(random_bytes(32));
$brands = $types = [];
$newProduct = null;
$message = '';
$values = array_fill_keys(['Ma_sua', 'Ten_sua', 'Ma_hang_sua', 'Ma_loai_sua', 'Trong_luong', 'Don_gia', 'TP_Dinh_Duong', 'Loi_ich', 'Hinh'], '');
if ($conn) {
    $result = mysqli_query($conn, 'SELECT Ma_hang_sua,Ten_hang_sua FROM hang_sua ORDER BY Ten_hang_sua');
    if ($result)
        $brands = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $result = mysqli_query($conn, 'SELECT Ma_loai_sua,Ten_loai FROM loai_sua ORDER BY Ten_loai');
    if ($result)
        $types = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $value)
        $values[$key] = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    $values['Hinh'] = '';
    $errors = [];
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['milk_csrf'], $_POST['csrf']))
        $errors[] = 'Phiên biểu mẫu hết hạn. Hãy tải lại trang.';
    if (!preg_match('/^[a-zA-Z0-9_-]{1,6}$/D', $values['Ma_sua']))
        $errors[] = 'Mã sữa gồm 1–6 ký tự chữ, số, gạch ngang hoặc gạch dưới.';
    if (!preg_match('/^.{1,100}$/us', $values['Ten_sua']))
        $errors[] = 'Tên sữa bắt buộc, tối đa 100 ký tự.';
    if (!in_array($values['Ma_hang_sua'], array_column($brands, 'Ma_hang_sua'), true))
        $errors[] = 'Hãy chọn hãng sữa hợp lệ.';
    if (!in_array($values['Ma_loai_sua'], array_column($types, 'Ma_loai_sua'), true))
        $errors[] = 'Hãy chọn loại sữa hợp lệ.';
    foreach (['Trong_luong' => 'Trọng lượng', 'Don_gia' => 'Đơn giá'] as $key => $label) {
        if (!ctype_digit($values[$key]) || (float)$values[$key] > 2147483647 || ($key === 'Trong_luong' && (int)$values[$key] === 0))
            $errors[] = $label . ' phải là số nguyên ' . ($key === 'Trong_luong' ? 'dương' : 'không âm') . ', tối đa 2.147.483.647.';
    }
    foreach (['TP_Dinh_Duong', 'Loi_ich'] as $key)
        if (strlen($values[$key]) > 10000)
            $errors[] = 'Thành phần và lợi ích tối đa 10.000 byte mỗi mục.';
    $upload = $_FILES['Hinh'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    $imageExtension = '';
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if (($upload['error'] ?? -1) !== UPLOAD_ERR_OK || !is_string($upload['tmp_name'] ?? null) || !is_uploaded_file($upload['tmp_name'])) {
            $errors[] = 'Không nhận được ảnh hợp lệ. Ảnh tối đa 5MB.';
        } elseif (filesize($upload['tmp_name']) === 0 || filesize($upload['tmp_name']) > 5 * 1024 * 1024) {
            $errors[] = 'Ảnh phải có dung lượng từ 1 byte đến 5MB.';
        } else {
            $extension = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $upload['tmp_name']);
            finfo_close($finfo);
            $allowed = ['jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'png'=>'image/png'];
            if (!isset($allowed[$extension]) || $allowed[$extension] !== $mime || @getimagesize($upload['tmp_name']) === false) {
                $errors[] = 'Chỉ nhận ảnh JPG, JPEG hoặc PNG đúng định dạng.';
            } else {
                $imageExtension = $extension;
            }
        }
    }

    if ($conn && !$errors) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO sua (Ma_sua,Ten_sua,Ma_hang_sua,Ma_loai_sua,Trong_luong,Don_gia,TP_Dinh_Duong,Loi_ich,Hinh) VALUES (?,?,?,?,?,?,?,?,?)');
        if ($stmt) {
            $savedImage = '';
            if ($imageExtension !== '') {
                $values['Hinh'] = hash('sha256', uniqid('', true).$upload['name']).'.'.$imageExtension;
                $savedImage = dirname(__DIR__,5).'/assets/Hinh_sua/'.$values['Hinh'];
                if (!move_uploaded_file($upload['tmp_name'], $savedImage)) {
                    $errors[] = 'Không lưu được ảnh vào assets/Hinh_sua.';
                    $savedImage = '';
                }
            }
            if (!$errors) {
                mysqli_stmt_bind_param($stmt, 'ssssiisss', $values['Ma_sua'], $values['Ten_sua'], $values['Ma_hang_sua'], $values['Ma_loai_sua'], $values['Trong_luong'], $values['Don_gia'], $values['TP_Dinh_Duong'], $values['Loi_ich'], $values['Hinh']);
                if (mysqli_stmt_execute($stmt)) {
                    $message = 'Thêm sữa thành công!';
                    $newProduct = $values;
                    foreach ($brands as $brand) {
                        if ($brand['Ma_hang_sua'] === $values['Ma_hang_sua']) {
                            $newProduct['Ten_hang_sua'] = $brand['Ten_hang_sua'];
                            break;
                        }
                    }
                    $_SESSION['milk_csrf'] = bin2hex(random_bytes(32));
                } else {
                    if ($savedImage !== '') unlink($savedImage);
                    $errors[] = mysqli_stmt_errno($stmt) === 1062 ? 'Mã sữa đã tồn tại.' : 'Không thêm được sữa. Hãy kiểm tra dữ liệu.';
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Không chuẩn bị được câu lệnh thêm sữa.';
        }
    }

    if ($errors)
        $error = trim($error . ' ' . implode(' ', $errors));
}
?>
<link rel="stylesheet" href="resources/css/milk-exercises.css">
<section class="sol-glass-card milk-exercises milk-search milk-create">
    <header class="milk-heading">
        <div>
            <span class="sol-badge sol-badge--amber">BÀI TẬP 22 (MYSQL 7) • THÀNH VIÊN 4</span>
            <h2>THÊM SỮA MỚI</h2>
        </div>
        <a class="sol-btn sol-btn--secondary sol-btn--sm" href="index.php?page=exercises">⬅ Quay Lại Portal</a>
    </header>
    <?php if ($error): ?>
        <p class="milk-notice" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="index.php?page=exercises&amp;exercise=22" enctype="multipart/form-data" class="milk-form milk-create-form">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['milk_csrf']) ?>">
        <?php foreach (['Ma_sua'=>['Mã sữa (tối đa 6 ký tự)',6],'Ten_sua'=>['Tên sữa',100]] as $key => [$label,$length]): ?>
            <label>
                <?= $label ?>
                *
                <input name="<?= $key ?>" maxlength="<?= $length ?>" value="<?= htmlspecialchars($values[$key]) ?>" required>
            </label>
        <?php endforeach; ?>
        <?php foreach (['Ma_hang_sua'=>[$brands,'Ma_hang_sua','Ten_hang_sua','Hãng sữa'],'Ma_loai_sua'=>[$types,'Ma_loai_sua','Ten_loai','Loại sữa']] as $key => [$options,$code,$name,$label]): ?>
            <label>
                <?= $label ?>
                *
                <select name="<?= $key ?>" required>
                    <option value="">Chọn <?= $label ?></option>
                    <?php foreach ($options as $option): ?>
                        <option value="<?= htmlspecialchars($option[$code]) ?>" <?= $values[$key] === $option[$code] ? 'selected' : '' ?>><?= htmlspecialchars($option[$name]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endforeach; ?>
        <?php foreach (['Trong_luong'=>'Trọng lượng (g/ml)','Don_gia'=>'Đơn giá (đ)'] as $key => $label): ?>
            <label>
                <?= $label ?>
                *
                <input type="number" name="<?= $key ?>" min="<?= $key === 'Trong_luong' ? 1 : 0 ?>" max="2147483647" step="1" value="<?= htmlspecialchars($values[$key]) ?>" required>
            </label>
        <?php endforeach; ?>
        <?php foreach (['TP_Dinh_Duong'=>'Thành phần dinh dưỡng','Loi_ich'=>'Lợi ích'] as $key => $label): ?>
            <label>
                <?= $label ?>
                <textarea name="<?= $key ?>" rows="4" maxlength="3000"><?= htmlspecialchars($values[$key]) ?></textarea>
            </label>
        <?php endforeach; ?>
        <label>
            Hình ảnh:
            <input type="file" name="Hinh" accept=".jpg,.jpeg,.png">
        </label>
        <p class="milk-upload-hint">Ảnh JPG/PNG, tối đa 5MB. Khi nhập sai, vui lòng chọn lại file ảnh.</p>
        <div class="milk-actions">
            <button type="submit" class="sol-btn sol-btn--solar">Thêm mới</button>
            <a class="sol-btn sol-btn--outline" href="index.php?page=exercises&amp;exercise=22">Nhập lại</a>
        </div>
    </form>
    <?php if ($newProduct): ?>
        <h3 class="milk-create-result-title">Kết quả sau khi thêm mới thành công</h3>
        <p class="milk-search-count" role="status"><?= htmlspecialchars($message) ?></p>
        <?php $row = $newProduct; ?>
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
</section>
<?php if ($conn) mysqli_close($conn); ?>
