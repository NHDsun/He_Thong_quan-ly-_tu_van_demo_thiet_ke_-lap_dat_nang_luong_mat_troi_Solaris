<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) require_once dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
});
use App\Hooks\UseProductCsrf;
use App\Hooks\UseProductSpecifications;
use App\Hooks\UseSolarCalculator;
use App\Modules\Member2_Products\ProductController;

session_set_save_handler(static fn () => true, static fn () => true, static fn () => '', static fn () => true, static fn () => true, static fn () => 0);
session_start();
$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void { if (!$condition) throw new RuntimeException($message); $checks++; };
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->sqliteCreateFunction('JSON_UNQUOTE', static fn ($value) => $value, 1);
$pdo->exec("CREATE TABLE users(id INTEGER PRIMARY KEY,role TEXT); INSERT INTO users VALUES(1,'admin'),(2,'customer')");
$pdo->exec('CREATE TABLE categories(id INTEGER PRIMARY KEY,name TEXT,slug TEXT,description TEXT)');
$categoryId = 0;
$categoryInsert = $pdo->prepare('INSERT INTO categories VALUES(?,?,?,?)');
foreach (['tam-pin' => 'Tấm pin năng lượng mặt trời', 'inverter' => 'Bộ biến tần Inverter', 'pin-luu-tru' => 'Pin lưu trữ điện', 'day-cap' => 'Dây cáp điện và phụ kiện'] as $slug => $name) $categoryInsert->execute([++$categoryId, $name, $slug, '']);
$pdo->exec('CREATE TABLE products(id INTEGER PRIMARY KEY AUTOINCREMENT,category_id INTEGER,product_code TEXT UNIQUE,name TEXT,capacity_watts INTEGER,price REAL,warranty_years INTEGER,technical_specs TEXT,image_url TEXT,stock_quantity INTEGER,is_active INTEGER,created_at TEXT DEFAULT CURRENT_TIMESTAMP)');
$insert = $pdo->prepare('INSERT INTO products(category_id,product_code,name,capacity_watts,price,warranty_years,technical_specs,image_url,stock_quantity,is_active) VALUES(?,?,?,?,?,?,?,?,?,?)');
for ($id = 1; $id <= 28; $id++) $insert->execute([1, 'PANEL-' . $id, 'Tấm pin Mono ' . (550 + $id) . ' W', 550 + $id, 2000000 + $id * 10000, 12, json_encode(['brand' => $id === 28 ? 'Hãng đã ẩn' : 'LONGi', 'power_w' => 550 + $id, 'pricing_unit' => 'tấm']), null, 0, $id === 28 ? 0 : 1]);
$insert->execute([2,'INV-1','Inverter 5kW',5000,15000000,5,'{"brand":"Deye","type":"Hybrid","rated_kw":5,"pricing_unit":"bộ"}',null,0,1]);
$insert->execute([3,'BAT-1','Pin lưu trữ 30.72kWh',30720,100000000,10,'{"brand":"Solaris","energy_kwh":30.72,"pricing_unit":"bộ"}',null,0,1]);
$insert->execute([4,'CAB-1','Cáp DC 6mm²',null,23600,2,'{"brand":"Solaris","pricing_unit":"m"}',null,0,1]);
$controller = new ProductController($pdo);
$_SESSION = ['user_id' => 1];
$token = UseProductCsrf::token();
$check($token === UseProductCsrf::token() && !UseProductCsrf::valid(null), 'Token không ổn định hoặc chấp nhận token thiếu.');
$_POST = ['product_id' => 1];
$check(!$controller->adminProductAction('admin_delete')['ok'] && (int) $pdo->query('SELECT is_active FROM products WHERE id=1')->fetchColumn() === 1, 'Thiếu CSRF vẫn ẩn được sản phẩm.');
$_POST['csrf_token'] = 'wrong';
$check(!$controller->adminProductAction('admin_delete')['ok'], 'Token sai vẫn được chấp nhận.');
$_POST['csrf_token'] = $token;
$check($controller->adminProductAction('admin_delete')['ok'], 'Admin không ẩn được sản phẩm.');
$check($controller->detail(1)['product']['is_active'] === 0, 'Admin không xem được sản phẩm đã ẩn.');
$_GET = ['visibility' => 'hidden']; $management = $controller->manage();
$check($management['counts']['hidden'] === 2 && count($management['products']) === 2, 'Danh sách sản phẩm đã ẩn không đúng.');
$check($controller->adminProductAction('admin_restore')['ok'], 'Admin không khôi phục được sản phẩm.');
$_SESSION['user_id'] = 2;
$check(!$controller->manage()['allowed'], 'Khách hàng mở được danh sách quản trị.');
$_POST['product_id'] = 28;
$check(!$controller->adminProductAction('admin_restore')['ok'] && $controller->detail(28)['product'] === null, 'Khách hàng khôi phục hoặc xem được sản phẩm ẩn.');
$_SESSION['user_id'] = 1;
$_GET = ['q' => 'Tấm pin', 'visibility' => 'active', 'p' => 2];
$management = $controller->manage();
$check($management['pager']['page'] === 2 && $management['pager']['total'] === 27 && count($management['products']) === 12, 'Phân trang quản trị sai.');
$_GET = ['category' => 'tam-pin']; $catalog = $controller->index();
$check(!$catalog['usingFallback'] && $catalog['filterOptions']['brands'] === ['LONGi'], 'Thương hiệu không được giới hạn theo danh mục / trạng thái.');
$check($catalog['filterOptions']['brands_by_category']['inverter'] === ['Deye'], 'Thiếu danh sách hãng theo inverter.');
$_GET = ['ids' => [2,1]]; $comparison = $controller->compare();
$check(array_column($comparison['products'], 'id') === [2,1], 'So sánh không giữ đúng sản phẩm đã chọn.');
foreach ([[1], [1,2,3,4], [1,1], [1,29], [1,28], ['<script>',2]] as $ids) { $_GET = ['ids' => $ids]; $check((bool) $controller->compare()['error'], 'Chấp nhận danh sách so sánh không hợp lệ.'); }

