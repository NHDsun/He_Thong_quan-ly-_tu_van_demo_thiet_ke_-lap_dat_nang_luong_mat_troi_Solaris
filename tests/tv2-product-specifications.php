<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once dirname(__DIR__) . '/app/Hooks/UseProductSpecifications.php';
require_once dirname(__DIR__) . '/app/Hooks/UseProductFilters.php';
require_once dirname(__DIR__) . '/app/Modules/Member2_Products/ProductController.php';

use App\Hooks\UseProductSpecifications;
use App\Modules\Member2_Products\ProductController;

$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT); INSERT INTO users VALUES (1, \'admin\'), (2, \'customer\')');
$pdo->exec("CREATE TABLE categories (id INTEGER PRIMARY KEY, name TEXT, slug TEXT); INSERT INTO categories VALUES (1,'Tấm pin','tam-pin'), (2,'Inverter','inverter'), (3,'Tủ điện','tu-dien'), (4,'Khung giá','khung-gia'), (5,'Dây cáp','day-cap'), (6,'Đồng hồ','dong-ho'), (7,'Pin lưu trữ','pin-luu-tru')");
$pdo->exec('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER, product_code TEXT UNIQUE, name TEXT, capacity_watts INTEGER, price REAL, warranty_years INTEGER, technical_specs TEXT, image_url TEXT, stock_quantity INTEGER, is_active INTEGER)');
$_SESSION = ['user_id' => 1];
$controller = new ProductController($pdo);
$check($controller->create()['allowed'] && count($controller->create()['categories']) === 7, 'Admin không mở được trang thêm sản phẩm.');
$_POST = [
    'name' => 'Inverter kiểm thử', 'product_code' => 'TEST-INV', 'category_id' => '2',
    'price' => '12000000', 'capacity_watts' => '6000', 'warranty_years' => '5', 'image_url' => 'inv.jpg',
    'specs' => ['brand' => 'Solis', 'type' => 'Hybrid', 'rated_kw' => '6', 'efficiency' => '98.5%'],
    'spec_metadata' => ['demo_data' => '0', 'price_type' => 'gia_tham_khao_vnd', 'image_type' => 'anh_san_pham_thuc_te'],
];
$result = $controller->adminProductAction('admin_create');
$check($result['ok'], 'Không tạo được sản phẩm từ các ô nhập.');
$record = $pdo->query('SELECT * FROM products')->fetch();
$specs = UseProductSpecifications::decode($record['technical_specs']);
$check($specs['brand'] === 'Solis' && $specs['type'] === 'Hybrid', 'Brand / Type không được lưu chính xác.');
$check($specs['rated_kw'] === 6 && $specs['demo_data'] === false, 'Mất kiểu số hoặc boolean.');
$check((int) $record['capacity_watts'] === 6000 && $result['product_id'] === (int) $record['id'], 'Không đồng bộ kW hoặc thiếu ID để chuyển đến sản phẩm mới.');
$specs['legacy_nested'] = ['voltage' => 48, 'enabled' => true];
$specs['custom_note'] = 'Giữ lại';
$save = $pdo->prepare('UPDATE products SET technical_specs=? WHERE id=?');
$save->execute([json_encode($specs), $record['id']]);
$_POST['product_id'] = (string) $record['id'];
$_POST['specs'] = ['brand' => 'Deye', 'type' => 'Hybrid', 'rated_kw' => '6.5', 'efficiency' => '', 'custom_note' => 'Đã sửa'];
$check($controller->adminProductAction('admin_update')['ok'], 'Không cập nhật được thông số.');
$updated = $pdo->query('SELECT * FROM products')->fetch();
$saved = UseProductSpecifications::decode($updated['technical_specs']);
$check($saved['brand'] === 'Deye' && $saved['rated_kw'] === 6.5, 'Cập nhật trường chữ / số không đúng.');
$check((int) $updated['capacity_watts'] === 6500, 'Công suất lọc chưa đồng bộ với thông số inverter.');
$check($saved['legacy_nested'] === $specs['legacy_nested'] && $saved['custom_note'] === 'Đã sửa', 'Mất thông số cũ không có trong biểu mẫu.');
$check(!array_key_exists('efficiency', $saved), 'Không xóa được thông số khi để trống.');
$_POST['specs']['rated_kw'] = '-5';
$check(!$controller->adminProductAction('admin_update')['ok'], 'Không được chấp nhận công suất âm.');
$check($pdo->query('SELECT technical_specs FROM products')->fetchColumn() === $updated['technical_specs'], 'Dữ liệu đã thay đổi sau khi nhập sai.');
$_POST['specs']['rated_kw'] = 'NaN';
$check(!$controller->adminProductAction('admin_update')['ok'], 'Không được chấp nhận giá trị không phải số.');
$_POST['specs']['rated_kw'] = '6.5';
$_POST['spec_metadata']['demo_data'] = 'false';
$check(!$controller->adminProductAction('admin_update')['ok'], 'Không được chấp nhận nguồn dữ liệu ngoài danh sách.');
$_POST['spec_metadata']['demo_data'] = '0';
$_SESSION['user_id'] = 2;
$check(!$controller->create()['allowed'], 'Khách hàng mở được trang thêm sản phẩm.');
$check(!$controller->adminProductAction('admin_update')['ok'], 'Khách hàng không được sửa sản phẩm.');
$check($pdo->query('SELECT technical_specs FROM products')->fetchColumn() === $updated['technical_specs'], 'Thao tác bị từ chối đã làm thay đổi dữ liệu.');
$_SESSION = [];
$check(!$controller->create()['allowed'], 'Khách vãng lai mở được trang thêm sản phẩm.');
$check(!$controller->adminProductAction('admin_create')['ok'], 'Khách vãng lai không được tạo sản phẩm.');

