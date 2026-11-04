<?php
declare(strict_types=1);

if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) { http_response_code(404); exit; }
if (PHP_SAPI === 'cli-server' && (!getenv('TV2_IMAGE_TEST_TOKEN') || !hash_equals(getenv('TV2_IMAGE_TEST_TOKEN'), $_SERVER['HTTP_X_TEST_TOKEN'] ?? ''))) { http_response_code(404); exit; }

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) require_once dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
});

use App\Hooks\UseProductImageUpload;
use App\Modules\Member2_Products\ProductController;

$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, role TEXT); INSERT INTO users VALUES (1,'admin'),(2,'customer')");
$pdo->exec("CREATE TABLE categories (id INTEGER PRIMARY KEY, slug TEXT); INSERT INTO categories VALUES (2,'inverter')");
$pdo->exec('CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER, product_code TEXT UNIQUE, name TEXT, capacity_watts INTEGER, price REAL, warranty_years INTEGER, technical_specs TEXT, image_url TEXT, stock_quantity INTEGER, is_active INTEGER)');
$pdo->exec("INSERT INTO products VALUES (1,2,'EXISTING','Existing inverter',5000,10000000,5,'{}','old.jpg',0,1)");
$controller = new ProductController($pdo);

if (PHP_SAPI === 'cli-server') {
    $_SESSION = ['user_id' => ($_SERVER['HTTP_X_TEST_CASE'] ?? '') === 'unauthorized' ? 2 : 1];
    $pattern = dirname(__DIR__) . '/assets/products/*';
    $before = glob($pattern) ?: [];
    $result = $controller->adminProductAction((string) ($_POST['cart_action'] ?? ''));
    $after = glob($pattern) ?: [];
    $newFiles = array_values(array_diff($after, $before));
    $record = $pdo->query('SELECT * FROM products ORDER BY id DESC LIMIT 1')->fetch();
    $response = ['result' => $result, 'record' => $record, 'new_files' => count($newFiles), 'image_hash' => null];
    foreach ($newFiles as $path) {
        $response['image_hash'] = hash_file('sha256', $path);
        UseProductImageUpload::discard(basename($path));
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) throw new RuntimeException($message);
    $checks++;
};
session_set_save_handler(static fn () => true, static fn () => true, static fn () => '', static fn () => true, static fn () => true, static fn () => 0);
session_start();
$_SESSION = ['user_id' => 42, 'other_state' => 'keep', 'solar_cart' => [1 => ['id' => 1, 'quantity' => 20], 2 => ['id' => 2, 'quantity' => 1]], 'solar_demo_selection' => [1]];
$_POST = ['selected' => [1]];
$controller->handleCart('clear');
$check(ProductController::cartItems() === [] && ProductController::cartCount() === 0, 'Chưa xóa toàn bộ kế hoạch.');
$check(ProductController::demoItems() === [] && !isset($_SESSION['solar_demo_selection']), 'Còn lựa chọn Demo sau khi xóa toàn bộ.');
$check($_SESSION['user_id'] === 42 && $_SESSION['other_state'] === 'keep', 'Xóa kế hoạch ảnh hưởng session khác.');
$check((int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() === 1, 'Xóa kế hoạch ảnh hưởng catalog sản phẩm.');
$controller->handleCart('clear');
$check(ProductController::cartItems() === [], 'Không xử lý được kế hoạch đã trống.');

$temporary = [];
$process = null;
register_shutdown_function(static function () use (&$temporary, &$process): void {
    if (is_resource($process)) { proc_terminate($process); proc_close($process); }
    foreach ($temporary as $path) if (is_file($path)) unlink($path);
});
$fixture = tempnam(sys_get_temp_dir(), 'tv2-image-'); $temporary[] = $fixture;
file_put_contents($fixture, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
$fake = tempnam(sys_get_temp_dir(), 'tv2-fake-'); $temporary[] = $fake; file_put_contents($fake, '<?php echo 1;');
$large = tempnam(sys_get_temp_dir(), 'tv2-large-'); $temporary[] = $large; file_put_contents($large, str_repeat('x', UseProductImageUpload::MAX_BYTES + 1));
$log = tempnam(sys_get_temp_dir(), 'tv2-server-'); $temporary[] = $log;
$listener = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if (!$listener) throw new RuntimeException($error);
$address = stream_socket_get_name($listener, false); fclose($listener);
$token = bin2hex(random_bytes(24)); putenv('TV2_IMAGE_TEST_TOKEN=' . $token);
$process = proc_open([PHP_BINARY, '-d', 'upload_max_filesize=8M', '-d', 'post_max_size=10M', '-d', 'upload_tmp_dir=' . sys_get_temp_dir(), '-S', $address, __FILE__], [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $pipes, dirname(__DIR__));
if (!is_resource($process)) throw new RuntimeException('Không khởi động được máy chủ kiểm thử.');
fclose($pipes[0]);
$started = false;
for ($attempt = 0; $attempt < 40; $attempt++) {
    $connection = @stream_socket_client('tcp://' . $address, $errno, $error, .1);
    if ($connection) { fclose($connection); $started = true; break; }
    usleep(50000);
}
$check($started, 'Máy chủ kiểm thử không sẵn sàng.');
$request = static function (string $case, array $changes = [], ?CURLFile $file = null) use ($address, $token): array {
    $fields = array_merge(['cart_action' => 'admin_create', 'name' => 'Upload test', 'product_code' => 'NEW', 'category_id' => 2, 'price' => 10000000, 'warranty_years' => 5, 'specs[rated_kw]' => 5, 'spec_metadata[demo_data]' => '0'], $changes);
    if ($file) $fields['product_image'] = $file;
    $curl = curl_init('http://' . $address . '/');
    curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $fields, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HTTPHEADER => ['X-Test-Token: ' . $token, 'X-Test-Case: ' . $case]]);
    $body = curl_exec($curl); $error = curl_error($curl); curl_close($curl);
    if ($body === false) throw new RuntimeException($error);
    try { return json_decode($body, true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException) { throw new RuntimeException('Phản hồi kiểm thử không hợp lệ: ' . substr($body, 0, 1500)); }
};
$image = static fn () => new CURLFile($fixture, 'image/png', 'local-photo.png');
$created = $request('valid', [], $image());
$check($created['result']['ok'] && $created['new_files'] === 1, 'Không tạo được sản phẩm cùng ảnh tải lên.');
$check((bool) preg_match('/^[a-f0-9]{64}\.png$/D', $created['record']['image_url']), 'Tên ảnh lưu không được băm an toàn.');
$check($created['image_hash'] === hash_file('sha256', $fixture), 'Ảnh tải lên không được lưu đúng nội dung.');
$updated = $request('update', ['cart_action' => 'admin_update', 'product_id' => 1], $image());
$check($updated['result']['ok'] && $updated['record']['image_url'] !== 'old.jpg', 'Không thay được ảnh khi sửa sản phẩm.');
$retained = $request('keep', ['cart_action' => 'admin_update', 'product_id' => 1]);
$check($retained['result']['ok'] && $retained['record']['image_url'] === 'old.jpg', 'Không chọn ảnh mới đã làm mất ảnh cũ.');
foreach ([['fake', new CURLFile($fake, 'image/png', 'fake.png')], ['script', new CURLFile($fake, 'application/octet-stream', 'photo.php')], ['large', new CURLFile($large, 'image/png', 'large.png')], ['unauthorized', $image()]] as [$case, $file]) {
    $rejected = $request($case, [], $file);
    $check(!$rejected['result']['ok'] && $rejected['new_files'] === 0, 'Chấp nhận upload không hợp lệ: ' . $case);
    $check($rejected['record']['image_url'] === 'old.jpg', 'Upload bị từ chối đã thay đổi ảnh cũ.');
}
$duplicate = $request('duplicate', ['product_code' => 'EXISTING'], $image());
$check(!$duplicate['result']['ok'] && $duplicate['new_files'] === 0, 'Lưu CSDL lỗi còn để lại ảnh tải lên.');
echo "PASS: {$checks} kiểm tra upload HTTP thật, ảnh giả / quá cỡ / phân quyền, thay và giữ ảnh, dọn ảnh khi lưu lỗi, xóa toàn bộ kế hoạch và lựa chọn Demo. Chỉ dùng CSDL bộ nhớ.\n";