$_POST = ['csrf_token' => $token, 'cart_action' => 'add', 'product_id' => 1];
$check($controller->handleCart('add')['ok'], 'Không thêm được thiết bị với token hợp lệ.');
$_POST['product_id'] = 29; $controller->handleCart('add');
$_SESSION['solar_cart'][1]['quantity'] = 20;
$_SESSION['solar_demo_selection'] = [1,29];
$before = $_SESSION['solar_cart'];
$_POST = ['csrf_token' => $token, 'selected' => [], 'quantity' => [1 => 12,29 => 1]];
$check(!$controller->handleCart('demo_selected')['ok'] && $_SESSION['solar_cart'] === $before, 'Bỏ chọn hết vẫn đồng bộ hoặc làm thay đổi kế hoạch.');
foreach ([['selected' => [999], 'quantity' => [1 => 12]], ['selected' => [1], 'quantity' => [1 => 12,29 => 0]], ['selected' => [1], 'quantity' => [1 => '1.5']], ['selected' => [1], 'quantity' => [1 => 100]], ['selected' => [1], 'quantity' => []]] as $payload) {
    $_POST = ['csrf_token' => $token] + $payload;
    $check(!$controller->handleCart('demo_selected')['ok'] && $_SESSION['solar_cart'] === $before, 'Số lượng / lựa chọn sai đã thay đổi session.');
}
$_POST = ['csrf_token' => $token, 'selected' => [1], 'quantity' => [1 => 12,29 => 2]];
$check($controller->handleCart('demo_selected')['ok'], 'Không đồng bộ được số lượng mới.');
$demo = ProductController::demoItems();
$check(count($demo) === 1 && $demo[0]['quantity'] === 12 && ProductController::cartItems()[29]['quantity'] === 2, 'Sai số lượng / lựa chọn bàn giao cho TV3.');
$check(UseSolarCalculator::analyzeCompatibility($demo)['total_panels'] === 12, 'TV3 không nhận đúng số lượng đã chọn.');
$_POST = ['product_id' => 1];
$check(!$controller->handleCart('clear')['ok'] && count(ProductController::cartItems()) === 2, 'Thiếu CSRF vẫn xóa được kế hoạch.');
$battery = ['category_name' => 'Pin lưu trữ điện', 'capacity_watts' => 30720, 'quantity' => 2, 'price' => 100000000];
$check(UseProductSpecifications::capacityLabel($battery,2) === '61,44 kWh', 'Pin lưu trữ vẫn hiển thị công suất thay vì dung lượng.');
$legacyCable = [['id' => 31,'category_name' => 'Dây cáp điện và phụ kiện','quantity' => 3,'price' => 123,'capacity_watts' => null]];
$display = $controller->cartDisplayItems($legacyCable);
$check(UseProductSpecifications::priceLabel($display[0]) === '123 đ/m' && $legacyCable[0]['price'] === $display[0]['price'], 'Hiển thị đơn vị giá đã thay đổi giá kế hoạch cũ.');

