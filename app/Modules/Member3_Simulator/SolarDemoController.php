<?php
/**
 * =========================================================================
 * KHU VỰC 3: DÀNH CHO THÀNH VIÊN 3 (FULLSTACK / SOLAR CALCULATOR & DATA)
 * =========================================================================
 * Module Controller điều phối nghiệp vụ Phân hệ 3:
 * 1. Tiếp nhận và xử lý yêu cầu tính toán cấu hình điện mặt trời (UseSolarCalculator).
 * 2. Lưu trữ và quản lý dữ liệu Báo giá khảo sát (bảng solar_quotes trong CSDL).
 * 3. Phân trang dữ liệu lịch sử báo giá tự động (> 20 dòng) qua UsePaginationState.
 */

namespace App\Modules\Member3_Simulator;

use App\Config\Database;
use App\Hooks\UseSolarCalculator;
use App\Hooks\UsePaginationState;
use PDO;

class SolarDemoController
{
    /**
     * Khởi tạo bảng solar_quotes nếu chưa tồn tại
     */
    public static function ensureTableExists(?PDO $pdo): void
    {
        if ($pdo === null) return;

        $sql = "CREATE TABLE IF NOT EXISTS `solar_quotes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NULL,
            `customer_name` VARCHAR(100) NOT NULL,
            `customer_phone` VARCHAR(20) NOT NULL,
            `customer_email` VARCHAR(100) NULL,
            `address` VARCHAR(255) NOT NULL,
            `monthly_electric_bill` DECIMAL(15,2) NOT NULL,
            `roof_area_sqm` DECIMAL(8,2) NOT NULL,
            `estimated_system_kwp` DECIMAL(6,2) NOT NULL,
            `panel_count` INT NOT NULL,
            `estimated_total_cost` DECIMAL(15,2) NOT NULL,
            `payback_years` DECIMAL(4,1) NOT NULL,
            `notes` TEXT NULL,
            `status` ENUM('pending', 'contacted', 'survey_scheduled', 'approved', 'rejected') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $pdo->exec($sql);
        } catch (\Exception $e) {
            // Ghi nhận im lặng nếu không có quyền CREATE TABLE
        }
    }

    /**
     * Xử lý luồng dữ liệu chính của trang Tư vấn & Demo Thiết kế Solar
     */
    public function handleRequest(): array
    {
        $pdo = Database::getPdo();
        self::ensureTableExists($pdo);

        $alert = null;
        $savedQuoteId = null;

        // Xử lý khi người dùng gửi form Đăng ký tư vấn / Lưu báo giá
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_quote') {
            $saveResult = $this->handleSaveQuote($pdo);
            $alert = $saveResult['alert'];
            $savedQuoteId = $saveResult['quote_id'] ?? null;
        }

        // Nhận tham số tính toán từ GET hoặc POST
        $bill = isset($_REQUEST['bill']) ? (float) preg_replace('/[^0-9]/', '', (string)$_REQUEST['bill']) : 3000000;
        if ($bill <= 0) $bill = 3000000;

        $roofArea = isset($_REQUEST['roof_area']) && $_REQUEST['roof_area'] !== '' ? (float) $_REQUEST['roof_area'] : null;
        $package = isset($_REQUEST['package']) ? trim((string)$_REQUEST['package']) : 'residential';
        $roofType = isset($_REQUEST['roof_type']) ? trim((string)$_REQUEST['roof_type']) : 'metal_roof';

        // Gọi Hook UseSolarCalculator
        $calcResult = UseSolarCalculator::calculate($bill, $roofArea, $package, $roofType);

        // Lấy danh sách lịch sử các gói báo giá kèm phân trang UsePaginationState
        $quotesData = $this->getQuotesList($pdo);

        return [
            'status'         => 'success',
            'member'         => 'Thành viên 3',
            'page'           => 'Tư vấn & Demo Thiết Kế (Solar Calculator)',
            'calc'           => $calcResult,
            'alert'          => $alert,
            'saved_quote_id' => $savedQuoteId,
            'quotes_list'    => $quotesData['list'],
            'pagination'     => $quotesData['pagination'],
            'db_connected'   => $pdo !== null,
            'db_error'       => Database::getLastError()
        ];
    }

    /**
     * Xử lý lưu thông tin báo giá vào CSDL
     */
    private function handleSaveQuote(?PDO $pdo): array
    {
        $name    = trim($_POST['customer_name'] ?? '');
        $phone   = trim($_POST['customer_phone'] ?? '');
        $email   = trim($_POST['customer_email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $bill    = (float) preg_replace('/[^0-9]/', '', (string)($_POST['monthly_bill'] ?? 3000000));
        $area    = (float) ($_POST['roof_area'] ?? 35);
        $package = trim($_POST['package'] ?? 'residential');
        $roofType= trim($_POST['roof_type'] ?? 'metal_roof');
        $notes   = trim($_POST['notes'] ?? '');

        if (empty($name) || empty($phone) || empty($address)) {
            return [
                'alert' => [
                    'type' => 'error',
                    'message' => 'Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ lắp đặt!'
                ]
            ];
        }

        // Tính toán lại chính xác từ Hook để đảm bảo tính toàn vẹn dữ liệu
        $calc = UseSolarCalculator::calculate($bill, $area, $package, $roofType);

        if ($pdo === null) {
            return [
                'alert' => [
                    'type' => 'warning',
                    'message' => 'CSDL MySQL hiện chưa được khởi chạy. Yêu cầu báo giá của bạn đã được tính toán thành công nhưng chưa thể ghi vào bảng solar_quotes. Vui lòng bật MySQL trong XAMPP Control Panel!'
                ]
            ];
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO `solar_quotes` 
                (`customer_name`, `customer_phone`, `customer_email`, `address`, `monthly_electric_bill`, `roof_area_sqm`, `estimated_system_kwp`, `panel_count`, `estimated_total_cost`, `payback_years`, `notes`, `status`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

            $stmt->execute([
                $name,
                $phone,
                $email,
                $address,
                $calc['monthly_bill'],
                $calc['required_roof_area'],
                $calc['estimated_kwp'],
                $calc['panel_count'],
                $calc['estimated_total_cost'],
                $calc['payback_years'],
                $notes
            ]);

            $newId = (int) $pdo->lastInsertId();

            return [
                'quote_id' => $newId,
                'alert' => [
                    'type' => 'success',
                    'message' => "Đăng ký thành công! Dự toán của bạn đã được lưu với Mã báo giá #SQ-" . str_pad((string)$newId, 4, '0', STR_PAD_LEFT) . ". Đội ngũ kỹ sư Solaris sẽ liên hệ khảo sát trong vòng 24h."
                ]
            ];
        } catch (\Exception $e) {
            return [
                'alert' => [
                    'type' => 'error',
                    'message' => 'Lỗi khi lưu báo giá vào CSDL: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Lấy danh sách lịch sử báo giá có phân trang (UsePaginationState)
     */
    private function getQuotesList(?PDO $pdo): array
    {
        if ($pdo === null) {
            return [
                'list' => [],
                'pagination' => new UsePaginationState(0, 20)
            ];
        }

        try {
            // Đếm tổng số bản ghi
            $countStmt = $pdo->query("SELECT COUNT(*) FROM `solar_quotes`");
            $total = (int) $countStmt->fetchColumn();

            // Khởi tạo Hook phân trang (20 bản ghi/trang theo quy chuẩn)
            $pagination = new UsePaginationState($total, 20, null, 'p');

            // Lấy danh sách bản ghi theo trang hiện tại
            $stmt = $pdo->prepare("SELECT * FROM `solar_quotes` ORDER BY `id` DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $pagination->getLimit(), PDO::PARAM_INT);
            $stmt->bindValue(2, $pagination->getOffset(), PDO::PARAM_INT);
            $stmt->execute();
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'list' => $list,
                'pagination' => $pagination
            ];
        } catch (\Exception $e) {
            return [
                'list' => [],
                'pagination' => new UsePaginationState(0, 20)
            ];
        }
    }
}
