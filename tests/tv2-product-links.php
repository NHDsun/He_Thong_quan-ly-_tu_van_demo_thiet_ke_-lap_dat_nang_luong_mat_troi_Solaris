<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) require_once dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
});

use App\Hooks\UseSolarCalculator;
use App\Hooks\UseProductRecommendations;

$check = static function (bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
};
$cartItems = [
    ['id' => 1, 'name' => 'Tấm pin 585W', 'product_code' => 'TEST-PANEL', 'category_name' => 'Tấm pin năng lượng mặt trời', 'capacity_watts' => 585, 'quantity' => 20, 'price' => 2000000, 'image_url' => null],
    ['id' => 2, 'name' => 'Inverter On-grid 6kW', 'product_code' => 'TEST-INVERTER', 'category_name' => 'Bộ biến tần Inverter', 'capacity_watts' => 6000, 'quantity' => 1, 'price' => 14000000, 'image_url' => null],
    ['id' => 3, 'name' => 'Pin Lithium 30.72kWh', 'product_code' => 'TEST-BATTERY', 'category_name' => 'Pin lưu trữ điện', 'capacity_watts' => 30720, 'quantity' => 1, 'price' => 100000000, 'image_url' => 'bat-20.jpg'],
];
$compatibility = UseSolarCalculator::analyzeCompatibility($cartItems);
$originalRules = $compatibility['rules'];
ob_start();
include dirname(__DIR__) . '/resources/views/pages/cart.blade.php';
$cart = ob_get_clean();
$check(str_contains($cart, 'system-map-icon has-product-image') && str_contains($cart, 'assets/products/bat-20.jpg'), 'Sơ đồ kế hoạch chưa hiển thị ảnh của sản phẩm đã chọn.');
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="UTF-8">' . $cart);
$xpath = new DOMXPath($dom);
$links = $xpath->query('//a[@class="compatibility-inline-product-link"]');
$check($links->length === 1, 'Thiếu liên kết trong câu cảnh báo Hybrid.');
$link = $links->item(0);
$check($link->textContent === 'Biến tần HYBRID (Deye / GoodWe Hybrid)', 'Liên kết không nằm trên đúng cụm từ.');
parse_str((string) parse_url($link->getAttribute('href'), PHP_URL_QUERY), $params);
$check($params['page'] === 'products' && $params['category'] === 'inverter' && $params['system_type'] === 'Hybrid', 'Liên kết không lọc đúng inverter Hybrid.');
$check(!str_contains($cart, 'Chọn Inverter Hybrid phù hợp →'), 'Cảnh báo còn liên kết Hybrid trùng lặp.');
$check($originalRules === $compatibility['rules'], 'Đã thay đổi kết quả tính toán tương thích.');
$cards = $xpath->query('//article[contains(@class,"product-advice-card")]');
$check(str_contains($cards->item(0)->getAttribute('class'), 'product-advice-card--danger'), 'Xung đột chưa được ưu tiên trước các khuyến nghị.');
$check($xpath->query('//div[@class="product-advisor-counts"]')->length === 1, 'Thiếu thống kê số cảnh báo.');
$advice = UseProductRecommendations::make($compatibility);
$check($advice['counts']['danger'] === 1 && $advice['counts']['warning'] >= 1, 'Đếm sai mức độ cảnh báo.');
foreach ($advice['actions'] as $action) {
    parse_str((string) parse_url((string) $action['action_url'], PHP_URL_QUERY), $target);
    if (str_contains($action['title'], 'Khung ray')) $check($target['category'] === 'khung-gia', 'Khuyến nghị khung ray còn dẫn tới tấm pin.');
    if (str_contains($action['title'], 'Tủ điện')) $check($target['category'] === 'tu-dien', 'Khuyến nghị tủ điện dẫn sai danh mục.');
    if (str_contains($action['title'], 'Giàn pin lớn hơn')) $check($target['category'] === 'inverter' && $target['system_type'] === 'Hybrid', 'Thay inverter cho hệ có pin lưu trữ chưa lọc Hybrid.');
}
$cartItems[0]['quantity'] = 12;
$cartItems[1]['capacity_watts'] = 10000;
$cartItems[1]['name'] = 'Inverter Hybrid 10kW';
$compatibility = UseSolarCalculator::analyzeCompatibility($cartItems);
$advice = UseProductRecommendations::make($compatibility);
foreach ($advice['actions'] as $action) {
    if (str_contains($action['title'], 'Inverter dư công suất')) {
        parse_str((string) parse_url($action['action_url'], PHP_URL_QUERY), $target);
        $check($target['category'] === 'tam-pin' && $action['action_label'] === 'Bổ sung tấm pin', 'Inverter dư công suất còn dẫn tới inverter.');
    }
}
ob_start();
include dirname(__DIR__) . '/resources/views/pages/cart.blade.php';
$hybridCart = ob_get_clean();
$check(str_contains($hybridCart, '<details class="product-advisor-successes"'), 'Các mục đã đạt chưa được thu gọn.');
$check(count($advice['successes']) >= 1 && $advice['counts']['danger'] === 0, 'Phân nhóm kiểm tra đã đạt sai.');
$check($xpath->query('//button[@name="cart_action" and @value="clear" and @formnovalidate]')->length === 1, 'Thiếu nút xóa tất cả sản phẩm trong kế hoạch.');
if (($argv[1] ?? '') === '--preview-cart') {
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><style>';
    foreach (['solaris-theme.css', 'products.css', 'cart.css', 'cart-grid.css', 'cart-polish.css', 'cart-system-map.css', 'cart-system-map-fix.css', 'products-advisor.css'] as $css) echo file_get_contents(dirname(__DIR__) . '/resources/css/' . $css);
    echo '</style><body>' . $hybridCart . '</body></html>';
    exit;
}
$unknown = UseProductRecommendations::make(['rules' => [['type' => 'info', 'title' => 'Thông tin khác', 'detail' => 'Tham khảo thêm']]]);
$check($unknown['actions'][0]['action_url'] === null, 'Tự tạo liên kết sản phẩm cho khuyến nghị chưa xác định.');
$rule = ['type' => 'danger', 'detail' => '<script>alert(1)</script> Biến tần HYBRID (Deye / GoodWe Hybrid) <img src=x onerror=alert(1)>'];
ob_start();
include dirname(__DIR__) . '/resources/views/components/product-compatibility-detail.blade.php';
$escaped = ob_get_clean();
$check(!str_contains($escaped, '<script>') && !str_contains($escaped, '<img '), 'Nội dung cảnh báo chưa được escape.');
echo "PASS: liên kết Hybrid, tấm pin, khung ray, tủ điện; ưu tiên cảnh báo, đếm trạng thái, thu gọn mục đạt, escape nội dung và giữ nguyên kết quả TV3.\n";
