<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) require_once dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
});

use App\Hooks\UseProductFilters;
use App\Modules\Member2_Products\ProductController;

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) throw new RuntimeException($message);
    $checks++;
};
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->sqliteCreateFunction('JSON_UNQUOTE', static fn ($value) => $value, 1);
$pdo->exec('CREATE TABLE categories (id INTEGER PRIMARY KEY, name TEXT, slug TEXT, description TEXT)');
$insert = $pdo->prepare('INSERT INTO categories VALUES (?,?,?,?)');
foreach (['tam-pin', 'inverter', 'pin-luu-tru', 'day-cap'] as $index => $slug) $insert->execute([$index + 1, $slug, $slug, '']);
$pdo->exec('CREATE TABLE products (id INTEGER PRIMARY KEY, category_id INTEGER, name TEXT, product_code TEXT, price REAL, capacity_watts INTEGER, warranty_years INTEGER, technical_specs TEXT, created_at TEXT, is_active INTEGER, image_url TEXT)');
$insert = $pdo->prepare('INSERT INTO products VALUES (?,?,?,?,?,?,?,?,?,?,?)');
for ($id = 1; $id <= 32; $id++) {
    $category = $id <= 26 ? 1 : ($id <= 28 ? 2 : ($id <= 31 ? 3 : 4));
    $specs = ['brand' => $id % 2 ? 'Deye' : 'LONGi'];
    if ($category === 2) $specs['type'] = $id === 27 ? 'Hybrid' : 'On-grid';
    if ($id === 29) $specs['energy_kwh'] = 5.12;
    if ($id === 30) $specs['energy_kwh'] = 10.24;
    $capacity = match ($category) { 1 => 500 + $id, 2 => $id === 27 ? 5000 : 10000, 3 => 999999, default => null };
    $insert->execute([$id, $category, 'Device ' . $id, 'TEST-' . $id, 100000 * $id, $capacity, $id % 4 + 5, json_encode($specs), '2026-10-01', $id === 26 ? 0 : 1, null]);
}
$controller = new ProductController($pdo);
$all = $pdo->query('SELECT p.*, c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1')->fetchAll();
$cases = [
    [],
    ['category' => 'tam-pin', 'q' => 'Device', 'min_capacity' => '505', 'max_capacity' => '520', 'brand' => 'LONGi', 'min_warranty' => '6'],
    ['category' => 'inverter', 'min_power_kw' => '4.5', 'max_power_kw' => '5.5', 'system_type' => 'Hybrid'],
    ['category' => 'pin-luu-tru', 'min_energy' => '5', 'max_energy' => '6'],
    ['category' => 'pin-luu-tru', 'min_energy' => '0'],
    ['category' => 'day-cap', 'min_capacity' => '9999999', 'system_type' => 'Hybrid'],
    ['q' => 'TEST-12'],
    ['min_price' => '500000', 'max_price' => '2100000', 'min_warranty' => '7'],
    ['q' => 'missing'],
];
foreach ($cases as $case) foreach (array_keys(UseProductFilters::SORTS) as $sort) {
    $_GET = $case + ['sort' => $sort];
    $data = $controller->index();
    $check(!$data['usingFallback'], 'Truy vấn SQL bị lỗi và chuyển sang dữ liệu demo.');
    $expected = UseProductFilters::filterProducts($all, $data['filters']);
    $check($data['pager']['total'] === count($expected), 'SQL và bộ lọc dữ liệu minh họa trả tổng khác nhau.');
    $check(array_column($data['products'], 'id') === array_column(array_slice($expected, 0, 12), 'id'), 'Sai thứ tự hoặc sai đơn vị lọc.');
}
$_GET = ['category' => 'tam-pin', 'q' => 'Device', 'p' => '2', 'sort' => 'price_asc'];
$second = $controller->index();
$check($second['pager']['page'] === 2 && count($second['products']) === 12 && (int) $second['products'][0]['id'] === 13, 'Phân trang sau lọc không đúng.');
$_GET['p'] = '999';
$check($controller->index()['pager']['page'] === 3, 'Không giới hạn trang vượt tổng số trang.');
$_GET = ['min_price' => '100', 'max_price' => '10'];
$invalid = $controller->index();
$check(count($invalid['filterErrors']) === 1 && !$invalid['products'], 'Không thông báo khoảng nhập ngược.');
foreach (['-1', 'NaN', '1e999'] as $bad) $check((bool) UseProductFilters::read(['min_price' => $bad])['filterErrors'], 'Chấp nhận số không hợp lệ.');
$check(UseProductFilters::read(['q' => ['invalid'], 'sort' => 'price DESC; DROP TABLE products'])['filters']['sort'] === 'newest', 'Không giới hạn lựa chọn sắp xếp.');

