<?php
declare(strict_types=1);

namespace App\Modules\Member4_Installation;

use App\Hooks\UseInstallationWorkflow as InstallationWorkflow;
use PDOException;
use RuntimeException;
use Throwable;

final class InstallationController
{
    public function index(): array
    {
        $_SESSION['installation_csrf'] ??= bin2hex(random_bytes(32));
        $view = ['actor' => null, 'error' => null, 'flash' => $_SESSION['installation_flash'] ?? null];
        unset($_SESSION['installation_flash']);
        try {
            $workflow = new InstallationWorkflow(Config::database());
            $post = $_SERVER['REQUEST_METHOD'] === 'POST';
            $action = (string) ($_POST['action'] ?? '');
            if ($post) {
                $this->checkCsrf();
            }
            if ($post && $action === 'login') {
                $this->login($workflow);
            }
            // TV1 có thể dùng session user_id; vai trò luôn được đọc lại từ CSDL.
            $userId = (int) ($_SESSION['user_id'] ?? $_SESSION['installation_user_id'] ?? 0);
            $actor = $workflow->rows('SELECT id,full_name,role FROM users WHERE id=?', [$userId])[0] ?? null;
            if ($actor && in_array($actor['role'], ['admin', 'technician'], true)) {
                $view['actor'] = $actor;
            } elseif ($actor) {
                throw new RuntimeException('Trang quản lý lắp đặt dành cho quản lý và kỹ thuật viên.');
            } else {
                return $view;
            }
            if ($post && $action === 'logout') {
                unset($_SESSION['installation_user_id']);
                $_SESSION['installation_csrf'] = bin2hex(random_bytes(32));
                $this->redirect();
            }
            if (isset($_GET['document'])) {
                $this->download($workflow, $actor, (int) $_GET['document']);
            }
            $id = max(0, (int) ($_GET['project'] ?? 0));
            if ($post) {
                try {
                    $workflow->act($actor, $id, $action, $_POST, $_FILES);
                    $_SESSION['installation_flash'] = 'Đã lưu thay đổi.';
                } catch (RuntimeException $error) {
                    $_SESSION['installation_flash'] = $error->getMessage();
                }
                $this->redirect($id);
            }
            $view['filter'] = (string) ($_GET['filter'] ?? ($actor['role'] === 'admin' ? 'new' : 'in_progress'));
            $view['listing'] = $workflow->listing($actor, $view['filter'], (int) ($_GET['p'] ?? 1));
            if ($id) {
                $view['detail'] = $workflow->detail($id, $actor, (int) ($_GET['log_page'] ?? 1), (int) ($_GET['request_page'] ?? 1));
                $project = $view['detail']['project'];
                $start = (string) ($_GET['from'] ?? $project['start_date'] ?? date('Y-m-d'));
                $end = (string) ($_GET['to'] ?? $project['planned_end_date'] ?? date('Y-m-d', strtotime('+20 days')));
                $view['availability'] = $workflow->availability($start, $end, (int) ($_GET['staff_page'] ?? 1), $id);
                $docPager = \App\Hooks\UsePaginationState::make(count($view['detail']['documents']), (int) ($_GET['doc_page'] ?? 1));
                $view['detail']['documents'] = array_slice($view['detail']['documents'], $docPager['offset'], $docPager['limit']);
                $view['detail']['docPager'] = $docPager;
            }
        } catch (PDOException $error) {
            error_log('Installation database: ' . $error->getMessage());
            $view['error'] = 'Chưa kết nối được dữ liệu lắp đặt. Hãy import database/tv4-installation.sql và kiểm tra cấu hình kết nối theo tài liệu TV4.';
        } catch (RuntimeException $error) {
            $view['error'] = $error->getMessage();
        } catch (Throwable $error) {
            error_log('Installation: ' . $error->getMessage());
            $view['error'] = 'Không thể xử lý yêu cầu. Vui lòng tải lại trang hoặc xem nhật ký máy chủ.';
        }
        return $view;
    }

    private function checkCsrf(): void
    {
        $token = (string) ($_POST['csrf'] ?? '');
        if (!hash_equals($_SESSION['installation_csrf'], $token)) {
            throw new RuntimeException('Phiên biểu mẫu hết hạn. Hãy tải lại trang.');
        }
    }

    private function login(InstallationWorkflow $workflow): void
    {
        if (getenv('SOLARIS_INSTALLATION_LOGIN') === '0') {
            throw new RuntimeException('Hãy đăng nhập qua hệ thống xác thực TV1.');
        }
        if (($_SESSION['installation_login_blocked_until'] ?? 0) > time()) {
            throw new RuntimeException('Đã thử quá nhiều lần. Vui lòng thử lại sau 5 phút.');
        }
        $user = $workflow->rows("SELECT * FROM users WHERE username=? AND role IN ('admin','technician')", [trim((string) ($_POST['username'] ?? ''))])[0] ?? null;
        if (!$user || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            $_SESSION['installation_login_attempts'] = ($_SESSION['installation_login_attempts'] ?? 0) + 1;
            if ($_SESSION['installation_login_attempts'] >= 5) {
                $_SESSION['installation_login_blocked_until'] = time() + 300;
                $_SESSION['installation_login_attempts'] = 0;
            }
            throw new RuntimeException('Tên đăng nhập hoặc mật khẩu không đúng.');
        }
        session_regenerate_id(true);
        $_SESSION['installation_user_id'] = (int) $user['id'];
        $_SESSION['installation_login_attempts'] = 0;
        $_SESSION['installation_csrf'] = bin2hex(random_bytes(32));
        $this->redirect();
    }

    private function redirect(int $id = 0): void
    {
        header('Location: index.php?page=installations' . ($id ? '&project=' . $id : ''), true, 303);
        exit;
    }

    private function download(InstallationWorkflow $workflow, array $actor, int $id): void
    {
        $document = $workflow->rows('SELECT * FROM project_documents WHERE id=?', [$id])[0] ?? null;
        if (!$document) {
            throw new RuntimeException('Không tìm thấy tài liệu.');
        }
        $workflow->project((int) $document['project_id'], $actor);
        $base = realpath(dirname(__DIR__, 3) . '/assets/uploads/installation');
        $path = realpath(dirname(__DIR__, 3) . '/' . $document['file_path']);
        if (!$base || !$path || !str_starts_with($path, $base . DIRECTORY_SEPARATOR) || !is_file($path)) {
            throw new RuntimeException('Tài liệu không còn trên máy chủ.');
        }
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Type: ' . $document['file_type']);
        header('Content-Length: ' . filesize($path));
        $inline = ($_GET['preview'] ?? '') === '1' && in_array($document['file_type'], ['image/jpeg', 'image/png'], true);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename=\"document-{$id}\"; filename*=UTF-8''" . rawurlencode($document['file_name']));
        readfile($path);
        exit;
    }
}