$valid = ['csrf_token' => $token,'name' => 'Sản phẩm mới','product_code' => 'NEW','category_id' => 2,'price' => '1000000','warranty_years' => '5','specs' => ['rated_kw' => '5'],'spec_metadata' => []];
foreach (['price' => ['-1','abc','1.234','1e999'], 'warranty_years' => ['-1','1.5','101'], 'name' => ['',str_repeat('x',256)], 'product_code' => [str_repeat('x',51)]] as $field => $badValues) foreach ($badValues as $value) {
    $_POST = array_replace($valid, [$field => $value]); $result = $controller->adminProductAction('admin_create');
    $check(!$result['ok'] && isset($result['fieldErrors'][$field]), 'Không báo đúng trường nhập sai: ' . $field);
}
$_POST = array_replace($valid, ['product_code' => 'INV-1']); $result = $controller->adminProductAction('admin_create');
$check(!$result['ok'] && isset($result['fieldErrors']['product_code']) && !str_contains($result['message'], 'SQL'), 'Lỗi mã trùng còn lộ thông tin SQL.');

$preview = $argv[1] ?? '';
if (str_starts_with($preview,'--preview-')) {
    $_GET = ['category' => 'tam-pin'];
    $productView = match ($preview) { '--preview-create' => $controller->create(), '--preview-manage' => $controller->manage(), '--preview-compare' => (static function () use ($controller): array { $_GET['ids'] = [1,2,3]; return $controller->compare(); })(), default => $controller->index() };
    $productView['isAdmin'] = true;
    ob_start(); include dirname(__DIR__) . '/resources/views/pages/products.blade.php'; $html = ob_get_clean();
    $html = preg_replace('/<link\b[^>]*>|<script\b[^>]*\bsrc=[^>]*>\s*<\/script>/i','',$html);
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Kiểm tra TV2</title><style>';
    foreach (['solaris-theme.css','products.css','products-polish.css','products-final.css','products-card-fix.css','products-filter.css','products-admin.css','products-price.css','products-workspace.css'] as $css) echo file_get_contents(dirname(__DIR__) . '/resources/css/' . $css);
    echo '</style><body>' . $html;
    foreach (['products-filter.js','products-price.js','products-image.js','products-catalog.js'] as $js) echo '<script>' . file_get_contents(dirname(__DIR__) . '/resources/js/' . $js) . '</script>';
    echo '</body></html>'; exit;
}
echo "PASS: {$checks} kiểm tra CSRF, số lượng / lựa chọn Demo, đơn vị, khôi phục / phân quyền, hãng theo danh mục, so sánh và dữ liệu quản trị. Không dùng CSDL thật.\n";
