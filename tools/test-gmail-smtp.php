<?php
/**
 * SCRIPT KIỂM THỬ KẾT NỐI VÀ GỬI EMAIL THỰC TẾ QUA GMAIL SMTP VÀ PHPMAILER
 * Dựa trên bài giảng: Phát Triển Phần Mềm Mã Nguồn Mở - Thầy Nguyễn Hải Triều (ĐH Nha Trang)
 * Slide: 39 - 44
 * 
 * Cách dùng trên dòng lệnh:
 *   php tools/test-gmail-smtp.php --to=email-cua-ban@gmail.com
 *   php tools/test-gmail-smtp.php --to=email-cua-ban@gmail.com --debug
 */

// 1. Import PHPMailer classes (Slide 39)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. Load Composer's autoloader & env.php (Slide 39)
require __DIR__ . '/../vendor/autoload.php';
$env = is_file(__DIR__ . '/../env.php') ? require __DIR__ . '/../env.php' : [];

$app_password = $env['mail_password'] ?? (getenv('GMAIL_APP_PASSWORD') ?: '');
$gmail_user   = $env['mail_username'] ?? (getenv('GMAIL_USERNAME') ?: '');

// Đọc tham số từ dòng lệnh
$options = getopt('', ['to:', 'debug']);
$toEmail = $options['to'] ?? $gmail_user;
$isDebug = isset($options['debug']);

echo "========================================================\n";
echo "   CHƯƠNG TRÌNH KIỂM THỬ GỬI GMAIL QUA PHPMAILER\n";
echo "========================================================\n\n";

echo "1. Thông tin cấu hình hiện tại:\n";
echo "   - Host: smtp.gmail.com\n";
echo "   - Port: 587 (STARTTLS)\n";
echo "   - Username: " . ($gmail_user ?: '(Chưa điền trong env.php)') . "\n";
echo "   - App Password: " . ($app_password ? str_repeat('*', strlen($app_password)) : '(Chưa điền trong env.php)') . "\n";
echo "   - Người nhận: " . ($toEmail ?: '(Chưa chỉ định qua --to=...)') . "\n\n";

if (empty($gmail_user) || empty($app_password) || $app_password === 'your-16-char-app-password') {
    echo "⚠️ THÔNG BÁO QUAN TRỌNG:\n";
    echo "   Bạn chưa cấu hình Gmail Username và Mật khẩu ứng dụng (App Password) trong file `env.php`.\n";
    echo "   👉 Hướng dẫn tạo mật khẩu ứng dụng Gmail 16 ký tự:\n";
    echo "      1. Bật Xác minh 2 bước trên tài khoản Google: https://myaccount.google.com/security\n";
    echo "      2. Vào liên kết: https://myaccount.google.com/u/0/apppasswords\n";
    echo "      3. Tạo mật khẩu ứng dụng mới (đặt tên ví dụ 'Solaris Mail')\n";
    echo "      4. Điền địa chỉ Gmail và mật khẩu 16 ký tự vào file `env.php`\n\n";
    echo "   Hệ thống trên Web hiện tại sẽ tự động chuyển sang chế độ Sandbox Hòm Thư Ảo\n";
    echo "   để người dùng vẫn kiểm thử tính năng đăng ký OTP bình thường mà không bị crash.\n";
    exit(0);
}

if (empty($toEmail)) {
    echo "Vui lòng truyền email người nhận qua tham số: --to=email@gmail.com\n";
    exit(1);
}

echo "2. Đang khởi tạo kết nối và gửi thử nghiệm đến {$toEmail}...\n";

// 3. Create an instance; passing `true` enables exceptions (Slide 39)
$mail = new PHPMailer(true);

try {
    // Server settings (Slide 39-40)
    if ($isDebug) {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    } else {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
    }
    
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $gmail_user;
    $mail->Password   = $app_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Recipients (Slide 41)
    $mail->setFrom($gmail_user, 'SOLARIS Energy System');
    $mail->addAddress($toEmail, 'Người Nhận Thử Nghiệm');

    // Content (Slide 42)
    $mail->isHTML(true);
    $mail->Subject = '[SOLARIS] Thử nghiệm gửi Email qua PHPMailer thành công!';
    $mail->Body    = '
    <div style="font-family: sans-serif; background: #0b1120; color: #ffffff; padding: 25px; border-radius: 10px;">
        <h2 style="color: #f59e0b;">SOLARIS ENERGY SYSTEM</h2>
        <p>Xin chào,</p>
        <p>Email này được gửi tự động từ máy chủ PHP sử dụng thư viện <strong>PHPMailer (v' . PHPMailer::VERSION . ')</strong> kết nối qua <strong>Gmail SMTP Server</strong> (smtp.gmail.com:587 TLS).</p>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; padding: 15px; border-radius: 8px; color: #34d399; font-weight: bold;">
            ✅ KẾT NỐI VÀ GỬI THƯ THÀNH CÔNG!
        </div>
        <p style="font-size: 12px; color: #94a3b8; margin-top: 20px;">
            Đồ án Phát Triển Phần Mềm Mã Nguồn Mở - ĐH Nha Trang<br>
            Giảng viên: Thầy Nguyễn Hải Triều
        </p>
    </div>';
    $mail->AltBody = "SOLARIS ENERGY: Thử nghiệm gửi email qua PHPMailer và Gmail SMTP thành công!";

    $mail->send();
    echo "\n🎉 THÀNH CÔNG: Message has been sent!\n";
    echo "   Email thử nghiệm đã được gửi tới: {$toEmail}\n";
    echo "   Vui lòng kiểm tra hộp thư đến (Inbox) hoặc Spam của bạn.\n";
} catch (Exception $e) {
    echo "\n❌ THẤT BẠI: Message could not be sent.\n";
    echo "   Mailer Error: {$mail->ErrorInfo}\n";
}
