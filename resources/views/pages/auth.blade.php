<?php
/**
 * View Giao Diện Đăng Nhập / Đăng Ký Tài Khoản Gmail & Xác Thực Mã OTP 6 Số
 * Phong cách Dark Solar Tech Glassmorphism đồng bộ Solaris v2.1
 * Phân hệ: Member 1 - Team Lead (Nguyễn Huy Đạt)
 */

$authStep = $authData['step'] ?? 'login';
$authError = $authData['error'] ?? null;
$authSuccess = $authData['success'] ?? null;
$pendingReg = $authData['pending'] ?? null;
$lastMail = $authData['last_mail'] ?? null;
?>

<div class="sol-auth-page">
    <div class="sol-auth-container">
        <!-- BRAND HEADER -->
        <div class="sol-auth-header">
            <a href="index.php?page=home" class="sol-brand" style="justify-content: center; margin-bottom: 0.75rem;">
                <div class="sol-brand__logo" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <img src="assets/solar-logo.svg" alt="Solaris Logo" style="width: 30px; height: 30px;">
                </div>
                <div class="sol-brand__text" style="text-align: left;">
                    <span class="sol-brand__name" style="font-size: 1.6rem; letter-spacing: 2px;">SOLAR<span>IS</span></span>
                    <span class="sol-brand__tagline" style="font-size: 0.75rem;">Hệ Sinh Thái Năng Lượng Mặt Trời Thông Minh</span>
                </div>
            </a>
        </div>

        <!-- AUTH GLASS CARD -->
        <div class="sol-auth-card">
            <!-- NOTIFICATIONS -->
            <?php if (!empty($authError)): ?>
                <div class="sol-alert sol-alert--danger" style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1rem; border-radius: 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; font-size: 0.875rem;">
                    <span style="font-size: 1.2rem;">⚠️</span>
                    <div><?= htmlspecialchars($authError) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($authSuccess)): ?>
                <div class="sol-alert sol-alert--success" style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1rem; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; font-size: 0.875rem;">
                    <span style="font-size: 1.2rem;">✅</span>
                    <div><?= htmlspecialchars($authSuccess) ?></div>
                </div>
            <?php endif; ?>

            <!-- NAVIGATION TABS (Chỉ hiển thị khi không ở bước verify_otp) -->
            <?php if ($authStep !== 'verify_otp'): ?>
                <div class="sol-auth-tabs">
                    <a href="index.php?page=auth&step=login" class="sol-auth-tab <?= $authStep === 'login' ? 'active' : '' ?>">
                        <span>🔑 Đăng Nhập</span>
                    </a>
                    <a href="index.php?page=auth&step=register" class="sol-auth-tab <?= $authStep === 'register' ? 'active' : '' ?>">
                        <span>✉️ Đăng Ký (Gmail OTP)</span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- ============================================================= -->
            <!-- 1. FORM ĐĂNG NHẬP                                             -->
            <!-- ============================================================= -->
            <?php if ($authStep === 'login'): ?>
                <div class="sol-auth-body">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <h3 style="color: #ffffff; font-size: 1.35rem; font-weight: 700; margin: 0 0 0.4rem;">Chào Mừng Trở Lại!</h3>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">
                            Đăng nhập để quản lý công trình, theo dõi tiến độ và tối ưu hóa giải pháp năng lượng mặt trời.
                        </p>
                    </div>

                    <form method="POST" action="index.php?page=auth" class="sol-auth-form">
                        <input type="hidden" name="auth_action" value="login">

                        <div class="sol-form-group" style="margin-bottom: 1.2rem;">
                            <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem;">
                                👤 Tên Đăng Nhập hoặc Gmail
                            </label>
                            <div class="sol-input-wrap">
                                <input type="text" name="identity" required class="sol-input" placeholder="admin_pro hoặc yourname@gmail.com" autofocus style="width: 100%; padding: 0.75rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                            </div>
                        </div>

                        <div class="sol-form-group" style="margin-bottom: 1.2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                                <label class="sol-form-label" style="font-size: 0.825rem; font-weight: 600; color: #cbd5e1;">
                                    🔒 Mật Khẩu
                                </label>
                                <a href="javascript:void(0)" onclick="alert('Mật khẩu mẫu chung của tất cả tài khoản kiểm thử là: Solaris@2026')" style="font-size: 0.78rem; color: var(--accent-gold); text-decoration: none;">Quên mật khẩu?</a>
                            </div>
                            <div class="sol-input-wrap" style="position: relative;">
                                <input type="password" id="loginPassword" name="password" required class="sol-input" placeholder="Nhập mật khẩu của bạn" style="width: 100%; padding: 0.75rem 2.8rem 0.75rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                                <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1rem;">👁️</button>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.825rem; color: var(--text-secondary);">
                            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                                <input type="checkbox" name="remember" checked style="accent-color: var(--accent-gold);">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <span style="color: #64748b;">Mật khẩu demo: <code style="color: #f59e0b;">Solaris@2026</code></span>
                        </div>

                        <button type="submit" class="sol-btn sol-btn--solar" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);">
                            ⚡ Đăng Nhập Vào Hệ Thống
                        </button>
                    </form>

                    <!-- HOẶC ĐĂNG NHẬP NHANH TÀI KHOẢN KIỂM THỬ -->
                    <div class="sol-auth-divider" style="display: flex; align-items: center; margin: 1.75rem 0 1.25rem; text-align: center; color: var(--text-muted); font-size: 0.78rem;">
                        <span style="flex: 1; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"></span>
                        <span style="padding: 0 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Hoặc Đăng Nhập Nhanh 1-Click (Dành Cho Hội Đồng Chấm Thi)</span>
                        <span style="flex: 1; border-bottom: 1px solid rgba(255, 255, 255, 0.1);"></span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.65rem;">
                        <a href="index.php?page=auth&switch_account=admin_pro" class="sol-quick-role-btn sol-quick-role-btn--admin" title="Vào vai Quản trị viên cấp cao">
                            <span style="font-size: 1.25rem;">🛡️</span>
                            <span style="font-weight: 700; font-size: 0.8rem; color: #fbbf24;">Admin Pro</span>
                            <span style="font-size: 0.7rem; color: #94a3b8;">Toàn quyền</span>
                        </a>

                        <a href="index.php?page=auth&switch_account=tech_pro" class="sol-quick-role-btn sol-quick-role-btn--tech" title="Vào vai Kỹ thuật viên trưởng">
                            <span style="font-size: 1.25rem;">🔧</span>
                            <span style="font-weight: 700; font-size: 0.8rem; color: #38bdf8;">Tech Pro</span>
                            <span style="font-size: 0.7rem; color: #94a3b8;">Khảo sát & Lắp</span>
                        </a>

                        <a href="index.php?page=auth&switch_account=customer_pro" class="sol-quick-role-btn sol-quick-role-btn--customer" title="Vào vai Khách hàng thực tế">
                            <span style="font-size: 1.25rem;">👤</span>
                            <span style="font-weight: 700; font-size: 0.8rem; color: #34d399;">Customer</span>
                            <span style="font-size: 0.7rem; color: #94a3b8;">Báo giá & Hồ sơ</span>
                        </a>
                    </div>
                </div>

            <!-- ============================================================= -->
            <!-- 2. FORM ĐĂNG KÝ BẰNG GMAIL (GỬI MÃ OTP)                       -->
            <!-- ============================================================= -->
            <?php elseif ($authStep === 'register'): ?>
                <div class="sol-auth-body">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <h3 style="color: #ffffff; font-size: 1.35rem; font-weight: 700; margin: 0 0 0.4rem;">Đăng Ký Tài Khoản Qua Gmail</h3>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">
                            Hệ thống sẽ gửi <strong style="color: #f59e0b;">mã xác nhận OTP 6 số</strong> đến địa chỉ Gmail của bạn để kích hoạt.
                        </p>
                    </div>

                    <form method="POST" action="index.php?page=auth" class="sol-auth-form">
                        <input type="hidden" name="auth_action" value="register">

                        <div class="sol-form-group" style="margin-bottom: 1rem;">
                            <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                                👤 Họ Và Tên <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" class="sol-input" placeholder="Ví dụ: Nguyễn Văn Hoàng" style="width: 100%; padding: 0.7rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                        </div>

                        <div class="sol-form-group" style="margin-bottom: 1rem;">
                            <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                                ✉️ Địa Chỉ Gmail Nhận Mã OTP <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="sol-input" placeholder="vidu@gmail.com" style="width: 100%; padding: 0.7rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(56, 189, 248, 0.5); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                            <div style="font-size: 0.75rem; color: #38bdf8; margin-top: 0.25rem;">
                                ℹ️ Mã xác thực 6 số sẽ được gửi trực tiếp đến hòm thư Gmail này.
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.75rem;">
                            <div class="sol-form-group">
                                <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                                    📞 Số Điện Thoại
                                </label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" class="sol-input" placeholder="0909xxxxxx" style="width: 100%; padding: 0.7rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                            </div>

                            <div class="sol-form-group">
                                <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                                    🏷️ Loại Tài Khoản
                                </label>
                                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 0.85rem; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 8px; color: #34d399; font-size: 0.85rem; font-weight: 600;">
                                    <span>👤</span>
                                    <span>Khách Hàng (Customer)</span>
                                </div>
                                <input type="hidden" name="role" value="customer">
                            </div>
                        </div>
                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: -0.25rem; margin-bottom: 1rem; line-height: 1.4;">
                            ℹ️ Đăng ký công khai áp dụng mặc định cho <strong>Khách hàng</strong>. Tài khoản Kỹ thuật viên do Quản trị viên cấp trong mục <em>Quản lý nhân viên</em>.
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 1.4rem;">
                            <div class="sol-form-group">
                                <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                                    🔒 Mật Khẩu (≥ 6 ký tự) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="password" name="password" required class="sol-input" placeholder="Tối thiểu 6 ký tự" style="width: 100%; padding: 0.7rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                            </div>

                            <div class="sol-form-group">
                                <label class="sol-form-label" style="display: block; font-size: 0.825rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.35rem;">
                                    🔒 Xác Nhận Mật Khẩu <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="password" name="confirm_password" required class="sol-input" placeholder="Nhập lại mật khẩu" style="width: 100%; padding: 0.7rem 1rem; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #ffffff; font-size: 0.9rem;">
                            </div>
                        </div>

                        <button type="submit" class="sol-btn sol-btn--solar" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);">
                            ✉️ Gửi Mã Xác Nhận OTP Đến Gmail
                        </button>
                    </form>
                </div>

            <!-- ============================================================= -->
            <!-- 3. MÀN HÌNH NHẬP MÃ XÁC THỰC OTP 6 SỐ TỪ GMAIL                -->
            <!-- ============================================================= -->
            <?php elseif ($authStep === 'verify_otp'): ?>
                <div class="sol-auth-body">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 0.75rem; background: rgba(245, 158, 11, 0.15); border: 2px solid #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
                            📩
                        </div>
                        <h3 style="color: #ffffff; font-size: 1.4rem; font-weight: 700; margin: 0 0 0.4rem;">Xác Thực Địa Chỉ Gmail</h3>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0 0 0.5rem;">
                            Hệ thống đã gửi mã xác nhận 6 số đến địa chỉ:
                        </p>
                        <div style="display: inline-block; padding: 0.3rem 0.85rem; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: 999px; color: #38bdf8; font-weight: 700; font-size: 0.9rem;">
                            <?= htmlspecialchars($pendingReg['email'] ?? 'yourname@gmail.com') ?>
                        </div>
                    </div>

                    <!-- FORM NHẬP 6 SỐ OTP -->
                    <form method="POST" action="index.php?page=auth" id="otpForm" class="sol-auth-form">
                        <input type="hidden" name="auth_action" value="verify_otp">
                        <input type="hidden" name="otp_code" id="finalOtpCode" value="">

                        <div style="margin-bottom: 1.5rem;">
                            <div style="text-align: center; font-size: 0.825rem; color: #94a3b8; margin-bottom: 0.75rem;">
                                NHẬP 6 CHỮ SỐ MÃ XÁC NHẬN
                            </div>
                            <div class="sol-otp-inputs" style="display: flex; justify-content: center; gap: 0.65rem;">
                                <?php for ($i = 0; $i < 6; $i++): ?>
                                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" class="sol-otp-digit" data-index="<?= $i ?>" style="width: 48px; height: 56px; text-align: center; font-size: 1.6rem; font-weight: 800; color: #fbbf24; background: rgba(15, 23, 42, 0.9); border: 2px solid rgba(245, 158, 11, 0.4); border-radius: 8px; font-family: monospace; transition: all 0.2s ease;">
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.85rem;">
                            <div style="color: var(--text-muted); display: flex; align-items: center; gap: 0.35rem;">
                                <span>⏱️ Hiệu lực:</span>
                                <strong id="otpCountdown" style="color: #f59e0b;">10:00</strong>
                            </div>

                            <!-- Nút mở Hộp thư Gmail mô phỏng -->
                            <?php if ($lastMail): ?>
                                <button type="button" onclick="openSimulatedMailModal()" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); color: #38bdf8; border-radius: 6px; padding: 0.35rem 0.75rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>📬 Xem Hộp Thư Gmail</span>
                                    <span style="background: #38bdf8; color: #0b1120; font-size: 0.7rem; border-radius: 999px; padding: 0 5px; font-weight: 800;">1</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <button type="submit" id="btnVerifyOtp" class="sol-btn sol-btn--solar" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35); margin-bottom: 1rem;">
                            ✅ Xác Nhận Mã OTP & Kích Hoạt
                        </button>
                    </form>

                    <!-- HÀNH ĐỘNG PHỤ: GỬI LẠI MÃ HOẶC SỬA GMAIL -->
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.825rem; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 1rem;">
                        <form method="POST" action="index.php?page=auth" style="margin: 0;">
                            <input type="hidden" name="auth_action" value="resend_otp">
                            <button type="submit" style="background: none; border: none; color: #38bdf8; cursor: pointer; font-size: 0.825rem; font-weight: 600; text-decoration: underline; padding: 0;">
                                🔄 Gửi lại mã OTP mới
                            </button>
                        </form>

                        <a href="index.php?page=auth&step=register" style="color: var(--text-muted); text-decoration: none;">
                            ← Thay đổi Gmail khác
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FOOTER DỰ ÁN TRÊN THẺ AUTH -->
            <div class="sol-auth-footer" style="padding: 1rem 1.75rem; background: rgba(0, 0, 0, 0.3); border-top: 1px solid rgba(255, 255, 255, 0.06); text-align: center; font-size: 0.78rem; color: #64748b;">
                Hệ Thống Quản Lý, Tư Vấn & Lắp Đặt Điện Mặt Trời SOLARIS v2.1 • Hotline: 0909.888.SOLAR
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL HỘP THƯ GMAIL MÔ PHỎNG (SOLARIS DEV GMAIL SANDBOX)                  -->
<!-- ========================================================================= -->
<?php if ($lastMail): ?>
<div class="sol-mail-modal-overlay" id="simulatedMailModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 999999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(8px);">
    <div class="sol-mail-modal" style="background: #0f172a; border: 1px solid rgba(56, 189, 248, 0.5); border-radius: 14px; width: 100%; max-width: 580px; box-shadow: 0 20px 50px rgba(0,0,0,0.8); overflow: hidden; animation: popIn 0.25s ease;">
        <!-- TOPBAR GMAIL -->
        <div style="background: #1e293b; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                <span style="font-size: 1.2rem;">📬</span>
                <span style="font-weight: 700; color: #ffffff; font-size: 0.95rem;">Hộp Thư Đến Gmail (PHPMailer & SMTP)</span>
                <?php if (!empty($lastMail['is_live'])): ?>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 999px; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; font-weight: 700;">🟢 Gmail SMTP Live</span>
                <?php else: ?>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 999px; background: rgba(245, 158, 11, 0.2); border: 1px solid #f59e0b; color: #fbbf24; font-weight: 700;">🟠 Sandbox Mô Phỏng</span>
                <?php endif; ?>
            </div>
            <button type="button" onclick="closeSimulatedMailModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.2rem; cursor: pointer;">✕</button>
        </div>

        <!-- THÔNG TIN THƯ -->
        <div style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(15, 23, 42, 0.6); font-size: 0.825rem; color: #cbd5e1;">
            <div style="margin-bottom: 0.35rem;"><strong>Từ:</strong> SOLARIS Energy System &lt;no-reply@solaris.vn&gt;</div>
            <div style="margin-bottom: 0.35rem;"><strong>Đến:</strong> <?= htmlspecialchars($lastMail['to']) ?></div>
            <div style="margin-bottom: 0.35rem;"><strong>Tiêu đề:</strong> <span style="color: #fbbf24;"><?= htmlspecialchars($lastMail['subject']) ?></span></div>
            <div style="margin-bottom: 0.35rem;"><strong>Giao thức:</strong> <code style="color: #38bdf8; font-family: monospace;">PHPMailer 7.1 / smtp.gmail.com:587 TLS</code></div>
            <div><strong>Thời gian gửi:</strong> <?= htmlspecialchars($lastMail['time']) ?></div>
        </div>

        <!-- NỘI DUNG THƯ VÀ NÚT COPY OTP -->
        <div style="padding: 1.5rem; text-align: center; background: #080c14;">
            <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.5rem;">MÃ XÁC THỰC OTP GMAIL CỦA BẠN LÀ:</div>
            <div style="display: inline-block; padding: 0.75rem 1.75rem; background: rgba(245, 158, 11, 0.15); border: 2px dashed #f59e0b; border-radius: 10px; margin-bottom: 1rem;">
                <span id="copyableOtp" style="font-size: 2.2rem; font-weight: 900; letter-spacing: 6px; color: #fbbf24; font-family: monospace;"><?= htmlspecialchars($lastMail['otp']) ?></span>
            </div>

            <div style="display: flex; justify-content: center; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" onclick="autoFillOtp('<?= htmlspecialchars($lastMail['otp']) ?>')" class="sol-btn sol-btn--solar sol-btn--sm" style="padding: 0.5rem 1.2rem; font-weight: 700;">
                    ⚡ Tự Động Điền & Xác Nhận Ngay
                </button>
                <button type="button" onclick="copyOtpText('<?= htmlspecialchars($lastMail['otp']) ?>')" class="sol-btn sol-btn--outline sol-btn--sm" style="padding: 0.5rem 1rem;">
                    📋 Sao Chép Mã
                </button>
            </div>
        </div>

        <div style="padding: 0.75rem 1.25rem; background: #1e293b; text-align: right; border-top: 1px solid rgba(255,255,255,0.1);">
            <button type="button" onclick="closeSimulatedMailModal()" class="sol-btn sol-btn--sm" style="background: rgba(255,255,255,0.1); color: #ffffff;">Đóng Hộp Thư</button>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* CSS CHO TRANG AUTH & OTP */
