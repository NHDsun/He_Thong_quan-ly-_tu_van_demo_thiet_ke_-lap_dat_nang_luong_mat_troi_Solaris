<?php
declare(strict_types=1);

// Đăng ký Autoloader chuẩn PSR-4 cho namespace App\
spl_autoload_register(function (string $class): void {
    $prefix = "App" . chr(92);
    $baseDir = __DIR__ . "/app/";
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace(chr(92), "/", $relativeClass) . ".php";
    if (is_file($file)) {
        require_once $file;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
}

$page = $_GET['page'] ?? 'home';
$validPages = ['home', 'products', 'cart', 'calculator', 'installations', 'exercises', 'profile'];
if (!in_array($page, $validPages, true)) {
    $page = 'home';
}

$pageTitles = [
    'home'          => 'SOLARIS - Năng Lượng Mặt Trời Thông Minh',
    'products'      => 'Giải Pháp & Sản Phẩm - SOLARIS',
    'cart'          => 'Kế Hoạch Dự Định Thiết Bị - SOLARIS',
    'calculator'    => 'Tư Vấn & Demo Thiết Kế - SOLARIS',
    'installations' => 'Quản Lý Lắp Đặt & Khảo Sát - SOLARIS',
    'exercises'     => 'Portal 24 Bài Tập Thực Hành - SOLARIS',
    'profile'       => 'Quản Lý Hồ Sơ Tài Khoản (Profile) - SOLARIS'
];
$title = $pageTitles[$page] ?? 'SOLARIS';

// -------------------------------------------------------------------------
// Điều phối cho phân hệ TV4 (Lắp đặt)
// -------------------------------------------------------------------------
if ($page === 'installations') {
    require __DIR__ . '/app/Modules/Member4_Installation/bootstrap.php';
}

// -------------------------------------------------------------------------
// Điều phối cho phân hệ TV2 (Sản phẩm & Giỏ hàng)
// -------------------------------------------------------------------------
if ($page === 'products') {
    $productController = new \App\Modules\Member2_Products\ProductController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $productController->handleCart((string) ($_POST['cart_action'] ?? ''));
        if (($_POST['cart_action'] ?? '') === 'add') {
            $returnParams = ['page' => 'products'];
            foreach (['p', 'q', 'category', 'min_price', 'max_price'] as $key) {
                if (isset($_GET[$key]) && $_GET[$key] !== '') $returnParams[$key] = $_GET[$key];
            }
            $anchor = !empty($_POST['product_id']) ? '#product-' . (int) $_POST['product_id'] : '';
            header('Location: index.php?' . http_build_query($returnParams) . $anchor);
            exit;
        }
        header('Location: index.php?page=cart');
        exit;
    }
    $productView = isset($_GET['product']) ? $productController->detail((int) $_GET['product']) : $productController->index();
}

if ($page === 'cart') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cartAction = (string) ($_POST['cart_action'] ?? '');
        (new \App\Modules\Member2_Products\ProductController())->handleCart($cartAction);
        header('Location: index.php?page=' . ($cartAction === 'demo_selected' ? 'calculator&source=plan' : 'cart'));
        exit;
    }
    $cartItems = \App\Modules\Member2_Products\ProductController::cartItems();
    $compatibility = \App\Hooks\UseSolarCalculator::analyzeCompatibility($cartItems);
}

// -------------------------------------------------------------------------
// Điều phối cho phân hệ TV3 (Solar Calculator)
// -------------------------------------------------------------------------
if ($page === 'calculator') {
    $solarCtrl = new \App\Modules\Member3_Simulator\SolarDemoController();
    $demoItems = class_exists(\App\Modules\Member2_Products\ProductController::class)
        ? \App\Modules\Member2_Products\ProductController::demoItems()
        : [];
    if (empty($demoItems) && class_exists(\App\Modules\Member2_Products\ProductController::class)) {
        $demoItems = \App\Modules\Member2_Products\ProductController::cartItems();
    }
    $solarData = $solarCtrl->index($demoItems);
    $calc = $solarData['calc'];
    $source = $solarData['source'];
    $has_cart_items = $solarData['has_cart_items'];
    $plan_calc = $solarData['plan_calc'];
    $bill_calc = $solarData['bill_calc'];
    $compatibility = $solarData['compatibility'];
    $cart_products = $solarData['cart_products'];
    $alert = $solarData['alert'];
    $quotes_list = $solarData['quotes_list'];
    $pagination = $solarData['pagination'];
    $db_connected = $solarData['db_connected'];
    $db_error = $solarData['db_error'];
}

