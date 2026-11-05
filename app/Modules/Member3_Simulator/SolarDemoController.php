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
use App\Hooks\UseInstallationWorkflow;
use PDO;

class SolarDemoController
{
    /**
     * Điểm vào nhận dữ liệu bàn giao từ Kế hoạch dự định TV2
     */
    public function index(array $cartProducts = []): array
    {
        $data = $this->handleRequest($cartProducts);
        return $data;
    }

    /**
     * Xử lý luồng dữ liệu chính của trang Tư vấn & Demo Thiết kế Solar
     */
    public function handleRequest(array $cartProducts = []): array
    {
        $pdo = Database::getPdo();
        $_SESSION['solar_csrf'] ??= bin2hex(random_bytes(32));

        $alert = $_SESSION['solar_flash'] ?? null;
        unset($_SESSION['solar_flash']);
        $savedQuoteId = null;
        $isPost = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');
        $postAction = trim((string) ($_POST['action'] ?? ''));
        $isUpdateRequested = false;

        // Nhận tham số tính toán từ GET hoặc POST (Hỗ trợ cả 'monthly_bill' và 'bill')
        $rawBill = $_REQUEST['monthly_bill'] ?? $_REQUEST['bill'] ?? null;
        $bill = $rawBill !== null ? (float) preg_replace('/[^0-9]/', '', (string)$rawBill) : 3000000;
        if ($bill < 500000) $bill = 3000000;

        $roofArea = isset($_REQUEST['roof_area']) && $_REQUEST['roof_area'] !== '' ? (float) $_REQUEST['roof_area'] : null;
        if ($roofArea !== null && $roofArea <= 0) {
            $roofArea = null;
        }
        $package = isset($_REQUEST['package']) ? trim((string)$_REQUEST['package']) : 'residential';
        if (!array_key_exists($package, UseSolarCalculator::PACKAGE_PRICING)) {
            $package = 'residential';
        }
        $roofType = isset($_REQUEST['roof_type']) ? trim((string)$_REQUEST['roof_type']) : 'metal_roof';
        if (!in_array($roofType, ['metal_roof', 'tile_roof', 'concrete_roof'], true)) {
            $roofType = 'metal_roof';
        }

        // Xử lý nút bấm "Bổ sung nhanh trọn bộ thiết bị chuẩn" vào Kế hoạch dự định
        if ($isPost && $postAction === 'fill_full_system') {
            $cartProducts = $this->handleFillFullSystem($pdo);
            $alert = [
                'type' => 'success',
                'message' => 'Đã tự động bổ sung đầy đủ trọn bộ 6 nhóm thiết bị kỹ thuật chuẩn vào Kế hoạch dự định! Vui lòng nhấn "Cập Nhật" để bắt đầu mô phỏng.'
            ];
        }

        // Xử lý nút bấm "Áp dụng cấu hình Hóa đơn điện vào Kế hoạch dự định"
        $forcedSource = null;
        if ($isPost && $postAction === 'apply_bill_to_plan') {
            $tempBillCalc = UseSolarCalculator::calculate($bill, $roofArea, $package, $roofType);
            $cartProducts = $this->handleApplyBillToPlan($pdo, $tempBillCalc);
            $forcedSource = 'plan';
            $isUpdateRequested = true;
            $alert = [
                'type' => 'success',
                'message' => 'Đã đồng bộ cấu hình từ Hóa đơn điện (' . $tempBillCalc['estimated_kwp'] . ' kWp • ' . $tempBillCalc['panel_count'] . ' tấm pin • ' . $tempBillCalc['package_name'] . ') sang Kế hoạch dự định và kích hoạt mô phỏng 2D!'
            ];
        }

        // Xác định nguồn tính toán: 'plan' (từ Kế hoạch dự định) hoặc 'bill' (từ Hóa đơn tiền điện)
        $hasCartItems = !empty($cartProducts);
        $requestedSource = $forcedSource ?? $_REQUEST['source'] ?? $_REQUEST['calc_source'] ?? null;
        if ($requestedSource !== null && in_array(trim((string)$requestedSource), ['plan', 'bill'], true)) {
            $source = trim((string)$requestedSource);
        } elseif (isset($_REQUEST['monthly_bill']) || isset($_REQUEST['bill']) || isset($_REQUEST['package'])) {
            $source = 'bill';
        } else {
            $source = $hasCartItems ? 'plan' : 'bill';
        }

        // Xử lý khi người dùng cập nhật mô phỏng
        if ($isPost && ($postAction === 'update_simulation' || isset($_POST['calc_source']))) {
            $isUpdateRequested = true;
        }

        // Xử lý workflow duyệt phương án / nộp dự toán / xác nhận dự án
        if ($isPost && in_array($postAction, ['approve_quote', 'reject_quote', 'submit_quote', 'save_quote', 'confirm_to_install'], true)) {
            $isUpdateRequested = true;
            try {
                if (!hash_equals($_SESSION['solar_csrf'], (string) ($_POST['csrf'] ?? ''))) {
                    throw new \RuntimeException('Phiên biểu mẫu hết hạn. Hãy tải lại trang.');
                }
                if (!$pdo) {
                    throw new \RuntimeException('Chưa kết nối được cơ sở dữ liệu.');
                }
                $userId = (int) ($_SESSION['user_id'] ?? 0);
                $workflow = new UseInstallationWorkflow($pdo);
                $projectId = null;
                if (in_array($postAction, ['approve_quote', 'reject_quote'], true)) {
                    $projectId = $workflow->reviewQuote((int) ($_POST['quote_id'] ?? 0), $userId, $postAction === 'approve_quote', (string) ($_POST['review_note'] ?? ''));
                } elseif ($postAction === 'submit_quote') {
                    $workflow->submitQuote((int) ($_POST['quote_id'] ?? 0), $userId);
                } elseif (in_array($postAction, ['save_quote', 'confirm_to_install'], true)) {
                    $result = $this->handleSaveQuote($pdo, $cartProducts, $postAction === 'confirm_to_install');
                    $savedQuoteId = $result['quote_id'];
                    $projectId = $result['project_id'];
                }
                if ($projectId) {
                    $_SESSION['installation_flash'] = 'Yêu cầu đã được tiếp nhận và đang chờ quản lý duyệt triển khai.';
                    header('Location: index.php?page=installations&project=' . $projectId, true, 303);
                    exit;
                }
                $_SESSION['solar_flash'] = ['type' => 'success', 'message' => match ($postAction) {
                    'save_quote' => 'Đã lưu dự toán nháp thành công. Bạn có thể gửi yêu cầu từ danh sách bên dưới.',
                    'approve_quote' => 'Đã duyệt phương án.',
                    'reject_quote' => 'Đã từ chối phương án và lưu phản hồi.',
                    default => 'Đã gửi yêu cầu thành công. Bạn có thể theo dõi trạng thái bên dưới.',
                }];
                header('Location: index.php?page=calculator&source=' . urlencode($source) . '#quote-history', true, 303);
                exit;
            } catch (\Throwable $error) {
                $businessError = $error instanceof \RuntimeException && !$error instanceof \PDOException;
                $alert = ['type' => 'error', 'message' => $businessError ? $error->getMessage() : 'Không thể lưu yêu cầu. Vui lòng thử lại.'];
                if (!$businessError) {
                    error_log('Solar quote: ' . $error->getMessage());
                }
            }
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

        // Tính toán theo cả 2 chế độ để người dùng dễ dàng chuyển đổi
        $planCalc = $hasCartItems ? UseSolarCalculator::calculateFromPlan($cartProducts, $roofArea, $roofType) : null;
        $billCalc = UseSolarCalculator::calculate($bill, $roofArea, $package, $roofType);

        // Quy tắc hiển thị các bước 2, 3, 4:
        // - Với 'plan': Cần nhấn Cập nhật và có đủ 6 nhóm thiết bị bắt buộc.
        // - Với 'bill': Hệ thống tự động tính toán trọn bộ thiết bị theo hóa đơn điện nên luôn sẵn sàng hiển thị từ Bước 2.
        $showStepsFrom2 = false;
        if ($source === 'bill') {
            $showStepsFrom2 = true;
            if ($isPost && $postAction === 'update_simulation' && $alert === null) {
                $alert = [
                    'type' => 'success',
                    'message' => 'Đã cập nhật tính toán theo hóa đơn ' . $billCalc['monthly_bill_formatted'] . '/tháng (' . $billCalc['package_name'] . '): Đề xuất hệ thống ' . $billCalc['estimated_kwp'] . ' kWp (' . $billCalc['panel_count'] . ' tấm pin Mono 550W) • Hoàn vốn ~' . $billCalc['payback_text'] . '.'
                ];
            }
        } elseif ($isUpdateRequested) {
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
        }

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
            'cart_products'        => $cartProducts,
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
     * Đồng bộ cấu hình ước tính từ Hóa đơn điện sang Kế hoạch dự định (Giỏ hàng thiết bị)
     */
    public function handleApplyBillToPlan(?PDO $pdo, array $billCalc): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['solar_cart'] ??= [];

        if ($pdo === null) {
            return array_values($_SESSION['solar_cart']);
        }

        $panelCount = max(4, (int) ($billCalc['panel_count'] ?? 10));
        $kwp = (float) ($billCalc['estimated_kwp'] ?? 5.5);
        $packageKey = (string) ($billCalc['package_key'] ?? 'residential');

        // Chọn model Inverter phù hợp với gói & công suất
        if ($packageKey === 'hybrid') {
            $invCode = $kwp >= 9 ? 'SOL-INV-006' : 'SOL-INV-005';
            $invQty = max(1, (int) ceil($kwp / ($invCode === 'SOL-INV-006' ? 10 : 8)));
        } elseif ($kwp >= 9) {
            $invCode = 'SOL-INV-003';
            $invQty = max(1, (int) ceil($kwp / 10));
        } elseif ($kwp >= 6) {
            $invCode = 'SOL-INV-004';
            $invQty = 1;
        } else {
            $invCode = 'SOL-INV-001';
            $invQty = 1;
        }

        $toSyncCodes = [
            'SOL-PAN-001' => $panelCount,
            $invCode      => $invQty,
            ($kwp >= 10 || $packageKey === 'hybrid') ? 'SOL-ACC-007' : 'SOL-ACC-006' => 1,
            'SOL-ACC-001' => max(4, (int) ceil($panelCount / 2)),
            'SOL-ACC-004' => max(1, (int) ceil($panelCount / 12)),
            'SOL-ACC-008' => 1,
        ];

        if ($packageKey === 'hybrid') {
            $batCode = $kwp >= 9 ? 'SOL-BAT-003' : 'SOL-BAT-002';
            $batQty = max(1, (int) ceil(($kwp * 1.2) / ($batCode === 'SOL-BAT-003' ? 10 : 5.12)));
            $toSyncCodes[$batCode] = $batQty;
        }

        try {
            $inClause = "'" . implode("','", array_keys($toSyncCodes)) . "'";
            $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                    FROM products p 
                    JOIN categories c ON c.id = p.category_id 
                    WHERE p.product_code IN ($inClause) AND p.is_active = 1";
            $stmt = $pdo->query($sql);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($products)) {
                $_SESSION['solar_cart'] = [];
                foreach ($products as $prod) {
                    $pid = (int) $prod['id'];
                    $code = $prod['product_code'];
                    $qty = $toSyncCodes[$code] ?? 1;

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
                        'quantity'        => $qty,
                    ];
                }
                $_SESSION['solar_demo_selection'] = array_keys($_SESSION['solar_cart']);
            }
        } catch (\Throwable $e) {}

