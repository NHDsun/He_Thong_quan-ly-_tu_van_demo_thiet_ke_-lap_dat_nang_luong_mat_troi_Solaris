<?php
/**
 * =========================================================================
 * KHU VỰC 3: DÀNH CHO THÀNH VIÊN 3 (FULLSTACK / SOLAR CALCULATOR & DATA)
 * =========================================================================
 * Module Controller điều phối nghiệp vụ Phân hệ 3:
 * 1. Tiếp nhận và xử lý yêu cầu tính toán cấu hình điện mặt trời (UseSolarCalculator).
 * 2. Đồng bộ thiết bị từ Kế hoạch dự định (Session solar_cart) sang bộ tính toán năng lượng.
 * 3. Lưu trữ và quản lý dữ liệu Báo giá khảo sát (bảng solar_quotes trong CSDL).
 * 4. Phân trang dữ liệu lịch sử báo giá tự động (> 20 dòng) qua UsePaginationState.
 */

namespace App\Modules\Member3_Simulator;

use App\Config\Database;
use App\Hooks\UseSolarCalculator;
use App\Hooks\UsePaginationState;
use PDO;

class SolarDemoController
{
    /**
     * Điểm vào nhận dữ liệu bàn giao từ Kế hoạch dự định TV2
     */
    public function index(array $cartProducts = []): array
    {
        $data = $this->handleRequest($cartProducts);
        $data['cart_products'] = $cartProducts;
        return $data;
    }

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
            `status` VARCHAR(20) NOT NULL DEFAULT 'approved',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $pdo->exec($sql);
            // Tự động đồng bộ toàn bộ các báo giá về trạng thái approved để các thành viên khác kiểm thử
            $pdo->exec("UPDATE `solar_quotes` SET `status` = 'approved' WHERE `status` != 'approved' OR `status` IS NULL");
        } catch (\Exception $e) {
            // Ghi nhận im lặng nếu không có quyền CREATE TABLE
        }
    }

    /**
     * Xử lý luồng dữ liệu chính của trang Tư vấn & Demo Thiết kế Solar
     */
    public function handleRequest(array $cartProducts = []): array
    {
        $pdo = Database::getPdo();
        self::ensureTableExists($pdo);

        $alert = null;
        $savedQuoteId = null;

        // Xử lý khi người dùng gửi form Đăng ký tư vấn / Lưu báo giá hoặc Xác nhận chuyển sang Quản lý lắp đặt
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $act = trim($_POST['action']);
            if ($act === 'save_quote' || $act === 'confirm_to_install') {
                $saveResult = $this->handleSaveQuote($pdo, $cartProducts, $act === 'confirm_to_install');
                $alert = $saveResult['alert'] ?? null;
                $savedQuoteId = $saveResult['quote_id'] ?? null;
            }
        }

        // Nhận tham số tính toán từ GET hoặc POST
        $bill = isset($_REQUEST['bill']) ? (float) preg_replace('/[^0-9]/', '', (string)$_REQUEST['bill']) : 3000000;
        if ($bill <= 0) $bill = 3000000;

        $roofArea = isset($_REQUEST['roof_area']) && $_REQUEST['roof_area'] !== '' ? (float) $_REQUEST['roof_area'] : null;
        $package = isset($_REQUEST['package']) ? trim((string)$_REQUEST['package']) : 'residential';
        $roofType = isset($_REQUEST['roof_type']) ? trim((string)$_REQUEST['roof_type']) : 'metal_roof';

        // Xác định nguồn tính toán: 'plan' (từ Kế hoạch dự định) hoặc 'bill' (từ Hóa đơn tiền điện)
        $hasCartItems = !empty($cartProducts);
        $source = isset($_REQUEST['source']) ? trim((string)$_REQUEST['source']) : ($hasCartItems ? 'plan' : 'bill');
        if (!in_array($source, ['plan', 'bill'], true)) {
            $source = $hasCartItems ? 'plan' : 'bill';
        }

        // Phân tích ghép đôi thiết bị từ Kế hoạch dự định
        $compatibility = UseSolarCalculator::analyzeCompatibility($cartProducts);

        // Tính toán theo cả 2 chế độ để người dùng dễ dàng chuyển đổi
        $planCalc = $hasCartItems ? UseSolarCalculator::calculateFromPlan($cartProducts, $roofArea, $roofType) : null;
        $billCalc = UseSolarCalculator::calculate($bill, $roofArea, $package, $roofType);

        // Chọn kết quả tính toán chính theo nguồn đang chọn
        $calcResult = ($source === 'plan' && $planCalc !== null) ? $planCalc : $billCalc;

        // Lấy danh sách lịch sử các gói báo giá kèm phân trang UsePaginationState
        $quotesData = $this->getQuotesList($pdo);

        return [
            'status'         => 'success',
            'member'         => 'Thành viên 3',
            'page'           => 'Tư vấn & Demo Thiết Kế (Solar Calculator)',
            'source'         => $source,
            'has_cart_items' => $hasCartItems,
            'calc'           => $calcResult,
            'plan_calc'      => $planCalc,
            'bill_calc'      => $billCalc,
            'compatibility'  => $compatibility,
            'alert'          => $alert,
            'saved_quote_id' => $savedQuoteId,
            'quotes_list'    => $quotesData['list'],
            'pagination'     => $quotesData['pagination'],
            'db_connected'   => $pdo !== null,
            'db_error'       => Database::getLastError()
        ];
    }

    /**
     * Xử lý lưu thông tin báo giá vào CSDL và chuyển tiếp sang Quản lý lắp đặt nếu xác nhận
     */
    private function handleSaveQuote(?PDO $pdo, array $cartProducts = [], bool $confirmToInstall = false): array
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
        $source  = trim($_POST['calc_source'] ?? 'bill');

        if (empty($name) || empty($phone) || empty($address)) {
            return [
                'alert' => [
                    'type' => 'error',
                    'message' => 'Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ lắp đặt!'
                ]
            ];
        }

        // Tính toán lại chính xác từ Hook để đảm bảo tính toàn vẹn dữ liệu
        if ($source === 'plan' && !empty($cartProducts)) {
            $calc = UseSolarCalculator::calculateFromPlan($cartProducts, $area, $roofType);
        } else {
            $calc = UseSolarCalculator::calculate($bill, $area, $package, $roofType);
        }

        if ($pdo === null) {
            return [
                'alert' => [
                    'type' => 'warning',
                    'message' => 'CSDL MySQL hiện chưa được khởi chạy. Yêu cầu báo giá của bạn đã được tính toán thành công nhưng chưa thể ghi vào bảng solar_quotes. Vui lòng bật MySQL trong XAMPP Control Panel!'
                ]
            ];
        }

        // Đảm bảo user_id luôn hợp lệ để các thành viên khác (TV4 Quản lý lắp đặt, TV1 Profile) không bị chặn khi test
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['installation_user_id'] ?? ($_SESSION['user']['id'] ?? 0));
        if ($userId <= 0 && $pdo !== null) {
            try {
                $uStmt = $pdo->query("SELECT id FROM users WHERE role = 'customer' ORDER BY id ASC LIMIT 1");
                $foundId = $uStmt->fetchColumn();
                $userId = $foundId ? (int)$foundId : 6;
            } catch (\Throwable $e) {
                $userId = 6;
            }
        }

        try {
            // Khi nhấn xác nhận là thành trạng thái approve luôn, bỏ hết các trạng thái khác để các thành viên khác test được
            $quoteStatus = 'approved';
            $stmt = $pdo->prepare("INSERT INTO `solar_quotes` 
                (`user_id`, `customer_name`, `customer_phone`, `customer_email`, `address`, `monthly_electric_bill`, `roof_area_sqm`, `estimated_system_kwp`, `panel_count`, `estimated_total_cost`, `payback_years`, `notes`, `status`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $userId,
                $name,
                $phone,
                $email,
                $address,
                $calc['monthly_bill'] ?? $calc['monthly_money'] ?? 3000000,
                $calc['required_roof_area'],
                $calc['estimated_kwp'],
                $calc['panel_count'],
                $calc['estimated_total_cost'],
                $calc['payback_years'],
                $notes,
                $quoteStatus
            ]);

            $newId = (int) $pdo->lastInsertId();

            // Nếu người dùng chọn "Xác nhận & Chuyển sang Quản lý lắp đặt"
            if ($confirmToInstall) {
                $code = sprintf('SOLAR-%s-%03d', date('Y'), $newId);
                $targetProjectId = null;

                // Nếu khách hàng đã đăng nhập, tự động khởi tạo dự án thi công ngay để theo dõi tiến độ
                if ($userId && $pdo) {
                    try {
                        $projStmt = $pdo->prepare("
                            INSERT INTO `projects` 
                            (`contract_code`, `quote_id`, `user_id`, `technician_id`, `project_name`, `capacity_kwp`, `total_amount`, `installation_address`, `status`, `current_stage`, `notes`, `created_at`)
                            VALUES (?, ?, ?, 3, ?, ?, ?, ?, 'pending_approval', 'survey', ?, NOW())
                        ");
                        $projStmt->execute([
                            $code,
                            $newId,
                            $userId,
                            'Dự án Điện Mặt Trời - ' . $name,
                            $calc['estimated_kwp'],
                            $calc['estimated_total_cost'],
                            $address,
                            $notes ?: 'Khởi tạo trực tiếp từ Tư vấn & Demo Thiết kế'
                        ]);
                        $targetProjectId = (int)$pdo->lastInsertId();

                        // Khởi tạo 6 giai đoạn thi công chuẩn
                        $stages = [
                            ['survey', 1, 'in_progress', 'Khảo sát hiện trạng mặt bằng mái'],
                            ['design', 2, 'locked', 'Thiết kế kỹ thuật CAD và sơ đồ đấu nối'],
                            ['procurement', 3, 'locked', 'Chuẩn bị xuất kho vật tư và tấm pin'],
                            ['installation', 4, 'locked', 'Thi công lắp đặt khung ray và tấm pin'],
                            ['grid_connection', 5, 'locked', 'Nghiệm thu đóng điện hòa lưới EVN'],
                            ['handover', 6, 'locked', 'Bàn giao hệ thống và kích hoạt bảo hành']
                        ];
                        foreach ($stages as $stg) {
                            $stgStmt = $pdo->prepare("INSERT INTO `project_stages` (`project_id`, `stage`, `position`, `status`, `review_note`) VALUES (?, ?, ?, ?, ?)");
                            $stgStmt->execute([$targetProjectId, $stg[0], $stg[1], $stg[2], $stg[3]]);
                        }
                    } catch (\Exception $e) {
                        // Bỏ qua lỗi phụ nếu đã tồn tại mã
                    }
                }

                $_SESSION['installation_flash'] = "Xác nhận thành công! Báo giá #SQ-" . str_pad((string)$newId, 4, '0', STR_PAD_LEFT) . " đã ở trạng thái Approved và hồ sơ dự án {$code} đã sẵn sàng cho phân hệ Quản Lý Lắp Đặt kiểm thử.";
                $redirectUrl = $targetProjectId ? "index.php?page=installations&project={$targetProjectId}" : "index.php?page=installations&from_quote={$newId}";
                header('Location: ' . $redirectUrl);
                exit;
            }

            return [
                'quote_id' => $newId,
                'alert' => [
                    'type' => 'success',
                    'message' => "Xác nhận thành công! Báo giá #SQ-" . str_pad((string)$newId, 4, '0', STR_PAD_LEFT) . " đã được lưu và tự động duyệt (Trạng thái: Approved), sẵn sàng để các thành viên khác kiểm thử."
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