$editorCategories = [
    ['id' => 1, 'name' => 'Tấm pin năng lượng mặt trời', 'slug' => 'tam-pin'],
    ['id' => 2, 'name' => 'Bộ biến tần Inverter', 'slug' => 'inverter'],
    ['id' => 7, 'name' => 'Pin lưu trữ', 'slug' => 'pin-luu-tru'],
];
$preview = $argv[1] ?? '';
$editorProduct = $preview === '--preview-create' ? [] : $updated;
ob_start();
include dirname(__DIR__) . '/resources/views/components/product-admin-form.blade.php';
$form = ob_get_clean();
$check(!str_contains($form, '<textarea') && !str_contains($form, 'name="technical_specs"'), 'Biểu mẫu vẫn yêu cầu nhập JSON.');
$check(str_contains($form, 'name="specs[brand]"') && str_contains($form, 'name="spec_metadata[demo_data]"'), 'Thiếu ô nhập có cấu trúc.');
$check(str_contains($form, 'data-product-price') && str_contains($form, 'Bước ±500.000 đ') && str_contains($form, 'step="any"'), 'Giá quản trị chưa có bước nhảy theo loại inverter.');
$check(str_contains($form, 'enctype="multipart/form-data"') && str_contains($form, 'type="file" name="product_image"'), 'Form chưa nhận ảnh từ máy tính.');
if (str_starts_with($preview, '--preview-')) {
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><style>';
    foreach (['solaris-theme.css', 'products.css', 'products-admin.css', 'products-price.css'] as $css) echo file_get_contents(dirname(__DIR__) . '/resources/css/' . $css);
    echo '</style><body><main class="products-page">' . $form . '</main><script>' . file_get_contents(dirname(__DIR__) . '/resources/js/products-price.js') . '</script><script>' . file_get_contents(dirname(__DIR__) . '/resources/js/products-image.js') . '</script></body></html>';
    exit;
}

$viewData = ['formInput' => [
    'cart_action' => 'admin_update', 'product_id' => $updated['id'],
    'name' => 'Tên chưa lưu', 'specs' => ['brand' => '<script>alert(1)</script>'],
    'spec_metadata' => ['demo_data' => '0'],
]];
ob_start();
include dirname(__DIR__) . '/resources/views/components/product-admin-form.blade.php';
$retryForm = ob_get_clean();
$check(str_contains($retryForm, 'Tên chưa lưu') && str_contains($retryForm, '&lt;script&gt;'), 'Không giữ hoặc escape nội dung nhập khi lưu lỗi.');
$check(!str_contains($retryForm, '<script>alert(1)</script>'), 'Nội dung nhập có thể chèn mã script.');

