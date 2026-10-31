<x-layouts.master title="SOLARIS - Portal 24 Bài Tập Thực Hành" activeTab="exercises">
    <div class="sol-container" style="padding-top: 2.5rem; padding-bottom: 4rem;">
        <div class="sol-section-header">
            <span class="sol-badge sol-badge--corona" style="margin-bottom: 0.75rem;">KHU VỰC DÙNG CHUNG</span>
            <h2 class="sol-section-header__title">Cổng Tích Hợp 24 Bài Tập Thực Hành</h2>
            <p class="sol-section-header__desc">
                Hệ thống bài tập chia đều cho 4 thành viên (mỗi người 6 bài), tuân thủ kiến trúc phân tầng Atomic và chuẩn giao diện Solaris.
            </p>
        </div>

        <!-- 4 Cột hiển thị 4 Thành viên và 24 bài tập -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            
            <!-- Thành viên 1: 6 Bài PHP & Form -->
            <div class="solar-glass-card" style="padding: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <span class="sol-badge sol-badge--corona">👤 Thành Viên 1 (Lead)</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">6 Bài Form</span>
                </div>
                <h3 style="font-size: 1.15rem; color: #ffffff; margin-bottom: 1rem;">Phần PHP & FORM</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.85rem;">
                    <li><a href="#" style="color: var(--text-secondary);">1. Diện tích hình chữ nhật</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">2. Diện tích & chu vi hình tròn</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">3. Thanh toán tiền điện</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">4. Cạnh huyền tam giác vuông</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">5. Tính tiền Karaoke</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">6. Phép tính trên 2 số</a></li>
                </ul>
            </div>

            <!-- Thành viên 2: 2 Bài Form + 4 Bài Mảng/Chuỗi -->
            <div class="solar-glass-card" style="padding: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <span class="sol-badge sol-badge--emerald">☀️ Thành Viên 2</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">2 Form + 4 Mảng</span>
                </div>
                <h3 style="font-size: 1.15rem; color: #ffffff; margin-bottom: 1rem;">Mảng & Chuỗi (P1)</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.85rem;">
                    <li><a href="#" style="color: var(--text-secondary);">7. Xử lý Form tính toán</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">8. Form tổng hợp nâng cao</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">1. Mảng: Thao tác số nguyên</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">2. Mảng: Tính năm âm lịch</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">3. Mảng: Đếm & Thay thế</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">4. Mảng: Sắp xếp tăng/giảm</a></li>
                </ul>
            </div>

            <!-- Thành viên 3: 3 Bài Mảng/Chuỗi + 3 Bài MySQL (Đã hoàn thành) -->
            <div class="solar-glass-card" style="padding: 1.75rem; border: 1px solid rgba(56, 189, 248, 0.35); box-shadow: 0 0 15px rgba(56, 189, 248, 0.08);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <span class="sol-badge sol-badge--ocean">🧮 Thành Viên 3 (Hoàn thành)</span>
                    <span style="font-size: 0.75rem; color: var(--accent-cyan); font-weight: 700;">6 Bài Hoàn Chỉnh</span>
                </div>
                <h3 style="font-size: 1.15rem; color: #ffffff; margin-bottom: 1rem;">Mảng (P2) & MySQL</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.85rem;">
                    <li><a href="index.php?page=exercises&exercise=13" style="color: #38bdf8; text-decoration: none; display: flex; justify-content: space-between;"><span>13. Mảng 5: Thay thế & Ma trận</span><span style="font-size: 0.75rem;">➔</span></a></li>
                    <li><a href="index.php?page=exercises&exercise=14" style="color: #38bdf8; text-decoration: none; display: flex; justify-content: space-between;"><span>14. Mảng 6: Sắp xếp & Nhị phân</span><span style="font-size: 0.75rem;">➔</span></a></li>
                    <li><a href="index.php?page=exercises&exercise=15" style="color: #38bdf8; text-decoration: none; display: flex; justify-content: space-between;"><span>15. Mảng 7: Ghép mảng & Chuẩn hóa</span><span style="font-size: 0.75rem;">➔</span></a></li>
                    <li><a href="index.php?page=exercises&exercise=16" style="color: #38bdf8; text-decoration: none; display: flex; justify-content: space-between;"><span>16. MySQL 1: Lưới hãng sữa</span><span style="font-size: 0.75rem;">➔</span></a></li>
                    <li><a href="index.php?page=exercises&exercise=17" style="color: #38bdf8; text-decoration: none; display: flex; justify-content: space-between;"><span>17. MySQL 2: Lưới khách hàng</span><span style="font-size: 0.75rem;">➔</span></a></li>
                    <li><a href="index.php?page=exercises&exercise=18" style="color: #38bdf8; text-decoration: none; display: flex; justify-content: space-between;"><span>18. MySQL 3: Lưới tùy biến kẻ sọc</span><span style="font-size: 0.75rem;">➔</span></a></li>
                </ul>
            </div>
    </div>
</x-layouts.master>
