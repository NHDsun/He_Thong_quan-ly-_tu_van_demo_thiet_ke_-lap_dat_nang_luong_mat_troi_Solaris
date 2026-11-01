<?php
/**
 * View: resources/views/pages/team-profile.blade.php
 * Phân hệ 1: Quản Lý Hồ Sơ Tài Khoản Cá Nhân (Profile Overview) & Đội Ngũ Dự Án 4 Thành Viên
 * Phụ trách: Thành viên 1 - Nguyễn Huy Đạt (MSSV: 65130410 - Trưởng nhóm kiêm PM)
 * 
 * ĐẶC TẢ GIAO DIỆN:
 * 1. Không phải form cài đặt chỉnh sửa cấu hình phức tạp.
 * 2. Trang Profile chuẩn hiển thị thông tin chung của người dùng (Khách hàng / Kỹ thuật viên / Quản trị viên).
 * 3. Hỗ trợ bộ chuyển đổi xem nhanh giữa 3 mức tài khoản (Admin, Technician, Customer).
 * 4. Hiển thị thông tin chung đặc thù:
 *    - Khách hàng: Kế hoạch thiết bị dự định, KW điện, tiền tạo ra/tháng, thời gian gỡ vốn, lịch sử báo giá.
 *    - Kỹ thuật viên: Thông tin chuyên môn, danh sách công trình phụ trách, hồ sơ khảo sát.
 *    - Quản trị viên: Bảng tóm tắt hoạt động toàn hệ thống, danh sách báo giá cần duyệt, quản trị công trình.
 * 5. Hồ sơ năng lực 4 thành viên (Nguyễn Huy Đạt 65130410 PM, Lê Chí Hải, Mai Đức Toàn - Báo Cáo, Phan Trần Thịnh) & Biểu đồ WBS.
 */

$tab = $_GET['tab'] ?? ($activeTab ?? 'account');
if (!in_array($tab, ['account', 'team'], true)) {
    $tab = 'account';
}

$userRole = $currentUser['role'] ?? 'admin';
$roleName = match ($userRole) {
    'admin' => 'Quản Trị Viên Cấp Cao (Admin)',
    'technician' => 'Kỹ Thuật Viên Trưởng (Technician)',
    default => 'Khách Hàng Thành Viên (Customer)'
};
?>