.sol-auth-page {
    min-height: 90vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    position: relative;
    z-index: 10;
}
.sol-auth-container {
    width: 100%;
    max-width: 480px;
}
.sol-auth-card {
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(245, 158, 11, 0.35);
    border-radius: 16px;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6), 0 0 30px rgba(245, 158, 11, 0.12);
    overflow: hidden;
    animation: fadeInScale 0.35s ease-out;
}
.sol-auth-tabs {
    display: flex;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(0, 0, 0, 0.2);
}
.sol-auth-tab {
    flex: 1;
    text-align: center;
    padding: 1rem 0.5rem;
    color: var(--text-muted);
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
}
.sol-auth-tab:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.03);
}
.sol-auth-tab.active {
    color: var(--accent-gold);
    border-bottom-color: var(--accent-gold);
    background: rgba(245, 158, 11, 0.08);
}
.sol-auth-body {
    padding: 2rem 1.75rem;
}
.sol-otp-digit:focus {
    outline: none;
    border-color: #38bdf8 !important;
    box-shadow: 0 0 12px rgba(56, 189, 248, 0.5);
    transform: scale(1.05);
}
.sol-quick-role-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.75rem 0.5rem;
    border-radius: 8px;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    transition: all 0.2s ease;
}
.sol-quick-role-btn:hover {
    transform: translateY(-2px);
    border-color: var(--accent-gold);
    background: rgba(245, 158, 11, 0.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
@keyframes fadeInScale {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
// XỬ LÝ 6 Ô NHẬP MÃ OTP THÔNG MINH
document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.sol-otp-digit');
    const finalOtpInput = document.getElementById('finalOtpCode');
    const otpForm = document.getElementById('otpForm');

    if (otpInputs.length > 0) {
        otpInputs[0].focus();

        otpInputs.forEach((input, index) => {
            // Khi gõ số
            input.addEventListener('input', function(e) {
                const val = this.value.replace(/[^0-9]/g, '');
                this.value = val ? val[0] : '';
                
                updateFinalOtp();

                if (this.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            // Khi nhấn phím Backspace hoặc mũi tên
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                } else if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                } else if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            // Khi paste (dán) cả chuỗi 6 số
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                if (pasteData.length >= 6) {
                    for (let i = 0; i < 6; i++) {
                        otpInputs[i].value = pasteData[i] || '';
                    }
                    updateFinalOtp();
                    otpInputs[5].focus();
                }
            });
        });

        function updateFinalOtp() {
            let combined = '';
            otpInputs.forEach(inp => combined += inp.value);
            if (finalOtpInput) finalOtpInput.value = combined;
        }

        if (otpForm) {
            otpForm.addEventListener('submit', function(e) {
                updateFinalOtp();
                if (finalOtpInput.value.length < 6) {
                    e.preventDefault();
                    alert('Vui lòng nhập đầy đủ 6 chữ số mã xác nhận OTP.');
                }
            });
        }

        // Đếm ngược 10 phút
        let secondsLeft = 600;
        const countdownEl = document.getElementById('otpCountdown');
        if (countdownEl) {
            const timer = setInterval(() => {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    countdownEl.innerText = 'Đã hết hạn';
                    countdownEl.style.color = '#ef4444';
                } else {
                    const m = Math.floor(secondsLeft / 60);
                    const s = secondsLeft % 60;
                    countdownEl.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                }
            }, 1000);
        }
    }
});

function togglePasswordVisibility(id, btn) {
    const inp = document.getElementById(id);
    if (inp) {
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerText = '🙈';
        } else {
            inp.type = 'password';
            btn.innerText = '👁️';
        }
    }
}

function openSimulatedMailModal() {
    const modal = document.getElementById('simulatedMailModal');
    if (modal) modal.style.display = 'flex';
}

function closeSimulatedMailModal() {
    const modal = document.getElementById('simulatedMailModal');
    if (modal) modal.style.display = 'none';
}

function autoFillOtp(otp) {
    const otpInputs = document.querySelectorAll('.sol-otp-digit');
    if (otpInputs.length === 6 && otp.length === 6) {
        for (let i = 0; i < 6; i++) {
            otpInputs[i].value = otp[i];
        }
        const finalOtpInput = document.getElementById('finalOtpCode');
        if (finalOtpInput) finalOtpInput.value = otp;
        closeSimulatedMailModal();
        const form = document.getElementById('otpForm');
        if (form) form.submit();
    }
}

function copyOtpText(otp) {
    navigator.clipboard.writeText(otp).then(() => {
        alert('Đã sao chép mã OTP: ' + otp);
    }).catch(() => {
        prompt('Sao chép mã OTP:', otp);
    });
}
</script>
