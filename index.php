<?php
$page = $_GET['page'] ?? 'home';
$validPages = ['home', 'products', 'calculator', 'installations', 'exercises', 'profile'];
if (!in_array($page, $validPages)) {
    $page = 'home';
}

$pageTitles = [
    'home' => 'SOLARIS - Năng Lượng Mặt Trời Thông Minh',
    'products' => 'Giải Pháp & Sản Phẩm - SOLARIS',
    'calculator' => 'Tư Vấn & Demo Thiết Kế - SOLARIS',
    'installations' => 'Quản Lý Lắp Đặt & Khảo Sát - SOLARIS',
    'exercises' => 'Portal 24 Bài Tập Thực Hành - SOLARIS',
    'profile' => 'Thông Tin Cá Nhân 4 Thành Viên - SOLARIS'
];
$title = $pageTitles[$page] ?? 'SOLARIS';
if ($page === 'installations') {
    require __DIR__ . '/app/Modules/Member4_Installation/bootstrap.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/solar-logo.svg">
    <link rel="stylesheet" href="resources/css/solaris-theme.css">
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

            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-size: 0.825rem;">
                    <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px #10b981;"></span>
                    <span>Hotline: <strong style="color: #ffffff;">0909.888.SOLAR</strong></span>
                </div>
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
                    <a href="index.php?page=home" class="sol-tab-item <?= $page === 'home' ? 'active' : '' ?>">
                        <span>🏠 Home (Trang Chủ)</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=products" class="sol-tab-item <?= $page === 'products' ? 'active' : '' ?>">
                        <span>☀️ Giải pháp & Sản phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=calculator" class="sol-tab-item <?= $page === 'calculator' ? 'active' : '' ?>">
                        <span>🧮 Tư vấn & Demo Thiết kế</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=installations" class="sol-tab-item <?= $page === 'installations' ? 'active' : '' ?>">
                        <span>📋 Quản lý Lắp đặt</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=exercises" class="sol-tab-item <?= $page === 'exercises' ? 'active' : '' ?>">
                        <span>📚 Bài Tập (24 Bài)</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=profile" class="sol-tab-item <?= $page === 'profile' ? 'active' : '' ?>">
                        <span>👤 Thông Tin Cá Nhân</span>
                    </a>
                </li>
            </ul>

            <div style="display: flex; align-items: center;">
                <span class="sol-badge sol-badge--amber">Solaris v2.0</span>
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
                        Nền tảng quản lý, mô phỏng cấu hình 3D và giám sát lắp đặt điện mặt trời thông minh. 
                        Tối ưu hóa từng bức xạ quang năng thành giá trị kinh tế bền vững cho ngôi nhà và doanh nghiệp.
                    </p>

                    <div class="sol-hero__actions">
                        <a href="index.php?page=calculator" class="sol-btn sol-btn--solar sol-btn--lg">
                            ⚡ Demo Thiết Kế & Báo Giá
                        </a>
                        <a href="index.php?page=products" class="sol-btn sol-btn--secondary sol-btn--lg">
                            🔍 Catalog Thiết Bị Pin
                        </a>
                        <a href="index.php?page=exercises" class="sol-btn sol-btn--outline sol-btn--lg">
                            📚 Portal 24 Bài Tập
                        </a>
                    </div>
                </div>
            </section>

            <div class="sol-container">
                <div class="sol-metrics-strip">
                    <div class="sol-glass-card sol-metric-card">
                        <div class="sol-metric-card__value">90<span class="unit">%</span></div>
                        <div class="sol-metric-card__label">Tiết kiệm hóa đơn điện hàng tháng</div>
                    </div>
                    <div class="sol-glass-card sol-metric-card">
                        <div class="sol-metric-card__value">25<span class="unit">Năm</span></div>
                        <div class="sol-metric-card__label">Bảo hành hiệu suất tấm pin > 80%</div>
                    </div>
                    <div class="sol-glass-card sol-metric-card">
                        <div class="sol-metric-card__value">3.5 - 4<span class="unit">Năm</span></div>
                        <div class="sol-metric-card__label">Thời gian thu hồi vốn đầu tư</div>
                    </div>
                    <div class="sol-glass-card sol-metric-card">
                        <div class="sol-metric-card__value">100<span class="unit">%</span></div>
                        <div class="sol-metric-card__label">Năng lượng sạch, giảm phát thải CO2</div>
                    </div>
                </div>
            </div>

            <section class="sol-section">
                <div class="sol-container">
                    <div class="sol-section-header">
                        <span class="sol-section-header__tag">Giải Pháp Toàn Diện</span>
                        <h2 class="sol-section-header__title">Cấu Hình Phù Hợp Mọi Quy Mô</h2>
                        <p class="sol-section-header__desc">
                            Tối ưu hóa công suất theo diện tích mái và mức tiêu thụ điện thực tế, đảm bảo sản lượng điện tối đa quanh năm.
                        </p>
                    </div>

                    <div class="sol-solutions-grid">
                        <div class="sol-glass-card sol-solution-card">
                            <div>
                                <span class="sol-badge sol-badge--amber">Hộ Gia Đình</span>
                                <h3 class="sol-solution-card__title">Điện Mặt Trời Áp Mái Dân Dụng</h3>
                                <p class="sol-solution-card__desc">
                                    Công suất từ 3 kWp đến 10 kWp. Cắt giảm giá điện bậc cao, giữ nhiệt độ mái nhà mát mẻ hơn 3-5°C.
                                </p>
                                <ul class="sol-solution-card__features">
                                    <li>Tấm pin Mono Half-cell 550W Tier-1</li>
                                    <li>Biến tần Inverter hòa lưới 1 pha / 3 pha IP65</li>
                                    <li>Giám sát sản lượng qua ứng dụng di động 24/7</li>
                                    <li>Hoàn vốn nhanh trong 3.5 - 4 năm</li>
                                </ul>
                            </div>
                            <a href="index.php?page=calculator&package=residential" class="sol-btn sol-btn--outline" style="width:100%;">
                                Dự Toán Gói Gia Đình
                            </a>
                        </div>

                        <div class="sol-glass-card sol-solution-card" style="border-color: rgba(245, 158, 11, 0.35);">
                            <div>
                                <span class="sol-badge sol-badge--emerald">Doanh Nghiệp</span>
                                <h3 class="sol-solution-card__title">Nhà Xưởng & Thương Mại C&I</h3>
                                <p class="sol-solution-card__desc">
                                    Quy mô từ 30 kWp đến 500 kWp+. Tiết kiệm hàng trăm triệu đồng chi phí điện sản xuất, đạt chứng chỉ xanh ESG.
                                </p>
                                <ul class="sol-solution-card__features">
                                    <li>Tối ưu cho biểu giá điện giờ cao điểm công nghiệp</li>
                                    <li>Hệ khung nhôm Anode chuyên dụng chống ăn mòn</li>
                                    <li>Tự động ngắt khi sự cố, đạt tiêu chuẩn PCCC</li>
                                    <li>Hỗ trợ hồ sơ đấu nối điện lưới quốc gia</li>
                                </ul>
                            </div>
                            <a href="index.php?page=calculator&package=commercial" class="sol-btn sol-btn--solar" style="width:100%;">
                                Dự Toán Gói Doanh Nghiệp
                            </a>
                        </div>

                        <div class="sol-glass-card sol-solution-card">
                            <div>
                                <span class="sol-badge sol-badge--cyan">Lưu Trữ Hybrid</span>
                                <h3 class="sol-solution-card__title">Hệ Thống Lưu Trữ ESS Hybrid</h3>
                                <p class="sol-solution-card__desc">
                                    Tích hợp pin Lithium LiFePO4 lưu trữ điện. Duy trì điện liên tục 24/7 kể cả khi lưới điện gặp sự cố mất điện.
                                </p>
                                <ul class="sol-solution-card__features">
                                    <li>Pin lưu trữ Lithium tuổi thọ trên 6.000 chu kỳ sạc</li>
                                    <li>Chuyển mạch tự động UPS dưới 10 mili-giây</li>
                                    <li>Tích trữ điện ban ngày, cấp điện sử dụng ban đêm</li>
                                    <li>An toàn tuyệt đối cho thiết bị điện tử quan trọng</li>
                                </ul>
                            </div>
                            <a href="index.php?page=calculator&package=hybrid" class="sol-btn sol-btn--outline" style="width:100%;">
                                Dự Toán Gói Lưu Trữ
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="sol-section">
                <div class="sol-container">
                    <div class="sol-calculator-widget">
                        <div style="text-align: center; max-width: 600px; margin: 0 auto;">
                            <span class="sol-badge sol-badge--amber">Mô Phỏng Nhanh</span>
                            <h2 style="font-family: var(--font-heading); font-size: 1.85rem; color: #ffffff; margin: 0.65rem 0 0.4rem;">
                                Ước Tính Cấu Hình Theo Hóa Đơn Điện
                            </h2>
                            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                                Kéo thanh trượt để xem công suất kWp, số tấm pin và diện tích mái cần thiết:
                            </p>
                        </div>

                        <div style="max-width: 680px; margin: 2rem auto 1.25rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                                <span style="font-size: 0.9rem; color: #cbd5e1; font-weight: 500;">Tiền điện trung bình / tháng:</span>
                                <span id="billDisplay" style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; color: var(--accent-gold);">
                                    3.000.000 VNĐ
                                </span>
                            </div>

                            <input 
                                type="range" 
                                id="billSlider" 
                                class="sol-slider-track" 
                                min="1000000" 
                                max="20000000" 
                                step="500000" 
                                value="3000000" 
                                oninput="calculateSolar(this.value)"
                            >

                            <div style="display: flex; justify-content: space-between; font-size: 0.725rem; color: var(--text-muted); margin-top: 0.4rem;">
                                <span>1 Triệu</span>
                                <span>5 Triệu</span>
                                <span>10 Triệu</span>
                                <span>15 Triệu</span>
                                <span>20 Triệu VNĐ</span>
                            </div>
                        </div>

                        <div class="sol-calc-stats" style="max-width: 820px; margin: 1.75rem auto 0;">
                            <div style="text-align: center;">
                                <div class="sol-calc-stat-val" id="resKwp">7.4 kWp</div>
                                <div class="sol-calc-stat-lbl">Công Suất Khuyến Nghị</div>
                            </div>
                            <div style="text-align: center;">
                                <div class="sol-calc-stat-val" id="resPanels">14 Tấm</div>
                                <div class="sol-calc-stat-lbl">Số Tấm Pin 550W</div>
                            </div>
                            <div style="text-align: center;">
                                <div class="sol-calc-stat-val" id="resArea">35 m²</div>
                                <div class="sol-calc-stat-lbl">Diện Tích Mái Khả Dụng</div>
                            </div>
                            <div style="text-align: center;">
                                <div class="sol-calc-stat-val" id="resSavings">~2.7 Tr/tháng</div>
                                <div class="sol-calc-stat-lbl">Tiết Kiệm Dự Kiến</div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 1.75rem;">
                            <a href="index.php?page=calculator" class="sol-btn sol-btn--solar">
                                Xem Chi Tiết Cấu Hình & Báo Giá Kỹ Thuật ➔
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="sol-section">
                <div class="sol-container">
                    <div class="sol-section-header">
                        <span class="sol-section-header__tag">Tiêu Chuẩn Thi Công</span>
                        <h2 class="sol-section-header__title">Quy Trình Triển Khai 5 Bước</h2>
                        <p class="sol-section-header__desc">
                            Được chuẩn hóa từ khảo sát hiện trường đến nghiệm thu hòa lưới và bảo trì dài hạn.
                        </p>
                    </div>

                    <div class="sol-workflow-strip">
                        <div class="sol-glass-card sol-workflow-step">
                            <div class="sol-workflow-step__num">01</div>
                            <h4 class="sol-workflow-step__title">Tiếp Nhận</h4>
                            <p class="sol-workflow-step__desc">Phân tích mức tiêu thụ và đặc thù phụ tải điện của công trình.</p>
                        </div>
                        <div class="sol-glass-card sol-workflow-step">
                            <div class="sol-workflow-step__num">02</div>
                            <h4 class="sol-workflow-step__title">Khảo Sát 3D</h4>
                            <p class="sol-workflow-step__desc">Đo đạc độ nghiêng, góc đón nắng và kiểm tra tải trọng kết cấu mái.</p>
                        </div>
                        <div class="sol-glass-card sol-workflow-step">
                            <div class="sol-workflow-step__num">03</div>
                            <h4 class="sol-workflow-step__title">Mô Phỏng</h4>
                            <p class="sol-workflow-step__desc">Lập bảng dự toán kỹ thuật và mô phỏng sản lượng điện chi tiết.</p>
                        </div>
                        <div class="sol-glass-card sol-workflow-step">
                            <div class="sol-workflow-step__num">04</div>
                            <h4 class="sol-workflow-step__title">Lắp Đặt</h4>
                            <p class="sol-workflow-step__desc">Thi công an toàn chuẩn PCCC, hoàn thiện đấu nối trong 1-3 ngày.</p>
                        </div>
                        <div class="sol-glass-card sol-workflow-step">
                            <div class="sol-workflow-step__num">05</div>
                            <h4 class="sol-workflow-step__title">Nghiệm Thu</h4>
                            <p class="sol-workflow-step__desc">Bàn giao tài khoản giám sát IoT và kích hoạt bảo hành 25 năm.</p>
                        </div>
                    </div>
                </div>
            </section>

        <?php elseif ($page === 'products'): ?>
            <div class="sol-container" style="padding: 4.5rem 1.5rem; text-align: center;">
                <div class="sol-glass-card" style="max-width: 720px; margin: 0 auto; padding: 3rem 2rem;">
                    <span class="sol-badge sol-badge--emerald" style="margin-bottom: 1rem;">Khu Vực 2 • Thành Viên 2</span>
                    <h2 style="color: #ffffff; font-size: 2rem; margin-bottom: 0.75rem;">Giải Pháp & Sản Phẩm Thiết Bị Pin</h2>
                    <p style="color: var(--text-secondary); margin-bottom: 1.75rem; line-height: 1.6; font-size: 0.95rem;">
                        Phân vùng dành cho Thành viên 2 phát triển Catalog Tấm pin 550W, Biến tần Inverter hòa lưới/hybrid, và bộ lọc sản phẩm có phân trang (>20 dòng).
                    </p>
                    <div style="background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem; text-align: left; margin-bottom: 1.75rem;">
                        <div style="color: var(--accent-amber); font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem;">File phụ trách của Thành viên 2:</div>
                        <code style="color: #38bdf8; font-family: var(--font-mono); font-size: 0.85rem;">
                            • app/Modules/Member2_Products/ProductController.php<br>
                            • resources/views/pages/products.blade.php
                        </code>
                    </div>
                    <a href="index.php?page=home" class="sol-btn sol-btn--solar">⬅ Quay Về Trang Chủ</a>
                </div>
            </div>

        <?php elseif ($page === 'calculator'): ?>
            <div class="sol-container" style="padding: 4.5rem 1.5rem; text-align: center;">
                <div class="sol-glass-card" style="max-width: 720px; margin: 0 auto; padding: 3rem 2rem;">
                    <span class="sol-badge sol-badge--cyan" style="margin-bottom: 1rem;">Khu Vực 3 • Thành Viên 3</span>
                    <h2 style="color: #ffffff; font-size: 2rem; margin-bottom: 0.75rem;">Tư Vấn & Demo Thiết Kế Solar</h2>
                    <p style="color: var(--text-secondary); margin-bottom: 1.75rem; line-height: 1.6; font-size: 0.95rem;">
                        Phân vùng dành cho Thành viên 3 phát triển Công cụ tính toán điện mặt trời (kWp, số pin 550W, chi phí, hoàn vốn) kết nối Hook <code>UseSolarCalculator.php</code>.
                    </p>
                    <div style="background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 1.25rem; text-align: left; margin-bottom: 1.75rem;">
                        <div style="color: var(--accent-amber); font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem;">File phụ trách của Thành viên 3:</div>
                        <code style="color: #38bdf8; font-family: var(--font-mono); font-size: 0.85rem;">
                            • app/Hooks/UseSolarCalculator.php<br>
                            • app/Modules/Member3_Simulator/SolarDemoController.php<br>
                            • resources/views/pages/demo-design.blade.php
                        </code>
                    </div>
                    <a href="index.php?page=home" class="sol-btn sol-btn--solar">⬅ Quay Về Trang Chủ</a>
                </div>
            </div>

        <?php elseif ($page === 'installations'): ?>
            <?php require __DIR__ . '/resources/views/pages/installations.blade.php'; ?>
        <?php elseif ($page === 'exercises'): ?>
            <div class="sol-container" style="padding: 3rem 0 4rem;">
                <div class="sol-section-header">
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.6rem;">Khu Vực 5 • Dùng Chung</span>
                    <h2 class="sol-section-header__title">Cổng Tích Hợp 24 Bài Tập Thực Hành</h2>
                    <p class="sol-section-header__desc">
                        Hệ thống bài tập chia đều cho 4 thành viên (mỗi người 6 bài), tuân thủ kiến trúc phân tầng Atomic và chuẩn giao diện Solaris.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-top: 1.5rem;">
                    <div class="sol-glass-card" style="padding: 1.65rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                            <span class="sol-badge sol-badge--amber">Thành Viên 1 (Lead)</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">6 Bài Form</span>
                        </div>
                        <h3 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.85rem;">Phần PHP & FORM</h3>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.825rem;">
                            <li><span style="color: var(--accent-amber);">1.</span> Diện tích hình chữ nhật</li>
                            <li><span style="color: var(--accent-amber);">2.</span> Diện tích & chu vi hình tròn</li>
                            <li><span style="color: var(--accent-amber);">3.</span> Thanh toán tiền điện</li>
                            <li><span style="color: var(--accent-amber);">4.</span> Cạnh huyền tam giác vuông</li>
                            <li><span style="color: var(--accent-amber);">5.</span> Tính tiền Karaoke</li>
                            <li><span style="color: var(--accent-amber);">6.</span> Phép tính trên 2 số</li>
                        </ul>
                    </div>

                    <div class="sol-glass-card" style="padding: 1.65rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                            <span class="sol-badge sol-badge--emerald">Thành Viên 2</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">2 Form + 4 Mảng</span>
                        </div>
                        <h3 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.85rem;">Mảng & Chuỗi (P1)</h3>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.825rem;">
                            <li><span style="color: var(--accent-emerald);">7.</span> Xử lý Form tính toán</li>
                            <li><span style="color: var(--accent-emerald);">8.</span> Form tổng hợp nâng cao</li>
                            <li><span style="color: var(--accent-emerald);">1.</span> Mảng: Thao tác số nguyên</li>
                            <li><span style="color: var(--accent-emerald);">2.</span> Mảng: Tính năm âm lịch</li>
                            <li><span style="color: var(--accent-emerald);">3.</span> Mảng: Đếm & Thay thế</li>
                            <li><span style="color: var(--accent-emerald);">4.</span> Mảng: Sắp xếp tăng/giảm</li>
                        </ul>
                    </div>

                    <div class="sol-glass-card" style="padding: 1.65rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                            <span class="sol-badge sol-badge--cyan">Thành Viên 3</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">3 Mảng + 3 MySQL</span>
                        </div>
                        <h3 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.85rem;">Mảng (P2) & MySQL</h3>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.825rem;">
                            <li><span style="color: var(--accent-cyan);">5.</span> Chuỗi: Ma trận số 2 chiều</li>
                            <li><span style="color: var(--accent-cyan);">6.</span> Chuỗi: Tìm kiếm nhị phân</li>
                            <li><span style="color: var(--accent-cyan);">7.</span> Chuỗi: Ghép và chuẩn hóa</li>
                            <li><span style="color: var(--accent-cyan);">1.</span> MySQL: Hiển thị lưới hãng sữa</li>
                            <li><span style="color: var(--accent-cyan);">2.</span> MySQL: Lưới khách hàng</li>
                            <li><span style="color: var(--accent-cyan);">3.</span> MySQL: Lưới sữa kẻ sọc</li>
                        </ul>
                    </div>

                    <div class="sol-glass-card" style="padding: 1.65rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
                            <span class="sol-badge sol-badge--amber">Thành Viên 4</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">6 Bài MySQL</span>
                        </div>
                        <h3 style="font-size: 1.1rem; color: #ffffff; margin-bottom: 0.85rem;">PHP & MySQL (P2)</h3>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.825rem;">
                            <li><span style="color: var(--accent-amber);">4.</span> MySQL: Lưới sữa phân trang (>20 dòng)</li>
                            <li><span style="color: var(--accent-amber);">5.</span> MySQL: Lưới sản phẩm kèm ảnh</li>
                            <li><span style="color: var(--accent-amber);">6.</span> MySQL: Chi tiết sản phẩm sữa</li>
                            <li><span style="color: var(--accent-amber);">7.</span> MySQL: Thêm mới sản phẩm sữa</li>
                            <li><span style="color: var(--accent-amber);">8.</span> MySQL: Tìm kiếm sản phẩm</li>
                            <li><span style="color: var(--accent-amber);">9.</span> MySQL: Tìm kiếm nâng cao</li>
                        </ul>
                    </div>
                </div>
            </div>

        <?php elseif ($page === 'profile'): ?>
            <div class="sol-container" style="padding: 3rem 0 5rem;">
                <div class="sol-section-header">
                    <span class="sol-badge sol-badge--amber">Nhóm Phát Triển</span>
                    <h2 class="sol-section-header__title">Hồ Sơ Năng Lực 4 Thành Viên</h2>
                    <p class="sol-section-header__desc">
                        Đội ngũ kỹ sư phát triển Hệ thống Năng lượng Mặt trời Solaris. Phân chia vai trò rõ ràng và phối hợp đồng bộ.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.25rem; margin-top: 2rem;">
                    <div class="sol-glass-card" style="padding: 2rem 1.5rem; text-align: center;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #004e89); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; border: 2px solid var(--accent-amber);">
                            👤
                        </div>
                        <h3 style="color: #ffffff; font-size: 1.2rem; margin-bottom: 0.25rem;">Thành Viên 1 (Bạn)</h3>
                        <div style="color: var(--accent-amber); font-size: 0.8rem; font-weight: 700; margin-bottom: 0.85rem;">TEAM LEAD & AUTH CORE</div>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; line-height: 1.55; margin-bottom: 1rem;">
                            Quản trị kiến trúc hệ thống, phát triển trang Home, Profile, Module Auth (Login/Session/Cookies) và 6 bài tập Form.
                        </p>
                        <span class="sol-badge sol-badge--amber">Phụ trách: Home & Profile</span>
                    </div>

                    <div class="sol-glass-card" style="padding: 2rem 1.5rem; text-align: center;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #0e1626); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; border: 2px solid var(--accent-emerald);">
                            ☀️
                        </div>
                        <h3 style="color: #ffffff; font-size: 1.2rem; margin-bottom: 0.25rem;">Thành Viên 2</h3>
                        <div style="color: var(--accent-emerald); font-size: 0.8rem; font-weight: 700; margin-bottom: 0.85rem;">FRONTEND & PRODUCTS</div>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; line-height: 1.55; margin-bottom: 1rem;">
                            Phát triển trang Giải pháp & Sản phẩm, Catalog tấm pin, Inverter, bộ lọc tìm kiếm và phụ trách 6 bài tập (Form + Mảng/Chuỗi).
                        </p>
                        <span class="sol-badge sol-badge--emerald">Phụ trách: Giải pháp & SP</span>
                    </div>

                    <div class="sol-glass-card" style="padding: 2rem 1.5rem; text-align: center;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #38bdf8, #0e1626); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; border: 2px solid var(--accent-cyan);">
                            🧮
                        </div>
                        <h3 style="color: #ffffff; font-size: 1.2rem; margin-bottom: 0.25rem;">Thành Viên 3</h3>
                        <div style="color: var(--accent-cyan); font-size: 0.8rem; font-weight: 700; margin-bottom: 0.85rem;">SIMULATOR & DATA HOOKS</div>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; line-height: 1.55; margin-bottom: 1rem;">
                            Phát triển công cụ Solar Calculator & Simulator tương tác realtime, Hook phân trang > 20 dòng và phụ trách 6 bài tập (Mảng + MySQL).
                        </p>
                        <span class="sol-badge sol-badge--cyan">Phụ trách: Demo Thiết Kế</span>
                    </div>

                    <div class="sol-glass-card" style="padding: 2rem 1.5rem; text-align: center;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #f43f5e); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; border: 2px solid var(--accent-amber);">
                            📋
                        </div>
                        <h3 style="color: #ffffff; font-size: 1.2rem; margin-bottom: 0.25rem;">Thành Viên 4</h3>
                        <div style="color: var(--accent-gold); font-size: 0.8rem; font-weight: 700; margin-bottom: 0.85rem;">INSTALLATION & QA/QC</div>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; line-height: 1.55; margin-bottom: 1rem;">
                            Phát triển trang Quản lý Lắp đặt, Module Upload tài liệu/ảnh khảo sát mặt bằng mái, kiểm thử chất lượng và phụ trách 6 bài tập MySQL.
                        </p>
                        <span class="sol-badge sol-badge--amber">Phụ trách: Quản Lý Lắp Đặt</span>
                    </div>
                </div>
            </div>
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
