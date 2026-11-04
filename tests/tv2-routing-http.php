<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$base = $argv[1] ?? '';
if (!preg_match('~^http://(localhost|127\.0\.0\.1)(:\d+)?/[^?]*index\.php$~D', $base)) throw new RuntimeException('Truyền URL index.php của máy chủ localhost để kiểm tra.');
$cookie = tempnam(sys_get_temp_dir(), 'tv2-cookie-');
register_shutdown_function(static function () use ($cookie): void { if (is_file($cookie)) unlink($cookie); });
$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void { if (!$condition) throw new RuntimeException($message); $checks++; };
$request = static function (string $query, ?array $post = null, bool $json = false) use ($base, $cookie): array {
    $curl = curl_init($base . '?' . $query);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEJAR => $cookie, CURLOPT_COOKIEFILE => $cookie, CURLOPT_TIMEOUT => 15]);
    if ($post !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); }
    if ($json) curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $response = curl_exec($curl);
    if ($response === false) throw new RuntimeException(curl_error($curl));
    $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); $status = curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl);
    $headers = substr($response,0,$size); $body = substr($response,$size);
    preg_match('/^Location:\s*(.+)$/mi',$headers,$location);
    return ['status' => $status,'location' => trim($location[1] ?? ''),'body' => $body];
};
$catalog = $request('page=products');
$check($catalog['status'] === 200, 'Không mở được catalog.');
preg_match('/name="csrf_token" value="([^"]+)"/',$catalog['body'],$csrf);
preg_match('/name="product_id" value="(\d+)"/',$catalog['body'],$id);
$check(isset($csrf[1],$id[1]), 'Catalog thiếu CSRF token hoặc sản phẩm.');
$token = $csrf[1]; $productId = (int) $id[1];
$invalid = $request('page=products',['cart_action'=>'add','product_id'=>$productId,'csrf_token'=>'wrong'],true);
$check($invalid['status'] === 403 && !json_decode($invalid['body'],true,512,JSON_THROW_ON_ERROR)['ok'], 'Endpoint JSON chấp nhận CSRF sai.');
$add = $request('page=products',['cart_action'=>'add','product_id'=>$productId,'csrf_token'=>$token],true);
$data = json_decode($add['body'],true,512,JSON_THROW_ON_ERROR);
$check($add['status'] === 200 && $data['ok'] && $data['count'] === 1, 'Thêm vào kế hoạch bằng JSON không đúng.');
$empty = $request('page=cart',['cart_action'=>'demo_selected','csrf_token'=>$token,'quantity'=>[$productId=>12]]);
$check($empty['location'] === 'index.php?page=cart', 'Bỏ chọn hết vẫn chuyển sang Demo.');
$sync = $request('page=cart',['cart_action'=>'demo_selected','csrf_token'=>$token,'quantity'=>[$productId=>12],'selected'=>[$productId]]);
$check($sync['location'] === 'index.php?page=calculator&source=plan', 'Không chuyển đúng sang Demo sau đồng bộ.');
$cart = $request('page=cart');
$check((bool) preg_match('/name="quantity\[' . $productId . '\]" value="12"/',$cart['body']), 'Số lượng mới chưa được lưu trong session HTTP.');
$check(str_contains($cart['body'],'form="cart-form"'), 'Nút Demo phía trên vẫn bỏ qua form kế hoạch.');
$denied = $request('page=products&action=manage');
$check($denied['status'] === 403, 'Khách truy cập được trang quản trị.');
$request('page=cart',['cart_action'=>'clear','csrf_token'=>$token]);
$cleared = $request('page=cart');
$check(!str_contains($cleared['body'],'id="cart-form"'), 'Xóa toàn bộ chưa làm trống kế hoạch HTTP.');
echo "PASS: {$checks} kiểm tra HTTP thật cho CSRF, JSON, session riêng, số lượng / lựa chọn Demo, phân quyền và xóa kế hoạch. Không lưu báo giá hoặc thay đổi CSDL sản phẩm.\n";