$productView = ['product' => $updated + ['category_name' => 'Inverter'], 'categories' => $editorCategories, 'isAdmin' => false];
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$detail = ob_get_clean();
$check(!str_contains($detail, '<dt>Demo Data') && !str_contains($detail, '<pre>'), 'Trang khách vẫn hiện thông tin quản trị / JSON.');
$check(str_contains($detail, 'Thương hiệu (Brand)') && str_contains($detail, 'Deye'), 'Thiếu thông số trên trang chi tiết.');
$check(!str_contains($detail, '<script>alert'), 'Nội dung thông số không an toàn.');
$productView['product']['category_slug'] = 'pin-luu-tru';
$productView['product']['technical_specs'] = json_encode(['rated_kw' => 999, 'energy_kwh' => 30.72]);
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$batteryDetail = ob_get_clean();
$check(str_contains($batteryDetail, 'Dung lượng lưu trữ (kWh)') && !str_contains($batteryDetail, 'Công suất định mức (kW)'), 'Trang chi tiết còn hiện thông số của danh mục cũ.');

$_SESSION = ['user_id' => 1];
foreach ([1 => ['power_w', '585', 585], 2 => ['rated_kw', '5.5', 5500], 7 => ['energy_kwh', '30.72', 30720], 5 => ['length', '100m', null]] as $categoryId => [$key, $value, $capacity]) {
    $_POST = ['name' => 'Thiết bị kiểm thử', 'product_code' => 'CATEGORY-' . $categoryId, 'category_id' => $categoryId, 'price' => '1000000', 'warranty_years' => 5, 'specs' => [$key => $value], 'spec_metadata' => []];
    if ($categoryId !== 7) $_POST['specs']['energy_kwh'] = '999';
    if ($categoryId !== 2) $_POST['specs']['rated_kw'] = '-100';
    $result = $controller->adminProductAction('admin_create');
    $check($result['ok'], 'Không lưu được danh mục ' . $categoryId);
    $categoryProduct = $pdo->query('SELECT * FROM products WHERE id=' . (int) $result['product_id'])->fetch();
    $check($categoryProduct['capacity_watts'] === $capacity, 'Sai dữ liệu công suất / dung lượng theo danh mục.');
    $categorySpecs = UseProductSpecifications::decode($categoryProduct['technical_specs']);
    $check($categoryId === 7 || !isset($categorySpecs['energy_kwh']), 'Lưu thông số pin lưu trữ vào danh mục khác.');
    $editorProduct = $categoryProduct;
    $editorCategories = $pdo->query('SELECT * FROM categories')->fetchAll();
    $viewData = [];
    ob_start();
    include dirname(__DIR__) . '/resources/views/components/product-admin-form.blade.php';
    $categoryForm = ob_get_clean();
    $formDom = new DOMDocument();
    @$formDom->loadHTML('<?xml encoding="UTF-8">' . $categoryForm);
    $xpath = new DOMXPath($formDom);
    foreach (['power_w' => 1, 'rated_kw' => 2, 'energy_kwh' => 7, 'length' => 5] as $field => $forCategory) {
        $control = $xpath->query('//input[@name="specs[' . $field . ']"]')->item(0);
        $check($control !== null && $control->hasAttribute('disabled') === ($categoryId !== $forCategory), 'Form hiện thông số sai danh mục: ' . $field);
    }
    $check(!str_contains($categoryForm, 'name="capacity_watts"'), 'Biểu mẫu còn yêu cầu nhập công suất W cho mọi danh mục.');
}
$_POST['category_id'] = 999;
$check(!$controller->adminProductAction('admin_create')['ok'], 'Lưu được danh mục không tồn tại.');
$productView = $controller->create();
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$createPage = ob_get_clean();
$check(str_contains($createPage, 'name="cart_action" value="admin_create"') && !str_contains($createPage, 'id="product-filter-form"'), 'Trang thêm sản phẩm chưa tách khỏi catalog.');
$_SESSION = [];
$productView = $controller->create();
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$guestCreate = ob_get_clean();
$check(!str_contains($guestCreate, 'data-product-editor') && str_contains($guestCreate, 'Chỉ quản trị viên'), 'Trang thêm vẫn hiện form cho khách.');
echo "PASS: tạo / sửa sản phẩm, kiểu dữ liệu, giữ thông số cũ, xóa thông số trống, kiểm tra dữ liệu sai, phân quyền và biểu mẫu. Không dùng CSDL thật.\n";
