<?php
declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Throwable;

/**
 * Dịch vụ gửi Email qua Gmail SMTP và PHPMailer
 * Dựa trên tài liệu giảng dạy: "Phát Triển Phần Mềm Mã Nguồn Mở - Advanced Topics"
 * Giảng viên: Nguyễn Hải Triều - Khoa CNTT, ĐH Nha Trang
 * 
 * Tính năng chính:
 * 1. Kết nối an toàn Gmail SMTP Server (smtp.gmail.com, Port 587 TLS) qua PHPMailer.
 * 2. Đọc cấu hình từ `env.php` (App Password 16 ký tự Google).
 * 3. Hỗ trợ gửi Email HTML UTF-8 tiếng Việt, kèm plain-text AltBody chống spam.
 * 4. Chế độ Sandbox Fallback thông minh: nếu chưa cấu hình Gmail hoặc mất mạng,
 *    hệ thống tự động lưu vào Hòm Thư Ảo Session để việc kiểm thử và nghiệm thu
 *    không bao giờ bị gián đoạn.
 */
class MailService
{
    private static ?array $config = null;

    /**
     * Nạp cấu hình từ env.php hoặc biến môi trường
     */
    public static function getConfig(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $envFile = dirname(__DIR__, 2) . '/env.php';
        $envConfig = is_file($envFile) ? require $envFile : [];

        self::$config = [
            'mail_mailer'       => $envConfig['mail_mailer'] ?? 'smtp',
            'mail_host'         => $envConfig['mail_host'] ?? 'smtp.gmail.com',
            'mail_port'         => (int)($envConfig['mail_port'] ?? 587),
            'mail_username'     => (string)($envConfig['mail_username'] ?? getenv('GMAIL_USERNAME') ?: ''),
            'mail_password'     => (string)($envConfig['mail_password'] ?? getenv('GMAIL_APP_PASSWORD') ?: ''),
            'mail_encryption'   => $envConfig['mail_encryption'] ?? 'tls',
            'mail_from_address' => (string)($envConfig['mail_from_address'] ?? 'no-reply@solaris.vn'),
            'mail_from_name'    => (string)($envConfig['mail_from_name'] ?? 'SOLARIS Energy System'),
        ];

        return self::$config;
    }

