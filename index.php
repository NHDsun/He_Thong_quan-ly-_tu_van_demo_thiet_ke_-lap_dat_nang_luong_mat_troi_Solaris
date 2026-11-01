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
    'profile'       => 'Thông Tin Cá Nhân 4 Thành Viên - SOLARIS'
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
// Điều phối cho phân hệ TV1 (Hồ sơ 4 Thành viên & Quản lý Profile Tài khoản)
// -------------------------------------------------------------------------
if ($page === 'profile') {
    $profileCtrl = new \App\Modules\Member1_Lead\ProfileController();
    $profileData = $profileCtrl->handleRequest();
    $currentUser = $profileData['current_user'];
    $roleStats = $profileData['role_stats'];
    $demoAccounts = $profileData['demo_accounts'];
    $teamMembers = $profileData['team_members'];
    $profileAlert = $profileData['alert'];
    $activeTab = $profileData['active_tab'];
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
<body class="solar-bg-grid">
    <div class="glow-ambient-1"></div>
    <div class="glow-ambient-2"></div>

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
                <a href="index.php?page=cart" class="sol-header-cart <?= $page === 'cart' ? 'active' : '' ?>" title="Xem kế hoạch dự định thiết bị" style="display: inline-flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.95rem; background: rgba(17, 24, 39, 0.9); border: 1px solid rgba(245, 158, 11, 0.45); border-radius: 6px; color: #ffffff !important; text-decoration: none !important; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.35);">
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
                    <a href="index.php?page=home" class="sol-tab-item <?= $page === 'home' ? 'active' : '' ?>" title="Trang chủ Solaris">
                        <span>🏠 Trang Chủ</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=products" class="sol-tab-item <?= $page === 'products' ? 'active' : '' ?>" title="Danh mục thiết bị quang điện">
                        <span>☀️ Sản Phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=calculator" class="sol-tab-item <?= $page === 'calculator' ? 'active' : '' ?>" title="Mô phỏng thiết kế & tính toán hoàn vốn">
                        <span>🧮 Demo Thiết Kế</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=installations" class="sol-tab-item <?= $page === 'installations' ? 'active' : '' ?>" title="Quản lý lắp đặt & thi công">
                        <span>📋 Lắp Đặt</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=exercises" class="sol-tab-item <?= $page === 'exercises' ? 'active' : '' ?>" title="Portal 24 bài tập thực hành">
                        <span>📚 Bài Tập (24 Bài)</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=profile" class="sol-tab-item <?= $page === 'profile' ? 'active' : '' ?>" title="Hồ sơ 4 thành viên & Quản trị tài khoản">
                        <span>👤 Hồ Sơ & Profile</span>
                    </a>
                </li>
            </ul>

            <div class="sol-nav-badge-wrap">
                <span class="sol-badge sol-badge--amber" style="font-size: 0.725rem; padding: 0.2rem 0.55rem; letter-spacing: 0.5px; white-space: nowrap;">SOLARIS V2.1</span>
            </div>
        </div>
    </nav>

    <main class="sol-main-content">
        <?php if ($page === 'home'): ?>
            <section class="sol-hero">
                <div class="sol-container">
                    <div class="sol-hero__badge">
                        <span class="sol-badge sol-badge--amber">
                            <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#f59e0b;box-shadow:0 0 6px #f59e0b;"></span>
                            Công Nghệ Năng Lượng Mặt Trời Thế Hệ Mới
                        </span>
                    </div>

                    <h1 class="sol-hero__title">
                        Khai Phá Nguồn Điện Mặt Trời <br>
                        <span class="highlight">Bền Vững & Tối Ưu Chi Phí</span>
                    </h1>

                    <p class="sol-hero__desc">
                        Nền tảng quản lý, mô phỏng cấu hình và giám sát lắp đặt điện mặt trời thông minh. 
                        Tối ưu hóa từng bức xạ quang năng thành giá trị kinh tế bền vững cho ngôi nhà và doanh nghiệp.
                    </p>

                    <div class="sol-hero__actions">
                        <a href="index.php?page=calculator" class="sol-btn sol-btn--solar sol-btn--lg">
                            ⚡ Demo Thiết Kế & Báo Giá
                        </a>
                        <a href="index.php?page=products" class="sol-btn sol-btn--outline sol-btn--lg">
                            Xem Danh Mục Thiết Bị
                        </a>
                    </div>
                </div>
            </section>

            <section class="sol-metrics">
                <div class="sol-container">
                    <div class="sol-metrics__grid">
                        <div class="sol-metric-card">
                            <span class="sol-metric-card__value">90%</span>
                            <span class="sol-metric-card__label">Tiết Kiệm Tiền Điện</span>
                        </div>
                        <div class="sol-metric-card">
                            <span class="sol-metric-card__value">25 Năm</span>
                            <span class="sol-metric-card__label">Bảo Hành Hiệu Suất</span>
                        </div>
                        <div class="sol-metric-card">
                            <span class="sol-metric-card__value">3 - 4 Năm</span>
                            <span class="sol-metric-card__label">Thời Gian Hoàn Vốn</span>
                        </div>
                        <div class="sol-metric-card">
                            <span class="sol-metric-card__value">100%</span>
                            <span class="sol-metric-card__label">Giám Sát Thông Minh</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="sol-solutions">
                <div class="sol-container">
                    <div class="sol-section-header">
                        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.75rem;">Mô Hình Hệ Thống</span>
                        <h2 class="sol-section-title">Giải Pháp Năng Lượng Tối Ưu</h2>
                        <p class="sol-section-subtitle">Được thiết kế linh hoạt theo nhu cầu sử dụng thực tế của hộ gia đình và doanh nghiệp</p>
                    </div>

                    <div class="sol-solutions__grid">
                        <div class="sol-solution-card">
                            <div class="sol-solution-card__badge">Phổ Biến Nhất</div>
                            <h3 class="sol-solution-card__title">Hệ Thống Hòa Lưới (On-Grid)</h3>
                            <p class="sol-solution-card__desc">Phù hợp hộ gia đình và doanh nghiệp sử dụng điện nhiều vào ban ngày. Chi phí đầu tư thấp nhất, hoàn vốn nhanh nhất.</p>
                            <ul class="sol-solution-card__features">
                                <li>Cắt giảm ngay bậc thang giá điện cao</li>
                                <li>Không tốn chi phí bảo dưỡng ắc quy</li>
                                <li>Tự động đồng bộ lưới điện EVN</li>
                            </ul>
                            <a href="index.php?page=calculator&package=residential" class="sol-btn sol-btn--secondary sol-btn--sm" style="width: 100%; text-align: center;">Khám Phá Gói Dân Dụng</a>
                        </div>

                        <div class="sol-solution-card">
                            <div class="sol-solution-card__badge" style="background: rgba(14, 165, 233, 0.2); color: #38bdf8;">Độ Tin Cậy Cao</div>
                            <h3 class="sol-solution-card__title">Hệ Thống Lưu Trữ (Hybrid ESS)</h3>
                            <p class="sol-solution-card__desc">Tích hợp pin lưu trữ Lithium LiFePO4 thế hệ mới. Đảm bảo nguồn điện liên tục 24/7 ngay cả khi lưới điện gặp sự cố.</p>
                            <ul class="sol-solution-card__features">
                                <li>Cung cấp điện dự phòng khi mất điện</li>
                                <li>Sử dụng điện mặt trời cả ban đêm</li>
                                <li>Chuyển mạch UPS không gián đoạn < 10ms</li>
                            </ul>
                            <a href="index.php?page=calculator&package=hybrid" class="sol-btn sol-btn--secondary sol-btn--sm" style="width: 100%; text-align: center;">Khám Phá Gói Hybrid</a>
                        </div>

                        <div class="sol-solution-card">
                            <div class="sol-solution-card__badge" style="background: rgba(168, 85, 247, 0.2); color: #c084fc;">Công Nghiệp</div>
                            <h3 class="sol-solution-card__title">Thương Mại & Công Nghiệp (C&I)</h3>
                            <p class="sol-solution-card__desc">Quy mô lớn từ 15kWp - 1MWp cho nhà xưởng, tòa nhà văn phòng, khu nghỉ dưỡng. Cắt giảm đỉnh phụ tải giờ cao điểm.</p>
                            <ul class="sol-solution-card__features">
                                <li>Giảm chi phí điện sản xuất giờ cao điểm</li>
                                <li>Đạt chứng chỉ xanh phát triển bền vững</li>
                                <li>Hệ số hoàn vốn hấp dẫn 3.2 - 3.8 năm</li>
                            </ul>
                            <a href="index.php?page=calculator&package=commercial" class="sol-btn sol-btn--secondary sol-btn--sm" style="width: 100%; text-align: center;">Khám Phá Gói Công Nghiệp</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="sol-calc-widget">
                <div class="sol-container">
                    <div class="sol-calc-widget__card">
                        <div class="sol-calc-widget__grid">
                            <div>
                                <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.75rem;">Công Cụ Trực Quan</span>
                                <h2 style="font-size: 1.85rem; color: #ffffff; margin-bottom: 0.75rem;">Ước Tính Tiềm Năng Năng Lượng</h2>
                                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.6;">
                                    Kéo chọn tiền điện trung bình hàng tháng của bạn để nhận ngay thông số hệ thống và mức tiết kiệm dự kiến tức thì.
                                </p>

                                <div class="sol-calc-form">
                                    <label class="sol-calc-label">
                                        <span>Tiền điện trung bình hàng tháng:</span>
                                        <strong id="billDisplay" style="color: var(--accent-gold); font-size: 1.15rem;">3.000.000 VNĐ</strong>
                                    </label>
                                    <input type="range" class="sol-range" id="billRange" min="500000" max="15000000" step="500000" value="3000000" oninput="calculateSolar(this.value)">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">
                                        <span>500K</span>
                                        <span>5 Tr</span>
                                        <span>10 Tr</span>
                                        <span>15 Tr+</span>
                                    </div>
                                </div>
                            </div>

                            <div class="sol-calc-result">
                                <div class="sol-calc-result__header">Thông Số Đề Xuất Nhanh</div>
                                <div class="sol-calc-result__item">
                                    <span>Công suất tối ưu:</span>
                                    <strong id="resKwp" style="color: var(--accent-gold);">7.4 kWp</strong>
                                </div>
                                <div class="sol-calc-result__item">
                                    <span>Số lượng tấm pin (550W):</span>
                                    <strong id="resPanels">14 Tấm</strong>
                                </div>
                                <div class="sol-calc-result__item">
                                    <span>Diện tích mái yêu cầu:</span>
                                    <strong id="resArea">~35 m²</strong>
                                </div>
                                <div class="sol-calc-result__item">
                                    <span>Tiền điện tiết kiệm ước tính:</span>
                                    <strong id="resSavings" style="color: #10b981;">~2.7 Tr/tháng</strong>
                                </div>
                                <div style="margin-top: 1.5rem;">
                                    <a href="index.php?page=calculator" class="sol-btn sol-btn--solar" style="width: 100%; text-align: center;">
                                        Xem Chi Tiết Cấu Hình & Báo Giá →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php elseif ($page === 'products'): ?>
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

    <script>
        function calculateSolar(val) {
            const bill = parseFloat(val);
            const billDisplay = document.getElementById('billDisplay');
            if (billDisplay) {
                billDisplay.innerText = new Intl.NumberFormat('vi-VN').format(bill) + ' VNĐ';
            }

            const monthlyKwh = bill / 3000;
            const kwp = (monthlyKwh / (4.5 * 30)).toFixed(1);
            const panels = Math.ceil((kwp * 1000) / 550);
            const area = Math.round(panels * 2.5);
            const savings = ((bill * 0.9) / 1000000).toFixed(1);

            const elKwp = document.getElementById('resKwp');
            const elPanels = document.getElementById('resPanels');
            const elArea = document.getElementById('resArea');
            const elSavings = document.getElementById('resSavings');

            if (elKwp) elKwp.innerText = kwp + ' kWp';
            if (elPanels) elPanels.innerText = panels + ' Tấm';
            if (elArea) elArea.innerText = area + ' m²';
            if (elSavings) elSavings.innerText = '~' + savings + ' Tr/tháng';
        }
    </script>
</body>
</html>