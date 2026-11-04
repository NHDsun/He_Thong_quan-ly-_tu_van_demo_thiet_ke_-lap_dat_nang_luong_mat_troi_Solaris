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
        $data['cart_products'] = $cartProducts;
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            try {
                if (!hash_equals($_SESSION['solar_csrf'], (string) ($_POST['csrf'] ?? ''))) {
                    throw new \RuntimeException('Phiên biểu mẫu hết hạn. Hãy tải lại trang.');
                }
                if (!$pdo) {
                    throw new \RuntimeException('Chưa kết nối được cơ sở dữ liệu.');
                }
                $userId = (int) ($_SESSION['user_id'] ?? 0);
                $workflow = new UseInstallationWorkflow($pdo);
                $action = (string) $_POST['action'];
                $projectId = null;
                if (in_array($action, ['approve_quote', 'reject_quote'], true)) {
                    $projectId = $workflow->reviewQuote((int) ($_POST['quote_id'] ?? 0), $userId, $action === 'approve_quote', (string) ($_POST['review_note'] ?? ''));
                } elseif ($action === 'submit_quote') {
                    $workflow->submitQuote((int) ($_POST['quote_id'] ?? 0), $userId);
                } elseif (in_array($action, ['save_quote', 'confirm_to_install'], true)) {
                    $result = $this->handleSaveQuote($pdo, $cartProducts, $action === 'confirm_to_install');
                    $savedQuoteId = $result['quote_id'];
                    $projectId = $result['project_id'];
                } else {
                    throw new \RuntimeException('Thao tác không hợp lệ.');
                }
                if ($projectId) {
                    $_SESSION['installation_flash'] = 'Yêu cầu đã được tiếp nhận và đang chờ quản lý duyệt triển khai.';
                    header('Location: index.php?page=installations&project=' . $projectId, true, 303);
                    exit;
                }
                $_SESSION['solar_flash'] = ['type' => 'success', 'message' => match ($action) {
                    'save_quote' => 'Đã lưu dự toán nháp. Bạn có thể gửi yêu cầu từ danh sách bên dưới.',
                    'approve_quote' => 'Đã duyệt phương án.',
                    'reject_quote' => 'Đã từ chối phương án và lưu phản hồi.',
                    default => 'Đã gửi yêu cầu. Bạn có thể theo dõi trạng thái bên dưới.',
                }];
                header('Location: index.php?page=calculator', true, 303);
                exit;
            } catch (\Throwable $error) {
                $businessError = $error instanceof \RuntimeException && !$error instanceof \PDOException;
                $alert = ['type' => 'error', 'message' => $businessError ? $error->getMessage() : 'Không thể lưu yêu cầu. Vui lòng thử lại.'];
                if (!$businessError) {
                    error_log('Solar quote: ' . $error->getMessage());
                }
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
        $source = (string) ($_POST['calc_source'] ?? 'bill');
        if ($source === 'plan' && empty($cartProducts)) {
            throw new \RuntimeException('Kế hoạch chưa có thiết bị. Hãy chọn thiết bị hoặc dùng chế độ hóa đơn.');
        }
        $bill = (float) preg_replace('/[^0-9]/', '', (string) ($_POST['monthly_bill'] ?? 3000000));
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
        $stmt = $pdo->prepare("SELECT q.*,p.id project_id,p.status project_status FROM solar_quotes q LEFT JOIN projects p ON p.quote_id=q.id WHERE {$where} ORDER BY q.status='pending_approval' DESC,q.id DESC LIMIT {$limit} OFFSET {$offset}");
        $stmt->execute($params);
        $list = $stmt->fetchAll();
        $itemsQuery = $pdo->prepare('SELECT product_name,quantity,unit_price FROM solar_quote_items WHERE quote_id=? ORDER BY id');
        foreach ($list as &$quote) {
            $quote['items'] = [];
            if ($quote['source'] === 'plan') {
                $itemsQuery->execute([$quote['id']]);
                $quote['items'] = $itemsQuery->fetchAll();
            }
        }
        unset($quote);
        return ['list' => $list, 'pagination' => $pagination];
    }
}