    /**
     * Kiểm tra xem cấu hình Gmail SMTP đã được điền thông tin thật hay chưa
     */
    public static function isConfigured(): bool
    {
        $config = self::getConfig();
        $user = trim($config['mail_username']);
        $pass = trim($config['mail_password']);

        return !empty($user) 
            && !empty($pass) 
            && $pass !== 'your-16-char-app-password'
            && filter_var($user, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Gửi email chung qua PHPMailer
     *
     * @param string $toEmail Email người nhận
     * @param string $toName Họ tên người nhận
     * @param string $subject Tiêu đề thư
     * @param string $htmlBody Nội dung HTML
     * @param string $altBody Nội dung văn bản thô (cho mail client không hỗ trợ HTML)
     * @param array $attachments Danh sách đường dẫn file đính kèm (nếu có)
     * @return array Kết quả gửi thư
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $altBody = '',
        array $attachments = []
    ): array {
        // Đảm bảo Composer Autoloader đã được nạp
        $autoloader = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (is_file($autoloader)) {
            require_once $autoloader;
        }

        $config = self::getConfig();
        $isLiveConfigured = self::isConfigured();

        // Chuẩn bị dữ liệu ghi nhận vào Session Sandbox
        $mailRecord = [
            'to'        => $toEmail,
            'to_name'   => $toName,
            'subject'   => $subject,
            'time'      => date('H:i:s d/m/Y'),
            'html'      => $htmlBody,
            'is_live'   => false,
            'status'    => 'sandbox'
        ];

        // 1. NẾU CHƯA CẤU HÌNH GMAIL APP PASSWORD THỰC TẾ -> Chế độ Sandbox giả lập an toàn
        if (!$isLiveConfigured || !class_exists(PHPMailer::class)) {
            $mailRecord['status'] = 'sandbox';
            $_SESSION['last_sent_mail'] = $mailRecord;

            return [
                'success' => true,
                'mode'    => 'sandbox',
                'message' => 'Hệ thống đang hoạt động ở chế độ Hòm Thư Ảo Sandbox (Vui lòng điền Gmail & App Password vào env.php để gửi email thật qua Google).',
                'record'  => $mailRecord
            ];
        }

        // 2. NẾU ĐÃ CẤU HÌNH THÔNG TIN GMAIL -> Thực hiện gửi thật qua PHPMailer & Gmail SMTP
        $mail = new PHPMailer(true);

        try {
            // Cấu hình máy chủ SMTP theo đúng slide 39-40
            $mail->isSMTP();
            $mail->Host       = $config['mail_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['mail_username'];
            $mail->Password   = $config['mail_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['mail_port'];
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10; // Giới hạn 10s tránh treo trang

            // Người gửi và người nhận (Slide 41)
            $fromAddress = $config['mail_from_address'] ?: $config['mail_username'];
            $mail->setFrom($fromAddress, $config['mail_from_name']);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($fromAddress, $config['mail_from_name']);

            // Đính kèm tệp nếu có (Slide 41)
            foreach ($attachments as $att) {
                if (is_file($att)) {
                    $mail->addAttachment($att);
                }
            }

            // Định dạng nội dung thư (Slide 42)
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody ?: strip_tags($htmlBody);

            // Bắn email đi (Slide 42)
            $mail->send();

            $mailRecord['is_live'] = true;
            $mailRecord['status']  = 'sent_via_gmail_smtp';
            $_SESSION['last_sent_mail'] = $mailRecord;

            return [
                'success' => true,
                'mode'    => 'live_smtp',
                'message' => "Mã xác nhận đã được gửi thành công đến hộp thư Gmail: {$toEmail}!",
                'record'  => $mailRecord
            ];
        } catch (Throwable $e) {
            $errorDetail = $mail->ErrorInfo ?: $e->getMessage();
            error_log("[SOLARIS MailService Error] " . $errorDetail);

            // Gặp sự cố mạng hoặc mật khẩu sai -> Tự động Fallback sang Sandbox để khách vẫn hoàn tất đăng ký được
            $mailRecord['status'] = 'fallback_error';
            $mailRecord['error']  = $errorDetail;
            $_SESSION['last_sent_mail'] = $mailRecord;

            return [
                'success'      => false,
                'mode'         => 'fallback_sandbox',
                'error'        => $errorDetail,
                'message'      => "Không thể kết nối Gmail SMTP ({$errorDetail}). Mã xác nhận đã được đồng bộ vào Hòm Thư Ảo để bạn tiếp tục.",
                'record'       => $mailRecord
            ];
        }
    }

    /**
     * Gửi Email Xác thực Đăng ký OTP 6 số chuyên nghiệp
     */
    public static function sendOtp(string $toEmail, string $fullName, string $otpCode): array
    {
        $subject = "[SOLARIS] Mã xác thực đăng ký tài khoản của bạn: {$otpCode}";

        $htmlBody = '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #0b1120; color: #f1f5f9; margin: 0; padding: 20px; }
                .email-card { max-width: 540px; margin: 0 auto; background: #0f172a; border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 12px; padding: 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
                .header { text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
                .brand { font-size: 26px; font-weight: 800; color: #f59e0b; letter-spacing: 1.5px; }
                .brand span { color: #38bdf8; }
                .badge { display: inline-block; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 999px; padding: 4px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-top: 6px; }
                .greeting { font-size: 16px; margin-bottom: 12px; color: #e2e8f0; }
                .otp-box { background: rgba(245, 158, 11, 0.08); border: 2px dashed #f59e0b; border-radius: 10px; text-align: center; padding: 22px; margin: 26px 0; }
                .otp-title { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; font-weight: 600; }
                .otp-number { font-size: 42px; font-weight: 900; letter-spacing: 10px; color: #fbbf24; font-family: "Courier New", Courier, monospace; text-shadow: 0 0 15px rgba(245, 158, 11, 0.6); }
                .otp-expire { font-size: 12px; color: #f87171; margin-top: 8px; font-weight: 600; }
                .note { font-size: 13px; color: #94a3b8; line-height: 1.6; margin-bottom: 24px; background: rgba(15, 23, 42, 0.6); border-left: 3px solid #f59e0b; padding: 10px 14px; border-radius: 4px; }
                .footer { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 18px; font-size: 12px; color: #64748b; text-align: center; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="email-card">
                <div class="header">
                    <div class="brand">SOLAR<span>IS</span> ENERGY</div>
                    <div class="badge">Nền Tảng Điện Mặt Trời Thông Minh Tier-1</div>
                </div>
                <div class="greeting">Kính chào Quý khách <strong>' . htmlspecialchars($fullName) . '</strong>,</div>
                <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6;">
                    Cảm ơn Quý khách đã quan tâm và khởi tạo tài khoản trên hệ sinh thái <strong>SOLARIS</strong>. Để hoàn tất xác thực thông tin và kích hoạt tài khoản thành viên, vui lòng nhập mã bảo mật 6 số dưới đây:
                </p>
                <div class="otp-box">
                    <div class="otp-title">MÃ XÁC THỰC MỘT LẦN (OTP)</div>
                    <div class="otp-number">' . htmlspecialchars($otpCode) . '</div>
                    <div class="otp-expire">⏱️ Mã có hiệu lực trong vòng 10 phút</div>
                </div>
                <div class="note">
                    🔒 <strong>Bảo mật tài khoản:</strong> Tuyệt đối không chia sẻ mã này cho bất kỳ ai, kể cả nhân viên hỗ trợ của chúng tôi. Nếu Quý khách không thực hiện đăng ký tài khoản này, xin vui lòng bỏ qua thư.
                </div>
                <div class="footer">
                    <strong>HỆ THỐNG QUẢN LÝ & MÔ PHỎNG ĐIỆN MẶT TRỜI SOLARIS</strong><br>
                    Đồ án môn học: Phát Triển Phần Mềm Mã Nguồn Mở • ĐH Nha Trang<br>
                    Trưởng nhóm: Nguyễn Huy Đạt • Giảng viên hướng dẫn: Thầy Nguyễn Hải Triều<br>
                    Hotline: 0909.888.SOLAR • Email: support@solaris.vn
                </div>
            </div>
        </body>
        </html>';

        $altBody = "Kính chào {$fullName},\n\nMã xác thực OTP đăng ký tài khoản SOLARIS của bạn là: {$otpCode}\nMã có hiệu lực trong 10 phút.\n\nTrân trọng,\nSOLARIS Energy System.";

        $result = self::send($toEmail, $fullName, $subject, $htmlBody, $altBody);

        // Bổ sung mã OTP vào record session để giao diện dễ truy xuất
        if (isset($_SESSION['last_sent_mail'])) {
            $_SESSION['last_sent_mail']['otp'] = $otpCode;
            $_SESSION['last_sent_mail']['full_name'] = $fullName;
            $_SESSION['last_sent_mail']['expires'] = date('H:i:s', time() + 600);
        }

        return $result;
    }

    /**
     * Gửi email thông báo khi dự án/báo giá được cập nhật
     */
    public static function sendProjectNotification(string $toEmail, string $customerName, string $projectCode, string $stageName, string $message): array
    {
        $subject = "[SOLARIS] Cập nhật tiến độ dự án {$projectCode}: {$stageName}";

        $htmlBody = '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #0b1120; color: #f1f5f9; padding: 20px; }
                .email-card { max-width: 540px; margin: 0 auto; background: #0f172a; border: 1px solid #38bdf8; border-radius: 12px; padding: 28px; }
                .brand { font-size: 22px; font-weight: 800; color: #f59e0b; text-align: center; margin-bottom: 20px; }
                .code { background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-weight: 700; padding: 3px 8px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="email-card">
                <div class="brand">SOLARIS ENERGY NOTIFICATION</div>
                <p>Kính gửi <strong>' . htmlspecialchars($customerName) . '</strong>,</p>
                <p>Dự án quang điện của Quý khách mang mã hiệu <span class="code">' . htmlspecialchars($projectCode) . '</span> vừa có cập nhật mới:</p>
                <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p style="margin: 0; color: #38bdf8; font-weight: 700;">Giai đoạn: ' . htmlspecialchars($stageName) . '</p>
                    <p style="margin: 8px 0 0; color: #cbd5e1;">' . nl2br(htmlspecialchars($message)) . '</p>
                </div>
                <p style="font-size: 13px; color: #94a3b8;">Quý khách có thể đăng nhập vào hệ thống để theo dõi hồ sơ chi tiết và hình ảnh nghiệm thu thực tế.</p>
            </div>
        </body>
        </html>';

        return self::send($toEmail, $customerName, $subject, $htmlBody);
    }
}
