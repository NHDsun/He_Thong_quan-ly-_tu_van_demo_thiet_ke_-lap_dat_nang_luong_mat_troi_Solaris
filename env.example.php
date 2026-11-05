<?php
/**
 * CẤU HÌNH MÔI TRƯỜNG GỬI EMAIL QUA GMAIL SMTP VÀ PHPMAILER
 * Tài liệu tham khảo: Bài giảng Phát Triển Phần Mềm Mã Nguồn Mở - ĐH Nha Trang
 * Giảng viên: Nguyễn Hải Triều
 * 
 * HƯỚNG DẪN THIẾT LẬP GMAIL APP PASSWORD:
 * 1. Bật xác minh 2 bước cho tài khoản Google của bạn.
 * 2. Truy cập: https://myaccount.google.com/u/0/apppasswords
 * 3. Tạo một mật khẩu ứng dụng (App Password) mới, đặt tên ví dụ "Solaris Mailer".
 * 4. Google sẽ cấp một chuỗi mật khẩu 16 ký tự (ví dụ: abcd efgh ijkl mnop).
 * 5. Sao chép chuỗi 16 ký tự đó vào biến $app_password dưới đây.
 * 6. Đổi tên file này thành `env.php` để kích hoạt gửi mail thật!
 */

// Mật khẩu ứng dụng 16 ký tự của Gmail
$app_password = 'your-16-char-app-password';

// Địa chỉ Gmail đóng vai trò làm Mail Server gửi đi
$gmail_username = 'your-email@gmail.com';

return [
    'mail_mailer'       => 'smtp',
    'mail_host'         => 'smtp.gmail.com',
    'mail_port'         => 587,
    'mail_username'     => $gmail_username,
    'mail_password'     => $app_password,
    'mail_encryption'   => 'tls', // PHPMailer::ENCRYPTION_STARTTLS
    'mail_from_address' => $gmail_username,
    'mail_from_name'    => 'SOLARIS Energy System',
];