// -------------------------------------------------------------------------
// Xác định tài khoản hiện tại toàn cục (phục vụ Navigation Bar & Profile)
// -------------------------------------------------------------------------
$currentUserId = (int)($_SESSION['user_id'] ?? $_SESSION['installation_user_id'] ?? 0);
$currentUser = null;
try {
    $pdoGlobal = \App\Config\Database::getPdo();
    if ($pdoGlobal && $currentUserId > 0) {
        $uStmt = $pdoGlobal->prepare("SELECT id, username, full_name, email, phone, role, created_at FROM users WHERE id = ?");
        $uStmt->execute([$currentUserId]);
        $currentUser = $uStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    if (!$currentUser && $pdoGlobal) {
        $uStmt = $pdoGlobal->query("SELECT id, username, full_name, email, phone, role, created_at FROM users WHERE username = 'admin_pro' LIMIT 1");
        $currentUser = $uStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($currentUser) {
            $_SESSION['user_id'] = (int)$currentUser['id'];
            $_SESSION['user_role'] = (string)$currentUser['role'];
            $_SESSION['user_name'] = (string)$currentUser['full_name'];
        }
    }
} catch (\Throwable $e) {}

if (!$currentUser) {
    $currentUser = [
        'id' => 10,
        'username' => 'admin_pro',
        'full_name' => 'Quản Trị Viên Cấp Cao (Admin Level)',
        'email' => 'admin.pro@solaris.vn',
        'phone' => '0901.888.001',
        'role' => 'admin',
        'created_at' => '2026-10-01 08:00:00'
    ];
}

// -------------------------------------------------------------------------
// Điều phối cho phân hệ TV1 (Hồ sơ 4 Thành viên & Quản lý Profile Tài khoản)
// -------------------------------------------------------------------------
if ($page === 'profile') {
    $profileCtrl = new \App\Modules\Member1_Lead\ProfileController();
    $profileData = $profileCtrl->handleRequest();
    $currentUser = $profileData['current_user'] ?? $currentUser;
    $roleStats = $profileData['role_stats'];
    $roleProjects = $profileData['role_projects'];
    $roleQuotes = $profileData['role_quotes'];
    $cartItems = $profileData['cart_items'];
    $cartCompat = $profileData['cart_compat'];
    $planCalc = $profileData['plan_calc'];
    $demoAccounts = $profileData['demo_accounts'];
    $teamMembers = $profileData['team_members'];
    $profileAlert = $profileData['alert'];
    $activeTab = $profileData['view_mode'] ?? 'account';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/solar-logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Montserrat:wght@600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/solaris-theme.css?v=<?= file_exists(__DIR__ . '/resources/css/solaris-theme.css') ? filemtime(__DIR__ . '/resources/css/solaris-theme.css') : '2.3' ?>">
</head>
<body class="solar-bg-grid <?= $page === 'home' ? 'solar-layout-home' : 'solar-layout-app' ?>">
    <div class="glow-ambient-1"></div>
    <div class="glow-ambient-2"></div>

<?php if ($page === 'home'): ?>
    <!-- ================================================================= -->
    <!-- GIAO DIỆN TRANG CHỦ (LANDING PAGE FULL-WIDTH VỚI NAVBAR NGANG)    -->
    <!-- ================================================================= -->
    <header class="sol-header">
        <div class="sol-container sol-header__inner">
            <a href="index.php?page=home" class="sol-brand">
                <div class="sol-brand__logo">
                    <img src="assets/solar-logo.svg" alt="Solaris Logo" style="width: 26px; height: 26px;">
                </div>
                <div class="sol-brand__text">
                    <span class="sol-brand__name">SOLAR<span>IS</span></span>
                    <span class="sol-brand__tagline">Khai Phá Năng Lượng Tương Lai</span>
                </div>
            </a>

            <div class="sol-header__actions">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-size: 0.825rem;">
                    <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px #10b981;"></span>
                    <span>Hotline: <strong style="color: #ffffff;">0909.888.SOLAR</strong></span>
                </div>

                <!-- Nút Kế Hoạch Dự Định nổi bật bên cạnh nút Khảo Sát Báo Giá -->
                <a href="index.php?page=cart" class="sol-header-cart" title="Xem kế hoạch dự định thiết bị" style="display: inline-flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.95rem; background: rgba(17, 24, 39, 0.9); border: 1px solid rgba(245, 158, 11, 0.45); border-radius: 6px; color: #ffffff !important; text-decoration: none !important; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.35);">
                    <span class="sol-header-cart__icon" style="font-size: 1.05rem; line-height: 1;">📋</span>
                    <span class="sol-header-cart__text" style="color: #ffffff !important; font-weight: 600;">Kế Hoạch Dự Định</span>
                    <span class="sol-header-cart__badge" style="display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); color: #080c14; font-weight: 800; font-size: 0.75rem; min-width: 22px; height: 20px; padding: 0 6px; border-radius: 999px; line-height: 1; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);"><?= class_exists(\App\Modules\Member2_Products\ProductController::class) ? \App\Modules\Member2_Products\ProductController::cartCount() : 0 ?></span>
                </a>

                <a href="index.php?page=calculator" class="sol-btn sol-btn--solar sol-btn--sm">
                    ⚡ Khảo Sát Báo Giá
                </a>
            </div>
        </div>
    </header>

    <nav class="sol-nav-wrapper">
        <div class="sol-container sol-nav-inner">
            <ul class="sol-nav-tabs">
                <li>
                    <a href="index.php?page=home" class="sol-tab-item active" title="Trang chủ Solaris">
                        <span>🏠 Trang Chủ</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=products" class="sol-tab-item" title="Danh mục thiết bị quang điện">
                        <span>☀️ Sản Phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=calculator" class="sol-tab-item" title="Mô phỏng thiết kế & tính toán hoàn vốn">
                        <span>🧮 Demo Thiết Kế</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=installations" class="sol-tab-item" title="Quản lý lắp đặt & thi công">
                        <span>📋 Lắp Đặt</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=exercises" class="sol-tab-item" title="Portal 24 bài tập thực hành">
                        <span>📚 Bài Tập (24 Bài)</span>
                    </a>
                </li>
            </ul>

            <div class="sol-nav-right">
                <a href="index.php?page=profile" class="sol-nav-profile-pill" title="Profile">
                    <span class="sol-nav-profile-pill__icon">👤</span>
                    <span class="sol-nav-profile-pill__text">Profile</span>
                    <span class="sol-nav-profile-pill__badge"><?= htmlspecialchars(strtoupper($currentUser['username'] ?? 'ADMIN_PRO')) ?></span>
                </a>
            </div>
        </div>
    </nav>

    <main class="sol-main-content">
        <!-- ================================================================= -->
        <!-- 1. HERO SECTION SIÊU HIỆN ĐẠI & DẢI THÔNG SỐ LIVE TICKER         -->
        <!-- ================================================================= -->
        <section class="sol-hero sol-hero--modern">
            <div class="sol-hero-sun-glow"></div>
            <div class="sol-container" style="position: relative; z-index: 2;">
                
                <!-- Dải thông số trực quan thời gian thực (Live Solar Ticker) -->
                <div class="sol-live-ticker">
                    <div class="sol-live-ticker__item">
                        <span class="sol-live-pulse-dot"></span>
                        <span>Bức xạ mặt trời hiện tại: <strong>5.4 kWh/m²/ngày</strong></span>
                    </div>
                    <div class="sol-live-ticker__item">
                        <span>⚡ Hiệu suất chuỗi Pin: <strong>98.8% Tier-1</strong></span>
                    </div>
                    <div class="sol-live-ticker__item">
                        <span>🌱 Giảm CO2 hôm nay: <strong>+1.4 Tấn</strong></span>
                    </div>
                    <div class="sol-live-ticker__item">
                        <span class="sol-realtime-badge" id="rtBadge">
                            <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#10b981;box-shadow:0 0 6px #10b981;"></span>
                            <span id="rtStatusText">Socket.io Realtime Sync</span>
                        </span>
                    </div>
                </div>

                <div class="sol-hero__badge" style="margin-bottom: 1.25rem;">
                    <span class="sol-badge sol-badge--amber" style="padding: 0.35rem 0.95rem; font-size: 0.8rem;">
                        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#f59e0b;box-shadow:0 0 8px #f59e0b;"></span>
                        Công Nghệ Điện Mặt Trời Thế Hệ Mới 2026 • Chuẩn Tier-1 BloombergNEF
                    </span>
                </div>

                <h1 class="sol-hero__title" style="font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: 1.15; margin-bottom: 1.25rem;">
                    Khai Phá Nguồn Điện Mặt Trời <br>
                    <span class="highlight" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #38bdf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Thông Minh, Bền Vững & Tối Ưu Chi Phí</span>
                </h1>

                <p class="sol-hero__desc" style="max-width: 780px; margin: 0 auto 2rem; font-size: 1.05rem; line-height: 1.65; color: #cbd5e1;">
                    Hệ thống quản lý, tư vấn thiết kế và giám sát thi công điện mặt trời toàn diện. Tự động liên kết kế hoạch dự định thiết bị, mô phỏng công suất phát điện $kWp$, tính toán thời gian gỡ vốn đầu tư và đồng bộ thời gian thực mượt mà.
                </p>

                <div class="sol-hero__actions" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="index.php?page=calculator" class="sol-btn sol-btn--solar sol-btn--lg" style="box-shadow: 0 6px 24px rgba(245, 158, 11, 0.45); font-size: 1rem; padding: 0.85rem 1.85rem;">
                        ⚡ Demo Thiết Kế & Báo Giá
                    </a>
                    <a href="index.php?page=cart" class="sol-btn sol-btn--secondary sol-btn--lg" style="font-size: 1rem; padding: 0.85rem 1.75rem;">
                        📋 Kế Hoạch Dự Định Thiết Bị
                    </a>
                    <a href="index.php?page=products" class="sol-btn sol-btn--outline sol-btn--lg" style="font-size: 1rem; padding: 0.85rem 1.75rem;">
                        ☀️ Khám Phá 26 Vật Tư Quang Điện
                    </a>
                </div>
            </div>
        </section>

        <!-- ================================================================= -->
        <!-- 2. BỘ ĐẾM CHỈ SỐ HOẠT ĐỘNG THÔNG MINH (ANIMATED METRICS COUNTER)    -->
        <!-- ================================================================= -->
        <section class="sol-metrics" style="padding: 1.5rem 0 3.5rem;">
            <div class="sol-container">
                <div class="sol-metrics__grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="sol-metric-card" style="position: relative; overflow: hidden;">
                        <span class="sol-metric-card__value sol-counter" data-target="90" data-suffix="%">90%</span>
                        <span class="sol-metric-card__label">Tiết Kiệm Tiền Điện Hàng Tháng</span>
                    </div>
                    <div class="sol-metric-card">
                        <span class="sol-metric-card__value sol-counter" data-target="25" data-suffix=" Năm">25 Năm</span>
                        <span class="sol-metric-card__label">Bảo Hành Hiệu Suất Tuyến Tính</span>
                    </div>
                    <div class="sol-metric-card">
                        <span class="sol-metric-card__value sol-counter" data-target="3.5" data-suffix=" Năm" data-decimal="1">3.5 Năm</span>
                        <span class="sol-metric-card__label">Thời Gian Hoàn Vốn Đầu Tư</span>
                    </div>
                    <div class="sol-metric-card">
                        <span class="sol-metric-card__value sol-counter" data-target="100" data-suffix="%">100%</span>
                        <span class="sol-metric-card__label">Giám Sát IoT Thời Gian Thực 24/7</span>
                    </div>
                    <div class="sol-metric-card">
                        <span class="sol-metric-card__value sol-counter" data-target="1250" data-suffix="+">1.250+</span>
                        <span class="sol-metric-card__label">Công Trình Đã Nghiệm Thu Đóng Điện</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================================================================= -->
        <!-- 3. SLIDER / CAROUSEL 4 GIẢI PHÁP NĂNG LƯỢNG SIÊU MƯỢT (SOCKET SYNC)-->
        <!-- ================================================================= -->
        <section class="sol-solutions" style="padding: 2rem 0 4rem; position: relative;">
            <div class="sol-container">
                <div class="sol-section-header" style="text-align: center; margin-bottom: 2rem;">
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.75rem;">MÔ HÌNH HỆ THỐNG TIÊU CHUẨN</span>
                    <h2 class="sol-section-title" style="font-size: 2.25rem;">4 Giải Pháp Năng Lượng Tối Ưu Cho Mọi Công Trình</h2>
                    <p class="sol-section-subtitle" style="max-width: 650px; margin: 0 auto;">
                        Chuyển slide mượt mà, đồng bộ trạng thái qua Socket.io và thiết kế phù hợp từng nhu cầu từ hộ gia đình đến nhà máy công nghiệp.
                    </p>
                </div>

                <!-- Bộ điều khiển Carousel & Phân loại -->
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <button type="button" class="sol-btn sol-btn--solar sol-btn--sm sol-cat-tab active" onclick="goToSlide(0)">Tất Cả (4 Gói)</button>
                    <button type="button" class="sol-btn sol-btn--secondary sol-btn--sm sol-cat-tab" onclick="goToSlide(0)">Hòa Lưới On-Grid</button>
                    <button type="button" class="sol-btn sol-btn--secondary sol-btn--sm sol-cat-tab" onclick="goToSlide(1)">Lưu Trữ Hybrid ESS</button>
                    <button type="button" class="sol-btn sol-btn--secondary sol-btn--sm sol-cat-tab" onclick="goToSlide(2)">Doanh Nghiệp C&I</button>
                    <button type="button" class="sol-btn sol-btn--secondary sol-btn--sm sol-cat-tab" onclick="goToSlide(3)">Nông Nghiệp Bơm Nước</button>
                </div>

                <!-- Track Carousel chuyển slide siêu mượt -->
                <div class="sol-carousel-wrapper">
                    <div class="sol-carousel-track" id="solutionsTrack">
                        
                        <!-- Slide 1: Hòa Lưới On-Grid -->
                        <div class="sol-carousel-slide">
                            <div class="sol-solution-box">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <span class="sol-badge sol-badge--amber">PHỔ BIẾN NHẤT</span>
                                        <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">Hoàn vốn ~3.5 năm</span>
                                    </div>
                                    <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0 0 0.65rem;">
                                        Hệ Thống Hòa Lưới (On-Grid)
                                    </h3>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.55; margin-bottom: 1.25rem;">
                                        Phù hợp tối ưu cho hộ gia đình và văn phòng làm việc tiêu thụ điện nhiều vào ban ngày. Cắt giảm ngay các bậc thang giá điện cao nhất của EVN.
                                    </p>
                                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.825rem; color: #cbd5e1;">
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #10b981;">✓</span> Chi phí đầu tư thấp nhất, không tốn bảo dưỡng ắc quy
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #10b981;">✓</span> Inverter hòa lưới hiệu suất 98.6% đồng bộ chuẩn EVN
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #10b981;">✓</span> Tự động ngắt Anti-Islanding bảo vệ an toàn điện lực
                                        </li>
                                    </ul>
                                </div>
                                <a href="index.php?page=calculator&package=residential" class="sol-btn sol-btn--solar sol-btn--sm" style="width: 100%; text-align: center;">
                                    Khám Phá Gói Dân Dụng →
                                </a>
                            </div>
                        </div>

                        <!-- Slide 2: Lưu Trữ Hybrid ESS -->
                        <div class="sol-carousel-slide">
                            <div class="sol-solution-box" style="border-color: rgba(56, 189, 248, 0.4);">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <span class="sol-badge sol-badge--cyan">ĐỘ TIN CẬY 24/7</span>
                                        <span style="font-size: 0.75rem; color: #38bdf8; font-weight: 700;">UPS &lt;10ms</span>
                                    </div>
                                    <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0 0 0.65rem;">
                                        Hệ Thống Lưu Trữ (Hybrid ESS)
                                    </h3>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.55; margin-bottom: 1.25rem;">
                                        Tích hợp Pin lưu trữ Lithium LiFePO4 thế hệ mới. Đảm bảo nguồn điện liên tục 24/7 cho thiết bị trọng yếu ngay cả khi lưới điện EVN bị mất.
                                    </p>
                                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.825rem; color: #cbd5e1;">
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #38bdf8;">✓</span> Cung cấp điện liên tục ban đêm và khi sự cố mất điện
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #38bdf8;">✓</span> Pin Lithium 10.000 chu kỳ sạc xả, tuổi thọ trên 15 năm
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #38bdf8;">✓</span> Chuyển mạch UPS không gián đoạn máy tính, camera & tủ lạnh
                                        </li>
                                    </ul>
                                </div>
                                <a href="index.php?page=calculator&package=hybrid" class="sol-btn sol-btn--secondary sol-btn--sm" style="width: 100%; text-align: center; border-color: rgba(56, 189, 248, 0.5);">
                                    Khám Phá Gói Hybrid Lưu Trữ →
                                </a>
                            </div>
                        </div>

                        <!-- Slide 3: Thương Mại & Nhà Xưởng C&I -->
                        <div class="sol-carousel-slide">
                            <div class="sol-solution-box" style="border-color: rgba(168, 85, 247, 0.4);">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <span class="sol-badge sol-badge--amber" style="background: rgba(168, 85, 247, 0.15); color: #c084fc; border-color: rgba(168, 85, 247, 0.3);">DOANH NGHIỆP C&I</span>
                                        <span style="font-size: 0.75rem; color: #c084fc; font-weight: 700;">Chứng chỉ ESG</span>
                                    </div>
                                    <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0 0 0.65rem;">
                                        Mái Nhà Xưởng (C&I 20kWp - 1MWp)
                                    </h3>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.55; margin-bottom: 1.25rem;">
                                        Quy mô lớn từ 20kWp đến hàng Megawatt cho nhà máy, khu công nghiệp, cao ốc văn phòng. Triệt tiêu đỉnh chi phí điện sản xuất giờ cao điểm.
                                    </p>
                                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.825rem; color: #cbd5e1;">
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #c084fc;">✓</span> Giảm chi phí điện sản xuất giờ cao điểm lên tới 4.000đ/kWh
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #c084fc;">✓</span> Đạt chứng chỉ xanh ESG, I-REC xuất khẩu châu Âu & Mỹ
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #c084fc;">✓</span> Thời gian hoàn vốn ấn tượng chỉ từ 3.2 đến 3.8 năm
                                        </li>
                                    </ul>
                                </div>
                                <a href="index.php?page=calculator&package=commercial" class="sol-btn sol-btn--secondary sol-btn--sm" style="width: 100%; text-align: center; border-color: rgba(168, 85, 247, 0.5);">
                                    Khám Phá Gói Doanh Nghiệp →
                                </a>
                            </div>
                        </div>

                        <!-- Slide 4: Nông Nghiệp Bơm Nước Năng Lượng Mặt Trời -->
                        <div class="sol-carousel-slide">
                            <div class="sol-solution-box" style="border-color: rgba(16, 185, 129, 0.4);">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                        <span class="sol-badge sol-badge--emerald">NÔNG NGHIỆP XANH</span>
                                        <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">Độc lập 100%</span>
                                    </div>
                                    <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0 0 0.65rem;">
                                        Bơm Nước Năng Lượng (Agri-Solar)
                                    </h3>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.55; margin-bottom: 1.25rem;">
                                        Giải pháp cấp nguồn tự động cho máy bơm nước tưới tiêu trang trại sầu riêng, thanh long, cà phê mà không cần kéo đường dây điện lưới tốn kém.
                                    </p>
                                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.825rem; color: #cbd5e1;">
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #10b981;">✓</span> Tiết kiệm 100% chi phí xăng dầu chạy máy nổ tưới tiêu
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #10b981;">✓</span> Tự động điều biến tốc độ bơm nước theo cường độ ánh nắng
                                        </li>
                                        <li style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="color: #10b981;">✓</span> Vận hành bền bỉ trên 25 năm trong điều kiện khắc nghiệt
                                        </li>
                                    </ul>
                                </div>
                                <a href="index.php?page=calculator&package=agri" class="sol-btn sol-btn--secondary sol-btn--sm" style="width: 100%; text-align: center; border-color: rgba(16, 185, 129, 0.5);">
                                    Khám Phá Gói Bơm Nước →
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Nút điều hướng Carousel (Prev / Next & Dots) -->
                <div class="sol-carousel-nav">
                    <button type="button" class="sol-carousel-btn" onclick="prevSlide()" aria-label="Slide trước">❮</button>
                    <div class="sol-carousel-dots" id="carouselDots">
                        <button type="button" class="sol-carousel-dot active" onclick="goToSlide(0)"></button>
                        <button type="button" class="sol-carousel-dot" onclick="goToSlide(1)"></button>
                        <button type="button" class="sol-carousel-dot" onclick="goToSlide(2)"></button>
                        <button type="button" class="sol-carousel-dot" onclick="goToSlide(3)"></button>
                    </div>
                    <button type="button" class="sol-carousel-btn" onclick="nextSlide()" aria-label="Slide tiếp theo">❯</button>
                </div>
            </div>
        </section>

        <!-- ================================================================= -->
        <!-- 4. CÔNG CỤ TÍNH TOÁN CÔNG SUẤT & THỜI GIAN GỠ VỐN (ROI CALCULATOR) -->
        <!-- ================================================================= -->
        <section class="sol-calc-widget" style="padding: 2.5rem 0 4.5rem;">
            <div class="sol-container">
                <div class="sol-calc-widget__card" style="border: 1.5px solid rgba(245, 158, 11, 0.45); box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
                    <div class="sol-calc-widget__grid">
                        <div>
                            <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.75rem;">CÔNG CỤ MÔ PHỎNG THÔNG MINH</span>
                            <h2 style="font-size: 2rem; color: #ffffff; margin-bottom: 0.75rem; font-family: var(--font-heading);">
                                Ước Tính Tiềm Năng Năng Lượng & Gỡ Vốn
                            </h2>
                            <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6;">
                                Kéo chọn mức tiền điện trung bình hàng tháng và khu vực địa lý của bạn để hệ thống tự động tính toán công suất $kWp$, số lượng tấm pin và thời gian hoàn vốn đầu tư dự kiến.
                            </p>

                            <!-- Chọn khu vực bức xạ nắng -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.5rem;">
                                    Khu vực địa lý lắp đặt (Số giờ nắng đỉnh):
                                </label>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;" id="regionBtns">
                                    <button type="button" class="sol-btn sol-btn--secondary sol-btn--sm sol-region-btn" data-hours="3.8" onclick="setRegion(3.8, this)">
                                        Miền Bắc (3.8h)
                                    </button>
                                    <button type="button" class="sol-btn sol-btn--secondary sol-btn--sm sol-region-btn" data-hours="4.8" onclick="setRegion(4.8, this)">
                                        Miền Trung (4.8h)
                                    </button>
                                    <button type="button" class="sol-btn sol-btn--solar sol-btn--sm sol-region-btn active" data-hours="5.2" onclick="setRegion(5.2, this)">
                                        Miền Nam (5.2h)
                                    </button>
                                </div>
                            </div>

                            <div class="sol-calc-form">
                                <label class="sol-calc-label">
                                    <span>Hóa đơn tiền điện bình quân:</span>
                                    <strong id="billDisplay" style="color: var(--accent-gold); font-size: 1.25rem;">3.000.000 VNĐ</strong>
                                </label>
                                <input type="range" class="sol-range" id="billRange" min="500000" max="20000000" step="500000" value="3000000" oninput="calculateSolarHome(this.value)">
                                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                                    <span>500K</span>
                                    <span>5 Triệu</span>
                                    <span>10 Triệu</span>
                                    <span>20 Triệu+</span>
                                </div>
                            </div>
                        </div>

                        <!-- Khối kết quả hiển thị thời gian gỡ vốn & công suất -->
                        <div class="sol-calc-result" style="background: rgba(8, 12, 20, 0.85); border: 1px solid var(--border-subtle); padding: 1.75rem; border-radius: var(--radius-md);">
                            <div class="sol-calc-result__header" style="color: #ffffff; font-size: 1.15rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem; margin-bottom: 1rem;">
                                📊 Thông Số Đề Xuất Tối Ưu
                            </div>
                            
                            <div class="sol-calc-result__item">
                                <span>Công suất khuyến nghị:</span>
                                <strong id="resKwp" style="color: var(--accent-gold); font-size: 1.15rem;">6.4 kWp</strong>
                            </div>
                            <div class="sol-calc-result__item">
                                <span>Số lượng tấm pin (Mono 550W):</span>
                                <strong id="resPanels">12 Tấm</strong>
                            </div>
                            <div class="sol-calc-result__item">
                                <span>Diện tích mái yêu cầu:</span>
                                <strong id="resArea">~30 m²</strong>
                            </div>
                            <div class="sol-calc-result__item">
                                <span>Tiền điện tiết kiệm ước tính:</span>
                                <strong id="resSavings" style="color: #10b981; font-size: 1.05rem;">~2.7 Tr/tháng (32.4 Tr/năm)</strong>
                            </div>
                            <div class="sol-calc-result__item" style="border-top: 1px dashed var(--border-subtle); padding-top: 0.75rem; margin-top: 0.75rem;">
                                <span style="font-weight: 700; color: #ffffff;">Thời gian gỡ vốn dự kiến:</span>
                                <strong id="resPayback" style="color: #f59e0b; font-size: 1.15rem;">3 Năm 6 Tháng</strong>
                            </div>

                            <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.65rem;">
                                <a id="btnSimulate" href="index.php?page=calculator&source=plan" class="sol-btn sol-btn--solar" style="width: 100%; text-align: center; font-size: 0.95rem;">
                                    🚀 Đưa Vào Kế Hoạch & Demo Thiết Kế 3D →
                                </a>
                                <a href="index.php?page=cart" class="sol-btn sol-btn--outline" style="width: 100%; text-align: center; font-size: 0.85rem;">
                                    📋 Xem Kế Hoạch Dự Định Thiết Bị
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================================================================= -->
        <!-- 5. QUY TRÌNH 5 BƯỚC THI CÔNG & LẮP ĐẶT CHUẨN QUỐC TẾ (KẾ THỪA TV4) -->
        <!-- ================================================================= -->
        <section style="padding: 2.5rem 0 4.5rem; background: rgba(8, 12, 20, 0.45); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
            <div class="sol-container">
                <div class="sol-section-header" style="text-align: center; margin-bottom: 2rem;">
                    <span class="sol-badge sol-badge--emerald" style="margin-bottom: 0.5rem;">QUY TRÌNH QUẢN LÝ THI CÔNG CHUẨN MỰC</span>
                    <h2 class="sol-section-title" style="font-size: 2.25rem;">5 Bước Lắp Đặt & Bàn Giao Hệ Thống Chuẩn EVN</h2>
                    <p class="sol-section-subtitle" style="max-width: 650px; margin: 0 auto;">
                        Phân hệ Quản Lý Lắp Đặt (TV4) kết hợp giám sát chặt chẽ từ khảo sát, thi công cơ điện đến nghiệm thu đóng điện lưới.
                    </p>
                </div>

                <div class="sol-steps-grid">
                    <div class="sol-step-card">
                        <span class="sol-step-badge">BƯỚC 1</span>
                        <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">🛰️</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; font-family: var(--font-heading); margin: 0 0 0.4rem;">
                            Khảo Sát Drone 3D
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            Quét hiện trạng mái nhà bằng Drone, đo đạc kết cấu giàn mái, kiểm tra hướng nắng và đo góc đổ bóng bằng cảm biến quang học.
                        </p>
                    </div>

                    <div class="sol-step-card">
                        <span class="sol-step-badge">BƯỚC 2</span>
                        <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">📐</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; font-family: var(--font-heading); margin: 0 0 0.4rem;">
                            Mô Phỏng Demo 3D
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            Phân hệ Demo Thiết Kế (TV3) dựng sơ đồ mảng pin 2D/3D, tính toán chuỗi String điện áp Voc/Isc và xuất bảng vật tư kỹ thuật BOM.
                        </p>
                    </div>

                    <div class="sol-step-card">
                        <span class="sol-step-badge">BƯỚC 3</span>
                        <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">🏗️</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; font-family: var(--font-heading); margin: 0 0 0.4rem;">
                            Thi Công Khung Giàn
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            Lắp đặt hệ thống rail nhôm Anode chống ăn mòn muối biển, bulong Inox 304 và cố định tấm pin Tier-1 chịu bão cấp 12 an toàn tuyệt đối.
                        </p>
                    </div>

                    <div class="sol-step-card">
                        <span class="sol-step-badge">BƯỚC 4</span>
                        <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">⚡</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; font-family: var(--font-heading); margin: 0 0 0.4rem;">
                            Đấu Nối Inverter & ATS
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            Đấu nối tủ điện chống sét lan truyền Type 2, tiếp địa trở kháng &lt;4 Ohm, đấu nối Inverter hòa lưới và Pin Lithium ESS lưu trữ.
                        </p>
                    </div>

                    <div class="sol-step-card">
                        <span class="sol-step-badge">BƯỚC 5</span>
                        <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">🔌</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; font-family: var(--font-heading); margin: 0 0 0.4rem;">
                            Nghiệm Thu Đóng Điện
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                            Phối hợp điện lực EVN lắp công tơ 2 chiều, tải lên biên bản nghiệm thu PCCC và kích hoạt ứng dụng giám sát SCADA trên di động.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================================================================= -->
        <!-- 6. BẢNG SO SÁNH HIỆU QUẢ: TRƯỚC VS SAU KHI LẮP ĐẶT SOLARIS        -->
        <!-- ================================================================= -->
        <section style="padding: 3rem 0 4.5rem;">
            <div class="sol-container">
                <div class="sol-section-header" style="text-align: center; margin-bottom: 2.5rem;">
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.5rem;">HIỆU QUẢ ĐẦU TƯ RÕ RỆT</span>
                    <h2 class="sol-section-title" style="font-size: 2.25rem;">So Sánh Trực Quan Trước & Sau Khi Lắp Điện Mặt Trời</h2>
                </div>

                <div class="sol-glass-card" style="padding: 1.75rem; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; color: #ffffff;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                                <th style="padding: 1rem 1.25rem; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Tiêu Chí Đánh Giá</th>
                                <th style="padding: 1rem 1.25rem; color: #f43f5e; font-size: 0.95rem;">❌ Trước Khi Lắp Đặt</th>
                                <th style="padding: 1rem 1.25rem; color: #10b981; font-size: 0.95rem;">✅ Sau Khi Lắp Hệ Thống Solaris</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <td style="padding: 1rem 1.25rem; font-weight: 600;">Hóa đơn tiền điện hàng tháng</td>
                                <td style="padding: 1rem 1.25rem; color: #fca5a5;">Chi trả toàn bộ theo bậc thang giá cao (lên tới 3.300đ/kWh)</td>
                                <td style="padding: 1rem 1.25rem; color: #86efac; font-weight: 700;">Cắt giảm 75% - 90% chi phí điện lưới EVN mỗi tháng</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <td style="padding: 1rem 1.25rem; font-weight: 600;">Ứng phó sự cố mất điện đột ngột</td>
                                <td style="padding: 1rem 1.25rem; color: #fca5a5;">Ngừng toàn bộ sinh hoạt, thiết bị máy tính, tủ lạnh, camera mất nguồn</td>
                                <td style="padding: 1rem 1.25rem; color: #86efac; font-weight: 700;">Hệ thống Hybrid tự động cấp nguồn UPS &lt;10ms không gián đoạn</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                <td style="padding: 1rem 1.25rem; font-weight: 600;">Nhiệt độ phòng áp mái nhà</td>
                                <td style="padding: 1rem 1.25rem; color: #fca5a5;">Nắng nóng bức xạ trực tiếp xuống sàn mái bê tông / mái tôn</td>
                                <td style="padding: 1rem 1.25rem; color: #86efac; font-weight: 700;">Giảm nhiệt độ tầng áp mái từ 3°C - 5°C, giảm tải máy lạnh</td>
                            </tr>
                            <tr>
                                <td style="padding: 1rem 1.25rem; font-weight: 600;">Giá trị bất động sản & Môi trường</td>
                                <td style="padding: 1rem 1.25rem; color: #fca5a5;">Không có điểm nhấn công nghệ xanh, phát thải khí CO2 trung bình</td>
                                <td style="padding: 1rem 1.25rem; color: #86efac; font-weight: 700;">Tăng giá trị công trình, đạt chuẩn công trình xanh net-zero ESG</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================================================================= -->
        <!-- 7. SHOWCASE DỰ ÁN TIÊU BIỂU & ĐỐI TÁC CÔNG NGHỆ TIER-1 TOÀN CẦU   -->
        <!-- ================================================================= -->
        <section style="padding: 2.5rem 0 5rem;">
            <div class="sol-container">
                <div class="sol-section-header" style="text-align: center; margin-bottom: 2rem;">
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.5rem;">CÔNG TRÌNH THỰC TẾ</span>
                    <h2 class="sol-section-title" style="font-size: 2.25rem;">Dự Án Tiêu Biểu Solaris Đã Triển Khai</h2>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
                    <div class="sol-glass-card" style="padding: 1.5rem;">
                        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.5rem;">BIỆT THỰ THẢO ĐIỀN</span>
                        <h4 style="color: #ffffff; font-size: 1.2rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                            Biệt Thự Vườn Thảo Điền (TP. Thủ Đức)
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; margin-bottom: 1rem;">
                            Hệ thống Hybrid ESS 15kWp kết hợp 30 tấm pin Longi 550W và Pin Lithium Sungrow 15kWh.
                        </p>
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #10b981; font-weight: 700; border-top: 1px dashed var(--border-subtle); padding-top: 0.75rem;">
                            <span>Sản lượng: 1.950 kWh/tháng</span>
                            <span>Tiết kiệm: ~6.2 Tr/tháng</span>
                        </div>
                    </div>

                    <div class="sol-glass-card" style="padding: 1.5rem;">
                        <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.5rem;">NHÀ MÁY DỆT MAY VSIP</span>
                        <h4 style="color: #ffffff; font-size: 1.2rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                            Nhà Máy Dệt May VSIP Hải Phòng
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; margin-bottom: 1rem;">
                            Hệ thống C&I Rooftop 250kWp sử dụng Inverter chuỗi Huawei FusionSolar và tấm Canadian Solar.
                        </p>
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #10b981; font-weight: 700; border-top: 1px dashed var(--border-subtle); padding-top: 0.75rem;">
                            <span>Sản lượng: 31.000 kWh/tháng</span>
                            <span>Hoàn vốn: 3.4 Năm</span>
                        </div>
                    </div>

                    <div class="sol-glass-card" style="padding: 1.5rem;">
                        <span class="sol-badge sol-badge--emerald" style="margin-bottom: 0.5rem;">RESORT CAM RANH</span>
                        <h4 style="color: #ffffff; font-size: 1.2rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                            Resort Nghỉ Dưỡng Cam Ranh (Khánh Hòa)
                        </h4>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; margin-bottom: 1rem;">
                            Hệ thống Hybrid ESS 100kWp độc lập kết hợp Deye Inverter và khối pin LiFePO4 dung lượng lớn.
                        </p>
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #10b981; font-weight: 700; border-top: 1px dashed var(--border-subtle); padding-top: 0.75rem;">
                            <span>Sản lượng: 14.500 kWh/tháng</span>
                            <span>Độc lập: 95%</span>
                        </div>
                    </div>
                </div>

                <!-- Đối tác công nghệ Tier-1 thế giới -->
                <div style="text-align: center;">
                    <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; margin-bottom: 1.25rem;">
                        ĐỐI TÁC THIẾT BỊ QUANG ĐIỆN TIER-1 TOÀN CẦU ĐỒNG HÀNH CÙNG SOLARIS
                    </div>
                    <div style="display: flex; justify-content: center; align-items: center; gap: 2rem; flex-wrap: wrap; opacity: 0.85;">
                        <span style="font-size: 1.05rem; font-weight: 800; color: #ffffff; letter-spacing: 1px;">LONGI</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #38bdf8; letter-spacing: 1px;">CanadianSolar</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #f43f5e; letter-spacing: 1px;">HUAWEI</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #f59e0b; letter-spacing: 1px;">SUNGROW</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #10b981; letter-spacing: 1px;">Growatt</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #a855f7; letter-spacing: 1px;">Deye</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #ffffff; letter-spacing: 1px;">Schneider</span>
                        <span style="font-size: 1.05rem; font-weight: 800; color: #38bdf8; letter-spacing: 1px;">EVN</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="sol-footer">
        <div class="sol-container">
            <div class="sol-footer__grid">
                <div>
                    <a href="index.php?page=home" class="sol-brand" style="margin-bottom: 0.85rem;">
                        <div class="sol-brand__logo">
                            <img src="assets/solar-logo.svg" alt="Solaris Logo" style="width: 24px; height: 24px;">
                        </div>
                        <span class="sol-brand__name">SOLAR<span>IS</span></span>
                    </a>
                    <p class="sol-footer__desc">
                        Hệ thống Quản lý, Tư vấn, Demo Thiết kế & Lắp đặt Năng lượng Mặt trời. 
                        Giải pháp công nghệ xanh tiên tiến kết hợp nền tảng bài tập học thuật toàn diện.
                    </p>
                </div>

                <div>
                    <h4 class="sol-footer__col-title">Điều Hướng Nhanh</h4>
                    <ul class="sol-footer__links">
                        <li><a href="index.php?page=home">Trang Chủ (Home)</a></li>
                        <li><a href="index.php?page=products">Giải pháp & Sản phẩm</a></li>
                        <li><a href="index.php?page=cart">Kế hoạch dự định thiết bị</a></li>
                        <li><a href="index.php?page=calculator">Tư vấn & Demo Thiết kế</a></li>
                        <li><a href="index.php?page=installations">Quản lý Lắp đặt</a></li>
                        <li><a href="index.php?page=exercises">Portal 24 Bài Tập</a></li>
                        <li><a href="index.php?page=profile">Hồ Sơ 4 Thành Viên</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="sol-footer__col-title">Giải Pháp Nổi Bật</h4>
                    <ul class="sol-footer__links">
                        <li><a href="index.php?page=calculator&package=residential">Điện mặt trời hộ gia đình</a></li>
                        <li><a href="index.php?page=calculator&package=commercial">Điện mặt trời nhà xưởng</a></li>
                        <li><a href="index.php?page=calculator&package=hybrid">Hệ thống Hybrid lưu trữ</a></li>
                        <li><a href="#">Bảo trì & Vệ sinh pin</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="sol-footer__col-title">Đội Ngũ Kỹ Sư</h4>
                    <ul class="sol-footer__links">
                        <li><a href="index.php?page=profile">TV1: Team Lead & Auth Core</a></li>
                        <li><a href="index.php?page=profile">TV2: Frontend & Products</a></li>
                        <li><a href="index.php?page=profile">TV3: Solar Simulator & Data</a></li>
                        <li><a href="index.php?page=profile">TV4: Installation & QA</a></li>
                    </ul>
                </div>
            </div>

            <div class="sol-footer__bottom">
                <div>
                    © 2026 <strong>SOLARIS Energy System</strong>. Đồ án môn học Phát triển Phần mềm Mã Nguồn Mở.
                </div>
                <div style="display: flex; gap: 1.25rem;">
                    <span>PHP 8.x + XAMPP + Blade</span>
                    <span>Repo: NHDsun/Solaris</span>
                </div>
            </div>
        </div>
    </footer>

<?php else: ?>
    <!-- ================================================================= -->
    <!-- GIAO DIỆN CÁC TRANG KHÁC: TỰ ĐỘNG CHUYỂN MENU DỌC BÊN TRÁI (1S)   -->
    <!-- ================================================================= -->
    <div class="sol-app-layout">
        <!-- THANH MENU DỌC BÊN TRÁI (LEFT SIDEBAR MENU) VỚI ANIMATION 1S -->
        <aside class="sol-sidebar" id="solSidebar">
            <div class="sol-sidebar__header">
                <a href="index.php?page=home" class="sol-brand">
                    <div class="sol-brand__logo">
                        <img src="assets/solar-logo.svg" alt="Solaris Logo" style="width: 26px; height: 26px;">
                    </div>
                    <div class="sol-brand__text">
                        <span class="sol-brand__name">SOLAR<span>IS</span></span>
                        <span class="sol-brand__tagline">Năng Lượng Tương Lai</span>
                    </div>
                </a>
            </div>

            <div class="sol-sidebar__nav-wrapper">
                <div class="sol-sidebar__section-label">HỆ THỐNG ĐIỀU HƯỚNG</div>
                <nav class="sol-sidebar__nav">
                    <a href="index.php?page=home" class="sol-sidebar-item">
                        <span class="sol-sidebar-item__icon">🏠</span>
                        <span class="sol-sidebar-item__text">Trang Chủ (Home)</span>
                    </a>
                    <a href="index.php?page=products" class="sol-sidebar-item <?= $page === 'products' ? 'active' : '' ?>">
                        <span class="sol-sidebar-item__icon">☀️</span>
                        <span class="sol-sidebar-item__text">Giải Pháp & Sản Phẩm</span>
                    </a>
                    <a href="index.php?page=calculator" class="sol-sidebar-item <?= $page === 'calculator' ? 'active' : '' ?>">
                        <span class="sol-sidebar-item__icon">🧮</span>
                        <span class="sol-sidebar-item__text">Tư Vấn & Demo Thiết Kế</span>
                    </a>
                    <a href="index.php?page=installations" class="sol-sidebar-item <?= $page === 'installations' ? 'active' : '' ?>">
                        <span class="sol-sidebar-item__icon">📋</span>
                        <span class="sol-sidebar-item__text">Quản Lý Lắp Đặt</span>
                    </a>
                    <a href="index.php?page=exercises" class="sol-sidebar-item <?= $page === 'exercises' ? 'active' : '' ?>">
                        <span class="sol-sidebar-item__icon">📚</span>
                        <span class="sol-sidebar-item__text">Portal Bài Tập (24 Bài)</span>
                    </a>

                    <div class="sol-sidebar__section-label" style="margin-top: 1.25rem;">DỰ ĐỊNH & THIẾT BỊ</div>
                    <a href="index.php?page=cart" class="sol-sidebar-item <?= $page === 'cart' ? 'active' : '' ?>">
                        <span class="sol-sidebar-item__icon">📋</span>
                        <span class="sol-sidebar-item__text">Kế Hoạch Dự Định</span>
                        <span class="sol-sidebar-cart-badge"><?= class_exists(\App\Modules\Member2_Products\ProductController::class) ? \App\Modules\Member2_Products\ProductController::cartCount() : 0 ?></span>
                    </a>
                </nav>
            </div>

            <div class="sol-sidebar__footer">
                <a href="index.php?page=profile" class="sol-nav-profile-pill sol-nav-profile-pill--sidebar <?= $page === 'profile' ? 'active' : '' ?>" title="Profile">
                    <span class="sol-nav-profile-pill__icon">👤</span>
                    <span class="sol-nav-profile-pill__text">Profile</span>
                    <span class="sol-nav-profile-pill__badge"><?= htmlspecialchars(strtoupper($currentUser['username'] ?? 'ADMIN_PRO')) ?></span>
                </a>
                <div class="sol-sidebar__hotline">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#10b981;box-shadow:0 0 6px #10b981;"></span>
                    <span>Hotline: <strong>0909.888.SOLAR</strong></span>
                </div>
            </div>
        </aside>

        <!-- NỘI DUNG CHÍNH (MAIN APP WORKSPACE) -->
        <div class="sol-app-main">
            <header class="sol-app-topbar">
                <div class="sol-app-topbar__left">
                    <button type="button" class="sol-sidebar-toggle" id="sidebarToggle" aria-label="Toggle Menu">
                        <span>☰</span>
                    </button>
                    <div class="sol-app-breadcrumb">
                        <span style="color: var(--text-muted);">Solaris</span>
                        <span style="color: var(--text-muted);">/</span>
                        <span style="color: var(--accent-gold); font-weight: 600;"><?= htmlspecialchars($title) ?></span>
                    </div>
                </div>
                <div class="sol-app-topbar__right" style="display: flex; align-items: center; gap: 0.75rem;">
                    <a href="index.php?page=calculator" class="sol-btn sol-btn--solar sol-btn--sm">
                        ⚡ Khảo Sát Báo Giá
                    </a>
                </div>
            </header>

            <main class="sol-main-content sol-main-content--app">
                <?php if ($page === 'products'): ?>
                    <?php require __DIR__ . '/resources/views/pages/products.blade.php'; ?>

                <?php elseif ($page === 'cart'): ?>
                    <?php require __DIR__ . '/resources/views/pages/cart.blade.php'; ?>

                <?php elseif ($page === 'calculator'): ?>
                    <?php include __DIR__ . '/resources/views/pages/calculator.blade.php'; ?>

                <?php elseif ($page === 'installations'): ?>
                    <?php require __DIR__ . '/resources/views/pages/installations.blade.php'; ?>

                <?php elseif ($page === 'exercises'): ?>
                    <?php 
                    $ex = isset($_GET['exercise']) ? (int)$_GET['exercise'] : null;
                    if ($ex === 1):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member1/exercise1_hinh_chu_nhat.php';
                        echo '</div>';
                    elseif ($ex === 2):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member1/exercise2_hinh_tron.php';
                        echo '</div>';
                    elseif ($ex === 3):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member1/exercise3_tien_dien.php';
                        echo '</div>';
                    elseif ($ex === 4):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member1/exercise4_tam_giac_vuong.php';
                        echo '</div>';
                    elseif ($ex === 5):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member1/exercise5_karaoke.php';
                        echo '</div>';
                    elseif ($ex === 6):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member1/exercise6_phep_tinh.php';
                        echo '</div>';
                    elseif ($ex === 13):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member3/exercise13_matrix_replace.php';
                        echo '</div>';
                    elseif ($ex == 14):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member3/exercise14_sort_binary_search.php';
                        echo '</div>';
                    elseif ($ex == 15):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member3/exercise15_merge_normalize.php';
                        echo '</div>';
                    elseif ($ex == 16):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member3/exercise16_mysql_hang_sua.php';
                        echo '</div>';
                    elseif ($ex == 17):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member3/exercise17_mysql_khach_hang.php';
                        echo '</div>';
                    elseif ($ex == 18):
                        echo '<div class="sol-container" style="padding: 2.5rem 1.5rem 4rem;">';
                        include __DIR__ . '/resources/views/pages/exercises/member3/exercise18_mysql_custom_grid.php';
                        echo '</div>';
                    else:
                        require __DIR__ . '/resources/views/pages/exercises/portal.blade.php';
                    endif;
                    ?>

                <?php elseif ($page === 'profile'): ?>
                    <?php require __DIR__ . '/resources/views/pages/team-profile.blade.php'; ?>

                <?php endif; ?>
            </main>

            <footer class="sol-footer sol-footer--app">
                <div class="sol-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                    <div>© 2026 <strong>SOLARIS Energy System</strong>. Đồ án môn học Phát triển Phần mềm Mã Nguồn Mở.</div>
                    <div>PHP 8.x + XAMPP • Repo: NHDsun/Solaris</div>
                </div>
            </footer>
        </div>
    </div>
<?php endif; ?>

    <!-- SOCKET.IO CLIENT CDN & REAL-TIME EVENT ENGINE -->
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <script>
        // 1. Sidebar Toggle cho giao diện dạng App trên Mobile/Tablet
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('solSidebar')?.classList.toggle('is-open');
        });

        // 2. KẾT NỐI SOCKET.IO CLIENT THỜI GIAN THỰC & ĐỒNG BỘ MƯỢT MÀ
        let socket = null;
        try {
            if (typeof io !== 'undefined') {
                socket = io('http://localhost:3001', {
                    transports: ['websocket', 'polling'],
                    reconnectionAttempts: 5,
                    timeout: 3000
                });

                socket.on('connect', () => {
                    console.log('⚡ [Socket.io Connected] Client ID:', socket.id);
                    const rtBadge = document.getElementById('rtBadge');
                    const rtText = document.getElementById('rtStatusText');
                    if (rtText) rtText.innerText = 'Socket.io Online (3001)';
                    if (rtBadge) {
                        rtBadge.style.borderColor = 'rgba(16, 185, 129, 0.5)';
                        rtBadge.style.color = '#10b981';
                    }
                });

                socket.on('disconnect', () => {
                    const rtText = document.getElementById('rtStatusText');
                    if (rtText) rtText.innerText = 'Realtime Standby';
                });

                // Nhận thông điệp khi có hành động (chuyển slide, thêm giỏ hàng, cập nhật dữ liệu)
                socket.on('solar:action', (payload) => {
                    console.log('⚡ [Realtime Action Event Received]:', payload);
                    showSolarToast(payload.message || 'Hành động dữ liệu năng lượng đã được đồng bộ');
                });

                // Nhận đồng bộ vị trí slide từ các client khác
                socket.on('solar:slide_change', (data) => {
                    if (typeof data.slideIndex === 'number' && typeof goToSlideInternal === 'function') {
                        goToSlideInternal(data.slideIndex, false);
                    }
                });
            }
        } catch (e) {
            console.warn('[Socket.io Fallback Mode]:', e.message);
        }

        // Hàm phát hành động lên socket server
        window.triggerSolarAction = function(actionType, message) {
            if (socket && socket.connected) {
                socket.emit('solar:action', {
                    type: actionType,
                    message: message || 'Thao tác hoàn tất',
                    url: window.location.href
                });
            }
        };

        // Hàm hiển thị thông báo Toast siêu mượt
        function showSolarToast(msg) {
            let toast = document.getElementById('solRealtimeToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'solRealtimeToast';
                toast.style.position = 'fixed';
                toast.style.bottom = '24px';
                toast.style.right = '24px';
                toast.style.padding = '0.75rem 1.25rem';
                toast.style.background = 'rgba(14, 22, 38, 0.95)';
                toast.style.border = '1px solid #f59e0b';
                toast.style.borderRadius = '8px';
                toast.style.color = '#ffffff';
                toast.style.fontSize = '0.85rem';
                toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.6), 0 0 15px rgba(245, 158, 11, 0.3)';
                toast.style.zIndex = '999999';
                toast.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
                toast.style.display = 'flex';
                toast.style.alignItems = 'center';
                toast.style.gap = '0.65rem';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<span style="color:#f59e0b;font-size:1.1rem;">⚡</span><span>${msg}</span>`;
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
            setTimeout(() => {
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(15px)';
                }
            }, 3200);
        }

        // 3. HIỆU ỨNG GỢN SÓNG QUANG NĂNG (SOLAR SHOCKWAVE RIPPLE) KHI NHẤN VÀO TRANG CHỦ
        document.addEventListener('click', function(e) {
            // Tạo vòng sóng quang năng tỏa ra từ điểm click
            const ripple = document.createElement('span');
            ripple.className = 'solar-shockwave';
            const size = 50;
            ripple.style.width = size + 'px';
            ripple.style.height = size + 'px';
            ripple.style.left = e.pageX + 'px';
            ripple.style.top = e.pageY + 'px';
            document.body.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 900);
        });

        // 4. BỘ ĐIỀU KHIỂN SLIDER / CAROUSEL 4 GIẢI PHÁP SIÊU MƯỢT
        let currentSlide = 0;
        const totalSlides = 4;
        let autoSlideTimer = null;

        function getSlideStep() {
            if (window.innerWidth >= 1100) return 33.333333;
            if (window.innerWidth >= 768) return 50;
            return 100;
        }

        function getMaxSlideIndex() {
            if (window.innerWidth >= 1100) return 1; // 4 slides - 3 visible = 1 step
            if (window.innerWidth >= 768) return 2;  // 4 slides - 2 visible = 2 steps
            return 3;
        }

        function goToSlideInternal(index, emitSocket = true) {
            const track = document.getElementById('solutionsTrack');
            const dots = document.querySelectorAll('.sol-carousel-dot');
            const tabs = document.querySelectorAll('.sol-cat-tab');
            if (!track) return;

            const maxIndex = getMaxSlideIndex();
            currentSlide = Math.max(0, Math.min(index, maxIndex));

            const step = getSlideStep();
            const offset = currentSlide * step;
            track.style.transform = `translate3d(-${offset}%, 0, 0)`;

            // Cập nhật trạng thái Dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });

            // Cập nhật Tab buttons
            tabs.forEach((tab, i) => {
                tab.classList.toggle('active', i === currentSlide + 1);
            });

            if (emitSocket && socket && socket.connected) {
                socket.emit('solar:slide_change', { slideIndex: currentSlide });
            }
        }

        function goToSlide(index) {
            goToSlideInternal(index, true);
            resetAutoSlide();
        }

        function nextSlide() {
            const maxIndex = getMaxSlideIndex();
            const nextIdx = (currentSlide >= maxIndex) ? 0 : currentSlide + 1;
            goToSlide(nextIdx);
        }

        function prevSlide() {
            const maxIndex = getMaxSlideIndex();
            const prevIdx = (currentSlide <= 0) ? maxIndex : currentSlide - 1;
            goToSlide(prevIdx);
        }

        function startAutoSlide() {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
            autoSlideTimer = setInterval(() => {
                nextSlide();
            }, 6000);
        }

        function resetAutoSlide() {
            clearInterval(autoSlideTimer);
            startAutoSlide();
        }

        // Tự động khởi chạy auto-slide và dừng khi hover
        const carouselEl = document.querySelector('.sol-carousel-wrapper');
        if (carouselEl) {
            carouselEl.addEventListener('mouseenter', () => clearInterval(autoSlideTimer));
            carouselEl.addEventListener('mouseleave', () => startAutoSlide());
            startAutoSlide();
        }

        // 5. CÔNG CỤ TÍNH TOÁN CÔNG SUẤT & THỜI GIAN GỠ VỐN (HOME ROI CALCULATOR)
        let currentPeakHours = 5.2; // Mặc định Miền Nam (5.2h nắng đỉnh)

        function setRegion(hours, btn) {
            currentPeakHours = parseFloat(hours) || 5.2;
            document.querySelectorAll('.sol-region-btn').forEach(b => {
                b.classList.remove('sol-btn--solar', 'active');
                b.classList.add('sol-btn--secondary');
            });
            if (btn) {
                btn.classList.remove('sol-btn--secondary');
                btn.classList.add('sol-btn--solar', 'active');
            }
            const range = document.getElementById('billRange');
            if (range) calculateSolarHome(range.value);
        }

        function calculateSolarHome(val) {
            const bill = parseFloat(val) || 3000000;
            const billDisplay = document.getElementById('billDisplay');
            if (billDisplay) {
                billDisplay.innerText = new Intl.NumberFormat('vi-VN').format(bill) + ' VNĐ';
            }

            // Đơn giá điện trung bình bậc thang EVN: ~3.000 VNĐ/kWh
            const monthlyKwh = bill / 3000;
            // Công thức chuẩn: kWp = (kWh/tháng) / (số giờ nắng * 30 ngày * 0.85 hiệu suất chuỗi)
            const kwpRaw = monthlyKwh / (currentPeakHours * 30 * 0.85);
            const kwp = Math.max(1.5, parseFloat(kwpRaw.toFixed(1)));
            const panels = Math.ceil((kwp * 1000) / 550);
            const area = Math.round(panels * 2.5); // Mỗi tấm Mono 550W chiếm ~2.5 m2 mái
            const monthlySavings = bill * 0.90; // Tiết kiệm 90%
            const annualSavings = monthlySavings * 12;

            // Chi phí đầu tư ước tính: ~13.500.000 VNĐ / kWp
            const estimatedCost = kwp * 13500000;
            const paybackYearsRaw = estimatedCost / (annualSavings > 0 ? annualSavings : 1);
            const paybackYears = Math.floor(paybackYearsRaw);
            const paybackMonths = Math.max(1, Math.round((paybackYearsRaw - paybackYears) * 12));

            // Hiển thị lên giao diện
            const elKwp = document.getElementById('resKwp');
            const elPanels = document.getElementById('resPanels');
            const elArea = document.getElementById('resArea');
            const elSavings = document.getElementById('resSavings');
            const elPayback = document.getElementById('resPayback');
            const btnSim = document.getElementById('btnSimulate');

            if (elKwp) elKwp.innerText = kwp + ' kWp';
            if (elPanels) elPanels.innerText = panels + ' Tấm';
            if (elArea) elArea.innerText = '~' + area + ' m²';
            if (elSavings) {
                elSavings.innerText = `~${(monthlySavings / 1000000).toFixed(1)} Tr/tháng (${(annualSavings / 1000000).toFixed(1)} Tr/năm)`;
            }
            if (elPayback) {
                elPayback.innerText = `${paybackYears} Năm ${paybackMonths} Tháng`;
            }
            if (btnSim) {
                btnSim.href = `index.php?page=calculator&source=plan&bill=${bill}&kwp=${kwp}`;
            }
        }

        // 6. HIỆU ỨNG SỐ NHẢY MƯỢT MÀ KHI CUỘN VÀO TẦM MẮT (ANIMATED COUNTER)
        function animateCounter(el) {
            const target = parseFloat(el.getAttribute('data-target')) || 0;
            const suffix = el.getAttribute('data-suffix') || '';
            const isDecimal = el.getAttribute('data-decimal') === '1';
            const duration = 1600;
            const startTime = performance.now();

            function updateCount(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // Easing out cubic
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const currentVal = easeOut * target;

                if (isDecimal) {
                    el.innerText = currentVal.toFixed(1) + suffix;
                } else if (target >= 1000) {
                    el.innerText = Math.round(currentVal).toLocaleString('vi-VN') + suffix;
                } else {
                    el.innerText = Math.round(currentVal) + suffix;
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCount);
                }
            }
            requestAnimationFrame(updateCount);
        }

        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.sol-counter').forEach(el => {
            counterObserver.observe(el);
        });

        // Kích hoạt tính toán ban đầu nếu có widget
        if (document.getElementById('billRange')) {
            calculateSolarHome(document.getElementById('billRange').value);
        }
    </script>
</body>
</html>