<div class="sol-container" style="padding-top: 2rem; padding-bottom: 5rem;">
    <!-- Tiêu đề trang -->
    <div class="sol-section-header" style="margin-bottom: 1.75rem;">
        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.5rem;">
            👤 Phân Hệ 1 • Quản Lý Hồ Sơ Tài Khoản & Đội Ngũ Dự Án
        </span>
        <h1 class="sol-section-header__title" style="font-size: 2.2rem;">
            <?= $tab === 'account' ? 'Profile Tài Khoản' : 'Hồ Sơ Đội Ngũ 4 Thành Viên & Biểu Đồ WBS' ?>
        </h1>
        <p class="sol-section-header__desc">
            <?= $tab === 'account' 
                ? 'Xem thông tin định danh, quyền hạn hệ thống, thiết bị trong kế hoạch dự định và lịch sử hoạt động của ' . htmlspecialchars($roleName) . '.'
                : 'Thông tin 4 thành viên phát triển dự án Solaris, bảng phân công nhiệm vụ và biểu đồ cơ cấu đóng góp dự án.' ?>
        </p>
    </div>

    <!-- Thông báo Alert nếu có -->
    <?php if (!empty($profileAlert)): ?>
        <div style="margin-bottom: 1.75rem; padding: 1rem 1.25rem; border-radius: var(--radius-md); background: <?= $profileAlert['type'] === 'success' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)' ?>; border: 1px solid <?= $profileAlert['type'] === 'success' ? '#10b981' : '#f43f5e' ?>; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #ffffff; font-size: 0.925rem;">
                <span style="font-size: 1.25rem;"><?= $profileAlert['type'] === 'success' ? '✅' : '❌' ?></span>
                <span><?= htmlspecialchars($profileAlert['message']) ?></span>
            </div>
            <a href="index.php?page=profile&tab=<?= htmlspecialchars($tab) ?>" style="color: var(--text-muted); text-decoration: none; font-size: 1.25rem;">&times;</a>
        </div>
    <?php endif; ?>

    <!-- THANH ĐIỀU HƯỚNG TAB CHÍNH -->
    <div style="display: flex; gap: 0.75rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem; flex-wrap: wrap;">
        <a href="index.php?page=profile&tab=account" class="sol-btn <?= $tab === 'account' ? 'sol-btn--solar' : 'sol-btn--outline' ?>" style="font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem;">
            <span>👤</span> Profile
            <span class="sol-badge <?= $userRole === 'admin' ? 'sol-badge--amber' : ($userRole === 'technician' ? 'sol-badge--cyan' : 'sol-badge--emerald') ?>" style="font-size: 0.7rem; padding: 0.1rem 0.45rem;">
                <?= htmlspecialchars(strtoupper($currentUser['username'] ?? 'ADMIN_PRO')) ?>
            </span>
        </a>
        <a href="index.php?page=profile&tab=team" class="sol-btn <?= $tab === 'team' ? 'sol-btn--solar' : 'sol-btn--outline' ?>" style="font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.2rem;">
            <span>👥</span> Đội Ngũ Phát Triển (4 Thành Viên) & Biểu Đồ
        </a>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: COI THÔNG TIN CHUNG TÀI KHOẢN (PROFILE OVERVIEW DASHBOARD)    -->
    <!-- ===================================================================== -->
    <?php if ($tab === 'account'): ?>

        <!-- THANH CÔNG CỤ HỒ SƠ NGƯỜI DÙNG & NÚT MỞ TÀI LIỆU KIỂM THỬ (DOC) -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; background: rgba(14, 22, 38, 0.6); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
                <span style="font-size: 0.85rem; color: #cbd5e1;">Hồ sơ thành viên: <strong style="color: #ffffff;"><?= htmlspecialchars($currentUser['full_name'] ?? 'Khách Hàng') ?></strong></span>
                <span class="sol-badge <?= $userRole === 'admin' ? 'sol-badge--amber' : ($userRole === 'technician' ? 'sol-badge--cyan' : 'sol-badge--emerald') ?>" style="font-size: 0.72rem; padding: 0.15rem 0.5rem;">
                    <?= htmlspecialchars($roleName) ?>
                </span>
            </div>
            <div>
                <button type="button" onclick="openDocModal()" class="sol-btn sol-btn--outline" style="border-color: #f59e0b; color: #f59e0b; font-size: 0.85rem; padding: 0.45rem 1rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <span>📄</span> Tài Liệu Nghiệm Thu & Kiểm Thử (DOC)
                    <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 999px;">TEST ACCOUNTS</span>
                </button>
            </div>
        </div>

        <!-- KHỐI 2: THẺ NHẬN DIỆN HỒ SƠ TÀI KHOẢN (USER PROFILE OVERVIEW CARD) -->
        <section class="sol-glass-card" style="padding: 2rem; margin-bottom: 2rem; border-left: 4px solid <?= $userRole === 'admin' ? 'var(--accent-amber)' : ($userRole === 'technician' ? 'var(--accent-cyan)' : 'var(--accent-emerald)') ?>;">
            <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 2rem; align-items: center;" class="sol-profile-header-grid">
                <!-- Avatar lớn -->
                <div style="text-align: center;">
                    <div style="width: 86px; height: 86px; border-radius: 50%; background: linear-gradient(135deg, <?= $userRole === 'admin' ? '#f59e0b, #b45309' : ($userRole === 'technician' ? '#0284c7, #0369a1' : '#10b981, #047857') ?>); display: flex; align-items: center; justify-content: center; font-size: 2.6rem; border: 3px solid <?= $userRole === 'admin' ? '#fbbf24' : ($userRole === 'technician' ? '#38bdf8' : '#34d399') ?>; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
                        <?= match ($userRole) {
                            'admin' => '👑',
                            'technician' => '🔧',
                            default => '👤'
                        } ?>
                    </div>
                    <span style="display: inline-block; margin-top: 0.5rem; font-size: 0.72rem; color: #10b981; font-weight: 600;">
                        ● Đang kích hoạt
                    </span>
                </div>

                <!-- Thông tin định danh chính -->
                <div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                        <h2 style="color: #ffffff; font-size: 1.6rem; font-family: var(--font-heading); margin: 0;">
                            <?= htmlspecialchars($currentUser['full_name'] ?? 'Người Dùng') ?>
                        </h2>
                        <span class="sol-badge <?= $userRole === 'admin' ? 'sol-badge--amber' : ($userRole === 'technician' ? 'sol-badge--cyan' : 'sol-badge--emerald') ?>" style="font-size: 0.78rem; padding: 0.2rem 0.6rem;">
                            <?= htmlspecialchars($roleName) ?>
                        </span>
                    </div>

                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0 0 1rem; line-height: 1.4;">
                        <?= match ($userRole) {
                            'admin' => 'Tài khoản điều hành cấp cao có toàn quyền quản trị danh mục thiết bị, phê duyệt các yêu cầu báo giá và điều phối kỹ thuật viên thi công.',
                            'technician' => 'Kỹ thuật viên phụ trách khảo sát mặt bằng mái nhà, giám sát quy trình lắp đặt 4 giai đoạn và nghiệm thu đóng điện.',
                            default => 'Khách hàng thành viên có quyền chọn thiết bị vào Kế hoạch dự định, mô phỏng công suất phát điện, tính tiền tiết kiệm và gửi yêu cầu khảo sát.'
                        } ?>
                    </p>

                    <!-- Lưới thông tin chi tiết -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.825rem; background: rgba(8, 12, 20, 0.55); padding: 0.85rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
                        <div>
                            <span style="color: var(--text-muted); display: block; font-size: 0.7rem; text-transform: uppercase;">Mã tài khoản:</span>
                            <strong style="color: #ffffff; font-family: var(--font-mono);">UID-2026-<?= str_pad((string)($currentUser['id'] ?? 1), 4, '0', STR_PAD_LEFT) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block; font-size: 0.7rem; text-transform: uppercase;">Tên đăng nhập:</span>
                            <strong style="color: var(--accent-gold); font-family: var(--font-mono);"><?= htmlspecialchars($currentUser['username'] ?? 'N/A') ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block; font-size: 0.7rem; text-transform: uppercase;">Email liên hệ:</span>
                            <strong style="color: var(--accent-cyan);"><?= htmlspecialchars($currentUser['email'] ?? 'N/A') ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); display: block; font-size: 0.7rem; text-transform: uppercase;">Số điện thoại:</span>
                            <strong style="color: #ffffff; font-family: var(--font-mono);"><?= htmlspecialchars($currentUser['phone'] ?? 'Chưa cập nhật') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Thống kê nhanh bên phải -->
                <div style="text-align: right; display: flex; flex-direction: column; gap: 0.5rem; justify-content: center;">
                    <div style="padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); min-width: 160px; text-align: center;">
                        <span style="font-size: 0.7rem; color: var(--text-muted); display: block; text-transform: uppercase;">Ngày tham gia:</span>
                        <strong style="color: #ffffff; font-size: 0.85rem;">
                            <?= !empty($currentUser['created_at']) ? date('d/m/Y', strtotime($currentUser['created_at'])) : '01/10/2026' ?>
                        </strong>
                    </div>
                    <div style="padding: 0.75rem 1rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); min-width: 160px; text-align: center;">
                        <span style="font-size: 0.7rem; color: var(--text-muted); display: block; text-transform: uppercase;">Cấp bậc xác thực:</span>
                        <span style="color: #10b981; font-weight: 700; font-size: 0.85rem;">✓ Đã Xác Minh</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- KHỐI 3: CÁC THẺ CHỈ SỐ HOẠT ĐỘNG (ROLE ACTIVITY STATS) -->
        <?php if (!empty($roleStats['items'])): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <?php foreach ($roleStats['items'] as $st): ?>
                    <div class="sol-glass-card" style="padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 50px; height: 50px; border-radius: 10px; background: rgba(8, 12, 20, 0.75); border: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                            <?= $st['icon'] ?>
                        </div>
                        <div>
                            <div style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 800; color: <?= $st['color'] ?>; line-height: 1.2;">
                                <?= $st['value'] ?>
                            </div>
                            <div style="font-size: 0.8rem; font-weight: 600; color: #ffffff; margin-top: 0.15rem;">
                                <?= htmlspecialchars($st['label']) ?>
                            </div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem;">
                                <?= htmlspecialchars($st['desc'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- KHỐI 4: THÔNG TIN CHUNG ĐẶC THÙ THEO VAI TRÒ (ROLE-SPECIFIC DASHBOARD) -->

        <!-- ======================= KHÁCH HÀNG (CUSTOMER) ======================= -->
        <?php if ($userRole === 'customer'): ?>
            
            <!-- Phần 4.1: Kế Hoạch Thiết Bị Đang Quan Tâm (Từ Kế Hoạch Dự Định) -->
            <section class="sol-glass-card" style="padding: 1.85rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
                    <div>
                        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.25rem;">KẾ HOẠCH DỰ ĐỊNH CỦA BẠN</span>
                        <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                            📋 Thiết Bị Đã Chọn & Thông Số Năng Lượng Ước Tính
                        </h3>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="index.php?page=cart" class="sol-btn sol-btn--outline sol-btn--sm">
                            Xem Kế Hoạch Dự Định (<?= count($cartItems) ?>)
                        </a>
                        <a href="index.php?page=calculator&source=plan" class="sol-btn sol-btn--solar sol-btn--sm">
                            ⚡ Mô Phỏng Thiết Kế & Hoàn Vốn
                        </a>
                    </div>
                </div>

                <?php if (empty($cartItems)): ?>
                    <div style="text-align: center; padding: 2rem 1rem; background: rgba(8, 12, 20, 0.4); border-radius: var(--radius-sm); border: 1px dashed var(--border-subtle);">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">☀️</div>
                        <h4 style="color: #ffffff; font-size: 1.05rem; margin: 0 0 0.4rem;">Bạn Chưa Thêm Thiết Bị Nào Vào Kế Hoạch Dự Định</h4>
                        <p style="color: var(--text-secondary); font-size: 0.825rem; max-width: 500px; margin: 0 auto 1.25rem;">
                            Hãy ghé thăm danh mục sản phẩm để chọn tấm pin mặt trời, biến tần Inverter hoặc tủ điện phù hợp với nhu cầu ngôi nhà của bạn.
                        </p>
                        <a href="index.php?page=products" class="sol-btn sol-btn--solar sol-btn--sm">
                            Khám Phá Danh Mục Thiết Bị Solar →
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Bảng các thiết bị trong Kế hoạch dự định -->
                    <div style="overflow-x: auto; margin-bottom: 1.5rem;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; color: #ffffff;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                                    <th style="padding: 0.75rem 1rem;">Mã thiết bị</th>
                                    <th style="padding: 0.75rem 1rem;">Tên sản phẩm</th>
                                    <th style="padding: 0.75rem 1rem;">Phân loại</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Số lượng</th>
                                    <th style="padding: 0.75rem 1rem; text-align: right;">Công suất đơn vị</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 600;">
                                            <?= htmlspecialchars($item['product_code'] ?? 'SOL-PROD') ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; font-weight: 600;">
                                            <?= htmlspecialchars($item['name'] ?? 'Thiết bị') ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; color: var(--text-secondary);">
                                            <?= htmlspecialchars($item['category_name'] ?? 'Phụ kiện') ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: center;">
                                            <span style="display: inline-block; padding: 0.2rem 0.6rem; background: rgba(245, 158, 11, 0.15); border: 1px solid var(--accent-amber); border-radius: 4px; font-weight: 700; color: var(--accent-gold);">
                                                <?= (int)($item['quantity'] ?? 1) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: var(--accent-emerald);">
                                            <?= !empty($item['capacity_watts']) ? (int)$item['capacity_watts'] . ' W' : 'Thiết bị phụ trợ' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hộp tính toán số KW, tiền tạo ra & thời gian gỡ vốn -->
                    <?php if (!empty($planCalc)): ?>
                        <div style="background: rgba(8, 12, 20, 0.65); border: 1px solid var(--accent-amber); border-radius: var(--radius-sm); padding: 1.25rem;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--accent-gold); text-transform: uppercase; margin-bottom: 0.75rem;">
                                ⚡ Kết Quả Dự Tính Năng Lượng & Kinh Tế Từ Kế Hoạch Này:
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: center;">
                                <div style="padding: 0.75rem; background: rgba(14, 22, 38, 0.7); border-radius: var(--radius-sm);">
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Tổng công suất giàn pin</div>
                                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--accent-amber); font-family: var(--font-heading);">
                                        <?= number_format($planCalc['total_kwp'] ?? 0, 2) ?> kWp
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary);"><?= (int)($planCalc['panel_count'] ?? 0) ?> tấm pin mặt trời</div>
                                </div>
                                <div style="padding: 0.75rem; background: rgba(14, 22, 38, 0.7); border-radius: var(--radius-sm);">
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Sản lượng điện tạo ra</div>
                                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--accent-cyan); font-family: var(--font-heading);">
                                        <?= number_format($planCalc['monthly_kwh'] ?? 0) ?> kWh/tháng
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary);">Trung bình 4.2h nắng/ngày</div>
                                </div>
                                <div style="padding: 0.75rem; background: rgba(14, 22, 38, 0.7); border-radius: var(--radius-sm);">
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Số tiền tạo ra / tiết kiệm</div>
                                    <div style="font-size: 1.35rem; font-weight: 800; color: var(--accent-emerald); font-family: var(--font-heading);">
                                        <?= number_format($planCalc['monthly_savings_vnd'] ?? 0) ?> đ/tháng
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary);">Giảm bậc thang giá điện</div>
                                </div>
                                <div style="padding: 0.75rem; background: rgba(14, 22, 38, 0.7); border-radius: var(--radius-sm);">
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">Dự tính thời gian gỡ vốn</div>
                                    <div style="font-size: 1.35rem; font-weight: 800; color: #f43f5e; font-family: var(--font-heading);">
                                        <?= $planCalc['payback_years'] ?? 4 ?> năm <?= $planCalc['payback_months'] ?? 6 ?> tháng
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary);">Tương đương ~18-22%/năm</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <!-- Phần 4.2: Hồ Sơ Yêu Cầu Báo Giá Đã Gửi -->
            <section class="sol-glass-card" style="padding: 1.85rem; margin-bottom: 2rem;">
                <div style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
                    <span class="sol-badge sol-badge--emerald" style="margin-bottom: 0.25rem;">HỒ SƠ KHẢO SÁT & BÁO GIÁ</span>
                    <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                        ⚡ Danh Sách Báo Giá Khảo Sát Đã Gửi Hệ Thống
                    </h3>
                </div>

                <?php if (empty($roleQuotes)): ?>
                    <p style="color: var(--text-secondary); font-size: 0.85rem;">Bạn chưa gửi yêu cầu báo giá nào. Hãy thực hiện tính toán tại mục Demo Thiết Kế để gửi yêu cầu.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; color: #ffffff;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                                    <th style="padding: 0.75rem 1rem;">Mã hồ sơ</th>
                                    <th style="padding: 0.75rem 1rem;">Họ tên khách</th>
                                    <th style="padding: 0.75rem 1rem;">Số điện thoại</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Công suất (kWp)</th>
                                    <th style="padding: 0.75rem 1rem; text-align: right;">Dự toán</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($roleQuotes, 0, 4) as $q): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--accent-cyan);">
                                            #BG-<?= str_pad((string)$q['id'], 4, '0', STR_PAD_LEFT) ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; font-weight: 600;">
                                            <?= htmlspecialchars($q['customer_name'] ?? 'Khách hàng') ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--text-secondary);">
                                            <?= htmlspecialchars($q['customer_phone'] ?? 'N/A') ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: var(--accent-amber);">
                                            <?= number_format((float)($q['estimated_system_kwp'] ?? 5.5), 1) ?> kWp
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--accent-emerald);">
                                            <?= number_format((float)($q['estimated_total_cost'] ?? 85000000)) ?> đ
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: center;">
                                            <span class="sol-badge sol-badge--emerald" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                                                <?= htmlspecialchars($q['status'] === 'approved' ? 'Đã duyệt' : ($q['status'] === 'survey_scheduled' ? 'Đã xếp lịch khảo sát' : 'Tiếp nhận')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

        <!-- ===================== KỸ THUẬT VIÊN (TECHNICIAN) ===================== -->
        <?php elseif ($userRole === 'technician'): ?>

            <!-- Phần 4.1: Hồ sơ năng lực & Phân công kỹ thuật -->
            <section class="sol-glass-card" style="padding: 1.85rem; margin-bottom: 2rem;">
                <div style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.25rem;">HỒ SƠ NĂNG LỰC HIỆN TRƯỜNG</span>
                        <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                            🔧 Thông Tin Chuyên Môn & Chứng Nhận Kỹ Thuật
                        </h3>
                    </div>
                    <a href="index.php?page=installations" class="sol-btn sol-btn--solar sol-btn--sm">
                        📋 Mở Phân Hệ Quản Lý Lắp Đặt
                    </a>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border-radius: var(--radius-sm); border-left: 3px solid var(--accent-cyan);">
                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Chứng chỉ chuyên môn</div>
                        <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-top: 0.25rem;">
                            Chứng Nhận Thi Công Solar Quốc Tế (SMA / Huawei)
                        </strong>
                        <div style="font-size: 0.75rem; color: var(--accent-emerald); margin-top: 0.2rem;">● Hiệu lực: 2024 - 2028</div>
                    </div>
                    <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border-radius: var(--radius-sm); border-left: 3px solid var(--accent-amber);">
                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">An toàn lao động</div>
                        <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-top: 0.25rem;">
                            Chứng Chỉ Làm Việc Trên Cao & An Toàn Điện Nhóm 4
                        </strong>
                        <div style="font-size: 0.75rem; color: var(--accent-gold); margin-top: 0.2rem;">● Cục An Toàn Lao Động cấp</div>
                    </div>
                    <div style="padding: 1rem; background: rgba(8, 12, 20, 0.6); border-radius: var(--radius-sm); border-left: 3px solid #10b981;">
                        <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Khu vực phụ trách</div>
                        <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-top: 0.25rem;">
                            Khu vực TP. Hồ Chí Minh, Bình Dương & Đồng Nai
                        </strong>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Sẵn sàng khảo sát trong 24h</div>
                    </div>
                </div>

                <!-- Bảng danh sách công trình đang phụ trách -->
                <div style="margin-bottom: 1rem;">
                    <h4 style="color: #ffffff; font-size: 1rem; margin-bottom: 0.75rem;">🏗️ Danh Sách Công Trình Thi Công Được Phân Công:</h4>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; color: #ffffff;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                                    <th style="padding: 0.75rem 1rem;">Mã hợp đồng</th>
                                    <th style="padding: 0.75rem 1rem;">Tên dự án</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Công suất</th>
                                    <th style="padding: 0.75rem 1rem;">Giai đoạn thi công</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($roleProjects)): ?>
                                    <?php foreach ($roleProjects as $p): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 600;">
                                                <?= htmlspecialchars($p['contract_code'] ?? 'SOLAR-2026') ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; font-weight: 600;">
                                                <?= htmlspecialchars($p['project_name'] ?? 'Công trình') ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: var(--accent-amber);">
                                                <?= number_format((float)($p['capacity_kwp'] ?? 7.7), 1) ?> kWp
                                            </td>
                                            <td style="padding: 0.75rem 1rem; color: var(--accent-gold);">
                                                <?= match ($p['current_stage'] ?? 'survey') {
                                                    'survey' => '1. Khảo sát hiện trường mái',
                                                    'material' => '2. Chuẩn bị vật tư thiết bị',
                                                    'installation' => '3. Thi công khung & tấm pin',
                                                    'handover' => '4. Nghiệm thu & Đóng điện EVN',
                                                    default => 'Đang thực hiện'
                                                } ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                                <span class="sol-badge <?= ($p['status'] ?? '') === 'completed' ? 'sol-badge--emerald' : 'sol-badge--amber' ?>" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                                                    <?= ($p['status'] ?? '') === 'completed' ? 'Hoàn thành' : 'Đang thi công' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="padding: 1rem; text-align: center; color: var(--text-secondary);">Chưa có công trình nào được phân công.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        <!-- ===================== QUẢN TRỊ VIÊN (ADMIN) ===================== -->
        <?php else: ?>

            <!-- Phần 4.1: Tổng quan quản trị & Báo giá cần duyệt -->
            <section class="sol-glass-card" style="padding: 1.85rem; margin-bottom: 2rem;">
                <div style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.25rem;">TRUNG TÂM ĐIỀU HÀNH TỔNG HỢP</span>
                        <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                            👑 Bảng Tổng Hợp Báo Giá & Hồ Sơ Dự Án Toàn Hệ Thống
                        </h3>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="index.php?page=products" class="sol-btn sol-btn--outline sol-btn--sm">
                            ☀️ Quản Lý Sản Phẩm (26 SP)
                        </a>
                        <a href="index.php?page=installations" class="sol-btn sol-btn--solar sol-btn--sm">
                            📋 Phân Bổ Kỹ Thuật Viên
                        </a>
                    </div>
                </div>

                <!-- Bảng danh sách báo giá chờ xử lý -->
                <div style="margin-bottom: 1.75rem;">
                    <h4 style="color: #ffffff; font-size: 1rem; margin-bottom: 0.75rem;">📋 Yêu Cầu Báo Giá Khảo Sát Khách Hàng Gần Đây:</h4>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; color: #ffffff;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                                    <th style="padding: 0.75rem 1rem;">ID</th>
                                    <th style="padding: 0.75rem 1rem;">Khách hàng</th>
                                    <th style="padding: 0.75rem 1rem;">Điện thoại</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Công suất (kWp)</th>
                                    <th style="padding: 0.75rem 1rem; text-align: right;">Kinh phí dự toán</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($roleQuotes)): ?>
                                    <?php foreach ($roleQuotes as $q): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--accent-cyan);">
                                                #BG-<?= str_pad((string)$q['id'], 3, '0', STR_PAD_LEFT) ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; font-weight: 600;">
                                                <?= htmlspecialchars($q['customer_name'] ?? 'Khách') ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--text-secondary);">
                                                <?= htmlspecialchars($q['customer_phone'] ?? 'N/A') ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: var(--accent-amber);">
                                                <?= number_format((float)($q['estimated_system_kwp'] ?? 5.5), 1) ?> kWp
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--accent-emerald);">
                                                <?= number_format((float)($q['estimated_total_cost'] ?? 85000000)) ?> đ
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                                <span class="sol-badge sol-badge--emerald" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                                                    <?= htmlspecialchars($q['status'] === 'approved' ? 'Đã duyệt' : ($q['status'] === 'survey_scheduled' ? 'Đã lên lịch khảo sát' : 'Tiếp nhận')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bảng tổng hợp công trình toàn hệ thống -->
                <div>
                    <h4 style="color: #ffffff; font-size: 1rem; margin-bottom: 0.75rem;">🏗️ Hợp Đồng Dự Án Thi Công Toàn Hệ Thống:</h4>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; color: #ffffff;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                                    <th style="padding: 0.75rem 1rem;">Mã hợp đồng</th>
                                    <th style="padding: 0.75rem 1rem;">Tên công trình</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Quy mô</th>
                                    <th style="padding: 0.75rem 1rem; text-align: right;">Giá trị hợp đồng</th>
                                    <th style="padding: 0.75rem 1rem; text-align: center;">Tiến độ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($roleProjects)): ?>
                                    <?php foreach ($roleProjects as $p): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--accent-cyan); font-weight: 600;">
                                                <?= htmlspecialchars($p['contract_code'] ?? 'SOLAR-2026') ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; font-weight: 600;">
                                                <?= htmlspecialchars($p['project_name'] ?? 'Công trình') ?>
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700; color: var(--accent-amber);">
                                                <?= number_format((float)($p['capacity_kwp'] ?? 7.7), 1) ?> kWp
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--accent-emerald);">
                                                <?= number_format((float)($p['total_amount'] ?? 115000000)) ?> đ
                                            </td>
                                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                                <span class="sol-badge <?= ($p['status'] ?? '') === 'completed' ? 'sol-badge--emerald' : 'sol-badge--amber' ?>" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                                                    <?= ($p['status'] ?? '') === 'completed' ? 'Hoàn tất nghiệm thu' : 'Đang triển khai' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        <?php endif; ?>

    <!-- ===================================================================== -->
    <!-- TAB 2: THÔNG TIN 4 THÀNH VIÊN DỰ ÁN & BIỂU ĐỒ TRÁCH NHIỆM WBS         -->
    <!-- ===================================================================== -->
    <?php else: ?>

        <!-- GRID 4 THẺ THÀNH VIÊN DỰ ÁN -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3.5rem;">
            <?php foreach ($teamMembers as $member): ?>
                <div class="sol-glass-card" style="padding: 1.85rem; border-top: 4px solid <?= $member['badge_color'] ?>; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                    <div>
                        <!-- Header thẻ thành viên -->
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                            <div style="width: 58px; height: 58px; border-radius: 50%; background: rgba(8, 12, 20, 0.85); border: 2px solid <?= $member['badge_color'] ?>; display: flex; align-items: center; justify-content: center; font-size: 1.85rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                                <?= $member['avatar_icon'] ?>
                            </div>
                            <div>
                                <span class="sol-badge" style="background: rgba(255,255,255,0.06); color: <?= $member['badge_color'] ?>; border: 1px solid <?= $member['badge_color'] ?>; font-size: 0.7rem; padding: 0.1rem 0.45rem; margin-bottom: 0.25rem; display: inline-block;">
                                    Thành Viên <?= $member['id'] ?> <?= $member['id'] === 1 ? '• Trưởng Nhóm (PM)' : '' ?>
                                </span>
                                <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                                    <?= htmlspecialchars($member['name']) ?>
                                </h3>
                                <div style="color: var(--accent-gold); font-size: 0.825rem; font-family: var(--font-mono); font-weight: 600;">
                                    MSSV: <?= htmlspecialchars($member['mssv']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Vai trò chính -->
                        <div style="padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.6); border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); margin-bottom: 1.25rem;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Vai trò trong dự án:</div>
                            <div style="color: #ffffff; font-size: 0.85rem; font-weight: 600; line-height: 1.35; margin-top: 0.15rem;">
                                <?= htmlspecialchars($member['role']) ?>
                            </div>
                        </div>

                        <!-- Tóm tắt & Danh sách công việc thực tế -->
                        <p style="color: var(--text-secondary); font-size: 0.825rem; line-height: 1.5; margin-bottom: 1.25rem;">
                            <?= htmlspecialchars($member['summary']) ?>
                        </p>

                        <div style="border-top: 1px solid var(--border-subtle); padding-top: 0.85rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.75rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; margin-bottom: 0.5rem;">
                                Chi tiết công việc phụ trách:
                            </div>
                            <ul style="padding-left: 1.15rem; margin: 0; font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5; display: flex; flex-direction: column; gap: 0.45rem;">
                                <?php foreach ($member['tasks'] as $task): ?>
                                    <li><?= htmlspecialchars($task) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer tỷ lệ đóng góp -->
                    <div style="border-top: 1px solid var(--border-subtle); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                        <span style="color: var(--text-muted);">Tỷ trọng đóng góp (WBS):</span>
                        <strong style="color: <?= $member['badge_color'] ?>; font-family: var(--font-heading); font-size: 1rem;">
                            <?= $member['weight'] ?>% Khối lượng
                        </strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ================================================================= -->
        <!-- BIỂU ĐỒ 1: QUY TRÌNH PHỐI HỢP LIÊN TỤC 4 PHÂN HỆ (WORKFLOW TIMELINE)-->
        <!-- ================================================================= -->
        <section class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 3.5rem;">
            <div style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">SƠ ĐỒ QUY TRÌNH KỸ THUẬT</span>
                <h3 style="color: #ffffff; font-size: 1.45rem; font-family: var(--font-heading); margin: 0;">
                    Biểu Đồ Luồng Tương Tác Giữa 4 Phân Hệ Trong Dự Án Solaris
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.35rem;">
                    Thể hiện sự kết nối dữ liệu chặt chẽ từ Khởi tạo kiến trúc cốt lõi (TV1) → Chọn thiết bị & kế hoạch (TV2) → Mô phỏng KW điện & ROI (TV3) → Quản lý thi công hiện trường (TV4).
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; position: relative;">
                
                <!-- Bước 1: TV1 -->
                <div style="background: rgba(8, 12, 20, 0.7); border: 1px solid #f59e0b; border-radius: var(--radius-md); padding: 1.5rem; position: relative;">
                    <div style="position: absolute; top: -12px; left: 1.25rem; background: #f59e0b; color: #080c14; font-weight: 800; font-size: 0.75rem; padding: 0.15rem 0.65rem; border-radius: 999px;">
                        GIAI ĐOẠN 1
                    </div>
                    <div style="font-size: 2rem; margin-bottom: 0.75rem;">🏗️</div>
                    <h4 style="color: #ffffff; font-size: 1.1rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                        TV1: Core Architecture & UI/UX
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        Xây dựng khung Router PSR-4, Solar Grid Theme, khắc phục triệt để lỗi hiển thị navbar, kiểm soát Session và hoàn thiện 6 bài tập Form cốt lõi.
                    </p>
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-subtle); font-size: 0.75rem; color: #f59e0b; font-weight: 600;">
                        → Xuất bản: Khung nền tảng & Theme chuẩn
                    </div>
                </div>

                <!-- Bước 2: TV2 -->
                <div style="background: rgba(8, 12, 20, 0.7); border: 1px solid #10b981; border-radius: var(--radius-md); padding: 1.5rem; position: relative;">
                    <div style="position: absolute; top: -12px; left: 1.25rem; background: #10b981; color: #080c14; font-weight: 800; font-size: 0.75rem; padding: 0.15rem 0.65rem; border-radius: 999px;">
                        GIAI ĐOẠN 2
                    </div>
                    <div style="font-size: 2rem; margin-bottom: 0.75rem;">☀️</div>
                    <h4 style="color: #ffffff; font-size: 1.1rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                        TV2: Catalog & Kế Hoạch Dự Định
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        Quản lý 26 sản phẩm Solar (Tấm pin, Inverter, Tủ điện, BESS), xây dựng Kế hoạch dự định (loại bỏ hiển thị giá tiền) và bảng ghép đôi tương thích.
                    </p>
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-subtle); font-size: 0.75rem; color: #10b981; font-weight: 600;">
                        → Dữ liệu: Danh sách thiết bị giỏ hàng
                    </div>
                </div>

                <!-- Bước 3: TV3 -->
                <div style="background: rgba(8, 12, 20, 0.7); border: 1px solid #38bdf8; border-radius: var(--radius-md); padding: 1.5rem; position: relative;">
                    <div style="position: absolute; top: -12px; left: 1.25rem; background: #38bdf8; color: #080c14; font-weight: 800; font-size: 0.75rem; padding: 0.15rem 0.65rem; border-radius: 999px;">
                        GIAI ĐOẠN 3
                    </div>
                    <div style="font-size: 2rem; margin-bottom: 0.75rem;">🧮</div>
                    <h4 style="color: #ffffff; font-size: 1.1rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                        TV3: Simulator, ROI & Báo Cáo
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        Đồng bộ thiết bị từ Kế hoạch dự định, tính toán sản lượng KW điện, số tiền tạo ra/tháng, thời gian gỡ vốn, sơ đồ mái 2D SVG và biên soạn hồ sơ báo cáo.
                    </p>
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-subtle); font-size: 0.75rem; color: #38bdf8; font-weight: 600;">
                        → Kết quả: Báo giá solar_quotes & Báo cáo
                    </div>
                </div>

                <!-- Bước 4: TV4 -->
                <div style="background: rgba(8, 12, 20, 0.7); border: 1px solid #fbbf24; border-radius: var(--radius-md); padding: 1.5rem; position: relative;">
                    <div style="position: absolute; top: -12px; left: 1.25rem; background: #fbbf24; color: #080c14; font-weight: 800; font-size: 0.75rem; padding: 0.15rem 0.65rem; border-radius: 999px;">
                        GIAI ĐOẠN 4
                    </div>
                    <div style="font-size: 2rem; margin-bottom: 0.75rem;">📋</div>
                    <h4 style="color: #ffffff; font-size: 1.1rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                        TV4: Quản Lý Lắp Đặt & Nghiệm Thu
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.8rem; line-height: 1.5; margin: 0;">
                        Chuyển đổi báo giá thành hợp đồng thi công thực tế, quản lý 4 giai đoạn lắp đặt, tải lên biên bản nghiệm thu PCCC/EVN và phân công kỹ thuật viên.
                    </p>
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px dashed var(--border-subtle); font-size: 0.75rem; color: #fbbf24; font-weight: 600;">
                        → Nghiệm thu: Đóng điện bàn giao công trình
                    </div>
                </div>

            </div>
        </section>

        <!-- ================================================================= -->
        <!-- BIỂU ĐỒ 2: BIỂU ĐỒ CƠ CẤU ĐÓNG GÓP TOÀN DỰ ÁN (CONTRIBUTION BARS)  -->
        <!-- ================================================================= -->
        <section class="sol-glass-card" style="padding: 2.25rem;">
            <div style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
                <span class="sol-badge sol-badge--emerald" style="margin-bottom: 0.35rem;">CƠ CẤU ĐÓNG GÓP (WBS WORK BREAKDOWN)</span>
                <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0;">
                    Biểu Đồ Tỷ Trọng Đóng Góp Xây Dựng Dự Án Solaris
                </h3>
            </div>

            <!-- Thanh tỷ trọng gộp 100% -->
            <div style="margin-bottom: 1.75rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                    <span>Tổng thể dự án: <strong>100% Khối Lượng Hoàn Thành</strong></span>
                    <span>4 Phân hệ phối hợp đồng bộ</span>
                </div>
                <div style="display: flex; width: 100%; height: 26px; border-radius: 999px; overflow: hidden; background: #080c14; border: 1px solid var(--border-subtle); box-shadow: inset 0 2px 6px rgba(0,0,0,0.5);">
                    <div style="width: 35%; background: linear-gradient(90deg, #f59e0b, #fbbf24); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #080c14;" title="Nguyễn Huy Đạt (35%)">TV1: Đạt (35%)</div>
                    <div style="width: 20%; background: linear-gradient(90deg, #10b981, #34d399); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #080c14;" title="Lê Chí Hải (20%)">TV2: Hải (20%)</div>
                    <div style="width: 25%; background: linear-gradient(90deg, #0284c7, #38bdf8); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #ffffff;" title="Mai Đức Toàn (25%)">TV3: Toàn (25%)</div>
                    <div style="width: 20%; background: linear-gradient(90deg, #f97316, #fb923c); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #080c14;" title="Phan Trần Thịnh (20%)">TV4: Thịnh (20%)</div>
                </div>
            </div>

            <!-- Chú thích 4 thanh năng lực chi tiết -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #f59e0b;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Nguyễn Huy Đạt (TV1 - PM)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Core Arch, Routing, UI/UX, Quản Lý Dự Án, 6 Bài Tập Form (35%)</div>
                </div>

                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #10b981;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Lê Chí Hải (TV2)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Catalog 26 SP, Kế hoạch dự định, Tương thích thiết bị (20%)</div>
                </div>

                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #38bdf8;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Mai Đức Toàn (TV3)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Simulator KW/ROI, SVG Mái, BOM, Phụ Trách Báo Cáo Dự Án (25%)</div>
                </div>

                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #fb923c;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Phan Trần Thịnh (TV4)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Auth 3 Cấp, Workflow Lắp Đặt, Phân Bổ Kỹ Thuật Viên (20%)</div>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <!-- ================================================================= -->
    <!-- MODAL TÀI LIỆU NGHIỆM THU & HƯỚNG DẪN KIỂM THỬ (DOC)               -->
    <!-- (Chứa toàn bộ thông tin tài khoản thử nghiệm dành cho Tester/Hội đồng) -->
    <!-- ================================================================= -->
    <div id="solDocModal" class="sol-doc-modal" aria-hidden="true">
        <div class="sol-doc-backdrop" onclick="closeDocModal()"></div>
        <div class="sol-doc-content" style="max-height: 85vh; overflow-y: auto !important;">
            <!-- Thanh tiêu đề dính trên cùng để luôn thấy nút Đóng khi cuộn -->
            <div style="position: sticky; top: -2rem; z-index: 10; background: #090e18; padding: 0.5rem 0 1rem; margin-top: -0.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle);">
                <div>
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.25rem;">📄 HỒ SƠ NGHIỆM THU ĐỒ ÁN SOLARIS V2.1</span>
                    <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0;">
                        Tài Liệu Nghiệm Thu & Danh Sách Tài Khoản Kiểm Thử
                    </h3>
                </div>
                <button type="button" onclick="closeDocModal()" class="sol-btn sol-btn--outline sol-btn--sm" style="border-color: #f59e0b; color: #f59e0b; padding: 0.35rem 0.85rem; font-size: 0.825rem;" title="Đóng cửa sổ tài liệu (ESC)">
                    ✕ Đóng (ESC)
                </button>
            </div>

            <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 1rem 0 1.25rem;">
                Tài liệu kỹ thuật phục vụ Giảng viên và Hội đồng nghiệm thu kiểm tra toàn bộ luồng chức năng phân quyền 3 cấp độ.
            </p>

            <!-- PHẦN 1: BẢNG TÀI KHOẢN MẪU PHÂN QUYỀN 3 MỨC -->
            <div style="margin-bottom: 1.75rem;">
                <h4 style="color: var(--accent-gold); font-size: 1.05rem; margin: 0 0 0.75rem; display: flex; align-items: center; gap: 0.45rem;">
                    <span>🔑</span> Danh Sách Tài Khoản Kiểm Thử Mẫu (Mật khẩu chung: <code style="color: #38bdf8; background: rgba(56,189,248,0.15); padding: 2px 6px; border-radius: 4px;">Solaris@2026</code>)
                </h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <?php foreach ($demoAccounts as $acc): 
                        $isCurrent = (!empty($currentUser) && $currentUser['username'] === $acc['username']);
                    ?>
                        <div style="background: rgba(14, 22, 38, 0.75); border: 1.5px solid <?= $isCurrent ? 'var(--accent-amber)' : 'var(--border-subtle)' ?>; border-radius: var(--radius-sm); padding: 1rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <span class="sol-badge <?= $acc['badge'] ?>" style="font-size: 0.68rem; padding: 0.1rem 0.45rem;">
                                        <?= htmlspecialchars($acc['level']) ?>
                                    </span>
                                    <?php if ($isCurrent): ?>
                                        <span style="font-size: 0.72rem; color: var(--accent-gold); font-weight: 700;">● Hiện tại</span>
                                    <?php endif; ?>
                                </div>
                                <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($acc['name']) ?>
                                </strong>
                                <div style="font-size: 0.78rem; color: #cbd5e1; margin-bottom: 0.2rem;">
                                    Username: <strong style="color: var(--accent-cyan); font-family: var(--font-mono);"><?= htmlspecialchars($acc['username']) ?></strong>
                                </div>
                                <div style="font-size: 0.78rem; color: #cbd5e1; margin-bottom: 0.6rem;">
                                    Mật khẩu: <strong style="color: #f59e0b; font-family: var(--font-mono);">Solaris@2026</strong>
                                </div>
                                <p style="font-size: 0.75rem; color: var(--text-secondary); line-height: 1.4; margin: 0 0 0.85rem;">
                                    <?= htmlspecialchars($acc['desc']) ?>
                                </p>
                            </div>

                            <div>
                                <?php if ($isCurrent): ?>
                                    <button class="sol-btn sol-btn--outline sol-btn--sm" disabled style="opacity: 0.65; cursor: default; width: 100%; font-size: 0.78rem;">
                                        ✓ Đang trong vai trò này
                                    </button>
                                <?php else: ?>
                                    <a href="index.php?page=profile&tab=account&switch_account=<?= htmlspecialchars($acc['username']) ?>" class="sol-btn sol-btn--solar sol-btn--sm" style="text-align: center; text-decoration: none; width: 100%; font-size: 0.78rem;">
                                        ⚡ Chuyển Sang Vai Trò Này
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PHẦN 2: MA TRẬN TÍNH NĂNG & HƯỚNG DẪN KIỂM THỬ CÁC LUỒNG -->
            <div style="background: rgba(8, 12, 20, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 1.25rem; font-size: 0.825rem; color: #cbd5e1; line-height: 1.6;">
                <h5 style="color: #ffffff; font-size: 0.925rem; margin: 0 0 0.5rem; font-family: var(--font-heading);">
                    📌 Hướng Dẫn Các Kịch Bản Kiểm Thử Chính Của Đồ Án:
                </h5>
                <ol style="margin: 0; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.45rem;">
                    <li><strong>Luồng 1 - Kế hoạch dự định sang Demo thiết kế:</strong> Vào <em>Sản Phẩm</em> → Chọn "Đưa vào kế hoạch" cho Tấm pin + Inverter → Mở <em>Kế Hoạch Dự Định</em> xem bảng tương thích → Nhấn "Mô Phỏng Thiết Kế" kiểm tra số KW điện tạo ra, tiền tiết kiệm/tháng và thời gian gỡ vốn.</li>
                    <li><strong>Luồng 2 - Profile người dùng:</strong> Xem thẻ thông tin theo từng vai trò (Customer có công suất/tiền gỡ vốn; Technician có chứng chỉ/công trình phụ trách; Admin có dashboard toàn sàn).</li>
                    <li><strong>Luồng 3 - Quản lý lắp đặt (TV4):</strong> Đăng nhập với <code>tech_pro</code> hoặc <code>admin_pro</code> để theo dõi 4 giai đoạn thi công và cập nhật nhật ký kiểm định.</li>
                    <li><strong>Luồng 4 - Portal 24 Bài tập:</strong> Truy cập mục <em>Bài Tập (24 Bài)</em> để chạy thử nghiệm các bài tập PHP thuần, xử lý form, chuỗi, mảng và kết nối CSDL MySQL.</li>
                </ol>
            </div>

            <div style="margin-top: 1.5rem; text-align: right; border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
                <button type="button" onclick="closeDocModal()" class="sol-btn sol-btn--solar sol-btn--sm">
                    Đóng Cửa Sổ Tài Liệu (ESC)
                </button>
            </div>
        </div>
    </div>

    <script>
        function openDocModal() {
            const modal = document.getElementById('solDocModal');
            if (modal) {
                modal.classList.add('is-active');
                modal.setAttribute('aria-hidden', 'false');
            }
        }
        function closeDocModal() {
            const modal = document.getElementById('solDocModal');
            if (modal) {
                modal.classList.remove('is-active');
                modal.setAttribute('aria-hidden', 'true');
            }
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDocModal();
        });
    </script>

</div>
