<x-layouts.master title="SOLARIS - Hồ Sơ Năng Lực 4 Thành Viên" activeTab="profile">
    <div class="sol-container" style="padding-top: 3rem; padding-bottom: 5rem;">
        <div class="sol-section-header">
            <span class="sol-badge sol-badge--corona">NHÓM PHÁT TRIỂN</span>
            <h2 class="sol-section-header__title">Hồ Sơ Năng Lực 4 Thành Viên</h2>
            <p class="sol-section-header__desc">
                Đội ngũ kỹ sư phát triển Hệ thống Năng lượng Mặt trời Solaris. Phân chia vai trò rõ ràng, chuyên nghiệp và phối hợp đồng bộ.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-top: 2.5rem;">
            <!-- TV1: Bạn - Team Lead -->
            <div class="solar-glass-card" style="padding: 2rem; text-align: center;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #004e89); margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 2px solid var(--solar-corona);">
                    👤
                </div>
                <h3 style="color: #ffffff; font-size: 1.25rem; margin-bottom: 0.35rem;">Thành Viên 1 (Bạn)</h3>
                <div style="color: var(--solar-corona); font-size: 0.825rem; font-weight: 700; margin-bottom: 1rem;">TEAM LEAD & AUTH CORE</div>
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.25rem;">
                    Quản trị dự án, thiết kế kiến trúc hệ thống, phát triển trang Home, Profile, Module Auth (Login/Session/Cookies) và 6 bài tập Form.
                </p>
                <span class="sol-badge sol-badge--corona">Phụ trách: Home & Profile</span>
            </div>

            <!-- TV2: Frontend Lead -->
            <div class="solar-glass-card" style="padding: 2rem; text-align: center;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #0f172a); margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 2px solid var(--emerald-orbit);">
                    ☀️
                </div>
                <h3 style="color: #ffffff; font-size: 1.25rem; margin-bottom: 0.35rem;">Thành Viên 2</h3>
                <div style="color: var(--emerald-orbit); font-size: 0.825rem; font-weight: 700; margin-bottom: 1rem;">FRONTEND LEAD & PRODUCTS</div>
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.25rem;">
                    Phát triển trang Giải pháp & Sản phẩm, Catalog tấm pin, Inverter, bộ lọc tìm kiếm và phụ trách 6 bài tập (Form + Mảng/Chuỗi).
                </p>
                <span class="sol-badge sol-badge--emerald">Phụ trách: Giải pháp & SP</span>
            </div>

            <!-- TV3: Fullstack Simulator -->
            <div class="solar-glass-card" style="padding: 2rem; text-align: center;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #38bdf8, #0f172a); margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 2px solid var(--ocean-sky);">
                    🧮
                </div>
                <h3 style="color: #ffffff; font-size: 1.25rem; margin-bottom: 0.35rem;">Thành Viên 3</h3>
                <div style="color: var(--ocean-sky); font-size: 0.825rem; font-weight: 700; margin-bottom: 1rem;">SIMULATOR & DATA HOOKS</div>
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.25rem;">
                    Phát triển công cụ Solar Calculator & Simulator tương tác realtime, Hook phân trang > 20 dòng và phụ trách 6 bài tập (Mảng + MySQL).
                </p>
                <span class="sol-badge sol-badge--ocean">Phụ trách: Demo Thiết Kế</span>
            </div>

            <!-- TV4: Fullstack Installation -->
            <div class="solar-glass-card" style="padding: 2rem; text-align: center;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #ef4444); margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 2px solid var(--solar-corona);">
                    📋
                </div>
                <h3 style="color: #ffffff; font-size: 1.25rem; margin-bottom: 0.35rem;">Thành Viên 4</h3>
                <div style="color: #fbbf24; font-size: 0.825rem; font-weight: 700; margin-bottom: 1rem;">INSTALLATION & QA/QC</div>
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.25rem;">
                    Phát triển trang Quản lý Lắp đặt, Module Upload tài liệu/ảnh khảo sát mái nhà, kiểm thử chất lượng và phụ trách 6 bài tập MySQL.
                </p>
                <span class="sol-badge sol-badge--corona">Phụ trách: Quản Lý Lắp Đặt</span>
            </div>
        </div>
    </div>
</x-layouts.master>
