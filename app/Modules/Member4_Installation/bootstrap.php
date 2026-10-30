<?php
declare(strict_types=1);

use App\Modules\Member4_Installation\InstallationController;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once __DIR__ . '/Config.php';
require_once dirname(__DIR__, 2) . '/Hooks/UsePaginationState.php';
require_once dirname(__DIR__, 2) . '/Hooks/UseFileUpload.php';
require_once dirname(__DIR__, 2) . '/Hooks/UseInstallationWorkflow.php';
require_once __DIR__ . '/InstallationController.php';
require_once __DIR__ . '/View.php';
$installationView = (new InstallationController())->index();
