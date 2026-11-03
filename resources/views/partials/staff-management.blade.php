<?php
/**
 * View Partial: resources/views/partials/staff-management.blade.php
 * Phân hệ Quản Lý Nhân Sự & Kỹ Thuật Viên (Dành riêng cho Quản trị viên Admin)
 * Phụ trách: Thành viên 1 - Nguyễn Huy Đạt (Project Lead & Core Dev)
 */
?>

<div id="staff-section" style="margin-top: 2rem;">
    <!-- THỐNG KÊ NHANH NHÂN SỰ KỸ THUẬT -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
        <div class="sol-glass-card" style="padding: 1.25rem; border-left: 3px solid #38bdf8; display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 2.2rem;">👨‍🔧</div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Tổng Kỹ Thuật Viên</div>
                <div style="font-size: 1.55rem; font-weight: 800; color: #38bdf8;"><?= count($technicians ?? []) ?> nhân sự</div>
                <div style="font-size: 0.72rem; color: #64748b;">Đội ngũ kỹ thuật trực tiếp thi công</div>
            </div>
        </div>

        <div class="sol-glass-card" style="padding: 1.25rem; border-left: 3px solid #f59e0b; display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 2.2rem;">🏗️</div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Công Trình Phụ Trách</div>
                <div style="font-size: 1.55rem; font-weight: 800; color: #f59e0b;">
                    <?= array_sum(array_column($technicians ?? [], 'project_count')) ?> dự án
                </div>
                <div style="font-size: 0.72rem; color: #64748b;">Đang thi công và giám sát an toàn</div>
            </div>
        </div>

        <div class="sol-glass-card" style="padding: 1.25rem; border-left: 3px solid #10b981; display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 2.2rem;">🛡️</div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Phân Quyền Khởi Tạo</div>
                <div style="font-size: 1.15rem; font-weight: 800; color: #10b981;">Admin Độc Quyền</div>
                <div style="font-size: 0.72rem; color: #64748b;">Chỉ Admin mới có quyền tạo KTV</div>
            </div>
        </div>
    </div>

    <!-- KHỐI FORM: KHỞI TẠO TÀI KHOẢN KỸ THUẬT VIÊN MỚI -->
    <section class="sol-glass-card" style="padding: 1.85rem; margin-bottom: 2rem; border-top: 3px solid #f59e0b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span class="sol-badge sol-badge--amber" style="margin-bottom: 0.25rem;">ADMIN PORTAL • CẤP QUYỀN NHÂN SỰ</span>
                <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                    ➕ Khởi Tạo Tài Khoản Kỹ Thuật Viên Mới
                </h3>
            </div>
            <div style="font-size: 0.8rem; color: #cbd5e1; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 6px; padding: 0.35rem 0.75rem;">
                🔑 Mật khẩu mặc định nếu để trống: <code style="color: #fbbf24; font-weight: 700;">Solaris@2026</code>
            </div>
        </div>

        <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 1.25rem; line-height: 1.5;">
            Trang đăng ký công khai ngoài trang chủ chỉ dành cho <strong>Khách hàng</strong>. Tài khoản <strong>Kỹ thuật viên</strong> do Quản trị viên (Admin) tạo tại đây để phân công đảm nhận các công trình lắp đặt điện mặt trời.
        </p>

        <form method="POST" action="index.php?page=profile&tab=staff">
            <input type="hidden" name="profile_action" value="create_technician">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                        👤 Họ và Tên Kỹ Thuật Viên <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="full_name" required placeholder="Ví dụ: Hoàng Văn Nam" style="width: 100%; padding: 0.7rem 0.95rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; color: #ffffff; font-size: 0.875rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                        🔑 Tên Đăng Nhập (Username)
                    </label>
                    <input type="text" name="username" placeholder="Ví dụ: kythuat.nam (để trống tự sinh từ email)" style="width: 100%; padding: 0.7rem 0.95rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; color: #ffffff; font-size: 0.875rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                        ✉️ Địa Chỉ Gmail / Email <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="email" name="email" required placeholder="Ví dụ: nam.hv@solaris.vn" style="width: 100%; padding: 0.7rem 0.95rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; color: #ffffff; font-size: 0.875rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                        📞 Số Điện Thoại
                    </label>
                    <input type="tel" name="phone" placeholder="0909777xxx" style="width: 100%; padding: 0.7rem 0.95rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; color: #ffffff; font-size: 0.875rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                        🔒 Mật Khẩu Khởi Tạo
                    </label>
                    <input type="password" name="password" placeholder="Mặc định: Solaris@2026 nếu để trống" style="width: 100%; padding: 0.7rem 0.95rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 8px; color: #ffffff; font-size: 0.875rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                        🏷️ Vai Trò Tài Khoản Cấp
                    </label>
                    <div style="padding: 0.65rem 0.95rem; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 8px; color: #38bdf8; font-size: 0.875rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔧</span>
                        <span>Kỹ Thuật Viên (Technician) • Mức 2 Hệ Thống</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="reset" class="sol-btn sol-btn--outline" style="font-size: 0.875rem; padding: 0.7rem 1.25rem;">
                    Làm mới form
                </button>
                <button type="submit" class="sol-btn sol-btn--solar" style="padding: 0.7rem 1.75rem; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);">
                    <span>💾</span> Khởi Tạo Tài Khoản Kỹ Thuật Viên
                </button>
            </div>
        </form>
    </section>

    <!-- BẢNG DANH SÁCH TẤT CẢ KỸ THUẬT VIÊN -->
    <section class="sol-glass-card" style="padding: 1.85rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span class="sol-badge sol-badge--cyan" style="margin-bottom: 0.25rem;">DANH SÁCH NHÂN SỰ HIỆN TRƯỜNG</span>
                <h3 style="color: #ffffff; font-size: 1.25rem; font-family: var(--font-heading); margin: 0;">
                    👷 Danh Sách Kỹ Thuật Viên (<?= count($technicians ?? []) ?> Nhân Sự)
                </h3>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="index.php?page=installations" class="sol-btn sol-btn--solar sol-btn--sm" style="font-size: 0.8rem;">
                    📋 Sang Module Phân Bổ Công Trình
                </a>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem; color: #ffffff;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-subtle); background: rgba(8, 12, 20, 0.7); text-align: left;">
                        <th style="padding: 0.75rem 1rem;">ID</th>
                        <th style="padding: 0.75rem 1rem;">Họ Tên Kỹ Thuật Viên</th>
                        <th style="padding: 0.75rem 1rem;">Tài Khoản (Username)</th>
                        <th style="padding: 0.75rem 1rem;">Email & Số Điện Thoại</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Dự Án Phụ Trách</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Lượt Phân Công</th>
                        <th style="padding: 0.75rem 1rem;">Ngày Khởi Tạo</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($technicians)): ?>
                        <?php foreach ($technicians as $tech): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--text-muted);">
                                    #<?= $tech['id'] ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; font-weight: 600;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(56, 189, 248, 0.2); border: 1px solid #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                            🔧
                                        </div>
                                        <div>
                                            <div style="color: #ffffff; font-weight: 700;"><?= htmlspecialchars($tech['full_name']) ?></div>
                                            <span class="sol-badge sol-badge--cyan" style="font-size: 0.65rem; padding: 0.05rem 0.4rem;">Kỹ thuật viên</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 0.75rem 1rem; font-family: var(--font-mono); color: var(--accent-amber); font-weight: 600;">
                                    <?= htmlspecialchars($tech['username']) ?>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <div style="color: #cbd5e1;"><?= htmlspecialchars($tech['email']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);"><?= htmlspecialchars($tech['phone'] ?? 'N/A') ?></div>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <span class="sol-badge <?= (int)$tech['project_count'] > 0 ? 'sol-badge--amber' : '' ?>" style="font-size: 0.75rem; padding: 0.15rem 0.55rem; <?= (int)$tech['project_count'] === 0 ? 'background: rgba(255,255,255,0.05); color: #94a3b8;' : '' ?>">
                                        <?= (int)$tech['project_count'] ?> công trình
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center; color: var(--accent-cyan); font-weight: 700;">
                                    <?= (int)$tech['assignment_count'] ?> lượt
                                </td>
                                <td style="padding: 0.75rem 1rem; font-size: 0.75rem; color: var(--text-muted);">
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($tech['created_at'] ?? 'now'))) ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="index.php?page=installations" class="sol-btn sol-btn--outline sol-btn--sm" style="font-size: 0.75rem; padding: 0.3rem 0.7rem; border-color: rgba(56, 189, 248, 0.4); color: #38bdf8;">
                                        📋 Phân công
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 1.5rem; text-align: center; color: var(--text-muted);">
                                Chưa có tài khoản kỹ thuật viên nào trong hệ thống.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