        return array_values($_SESSION['solar_cart']);
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
                $_SESSION['solar_demo_selection'] = array_keys($_SESSION['solar_cart']);
            } catch (\Throwable $e) {}
        }

        return array_values($_SESSION['solar_cart']);
    }

    /**
     * Xử lý lưu thông tin báo giá vào CSDL và chuyển tiếp sang Quản lý lắp đặt nếu xác nhận
     */
    private function handleSaveQuote(?PDO $pdo, array $cartProducts = [], bool $confirmToInstall = false): array
    {
        $source = (string) ($_POST['calc_source'] ?? $_REQUEST['source'] ?? 'bill');
        if ($source === 'plan' && empty($cartProducts)) {
            throw new \RuntimeException('Kế hoạch chưa có thiết bị. Hãy chọn thiết bị hoặc dùng chế độ hóa đơn.');
        }
        $rawBill = $_POST['monthly_bill'] ?? $_POST['bill'] ?? 3000000;
        $bill = (float) preg_replace('/[^0-9]/', '', (string) $rawBill);
        $area = (float) ($_POST['roof_area'] ?? 35);
        if ($bill <= 0 || $area <= 0) {
            throw new \RuntimeException('Tiền điện và diện tích mái phải lớn hơn 0.');
        }
        $roofType = (string) ($_POST['roof_type'] ?? 'metal_roof');
        $calc = $source === 'plan'
            ? UseSolarCalculator::calculateFromPlan($cartProducts, $area, $roofType)
            : UseSolarCalculator::calculate($bill, $area, (string) ($_POST['package'] ?? 'residential'), $roofType);
        return (new UseInstallationWorkflow($pdo))->registerQuote(
            (int) ($_SESSION['user_id'] ?? 0), $_POST, $calc, $source, $confirmToInstall,
            (string) ($_POST['request_key'] ?? ''), $source === 'plan' ? $cartProducts : []
        );
    }

    /**
     * Lấy danh sách lịch sử báo giá có phân trang (UsePaginationState)
     */
    private function getQuotesList(?PDO $pdo): array
    {
        $empty = ['list' => [], 'pagination' => new UsePaginationState(0, 20)];
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if (!$pdo || !$userId) {
            return $empty;
        }
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id=?');
        $stmt->execute([$userId]);
        $role = $stmt->fetchColumn();
        if (!in_array($role, ['admin', 'customer'], true)) {
            return $empty;
        }
        $where = $role === 'admin' ? "q.status<>'draft'" : 'q.user_id=?';
        $params = $role === 'admin' ? [] : [$userId];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM solar_quotes q WHERE {$where}");
        $stmt->execute($params);
        $pagination = new UsePaginationState((int) $stmt->fetchColumn(), 20, null, 'p');
        $limit = $pagination->getLimit();
        $offset = $pagination->getOffset();
        $schema = [];
        $columns = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('solar_quotes','projects','solar_quote_items')");
        foreach ($columns as $column) {
            $schema[$column['TABLE_NAME']][$column['COLUMN_NAME']] = true;
        }
        $quoteColumns = $schema['solar_quotes'] ?? [];
        $projectColumns = $schema['projects'] ?? [];
        $hasProjects = isset($projectColumns['id'], $projectColumns['quote_id']);
        $joinProjects = $hasProjects ? ' LEFT JOIN projects p ON p.quote_id=q.id' : '';
        $projectId = $hasProjects ? 'p.id AS project_id' : 'NULL AS project_id';
        $projectStatus = isset($projectColumns['status']) ? 'p.status AS project_status' : "'Chưa cập nhật' AS project_status";
        $source = isset($quoteColumns['source']) ? '' : ", 'bill' AS source";
        $reviewNote = isset($quoteColumns['review_note']) ? '' : ', NULL AS review_note';
        $stmt = $pdo->prepare("SELECT q.*{$source}{$reviewNote}, {$projectId}, {$projectStatus} FROM solar_quotes q{$joinProjects} WHERE {$where} ORDER BY q.status='pending_approval' DESC,q.id DESC LIMIT {$limit} OFFSET {$offset}");
        $stmt->execute($params);
        $list = $stmt->fetchAll();
        $itemsQuery = isset($schema['solar_quote_items']['quote_id'])
            ? $pdo->prepare('SELECT product_name,quantity,unit_price FROM solar_quote_items WHERE quote_id=? ORDER BY id')
            : null;
        foreach ($list as &$quote) {
            $quote['items'] = [];
            if ((($quote['source'] ?? '') === 'plan') && $itemsQuery !== null) {
                $itemsQuery->execute([$quote['id']]);
                $quote['items'] = $itemsQuery->fetchAll();
            }
        }
        unset($quote);
        return ['list' => $list, 'pagination' => $pagination];
    }
}
