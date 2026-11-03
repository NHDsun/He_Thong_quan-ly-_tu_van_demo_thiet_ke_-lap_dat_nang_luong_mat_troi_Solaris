<?php
/**
 * =========================================================================
 * KHU VỰC 1: DÀNH CHO THÀNH VIÊN 1 (NGUYỄN HUY ĐẠT - MSSV: 65130410)
 * VAI TRÒ: TRƯỞNG NHÓM & QUẢN LÝ DỰ ÁN (PROJECT MANAGER - PM) / FULLSTACK CORE & UI/UX
 * =========================================================================
 * Module ProfileController:
 * 1. Hiển thị thông tin chung người dùng theo chuẩn các trang Profile chuyên nghiệp (Facebook, GitHub, Cổng thông tin).
 * 2. Phân tách và hiển thị thông tin chung đặc thù của từng đối tượng:
 *    - Khách hàng (Customer): Thông tin khách, kế hoạch thiết bị dự định, báo giá đã gửi, trạng thái công trình.
 *    - Kỹ thuật viên (Technician): Thông tin nhân sự, chuyên môn, danh sách công trình phụ trách, tài liệu khảo sát.
 *    - Quản trị viên (Admin): Thông tin điều hành, quyền hạn hệ thống, tổng quan dự án toàn hệ thống, báo giá cần duyệt.
 * 3. Hỗ trợ chuyển đổi vai trò xem thông tin chung giữa 3 đối tượng (admin_pro, tech_pro, customer_pro).
 * 4. Lưu giữ hồ sơ 4 thành viên phát triển dự án và biểu đồ quy trình phối hợp WBS.
 */

namespace App\Modules\Member1_Lead;

use App\Config\Database;
use App\Modules\Member2_Products\ProductController;
use App\Hooks\UseSolarCalculator;
use PDO;
use Throwable;

