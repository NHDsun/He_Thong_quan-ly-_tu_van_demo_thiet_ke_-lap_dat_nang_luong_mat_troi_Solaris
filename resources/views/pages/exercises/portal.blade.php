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

            <!-- Thành viên 3: 3 Bài Mảng/Chuỗi + 3 Bài MySQL -->
            <div class="solar-glass-card" style="padding: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <span class="sol-badge sol-badge--ocean">🧮 Thành Viên 3</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">3 Mảng + 3 MySQL</span>
                </div>
                <h3 style="font-size: 1.15rem; color: #ffffff; margin-bottom: 1rem;">Mảng (P2) & MySQL</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.85rem;">
                    <li><a href="#" style="color: var(--text-secondary);">5. Chuỗi: Ma trận số 2 chiều</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">6. Chuỗi: Tìm kiếm nhị phân</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">7. Chuỗi: Ghép và chuẩn hóa</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">1. MySQL: Hiển thị lưới hãng sữa</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">2. MySQL: Lưới khách hàng</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">3. MySQL: Lưới sữa kẻ sọc</a></li>
                </ul>
            </div>

            <!-- Thành viên 4: 6 Bài PHP & MySQL (Quản lý bán sữa...) -->
            <div class="solar-glass-card" style="padding: 1.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <span class="sol-badge sol-badge--corona">📋 Thành Viên 4</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">6 Bài MySQL</span>
                </div>
                <h3 style="font-size: 1.15rem; color: #ffffff; margin-bottom: 1rem;">PHP & MySQL (P2)</h3>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.85rem;">
                    <li><a href="#" style="color: var(--text-secondary);">4. MySQL: Lưới sữa có phân trang (>20 dòng)</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">5. MySQL: Lưới sản phẩm kèm ảnh</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">6. MySQL: Chi tiết sản phẩm sữa</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">7. MySQL: Thêm mới sản phẩm sữa</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">8. MySQL: Tìm kiếm sản phẩm</a></li>
                    <li><a href="#" style="color: var(--text-secondary);">9. MySQL: Tìm kiếm nâng cao</a></li>
                </ul>
            </div>

        </div>
    </div>
</x-layouts.master>
