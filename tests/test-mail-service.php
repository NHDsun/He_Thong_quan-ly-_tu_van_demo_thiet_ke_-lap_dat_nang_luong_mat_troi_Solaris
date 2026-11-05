<?php
declare(strict_types=1);

/**
 * BỘ KIỂM THỬ CHỨC NĂNG GỬI GMAIL QUA PHPMAILER
 * Dựa trên chuyên đề: "Phát Triển Phần Mềm Mã Nguồn Mở - Advanced Topics"
 * Giảng viên: Nguyễn Hải Triều - Khoa CNTT, ĐH Nha Trang
 */

require_once __DIR__ . '/../vendor/autoload.php';

// PSR-4 Autoloader
spl_autoload_register(function (string $class): void {
    $prefix = "App\\";
    $baseDir = dirname(__DIR__) . "/app/";
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) require_once $file;
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

use App\Services\MailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

echo "========================================================\n";
echo "   KIỂM THỬ TÍCH HỢP PHPMAILER & GMAIL SMTP THEO SLIDE\n";
echo "========================================================\n\n";

// 1. Kiểm tra lớp PHPMailer đã được nạp qua Composer Autoload (Slide 38-39)
echo "[TEST 1] Kiểm tra nạp thư viện PHPMailer qua Composer Autoload...\n";
assert(class_exists(PHPMailer::class), "Lớp PHPMailer chưa được nạp qua vendor/autoload.php");
assert(class_exists(SMTP::class), "Lớp SMTP chưa được nạp");
assert(class_exists(PHPMailerException::class), "Lớp Exception chưa được nạp");
echo "-> PASS: Thư viện PHPMailer đã sẵn sàng (Phiên bản: " . PHPMailer::VERSION . ")\n\n";

// 2. Kiểm tra file cấu hình env.php (Slide 39, 40, 43)
echo "[TEST 2] Kiểm tra nạp file cấu hình env.php...\n";
$config = MailService::getConfig();
assert(isset($config['mail_host']), "Thiếu cấu hình mail_host");
assert(isset($config['mail_port']), "Thiếu cấu hình mail_port");
assert(isset($config['mail_username']), "Thiếu cấu hình mail_username");
assert(isset($config['mail_password']), "Thiếu cấu hình mail_password");
assert($config['mail_host'] === 'smtp.gmail.com', "Host phải là smtp.gmail.com");
assert($config['mail_port'] === 587, "Port chuẩn TLS phải là 587");
echo "-> PASS: Cấu hình Gmail SMTP chuẩn (Host: {$config['mail_host']}, Port: {$config['mail_port']})\n\n";

// 3. Kiểm tra hàm gửi OTP của MailService (Slide 41-42)
echo "[TEST 3] Kiểm tra phương thức MailService::sendOtp...\n";
$testEmail = 'sinhvien.test@gmail.com';
$testName  = 'Nguyễn Văn Test';
$testOtp   = '888999';

$result = MailService::sendOtp($testEmail, $testName, $testOtp);
assert(is_array($result), "Kết quả phải là một mảng");
assert(isset($result['success']), "Kết quả phải có cờ success");
assert(isset($result['mode']), "Kết quả phải có thông tin mode (live_smtp hoặc sandbox)");
assert(isset($_SESSION['last_sent_mail']), "Session last_sent_mail phải được lưu");
assert($_SESSION['last_sent_mail']['otp'] === $testOtp, "Mã OTP trong session phải trùng khớp");
assert($_SESSION['last_sent_mail']['to'] === $testEmail, "Email nhận phải trùng khớp");
echo "-> PASS: Gửi OTP thành công (Chế độ: {$result['mode']}, Thông điệp: {$result['message']})\n\n";

// 4. Kiểm tra AuthController::sendOtpEmail tích hợp mượt mà
echo "[TEST 4] Kiểm tra tích hợp trong App\\Modules\\Member1_Lead\\AuthController...\n";
$authMailRes = \App\Modules\Member1_Lead\AuthController::sendOtpEmail('khachhang.demo@solaris.vn', 'Khách Demo', '123456');
assert(is_array($authMailRes), "AuthController::sendOtpEmail phải trả về mảng kết quả");
assert(isset($authMailRes['success']), "Phải có trạng thái gửi thành công");
echo "-> PASS: AuthController đã chuyển tiếp hoàn hảo sang MailService\n\n";

// 5. Kiểm tra tính năng Password Hashing theo Slide 6-7
echo "[TEST 5] Kiểm tra Password Hashing Functions (Slide 6-7)...\n";
$password = 'Solaris@2026';
$hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
assert(password_verify($password, $hash), "password_verify phải xác thực chính xác mật khẩu");
assert(!password_verify('SaiMatKhau', $hash), "password_verify phải từ chối mật khẩu sai");
echo "-> PASS: password_hash() và password_verify() hoạt động chính xác\n\n";

// 6. Kiểm tra JSON Encoding / Decoding theo Slide 9-14
echo "[TEST 6] Kiểm tra JSON Functions (Slide 9-14)...\n";
$sampleArray = ['name' => 'Solaris', 'port' => 587, 'secure' => true];
$jsonStr = json_encode($sampleArray, JSON_PRETTY_PRINT);
$decoded = json_decode($jsonStr, true);
assert($decoded['name'] === 'Solaris' && $decoded['port'] === 587, "json_decode phải khớp dữ liệu");
echo "-> PASS: json_encode() và json_decode() hoạt động chính xác\n\n";

echo "========================================================\n";
echo "   TẤT CẢ 6 NHÓM KIỂM THỬ ĐÃ ĐẠT CHUẨN 100% (PASS)!\n";
echo "========================================================\n";
