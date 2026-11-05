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
        $isPost = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
        $postAction = trim($_POST['action'] ?? '');
        $isUpdateRequested = false;

        // Xử lý nút bấm "Bổ sung nhanh trọn bộ thiết bị chuẩn" vào Kế hoạch dự định
        if ($isPost && $postAction === 'fill_full_system') {
            $cartProducts = $this->handleFillFullSystem($pdo);
            $alert = [
                'type' => 'success',
                'message' => 'Đã tự động bổ sung đầy đủ trọn bộ 6 nhóm thiết bị kỹ thuật chuẩn vào Kế hoạch dự định! Vui lòng nhấn "Cập Nhật" để bắt đầu mô phỏng.'
            ];
        }

        // Xử lý khi người dùng gửi form Đăng ký tư vấn / Lưu báo giá hoặc Xác nhận chuyển sang Quản lý lắp đặt
        if ($isPost && ($postAction === 'save_quote' || $postAction === 'confirm_to_install')) {
            $saveResult = $this->handleSaveQuote($pdo, $cartProducts, $postAction === 'confirm_to_install');
            $alert = $saveResult['alert'] ?? null;
            $savedQuoteId = $saveResult['quote_id'] ?? null;
            $isUpdateRequested = true;
        } elseif ($isPost && ($postAction === 'update_simulation' || isset($_POST['calc_source']))) {
            $isUpdateRequested = true;
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

        // Kiểm tra 6 nhóm thiết bị bắt buộc để cấu thành một hệ thống điện mặt trời hoàn chỉnh
        $missingRequired = [];
        if (($compatibility['total_panels'] ?? 0) <= 0) {
            $missingRequired[] = 'Tấm Pin Năng Lượng Mặt Trời';
        }
        if (($compatibility['total_inverters'] ?? 0) <= 0) {
            $missingRequired[] = 'Biến Tần Inverter';
        }
        if (empty($compatibility['has_protection_box'])) {
            $missingRequired[] = 'Tủ Điện AC/DC & Chống Sét';
        }
        if (empty($compatibility['has_mounting_rail'])) {
            $missingRequired[] = 'Khung Giá / Ray Nhôm & Kẹp Pin';
        }
        if (empty($compatibility['has_cables'])) {
            $missingRequired[] = 'Cáp Điện DC Solar 4.0mm² & MC4';
        }
        if (empty($compatibility['has_smart_meter'])) {
            $missingRequired[] = 'Đồng Hồ Smart Meter 2 Chiều';
        }
        $isHardwareComplete = empty($missingRequired);

        // Quy tắc theo yêu cầu:
        // 1. Chỉ khi nhấn CẬP NHẬT thì mới hiển thị từ Bước 2 trở đi.
        // 2. Nếu từ Kế hoạch dự định, yêu cầu phải ĐẦY ĐỦ các thiết bị bắt buộc.
        $showStepsFrom2 = false;
        if ($isUpdateRequested) {
            if ($source === 'plan') {
                if ($isHardwareComplete) {
                    $showStepsFrom2 = true;
                } else {
                    $showStepsFrom2 = false;
                    if ($alert === null) {
                        $alert = [
                            'type' => 'warning',
                            'message' => 'Yêu cầu phải có đầy đủ các thiết bị bắt buộc trong Kế hoạch dự định trước khi hiển thị mô phỏng! Hiện đang thiếu: ' . implode(', ', $missingRequired) . '. Hãy bổ sung thiết bị hoặc nhấn nút "Bổ Sung Đầy Đủ Trọn Bộ Chuẩn".'
                        ];
                    }
                }
            } else {
                // Chế độ hóa đơn điện: Đã được hệ thống tự động sinh trọn bộ thiết bị
                $showStepsFrom2 = true;
            }
        }

        // Tính toán theo cả 2 chế độ để người dùng dễ dàng chuyển đổi
        $planCalc = $hasCartItems ? UseSolarCalculator::calculateFromPlan($cartProducts, $roofArea, $roofType) : null;
        $billCalc = UseSolarCalculator::calculate($bill, $roofArea, $package, $roofType);

        // Chọn kết quả tính toán chính theo nguồn đang chọn
        $calcResult = ($source === 'plan' && $planCalc !== null) ? $planCalc : $billCalc;

        // Lấy danh sách lịch sử các gói báo giá kèm phân trang UsePaginationState
        $quotesData = $this->getQuotesList($pdo);

        return [
            'status'               => 'success',
            'member'               => 'Thành viên 3',
            'page'                 => 'Tư vấn & Demo Thiết Kế (Solar Calculator)',
            'source'               => $source,
            'has_cart_items'       => $hasCartItems,
            'calc'                 => $calcResult,
            'plan_calc'            => $planCalc,
            'bill_calc'            => $billCalc,
            'compatibility'        => $compatibility,
            'alert'                => $alert,
            'saved_quote_id'       => $savedQuoteId,
            'quotes_list'          => $quotesData['list'],
            'pagination'           => $quotesData['pagination'],
            'db_connected'         => $pdo !== null,
            'db_error'             => Database::getLastError(),
            'show_steps_from_2'    => $showStepsFrom2,
            'is_hardware_complete' => $isHardwareComplete,
            'missing_required'     => $missingRequired,
            'is_update_requested'  => $isUpdateRequested
        ];
    }

    /**
     * Tự động bổ sung đầy đủ trọn bộ các nhóm thiết bị bắt buộc còn thiếu vào Kế hoạch dự định
     */
    public function handleFillFullSystem(?PDO $pdo): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['solar_cart'] ??= [];

        if ($pdo === null) {
            return $_SESSION['solar_cart'];
        }

        // Kiểm tra các nhóm thiết bị hiện có trong giỏ hàng để bảo toàn sản phẩm người dùng đã chọn
        $hasPanels = false;
        $hasInverter = false;
        $hasBox = false;
        $hasRail = false;
        $hasCable = false;
        $hasMeter = false;

        foreach ($_SESSION['solar_cart'] as $item) {
            $code = strtoupper((string)($item['product_code'] ?? ''));
            $name = mb_strtolower((string)($item['name'] ?? ''));
            $slug = mb_strtolower((string)($item['category_slug'] ?? ''));
            if (str_contains($code, 'PAN') || str_contains($slug, 'tam-pin') || str_contains($name, 'tấm pin') || str_contains($name, 'solar panel')) $hasPanels = true;
            if (str_contains($code, 'INV') || str_contains($slug, 'inverter') || str_contains($name, 'biến tần') || str_contains($name, 'inverter')) $hasInverter = true;
            if (str_contains($code, 'ACC-006') || str_contains($code, 'ACC-007') || str_contains($slug, 'tu-dien') || str_contains($name, 'tủ điện')) $hasBox = true;
            if (str_contains($code, 'ACC-001') || str_contains($code, 'ACC-002') || str_contains($code, 'ACC-003') || str_contains($slug, 'khung-gia') || str_contains($name, 'rail') || str_contains($name, 'khung')) $hasRail = true;
            if (str_contains($code, 'ACC-004') || str_contains($code, 'ACC-005') || str_contains($slug, 'day-cap') || str_contains($name, 'cáp')) $hasCable = true;
            if (str_contains($code, 'ACC-008') || str_contains($slug, 'dong-ho') || str_contains($name, 'đồng hồ') || str_contains($name, 'meter')) $hasMeter = true;
        }

        // Bổ sung các nhóm còn thiếu tương ứng
        $toAddCodes = [];
        if (!$hasPanels)   $toAddCodes['SOL-PAN-001'] = 10; // 10 x Tấm Pin Canadian Solar 550W
        if (!$hasInverter) $toAddCodes['SOL-INV-001'] = 1;  // 1 x Biến Tần Hòa Lưới Sungrow 5kW
        if (!$hasBox)      $toAddCodes['SOL-ACC-006'] = 1;  // 1 x Tủ Điện AC/DC & Chống Sét 5kW
        if (!$hasRail)     $toAddCodes['SOL-ACC-001'] = 4;  // 4 x Thanh Rail Nhôm AL6005 4.2m
        if (!$hasCable)    $toAddCodes['SOL-ACC-004'] = 1;  // 1 x Cuộn Cáp DC 4.0mm2 100m
        if (!$hasMeter)    $toAddCodes['SOL-ACC-008'] = 1;  // 1 x Đồng Hồ Smart Meter Chint 1 Pha

        if (!empty($toAddCodes)) {
            try {
                $inClause = "'" . implode("','", array_keys($toAddCodes)) . "'";
                $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                        FROM products p 
                        JOIN categories c ON c.id = p.category_id 
                        WHERE p.product_code IN ($inClause) AND p.is_active = 1";
                $stmt = $pdo->query($sql);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($products as $prod) {
                    $pid = (int) $prod['id'];
                    $code = $prod['product_code'];
                    $defaultQty = $toAddCodes[$code] ?? 1;

                    $_SESSION['solar_cart'][$pid] = [
                        'id'              => $pid,
                        'product_code'    => $code,
                        'name'            => $prod['name'],
                        'category_id'     => (int) ($prod['category_id'] ?? 0),
                        'category_name'   => $prod['category_name'],
                        'category_slug'   => $prod['category_slug'] ?? '',
                        'capacity_watts'  => $prod['capacity_watts'],
                        'price'           => (float) $prod['price'],
                        'warranty_years'  => (int) ($prod['warranty_years'] ?? 5),
                        'technical_specs' => $prod['technical_specs'] ?? '{}',
                        'image_url'       => $prod['image_url'],
                        'quantity'        => $defaultQty,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        return class_exists(\App\Modules\Member2_Products\ProductController::class)
            ? \App\Modules\Member2_Products\ProductController::cartItems()
            : array_values($_SESSION['solar_cart']);
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

                        // Đồng bộ danh mục thiết bị từ Kế hoạch vào bảng installation_items cho phân hệ Lắp đặt (TV4)
                        if (!empty($cartProducts)) {
                            foreach ($cartProducts as $p) {
                                if (!empty($p['id']) && (int)$p['id'] > 0) {
                                    $itemStmt = $pdo->prepare("INSERT INTO `installation_items` (`project_id`, `product_id`, `quantity`, `unit_price`) VALUES (?, ?, ?, ?)");
                                    $itemStmt->execute([$targetProjectId, (int)$p['id'], max(1, (int)($p['quantity'] ?? 1)), (float)($p['price'] ?? 0)]);
                                }
                            }
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