$input = ['category' => 'inverter', 'q' => 'Hybrid & Deye', 'brand' => 'Deye', 'system_type' => 'Hybrid', 'min_price' => 0, 'max_price' => 50000000, 'min_power_kw' => 5, 'max_power_kw' => 10, 'min_warranty' => 5, 'sort' => 'price_asc'];
$filters = UseProductFilters::read($input)['filters'];
parse_str((string) parse_url(UseProductFilters::url($filters, ['p' => 2, 'product' => 27]), PHP_URL_QUERY), $params);
foreach ($input as $key => $value) $check((string) $params[$key] === (string) $value, 'Liên kết làm mất điều kiện ' . $key);
$check($params['p'] === '2' && $params['product'] === '27' && !isset($params['search']), 'Sai tham số liên kết chi tiết.');
parse_str((string) parse_url(UseProductFilters::url($filters, ['brand' => '', 'p' => 1]), PHP_URL_QUERY), $params);
$check(!isset($params['brand']) && $params['q'] === $input['q'] && $params['p'] === '1', 'Xóa một nhãn làm mất điều kiện khác.');
parse_str((string) parse_url(UseProductFilters::url($filters, ['category' => 'day-cap']), PHP_URL_QUERY), $params);
$check(!isset($params['min_power_kw'], $params['system_type']), 'Đổi danh mục vẫn còn lọc không phù hợp.');

$offline = new ProductController(new PDO('sqlite::memory:'));
$_GET = ['category' => 'pin-luu-tru', 'min_energy' => '9', 'max_energy' => '11'];
$demo = $offline->index();
$check($demo['usingFallback'] && count($demo['products']) === 1, 'Dữ liệu minh họa không lọc theo kWh.');
$check(UseProductFilters::capacityLabel($demo['products'][0]) === '10 kWh', 'Hiển thị sai đơn vị pin lưu trữ.');
$check(UseProductFilters::capacityLabel(['category_slug' => 'inverter', 'capacity_watts' => 5500]) === '5.5 kW', 'Hiển thị sai đơn vị inverter.');
$check(UseProductFilters::capacityLabel(['category_slug' => 'tam-pin', 'capacity_watts' => 550]) === '550 W', 'Hiển thị sai đơn vị tấm pin.');

$_GET = ['category' => 'tam-pin', 'q' => 'Device', 'p' => 2, 'sort' => 'price_asc'];
$productView = $controller->index();
$_SESSION = [];
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$html = ob_get_clean();
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
$xpath = new DOMXPath($dom);
$check($xpath->query('//form[@method="get"]')->length === 1, 'Catalog còn nhiều form lọc.');
$check($xpath->query('//input[@name="q"]')->length === 1 && $xpath->query('//input[@name="min_price"]')->length === 1, 'Trùng ô tìm kiếm hoặc khoảng giá.');
$check($xpath->query('//select[@name="sort" and @form="product-filter-form"]')->length === 1, 'Sắp xếp không liên kết với bộ lọc.');
$check(!str_contains($html, 'action=create'), 'Catalog hiện nút thêm cho khách.');
$check(str_contains($html, 'products-filter-bar') && !str_contains($html, 'products-sidebar'), 'Bộ lọc chưa chuyển sang thanh ngang.');
$check($xpath->query('//span[@data-product-price]')->length === 2 && $xpath->query('//button[@data-price-direction]')->length === 4, 'Thiếu nút tăng / giảm giá trong bộ lọc.');
foreach (['tam-pin' => 100000, 'inverter' => 500000, 'pin-luu-tru' => 1000000, 'tu-dien' => 50000, 'khung-gia' => 10000, 'day-cap' => 1000, 'dong-ho' => 50000, '' => 100000] as $category => $step) {
    $check(UseProductFilters::priceStep($category) === $step, 'Bước giá không phù hợp danh mục ' . $category);
}
$check($xpath->query('//input[@name="min_price" and @step="any"]')->length === 1, 'Bộ lọc ép làm tròn giá theo bước nhảy.');
$productView['isAdmin'] = true;
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$adminCatalog = ob_get_clean();
$check(str_contains($adminCatalog, 'action=create') && !str_contains($adminCatalog, 'data-product-editor'), 'Catalog admin vẫn nhúng form thêm sản phẩm.');
foreach ($xpath->query('//nav[contains(@class,"products-pager")]/a') as $link) {
    parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $params);
    $check($params['q'] === 'Device' && $params['category'] === 'tam-pin' && $params['sort'] === 'price_asc', 'Liên kết phân trang làm mất bộ lọc.');
}
$check(str_contains($html, 'rel="prev"') && str_contains($html, 'rel="next"'), 'Thiếu nút Trước/Sau.');
$productView = $controller->detail(13);
ob_start();
include dirname(__DIR__) . '/resources/views/pages/products.blade.php';
$detail = ob_get_clean();
$check(str_contains($detail, 'q=Device') && str_contains($detail, 'p=2') && str_contains($detail, 'sort=price_asc'), 'Quay lại từ chi tiết làm mất bộ lọc / trang.');
echo "PASS: {$checks} kiểm tra bộ lọc SQL / demo, W/kW/kWh, sắp xếp, phân trang, URL và giao diện. Không dùng CSDL thật.\n";
