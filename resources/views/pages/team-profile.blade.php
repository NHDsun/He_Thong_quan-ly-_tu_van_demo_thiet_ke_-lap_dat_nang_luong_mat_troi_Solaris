<?php
/**
 * View: resources/views/pages/team-profile.blade.php
 * Phân hệ 1: Hồ Sơ Năng Lực 4 Thành Viên & Quản Lý Profile Tài Khoản (Admin / Staff / Khách Hàng)
 * Phụ trách: Thành viên 1 - Nguyễn Huy Đạt (MSSV: 65130410 - Trưởng nhóm kiêm PM)
 */

$tab = $_GET['tab'] ?? ($activeTab ?? 'team');
if (!in_array($tab, ['team', 'account'], true)) {
    $tab = 'team';
}
?>

<div class="sol-container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <!-- Tiêu đề phân hệ -->
    <div class="sol-section-header" style="margin-bottom: 2rem;">
        <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.65rem;">
            👤 Phân Hệ 1 • Hồ Sơ Đội Ngũ & Quản Lý Profile Tài Khoản
        </span>
        <h1 class="sol-section-header__title" style="font-size: 2.3rem;">
            Hồ Sơ Năng Lực Dự Án & Quản Trị Tài Khoản
        </h1>
        <p class="sol-section-header__desc">
            Thông tin chi tiết 4 thành viên phát triển dự án Solaris, bảng phân công trách nhiệm, biểu đồ phối hợp quy trình và hệ thống quản lý hồ sơ tài khoản cá nhân cho Admin, Nhân viên kỹ thuật và Khách hàng.
        </p>
    </div>

    <!-- Thông báo Alert nếu có -->
    <?php if (!empty($profileAlert)): ?>
        <div style="margin-bottom: 2rem; padding: 1.15rem 1.4rem; border-radius: var(--radius-md); background: <?= $profileAlert['type'] === 'success' ? 'rgba(16, 185, 129, 0.15)' : ($profileAlert['type'] === 'warning' ? 'rgba(245, 158, 11, 0.15)' : 'rgba(244, 63, 94, 0.15)') ?>; border: 1px solid <?= $profileAlert['type'] === 'success' ? '#10b981' : ($profileAlert['type'] === 'warning' ? '#f59e0b' : '#f43f5e') ?>; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #ffffff; font-size: 0.95rem;">
                <span style="font-size: 1.35rem;"><?= $profileAlert['type'] === 'success' ? '✅' : ($profileAlert['type'] === 'warning' ? '⚠️' : '❌') ?></span>
                <span><?= htmlspecialchars($profileAlert['message']) ?></span>
            </div>
            <a href="index.php?page=profile&tab=<?= htmlspecialchars($tab) ?>" style="color: var(--text-muted); text-decoration: none; font-size: 1.25rem;">&times;</a>
        </div>
    <?php endif; ?>

    <!-- THANH CHUYỂN ĐỔI TAB: ĐỘI NGŨ DỰ ÁN vs HỒ SƠ TÀI KHOẢN -->
    <div style="display: flex; gap: 0.75rem; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; flex-wrap: wrap;">
        <a href="index.php?page=profile&tab=team" class="sol-btn <?= $tab === 'team' ? 'sol-btn--solar' : 'sol-btn--outline' ?>" style="font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem;">
            <span>👥</span> Đội Ngũ Phát Triển (4 Thành Viên) & Biểu Đồ
        </a>
        <a href="index.php?page=profile&tab=account" class="sol-btn <?= $tab === 'account' ? 'sol-btn--solar' : 'sol-btn--outline' ?>" style="font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem;">
            <span>👤</span> Quản Lý Hồ Sơ Tài Khoản (Profile)
            <?php if (!empty($currentUser)): ?>
                <span class="sol-badge sol-badge--cyan" style="font-size: 0.7rem; padding: 0.1rem 0.45rem;">
                    <?= htmlspecialchars($currentUser['username']) ?>
                </span>
            <?php endif; ?>
        </a>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: THÔNG TIN 4 THÀNH VIÊN DỰ ÁN & BIỂU ĐỒ TRÁCH NHIỆM             -->
    <!-- ===================================================================== -->
    <?php if ($tab === 'team'): ?>

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
                                    Thành Viên <?= $member['id'] ?> <?= $member['id'] === 1 ? '• Trưởng Nhóm' : '' ?>
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

                    <!-- Thanh tỷ trọng đóng góp -->
                    <div style="border-top: 1px solid var(--border-subtle); padding-top: 0.85rem; margin-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                            <span>Đóng góp dự án:</span>
                            <strong style="color: <?= $member['badge_color'] ?>; font-family: var(--font-mono); font-size: 0.85rem;"><?= $member['weight'] ?>%</strong>
                        </div>
                        <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden;">
                            <div style="width: <?= $member['weight'] ?>%; height: 100%; background: <?= $member['badge_color'] ?>; border-radius: 999px;"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ================================================================= -->
        <!-- BIỂU ĐỒ 1: SƠ ĐỒ QUY TRÌNH PHỐI HỢP & MA TRẬN TRÁCH NHIỆM DỰ ÁN    -->
        <!-- ================================================================= -->
        <section class="sol-glass-card" style="padding: 2.25rem; margin-bottom: 3.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.35rem;">SƠ ĐỒ QUY TRÌNH DỰ ÁN (WORKFLOW & WBS)</span>
                    <h3 style="color: #ffffff; font-size: 1.4rem; font-family: var(--font-heading); margin: 0;">
                        Biểu Đồ Quy Trình Phối Hợp Giữa 4 Thành Viên Trong Dự Án
                    </h3>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">
                    <span>Mô hình phối hợp: <strong>Agile Scrum / Git Flow</strong></span>
                </div>
            </div>

            <!-- Flowchart HTML/SVG Trực Quan -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; position: relative;">
                
                <!-- Bước 1: TV1 (PM) -->
                <div style="padding: 1.25rem; background: rgba(8, 12, 20, 0.7); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: var(--radius-sm); position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span class="sol-badge sol-badge--amber" style="font-size: 0.7rem;">Bước 1 • Khởi Động</span>
                        <span style="font-size: 1.25rem;">👑</span>
                    </div>
                    <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.35rem;">TV1: Nguyễn Huy Đạt (PM)</strong>
                    <p style="color: var(--text-secondary); font-size: 0.775rem; line-height: 1.45; margin: 0;">
                        Lập kế hoạch tổng thể, thiết kế kiến trúc Router `index.php`, chuẩn hóa UI/UX và font chữ, sửa lỗi CSDL UTF-8.
                    </p>
                    <div style="margin-top: 0.85rem; font-size: 0.725rem; color: var(--accent-gold); font-weight: 600;">
                        ➔ Bàn giao khung sườn & CSDL
                    </div>
                </div>

                <!-- Bước 2: TV2 -->
                <div style="padding: 1.25rem; background: rgba(8, 12, 20, 0.7); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: var(--radius-sm); position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span class="sol-badge sol-badge--emerald" style="font-size: 0.7rem;">Bước 2 • Thiết Bị</span>
                        <span style="font-size: 1.25rem;">☀️</span>
                    </div>
                    <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.35rem;">TV2: Lê Chí Hải</strong>
                    <p style="color: var(--text-secondary); font-size: 0.775rem; line-height: 1.45; margin: 0;">
                        Xây dựng Catalog 26 sản phẩm thực tế, Module Kế hoạch dự định (`solar_cart`) và Bảng kiểm tra tương thích ghép đôi.
                    </p>
                    <div style="margin-top: 0.85rem; font-size: 0.725rem; color: #10b981; font-weight: 600;">
                        ➔ Chuyển giao thiết bị cho Demo
                    </div>
                </div>

                <!-- Bước 3: TV3 -->
                <div style="padding: 1.25rem; background: rgba(8, 12, 20, 0.7); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: var(--radius-sm); position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span class="sol-badge sol-badge--cyan" style="font-size: 0.7rem;">Bước 3 • Mô Phỏng</span>
                        <span style="font-size: 1.25rem;">🧮</span>
                    </div>
                    <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.35rem;">TV3: Mai Đức Toàn</strong>
                    <p style="color: var(--text-secondary); font-size: 0.775rem; line-height: 1.45; margin: 0;">
                        Thuật toán tính KW, tiền điện/tháng, thời gian hoàn vốn ROI; sơ đồ 2D SVG, bóc tách BOM và <strong>phụ trách tài liệu báo cáo</strong>.
                    </p>
                    <div style="margin-top: 0.85rem; font-size: 0.725rem; color: #38bdf8; font-weight: 600;">
                        ➔ Lưu báo giá sang Lắp đặt
                    </div>
                </div>

                <!-- Bước 4: TV4 -->
                <div style="padding: 1.25rem; background: rgba(8, 12, 20, 0.7); border: 1px solid rgba(251, 191, 36, 0.4); border-radius: var(--radius-sm); position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span class="sol-badge sol-badge--amber" style="font-size: 0.7rem;">Bước 4 • Triển Khai</span>
                        <span style="font-size: 1.25rem;">📋</span>
                    </div>
                    <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.35rem;">TV4: Phan Trần Thịnh</strong>
                    <p style="color: var(--text-secondary); font-size: 0.775rem; line-height: 1.45; margin: 0;">
                        Tiếp nhận báo giá, phân bổ kỹ thuật viên, quản lý 4 giai đoạn lắp đặt, tải lên biên bản khảo sát nghiệm thu PCCC/EVN.
                    </p>
                    <div style="margin-top: 0.85rem; font-size: 0.725rem; color: #fbbf24; font-weight: 600;">
                        ➔ Nghiệm thu & Báo cáo tổng thể
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
                <div style="display: flex; width: 100%; height: 24px; border-radius: 999px; overflow: hidden; background: #080c14; border: 1px solid var(--border-subtle);">
                    <div style="width: 35%; background: linear-gradient(90deg, #f59e0b, #fbbf24); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #080c14;" title="Nguyễn Huy Đạt (35%)">TV1 (35%)</div>
                    <div style="width: 20%; background: linear-gradient(90deg, #10b981, #34d399); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #080c14;" title="Lê Chí Hải (20%)">TV2 (20%)</div>
                    <div style="width: 25%; background: linear-gradient(90deg, #0284c7, #38bdf8); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #ffffff;" title="Mai Đức Toàn (25%)">TV3 (25%)</div>
                    <div style="width: 20%; background: linear-gradient(90deg, #f97316, #fb923c); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #080c14;" title="Phan Trần Thịnh (20%)">TV4 (20%)</div>
                </div>
            </div>

            <!-- Chú thích 4 thanh năng lực chi tiết -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #f59e0b;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Nguyễn Huy Đạt (TV1 - PM)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Core Arch, Routing, UI/UX, 6 Bài Tập Form (35%)</div>
                </div>

                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #10b981;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Lê Chí Hải (TV2)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Catalog 26 SP, Kế hoạch dự định, Ghép đôi (20%)</div>
                </div>

                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #38bdf8;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Mai Đức Toàn (TV3)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Simulator KW/ROI, 2D SVG, BOM, Báo Cáo Dự Án (25%)</div>
                </div>

                <div style="padding: 0.85rem; background: rgba(8, 12, 20, 0.5); border-radius: var(--radius-sm); border-left: 3px solid #fb923c;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #ffffff;">Phan Trần Thịnh (TV4)</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.2rem;">Auth 3 Cấp, Workflow Lắp Đặt, Upload Tài Liệu (20%)</div>
                </div>
            </div>
        </section>

    <!-- ===================================================================== -->
    <!-- TAB 2: CHỨC NĂNG QUẢN LÝ HỒ SƠ TÀI KHOẢN (PROFILE: ADMIN/STAFF/USER)  -->
    <!-- ===================================================================== -->
    <?php else: ?>

        <!-- KHỐI 1: BỘ CHUYỂN ĐỔI NHANH VAI TRÒ TÀI KHOẢN (QUICK ROLE SWITCHER) -->
        <section class="sol-glass-card" style="padding: 1.75rem; margin-bottom: 2rem; border-color: rgba(245, 158, 11, 0.35);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.35rem;">BỘ CHUYỂN ĐỔI TÀI KHOẢN TRẢI NGHIỆM</span>
                    <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                        Chuyển Đổi Nhanh Giữa 3 Mức Phân Quyền (Demo One-Click)
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 0.825rem; margin: 0.25rem 0 0;">
                        Nhấn vào vai trò bất kỳ để đăng nhập tức thì và kiểm thử giao diện phân quyền của đối tượng đó:
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                <?php foreach ($demoAccounts as $acc): 
                    $isCurrent = (!empty($currentUser) && $currentUser['username'] === $acc['username']);
                ?>
                    <div style="padding: 1.15rem; background: <?= $isCurrent ? 'rgba(245, 158, 11, 0.12)' : 'rgba(8, 12, 20, 0.6)' ?>; border: 1px solid <?= $isCurrent ? 'var(--accent-amber)' : 'var(--border-subtle)' ?>; border-radius: var(--radius-sm); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <span class="sol-badge <?= $acc['badge'] ?>" style="font-size: 0.7rem; padding: 0.1rem 0.45rem;">
                                    <?= htmlspecialchars($acc['level']) ?>
                                </span>
                                <?php if ($isCurrent): ?>
                                    <span style="font-size: 0.725rem; color: var(--accent-gold); font-weight: 700;">● Đang chọn</span>
                                <?php endif; ?>
                            </div>
                            <strong style="color: #ffffff; font-size: 0.95rem; display: block; margin-bottom: 0.2rem;">
                                <?= htmlspecialchars($acc['name']) ?>
                            </strong>
                            <div style="font-size: 0.8rem; color: var(--accent-cyan); font-family: var(--font-mono); margin-bottom: 0.4rem;">
                                User: <?= htmlspecialchars($acc['username']) ?> • Pass: Solaris@2026
                            </div>
                            <p style="font-size: 0.775rem; color: var(--text-secondary); line-height: 1.4; margin: 0 0 0.85rem;">
                                <?= htmlspecialchars($acc['desc']) ?>
                            </p>
                        </div>

                        <?php if ($isCurrent): ?>
                            <button class="sol-btn sol-btn--outline sol-btn--sm" disabled style="opacity: 0.6; cursor: default; width: 100%;">
                                ✓ Đang hoạt động
                            </button>
                        <?php else: ?>
                            <a href="index.php?page=profile&tab=account&switch_account=<?= htmlspecialchars($acc['username']) ?>" class="sol-btn sol-btn--solar sol-btn--sm" style="text-align: center; text-decoration: none; width: 100%;">
                                ⚡ Đổi sang vai trò này
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- KHỐI 2: CHI TIẾT THÔNG TIN HỒ SƠ TÀI KHOẢN HIỆN TẠI & THỐNG KÊ -->
        <div style="display: grid; grid-template-columns: 1fr 1.4fr; gap: 2rem; margin-bottom: 2.5rem;" class="sol-calc-grid-layout">
            
            <!-- Cột trái: Thẻ Tổng Quan Hồ Sơ Cá Nhân -->
            <div class="sol-glass-card" style="padding: 2rem 1.85rem;">
                <div style="text-align: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1.25rem;">
                    <div style="width: 76px; height: 76px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #0284c7); margin: 0 auto 0.85rem; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; border: 3px solid var(--accent-gold); box-shadow: 0 0 16px rgba(245, 158, 11, 0.4);">
                        <?= match ($currentUser['role'] ?? 'guest') {
                            'admin' => '👑',
                            'technician' => '🔧',
                            default => '👤'
                        } ?>
                    </div>
                    <h3 style="color: #ffffff; font-size: 1.35rem; font-family: var(--font-heading); margin: 0 0 0.35rem;">
                        <?= htmlspecialchars($currentUser['full_name'] ?? 'Khách Trải Nghiệm') ?>
                    </h3>
                    <div style="display: inline-block;">
                        <span class="sol-badge <?= match ($currentUser['role'] ?? 'guest') {
                            'admin' => 'sol-badge--amber',
                            'technician' => 'sol-badge--cyan',
                            default => 'sol-badge--emerald'
                        } ?>">
                            Vai trò: <?= strtoupper($currentUser['role'] ?? 'CUSTOMER') ?>
                        </span>
                    </div>
                </div>

                <!-- Chi tiết trường dữ liệu -->
                <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.85rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <span style="color: var(--text-muted);">Tên đăng nhập:</span>
                        <strong style="color: #ffffff; font-family: var(--font-mono);"><?= htmlspecialchars($currentUser['username'] ?? 'N/A') ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <span style="color: var(--text-muted);">Địa chỉ Email:</span>
                        <strong style="color: var(--accent-cyan);"><?= htmlspecialchars($currentUser['email'] ?? 'N/A') ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <span style="color: var(--text-muted);">Số điện thoại:</span>
                        <strong style="color: #ffffff; font-family: var(--font-mono);"><?= htmlspecialchars($currentUser['phone'] ?? 'Chưa cập nhật') ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <span style="color: var(--text-muted);">Thời gian khởi tạo:</span>
                        <span style="color: var(--text-secondary); font-size: 0.8rem;"><?= !empty($currentUser['created_at']) ? date('d/m/Y H:i', strtotime($currentUser['created_at'])) : 'Mặc định' ?></span>
                    </div>
                </div>

                <!-- Thống kê hoạt động theo vai trò -->
                <?php if (!empty($roleStats['items'])): ?>
                    <div style="border-top: 1px solid var(--border-subtle); padding-top: 1rem;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; margin-bottom: 0.75rem;">
                            Hoạt động & Quyền hạn thực tế:
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                            <?php foreach ($roleStats['items'] as $st): ?>
                                <div style="padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.6); border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); text-align: center;">
                                    <div style="font-size: 1.25rem; margin-bottom: 0.15rem;"><?= $st['icon'] ?></div>
                                    <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 800; color: <?= $st['color'] ?>;">
                                        <?= $st['value'] ?>
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem;">
                                        <?= htmlspecialchars($st['label']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột phải: 2 Form Cập Nhật Hồ Sơ & Đổi Mật Khẩu -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                
                <!-- Form 1: Cập Nhật Thông Tin Cá Nhân -->
                <div class="sol-glass-card" style="padding: 1.85rem;">
                    <div style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
                        <h4 style="color: #ffffff; font-size: 1.15rem; font-family: var(--font-heading); margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                            <span>✏️</span> Cập Nhật Thông Tin Cá Nhân
                        </h4>
                    </div>

                    <form method="POST" action="index.php?page=profile&tab=account">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="user_id" value="<?= (int)($currentUser['id'] ?? 0) ?>">

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">
                                    Họ và tên hiển thị <span style="color: var(--accent-rose);">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="full_name" 
                                    required 
                                    value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>"
                                    style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.875rem;"
                                >
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">
                                    Số điện thoại liên hệ
                                </label>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"
                                    placeholder="Ví dụ: 0912 345 678"
                                    style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.875rem;"
                                >
                            </div>
                        </div>

                        <div style="margin-bottom: 1.25rem;">
                            <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">
                                Địa chỉ Email <span style="color: var(--accent-rose);">*</span>
                            </label>
                            <input 
                                type="email" 
                                name="email" 
                                required 
                                value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
                                style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.875rem;"
                            >
                        </div>

                        <button type="submit" class="sol-btn sol-btn--solar" style="font-size: 0.875rem;">
                            💾 Lưu Thay Đổi Hồ Sơ
                        </button>
                    </form>
                </div>

                <!-- Form 2: Đổi Mật Khẩu Tài Khoản -->
                <div class="sol-glass-card" style="padding: 1.85rem;">
                    <div style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
                        <h4 style="color: #ffffff; font-size: 1.15rem; font-family: var(--font-heading); margin: 0; display: flex; align-items: center; gap: 0.45rem;">
                            <span>🔒</span> Đổi Mật Khẩu Tài Khoản
                        </h4>
                    </div>

                    <form method="POST" action="index.php?page=profile&tab=account">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="user_id" value="<?= (int)($currentUser['id'] ?? 0) ?>">

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">
                                Mật khẩu hiện tại <span style="color: var(--accent-rose);">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="current_password" 
                                required 
                                placeholder="Nhập mật khẩu hiện tại (Ví dụ: Solaris@2026)"
                                style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.875rem;"
                            >
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                            <div>
                                <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">
                                    Mật khẩu mới <span style="color: var(--accent-rose);">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    name="new_password" 
                                    required 
                                    placeholder="Tối thiểu 6 ký tự"
                                    style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.875rem;"
                                >
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.825rem; color: #cbd5e1; font-weight: 600; margin-bottom: 0.35rem;">
                                    Xác nhận mật khẩu mới <span style="color: var(--accent-rose);">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    name="confirm_password" 
                                    required 
                                    placeholder="Nhập lại mật khẩu mới"
                                    style="width: 100%; padding: 0.65rem 0.85rem; background: rgba(8, 12, 20, 0.7); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); color: #ffffff; font-size: 0.875rem;"
                                >
                            </div>
                        </div>

                        <button type="submit" class="sol-btn sol-btn--outline" style="font-size: 0.875rem;">
                            🔑 Cập Nhật Mật Khẩu Mới
                        </button>
                    </form>
                </div>

            </div>
        </div>

    <?php endif; ?>

</div>