class ProfileController
{
    private ?PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getPdo();
    }

    /**
     * Xử lý luồng dữ liệu chính của trang Profile & Thành viên
     */
    public function handleRequest(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $alert = $_SESSION['profile_alert'] ?? null;
        unset($_SESSION['profile_alert']);

        // 1. Xử lý Chuyển đổi vai trò xem thông tin chung (Switch Role View)
        if (isset($_GET['switch_account'])) {
            $switchUser = trim((string)$_GET['switch_account']);
            if (in_array($switchUser, ['admin_pro', 'tech_pro', 'customer_pro'], true) && $this->pdo) {
                try {
                    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
                    $stmt->execute([$switchUser]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        $_SESSION['is_logged_in'] = true;
                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['installation_user_id'] = (int)$user['id'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['user_name'] = $user['full_name'];
                        $_SESSION['user_email'] = $user['email'];

                        $roleTitle = match ($user['role']) {
                            'admin' => 'Quản Trị Viên Cấp Cao (Admin)',
                            'technician' => 'Kỹ Thuật Viên Trưởng (Technician)',
                            default => 'Khách Hàng Thành Viên (Customer)'
                        };

                        $_SESSION['profile_alert'] = [
                            'type' => 'success',
                            'message' => "Đang xem hồ sơ thông tin chung của: {$user['full_name']} (Mức: {$roleTitle})!"
                        ];
                        header('Location: index.php?page=profile');
                        exit;
                    }
                } catch (Throwable $e) {
                    $alert = ['type' => 'error', 'message' => 'Lỗi khi chuyển đổi: ' . $e->getMessage()];
                }
            }
        }

        // 2. Lấy thông tin tài khoản hiện tại
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentUser = null;
        if ($this->pdo && $currentUserId > 0) {
            try {
                $stmt = $this->pdo->prepare("SELECT id, username, full_name, email, phone, role, created_at, updated_at FROM users WHERE id = ?");
                $stmt->execute([$currentUserId]);
                $currentUser = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Throwable) {
                $currentUser = null;
            }
        }

        // Nếu chưa đăng nhập hoặc tài khoản không tồn tại -> bắt buộc về trang auth
        if (!$currentUser) {
            header('Location: index.php?page=auth');
            exit;
        }

        // 2.1 Xử lý Tạo Tài Khoản Kỹ Thuật Viên Mới (Chỉ dành riêng cho Quản trị viên Admin)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['profile_action'] ?? '') === 'create_technician') {
            if (($currentUser['role'] ?? '') !== 'admin') {
                $_SESSION['profile_alert'] = [
                    'type' => 'error',
                    'message' => 'Bạn không có quyền quản trị viên để tạo tài khoản nhân viên / kỹ thuật viên.'
                ];
                header('Location: index.php?page=profile');
                exit;
            }

            $fullName = trim((string)($_POST['full_name'] ?? ''));
            $username = strtolower(trim((string)($_POST['username'] ?? '')));
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            // Nếu mật khẩu để trống thì dùng mật khẩu mặc định Solaris@2026
            if ($password === '') {
                $password = 'Solaris@2026';
            }

            // Chuẩn hóa username nếu chưa nhập
            if ($username === '') {
                $emailParts = explode('@', $email);
                $username = preg_replace('/[^a-zA-Z0-9_.]/', '', $emailParts[0] ?? 'tech');
                if (strlen($username) < 3) $username .= rand(100, 999);
            }

            if ($fullName === '' || $email === '') {
                $_SESSION['profile_alert'] = [
                    'type' => 'error',
                    'message' => 'Vui lòng nhập đầy đủ Họ tên và Email của Kỹ thuật viên.'
                ];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['profile_alert'] = [
                    'type' => 'error',
                    'message' => 'Địa chỉ Email không đúng định dạng chuẩn.'
                ];
            } elseif (strlen($password) < 6) {
                $_SESSION['profile_alert'] = [
                    'type' => 'error',
                    'message' => 'Mật khẩu phải có độ dài tối thiểu từ 6 ký tự trở lên.'
                ];
            } elseif ($this->pdo) {
                try {
                    $checkStmt = $this->pdo->prepare("SELECT id, username, email FROM users WHERE username = ? OR email = ? LIMIT 1");
                    $checkStmt->execute([$username, $email]);
                    $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if ($exists) {
                        if (strtolower((string)$exists['username']) === $username) {
                            $_SESSION['profile_alert'] = [
                                'type' => 'error',
                                'message' => "Tên đăng nhập '{$username}' đã tồn tại trên hệ thống. Vui lòng chọn tên khác."
                            ];
                        } else {
                            $_SESSION['profile_alert'] = [
                                'type' => 'error',
                                'message' => "Địa chỉ Email '{$email}' đã được đăng ký. Vui lòng sử dụng email khác."
                            ];
                        }
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $insertStmt = $this->pdo->prepare("
                            INSERT INTO users (username, password_hash, full_name, email, phone, role, created_at)
                            VALUES (?, ?, ?, ?, ?, 'technician', NOW())
                        ");
                        $insertStmt->execute([
                            $username,
                            $hash,
                            $fullName,
                            $email,
                            $phone ?: '0909777999'
                        ]);

                        $_SESSION['profile_alert'] = [
                            'type' => 'success',
                            'message' => "Đã tạo thành công tài khoản Kỹ thuật viên: '{$fullName}' (Tài khoản: {$username})! Mật khẩu đăng nhập: {$password}"
                        ];
                    }
                } catch (Throwable $e) {
                    $_SESSION['profile_alert'] = [
                        'type' => 'error',
                        'message' => 'Lỗi khi lưu tài khoản kỹ thuật viên: ' . $e->getMessage()
                    ];
                }
            }

            header('Location: index.php?page=profile&tab=staff#staff-section');
            exit;
        }

        // 3. Thống kê theo vai trò của tài khoản hiện tại
        $stats = $this->getRoleStats($currentUser);

        // 4. Lấy danh sách công trình lắp đặt liên quan đến đối tượng hiện tại
        $projects = $this->getRoleProjects($currentUser);

        // 5. Lấy danh sách báo giá liên quan đến đối tượng hiện tại
        $quotes = $this->getRoleQuotes($currentUser);

        // 6. Lấy danh sách nhân viên kỹ thuật viên (cho Quản trị viên)
        $technicians = $this->getTechnicians();

        // 7. Lấy thông tin kế hoạch dự định (cho Khách hàng)
        $cartItems = class_exists(ProductController::class) ? ProductController::cartItems() : [];
        $cartCompat = class_exists(UseSolarCalculator::class) ? UseSolarCalculator::analyzeCompatibility($cartItems) : [];
        $planCalc = class_exists(UseSolarCalculator::class) ? UseSolarCalculator::calculateFromPlan($cartItems) : [];

        // 8. Danh sách 3 tài khoản demo mẫu
        $demoAccounts = $this->getDemoAccounts();

        // 9. Thông tin chi tiết 4 Thành viên dự án
        $teamMembers = $this->getTeamMembers();

        return [
            'current_user'  => $currentUser,
            'role_stats'    => $stats,
            'role_projects' => $projects,
            'role_quotes'   => $quotes,
            'technicians'   => $technicians,
            'cart_items'    => $cartItems,
            'cart_compat'   => $cartCompat,
            'plan_calc'     => $planCalc,
            'demo_accounts' => $demoAccounts,
            'team_members'  => $teamMembers,
            'alert'         => $alert,
            'view_mode'     => $_GET['tab'] ?? $_GET['view'] ?? 'account'
        ];
    }

    /**
     * Thống kê hoạt động dựa theo vai trò tài khoản
     */
    private function getRoleStats(?array $user): array
    {
        $role = $user['role'] ?? 'customer';
        $userId = (int)($user['id'] ?? 0);
        $userEmail = (string)($user['email'] ?? '');

        $stats = [
            'role' => $role,
            'items' => []
        ];

        if (!$this->pdo || !$userId) return $stats;

        try {
            if ($role === 'admin') {
                $totalProjects = (int)$this->pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
                $totalQuotes = (int)$this->pdo->query("SELECT COUNT(*) FROM solar_quotes")->fetchColumn();
                $totalProducts = (int)$this->pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
                $totalUsers = (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

                $stats['items'] = [
                    ['label' => 'Tổng dự án toàn hệ thống', 'value' => $totalProjects . ' công trình', 'icon' => '🏗️', 'color' => '#f59e0b', 'desc' => 'Đang giám sát & quản trị'],
                    ['label' => 'Yêu cầu báo giá nhận được', 'value' => $totalQuotes . ' hồ sơ', 'icon' => '📋', 'color' => '#10b981', 'desc' => 'Cần xử lý & liên hệ'],
                    ['label' => 'Thiết bị trong Catalog', 'value' => $totalProducts . ' sản phẩm', 'icon' => '☀️', 'color' => '#38bdf8', 'desc' => 'Đang kinh doanh hoạt động'],
                    ['label' => 'Tài khoản người dùng', 'value' => $totalUsers . ' tài khoản', 'icon' => '👥', 'color' => '#ec4899', 'desc' => 'Quản trị viên, thợ & khách']
                ];
            } elseif ($role === 'technician') {
                $myProjectsStmt = $this->pdo->prepare("
                    SELECT COUNT(DISTINCT p.id) FROM projects p 
                    WHERE p.technician_id = ? 
                       OR EXISTS (SELECT 1 FROM project_assignments a WHERE a.project_id = p.id AND a.technician_id = ? AND a.ended_at IS NULL)
                ");
                $myProjectsStmt->execute([$userId, $userId]);
                $myProjects = (int)$myProjectsStmt->fetchColumn();

                $docsStmt = $this->pdo->prepare("SELECT COUNT(*) FROM project_documents WHERE uploaded_by = ?");
                $docsStmt->execute([$userId]);
                $totalDocs = (int)$docsStmt->fetchColumn();

                $logsStmt = $this->pdo->prepare("SELECT COUNT(*) FROM project_stage_logs WHERE actor_id = ?");
                $logsStmt->execute([$userId]);
                $totalLogs = (int)$logsStmt->fetchColumn();

                $stats['items'] = [
                    ['label' => 'Công trình được phân công', 'value' => $myProjects . ' dự án', 'icon' => '🔧', 'color' => '#38bdf8', 'desc' => 'Trực tiếp thi công hiện trường'],
                    ['label' => 'Hồ sơ tài liệu khảo sát', 'value' => $totalDocs . ' tài liệu', 'icon' => '📁', 'color' => '#10b981', 'desc' => 'Biên bản PCCC & EVN tải lên'],
                    ['label' => 'Nhật ký thi công đã ghi', 'value' => $totalLogs . ' nhật ký', 'icon' => '📝', 'color' => '#f59e0b', 'desc' => 'Ghi nhận tiến độ công trình']
                ];
            } else {
                // Khách hàng (Customer) - CHỈ ĐẾM CỦA RIÊNG KHÁCH HÀNG NÀY!
                $cartCount = class_exists(ProductController::class) ? ProductController::cartCount() : 0;
                
                $quotesStmt = $this->pdo->prepare("SELECT COUNT(*) FROM solar_quotes WHERE user_id = ? OR (customer_email = ? AND customer_email != '')");
                $quotesStmt->execute([$userId, $userEmail]);
                $myQuotes = (int)$quotesStmt->fetchColumn();

                $projStmt = $this->pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
                $projStmt->execute([$userId]);
                $myProjects = (int)$projStmt->fetchColumn();

                $stats['items'] = [
                    ['label' => 'Thiết bị trong Kế hoạch', 'value' => $cartCount . ' món', 'icon' => '📋', 'color' => '#f59e0b', 'desc' => 'Đang quan tâm cấu hình'],
                    ['label' => 'Báo giá khảo sát đã gửi', 'value' => $myQuotes . ' hồ sơ', 'icon' => '⚡', 'color' => '#10b981', 'desc' => 'Hồ sơ yêu cầu của bạn'],
                    ['label' => 'Hợp đồng công trình của bạn', 'value' => $myProjects . ' dự án', 'icon' => '🏗️', 'color' => '#38bdf8', 'desc' => 'Đang triển khai lắp đặt']
                ];
            }
        } catch (Throwable) {}

        return $stats;
    }

    /**
     * Lấy danh sách công trình lắp đặt liên quan đến vai trò
     */
    private function getRoleProjects(?array $user): array
    {
        if (!$this->pdo || empty($user)) return [];
        try {
            $role = $user['role'] ?? 'customer';
            $userId = (int)($user['id'] ?? 0);

            if ($role === 'customer') {
                // Khách hàng: CHỈ XEM CÔNG TRÌNH CỦA CHÍNH MÌNH
                $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY id DESC");
                $stmt->execute([$userId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($role === 'technician') {
                // Kỹ thuật viên: CHỈ XEM CÔNG TRÌNH ĐƯỢC PHÂN CÔNG
                $stmt = $this->pdo->prepare("
                    SELECT p.* FROM projects p 
                    WHERE p.technician_id = ? 
                       OR EXISTS (SELECT 1 FROM project_assignments a WHERE a.project_id = p.id AND a.technician_id = ? AND a.ended_at IS NULL)
                    ORDER BY p.id DESC
                ");
                $stmt->execute([$userId, $userId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Admin: xem các dự án toàn sàn
                $stmt = $this->pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 10");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Lấy danh sách báo giá liên quan đến vai trò
     */
    private function getRoleQuotes(?array $user): array
    {
        if (!$this->pdo || empty($user)) return [];
        try {
            $role = $user['role'] ?? 'customer';
            $userId = (int)($user['id'] ?? 0);
            $userEmail = (string)($user['email'] ?? '');

            if ($role === 'customer') {
                // Khách hàng: CHỈ XEM BÁO GIÁ CỦA CHÍNH MÌNH (theo user_id hoặc email)
                $stmt = $this->pdo->prepare("SELECT * FROM solar_quotes WHERE user_id = ? OR (customer_email = ? AND customer_email != '') ORDER BY id DESC");
                $stmt->execute([$userId, $userEmail]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } elseif ($role === 'technician') {
                // Kỹ thuật viên không cần quản lý báo giá bán lẻ
                return [];
            } else {
                // Admin: xem các báo giá toàn sàn
                $stmt = $this->pdo->query("SELECT * FROM solar_quotes ORDER BY id DESC LIMIT 10");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Danh sách 3 tài khoản demo mẫu
     */
    private function getDemoAccounts(): array
    {
        return [
            [
                'username'  => 'admin_pro',
                'role'      => 'admin',
                'name'      => 'Quản Trị Viên Cấp Cao',
                'email'     => 'admin.pro@solaris.vn',
                'badge'     => 'sol-badge--amber',
                'level'     => 'Mức 1 • Quản Trị Viên (Admin)',
                'desc'      => 'Xem thông tin quản trị hệ thống, duyệt báo giá, phân công kỹ thuật viên.'
            ],
            [
                'username'  => 'tech_pro',
                'role'      => 'technician',
                'name'      => 'Kỹ Thuật Viên Trưởng',
                'email'     => 'tech.pro@solaris.vn',
                'badge'     => 'sol-badge--cyan',
                'level'     => 'Mức 2 • Kỹ Thuật Viên (Staff)',
                'desc'      => 'Xem thông tin nhân sự kỹ thuật, công trình phụ trách, hồ sơ khảo sát mái.'
            ],
            [
                'username'  => 'customer_pro',
                'role'      => 'customer',
                'name'      => 'Khách Hàng Trải Nghiệm',
                'email'     => 'customer.pro@gmail.com',
                'badge'     => 'sol-badge--emerald',
                'level'     => 'Mức 3 • Khách Hàng (Customer)',
                'desc'      => 'Xem kế hoạch thiết bị đã chọn, lịch sử báo giá đã gửi và thông số công suất.'
            ]
        ];
    }

    /**
     * Danh sách hồ sơ 4 Thành viên
     */
    private function getTeamMembers(): array
    {
        return [
            [
                'id'          => 1,
                'name'        => 'Nguyễn Huy Đạt',
                'mssv'        => '65130410',
                'role'        => 'Trưởng Nhóm & Quản Lý Dự Án (PM) • Fullstack Core & UI/UX',
                'avatar_icon' => '👑',
                'badge_color' => '#f59e0b',
                'weight'      => 35,
                'summary'     => 'Điều phối tiến độ tổng thể toàn dự án (PM), thiết kế kiến trúc hệ thống, điều phối Git workflow, chuẩn hóa UI/UX và font chữ tiếng Việt, sửa lỗi CSDL UTF-8, phụ trách Portal bài tập và hoàn thiện 6 bài tập Form cốt lõi.',
                'tasks'       => [
                    'Quản lý dự án (Project Manager): Lập kế hoạch, theo dõi tiến độ, phân chia nhiệm vụ cho 4 thành viên và review/hợp nhất code trên Git (main, feature/solar-plan-sync).',
                    'Kiến trúc hệ thống: Xây dựng bộ điều phối trung tâm index.php, xử lý routing, cấu trúc module và session bảo mật.',
                    'Giao diện & Trải nghiệm (UI/UX): Thiết kế Solar Theme, xử lý font chữ tiếng Việt không chân Be Vietnam Pro, khắc phục lỗi che khuất navbar và thanh điều hướng.',
                    'Cơ sở dữ liệu: Xử lý dứt điểm lỗi mã hóa ký tự Mojibake UTF-8 trong MySQL, chuẩn hóa cấu trúc dữ liệu tiếng Việt.',
                    'Portal Bài tập: Hoàn thiện 100% phân hệ 6 bài tập thực hành cá nhân (Bài 1 - 6) liên kết trực tiếp vào portal chung.'
                ]
            ],
            [
                'id'          => 2,
                'name'        => 'Lê Chí Hải',
                'mssv'        => 'Thành viên 2',
                'role'        => 'Phụ Trách Module Catalog Sản Phẩm & Kế Hoạch Dự Định',
                'avatar_icon' => '☀️',
                'badge_color' => '#10b981',
                'weight'      => 20,
                'summary'     => 'Thiết kế cơ sở dữ liệu danh mục & 26 thiết bị quang điện thực tế kèm hơn 160 hình ảnh, lập trình bộ lọc tìm kiếm phân trang và xây dựng Module Kế hoạch dự định thiết bị.',
                'tasks'       => [
                    'Catalog thiết bị: Xây dựng CSDL bảng products, categories với 4 danh mục và 26 sản phẩm thương mại (Longi, Canadian, Sungrow, Huawei, Deye...).',
                    'Bộ lọc & Tìm kiếm: Lập trình chức năng lọc theo danh mục, khoảng giá, từ khóa và phân trang bằng Hook UsePaginationState.',
                    'Kế hoạch dự định (Cart): Xây dựng tính năng lưu trữ thiết bị trong session, điều chỉnh số lượng, loại bỏ hiển thị giá tiền VNĐ theo yêu cầu.',
                    'Tương thích ghép đôi: Phối hợp xây dựng bảng kiểm tra tương thích giữa tấm pin và inverter trên trang kế hoạch.'
                ]
            ],
            [
                'id'          => 3,
                'name'        => 'Mai Đức Toàn',
                'mssv'        => 'Thành viên 3',
                'role'        => 'Phụ Trách Simulator, Sizing Engine & Phụ Trách Báo Cáo Dự Án',
                'avatar_icon' => '🧮',
                'badge_color' => '#38bdf8',
                'weight'      => 25,
                'summary'     => 'Phát triển thuật toán quang điện UseSolarCalculator, tính toán KW điện, tiền tạo ra và thời gian hoàn vốn ROI; lập trình mô phỏng mặt bằng mái 2D SVG; bóc tách BOM; quản trị solar_quotes và chịu trách nhiệm chính về hồ sơ báo cáo dự án.',
                'tasks'       => [
                    'Thuật toán tính toán năng lượng: Lập trình UseSolarCalculator.php tính sản lượng kWh, số tiền tạo ra hàng tháng và thời gian hoàn vốn đầu tư ROI.',
                    'Đồng bộ Kế hoạch dự định: Triển khai calculateFromPlan() lấy thiết bị từ giỏ hàng sang bộ tính toán mô phỏng.',
                    'Mô phỏng mặt bằng 2D SVG: Lập trình sơ đồ bố trí giàn pin động theo hàng x cột và góc đón nắng tối ưu.',
                    'Bóc tách vật tư (BOM) & Báo giá: Quản lý bảng solar_quotes, bóc tách danh mục thiết bị và phân trang báo giá.',
                    'Phụ trách báo cáo (Documentation Lead): Soạn thảo tài liệu đặc tả kỹ thuật, slide thuyết trình và hồ sơ nghiệm thu dự án.'
                ]
            ],
            [
                'id'          => 4,
                'name'        => 'Phan Trần Thịnh',
                'mssv'        => 'Thành viên 4',
                'role'        => 'Phụ Trách Module Quản Lý Lắp Đặt, Khảo Sát & Phân Quyền Bảo Mật',
                'avatar_icon' => '📋',
                'badge_color' => '#fbbf24',
                'weight'      => 20,
                'summary'     => 'Thiết kế cơ sở dữ liệu phân quyền 3 mức tài khoản, xây dựng quy trình quản lý lắp đặt 4 giai đoạn, tải lên hồ sơ khảo sát an toàn và bảo mật CSRF.',
                'tasks'       => [
                    'Hệ thống phân quyền (Auth): Thiết kế bảng users với 3 mức quyền: Admin, Kỹ thuật viên (Technician) và Khách hàng (Customer).',
                    'Quản lý lắp đặt (Installation): Xây dựng UseInstallationWorkflow.php điều phối dự án qua 4 giai đoạn từ tiếp nhận đến nghiệm thu.',
                    'Tải lên tài liệu (File Upload): Phát triển UseFileUpload.php kiểm soát an toàn định dạng file và dung lượng khi upload hồ sơ khảo sát.',
                    'Điều phối kỹ thuật viên: Xây dựng giao diện phân bổ kỹ thuật viên trưởng và theo dõi lịch trình thi công hiện trường.'
                ]
            ]
        ];
    }

    /**
     * Lấy danh sách kỹ thuật viên kèm số lượng công trình phân công
     */
    public function getTechnicians(): array
    {
        if (!$this->pdo) return [];
        try {
            $sql = "
                SELECT u.id, u.username, u.full_name, u.email, u.phone, u.role, u.created_at,
                       COUNT(DISTINCT p.id) as project_count,
                       COUNT(DISTINCT a.id) as assignment_count
                FROM users u
                LEFT JOIN projects p ON p.technician_id = u.id
                LEFT JOIN project_assignments a ON a.technician_id = u.id AND a.ended_at IS NULL
                WHERE u.role = 'technician'
                GROUP BY u.id
                ORDER BY u.id DESC
            ";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }
}
