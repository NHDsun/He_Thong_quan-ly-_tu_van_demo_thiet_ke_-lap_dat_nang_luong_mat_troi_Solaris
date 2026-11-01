# ☀️ BÁO CÁO ĐẶC TẢ KỸ THUẬT, CƠ SỞ DỮ LIỆU & PHÂN QUYỀN HỆ THỐNG SOLARIS
**Học phần:** Phát Triển Phần Mềm Mã Nguồn Mở (PTPMMNM)  
**Đơn vị:** Khoa Công Nghệ Thông Tin - Bộ Môn Công Nghệ Phần Mềm & Hệ Thống Thông Tin  
**Dự án:** Hệ Thống Quản Lý, Tư Vấn, Mô Phỏng Thiết Kế & Lắp Đặt Năng Lượng Mặt Trời Solaris (Phiên bản v2.1)  
**Mã nguồn Repository:** `NHDsun/He_Thong_quan-ly-_tu_van_demo_thiet_ke_-lap_dat_nang_luong_mat_troi_Solaris`  
**Nhánh phát triển:** `feature/solar-plan-sync`

---

## 👥 I. DANH SÁCH THÀNH VIÊN VÀ PHÂN CÔNG NHIỆM VỤ DỰ ÁN

| STT | Thành Viên | MSSV | Vai Trò Chính | Nhiệm Vụ & Phân Hệ Phụ Trách | Tỷ Trọng Đóng Góp |
|:---:|:---|:---:|:---|:---|:---:|
| **1** | **Nguyễn Huy Đạt** | **65130410** | **Trưởng Nhóm & Quản Lý Dự Án (PM)**<br>Fullstack Core & UI/UX | • Lập kế hoạch, theo dõi tiến độ tổng thể (PM), review & hợp nhất mã nguồn Git.<br>• Thiết kế kiến trúc điều phối trung tâm `index.php`, Session Auth bảo mật, cấu trúc Module.<br>• Thiết kế giao diện Solar Theme chuẩn Be Vietnam Pro, khắc phục lỗi font tiếng Việt.<br>• Xây dựng phân hệ Hồ sơ Profile người dùng và thanh điều hướng Dual Navigation (Ngang &amp; Dọc 1s).<br>• Hoàn thiện phân hệ 6 bài tập Form cốt lõi (Bài 1 - 6). | **35%** |
| **2** | **Lê Chí Hải** | *Thành viên 2* | **Frontend Lead & Catalog Thiết Bị** | • Xây dựng CSDL danh mục và 26 thiết bị quang điện thực tế (Longi, Canadian, Sungrow, Huawei...).<br>• Xây dựng bộ lọc tìm kiếm sản phẩm phân trang bằng Hook `UsePaginationState`.<br>• Xây dựng Module Kế hoạch dự định thiết bị (Cart), loại bỏ hiển thị giá tiền VNĐ theo yêu cầu.<br>• Hoàn thiện 6 bài tập thực hành (Bài 7, 8 Form + Bài 1 - 4 Mảng). | **20%** |
| **3** | **Mai Đức Toàn** | *Thành viên 3* | **Solar Simulator & Data**<br>**Phụ Trách Báo Cáo Dự Án** | • Xây dựng bộ mô phỏng tính toán công suất quang điện và hoàn vốn Hook `UseSolarCalculator.php`.<br>• Dựng giao diện mô phỏng 2D/3D sơ đồ mảng pin và xuất bảng vật tư kỹ thuật BOM.<br>• Phụ trách biên tập và chuẩn hóa Hồ sơ kỹ thuật, Báo cáo đồ án & Tài liệu đặc tả hệ thống.<br>• Hoàn thiện 6 bài tập thực hành (Bài 5 - 7 Mảng + Bài 1 - 3 MySQL). | **25%** |
| **4** | **Phan Trần Thịnh** | *Thành viên 4* | **Installation Workflow & QA** | • Xây dựng quy trình Quản lý thi công lắp đặt 4 giai đoạn, tra cứu tiến độ công trình `SOLAR-YYYY-XXX`.<br>• Xây dựng Hook tải file an toàn `UseFileUpload.php` chống Web Shell, băm SHA-256.<br>• Phân bổ kỹ thuật viên hiện trường và biên bản nghiệm thu kỹ thuật EVN.<br>• Hoàn thiện 6 bài tập thực hành MySQL nâng cao (Bài 4 - 9 MySQL). | **20%** |

---

## 🏗️ II. KIẾN TRÚC HỆ THỐNG & ĐẶC TẢ CÁC TÍNH NĂNG NÂNG CẤP MỚI NHẤT (V2.1)

### 1. Đồng bộ Kế Hoạch Dự Định sang Demo Thiết Kế & Ghép Đôi Thiết Bị
- **Cơ chế hoạt động:** Cho phép người dùng chuyển toàn bộ thiết bị đã chọn trong Kế hoạch dự định (`page=cart`) sang phân hệ Demo Thiết Kế (`page=calculator&source=plan`).
- **Công thức tính toán thời gian thực:**
  $$	ext{Tổng Công Suất (kWp)} = rac{\sum (	ext{Số lượng tấm pin} 	imes 	ext{Công suất W})}{1.000}$$
  $$	ext{Điện Năng Sinh Ra (kWh/tháng)} = 	ext{kWp} 	imes 	ext{Số Giờ Nắng Đỉnh} 	imes 30 	imes 0.85$$
  $$	ext{Tiền Tiết Kiệm/Tháng (VNĐ)} = 	ext{Điện Năng/Tháng} 	imes 3.000	ext{ đ/kWh}$$
  $$	ext{Thời Gian Gỡ Vốn (Năm)} = rac{	ext{Tổng Chi Phí Đầu Tư}}{	ext{Tiền Tiết Kiệm Hàng Năm}}$$
- **Ma trận ghép đôi tương thích tự động:** Đánh giá độ tương thích điện áp và công suất giữa Tấm pin $\leftrightarrow$ Inverter hòa lưới $\leftrightarrow$ Tủ điện bảo vệ ATS $\leftrightarrow$ Pin Lithium ESS lưu trữ.

### 2. Hệ Thống Phân Quyền 3 Mức Tài Khoản & Profile Người Dùng
- **Mức 1 - Quản trị viên (Admin):** `admin_pro` / `Solaris@2026`. Quản trị danh mục, giám sát toàn sàn, phê duyệt báo giá và phân công nhân sự.
- **Mức 2 - Kỹ thuật viên (Technician):** `tech_pro` / `Solaris@2026`. Quản lý tiến độ 4 giai đoạn lắp đặt, tải ảnh hiện trường, checklist kiểm tra an toàn điện lực.
- **Mức 3 - Khách hàng thành viên (Customer):** `customer_pro` / `Solaris@2026`. Xem thông số cá nhân, kế hoạch thiết bị dự định, dự toán tiền tiết kiệm, sổ tay bảo hành điện tử.
- **Tài liệu kiểm thử chuyên biệt:** Toàn bộ thông tin tài khoản mẫu và hướng dẫn nghiệm thu được tách riêng vào **Modal DOC** chuyên nghiệp có nút 1-click chuyển vai trò, giữ cho giao diện Profile chính hoàn toàn trong sạch phục vụ người dùng thực tế.

### 3. Thanh Điều Hướng Thông Minh 2 Chế Độ (Dual Navigation)
- **Trang Chủ (Landing Page):** Thanh điều hướng ngang full-width rộng thoáng, loại bỏ nhãn phiên bản thừa, làm nổi bật nút `Profile` màu vàng rực rỡ bo góc 12px theo đúng mẫu thiết kế.
- **Các Trang Con (App Workspace):** Tự động chuyển đổi thành Thanh Menu Dọc Bên Trái (Left Sidebar) với hiệu ứng trượt mượt mà trong đúng 1 giây (`cubic-bezier(0.16, 1, 0.3, 1)`), tối ưu không gian làm việc chuyên nghiệp cho kỹ sư và quản trị viên.

### 4. Trang Chủ Năng Lượng Đẳng Cấp & Socket.io Realtime Gateway
- **Cổng Socket.io Real-time (Port 3001):** Máy chủ Node.js phát sóng các sự kiện `solar:action`, `solar:slide_change`, `solar:plan_update` giúp đồng bộ dữ liệu tức thì giữa các tab và người dùng.
- **Hiệu ứng quang năng Solar Shockwave Ripple:** Sóng năng lượng vàng rực rỡ tỏa ra từ điểm nhấp chuột trên màn hình.
- **Slider 4 giải pháp hệ thống siêu mượt:** Hòa lưới (On-grid), Lưu trữ (Hybrid ESS), Doanh nghiệp (C&I Rooftop), Nông nghiệp bơm nước (Agri-Solar).
- **Bộ tính toán Solar ROI Sizing 3 miền:** Tùy biến số giờ nắng theo Miền Bắc (3.8h), Miền Trung (4.8h), Miền Nam (5.2h).
- **Quy trình 5 bước thi công chuẩn EVN:** Khảo sát Drone 3D $ightarrow$ Demo 3D & BOM $ightarrow$ Thi công khung giàn $ightarrow$ Đấu nối Inverter & Pin $ightarrow$ Nghiệm thu đóng điện EVN & SCADA App.

---

## 📊 III. BIỂU ĐỒ CƠ CẤU ĐÓNG GÓP TOÀN DỰ ÁN (WBS WORK BREAKDOWN)

```
========================================================================================
TIẾN ĐỘ VÀ CƠ CẤU TỶ TRỌNG ĐÓNG GÓP XÂY DỰNG DỰ ÁN SOLARIS (HOÀN THÀNH 100%)
========================================================================================
[==================================== 100% TỔNG KHỐI LƯỢNG ====================================]
[ TV1: Nguyễn Huy Đạt (35%) ][ TV2: Lê Chí Hải (20%) ][ TV3: Mai Đức Toàn (25%) ][ TV4: Thịnh (20%) ]
----------------------------------------------------------------------------------------
• TV1 (PM Nguyễn Huy Đạt):  Core Arch, Session Auth, Dual Nav, Profile, 6 Bài tập Form (35%)
• TV2 (Lê Chí Hải):         Catalog 26 SP, Kế hoạch dự định (Cart), Ghép đôi tương thích (20%)
• TV3 (Mai Đức Toàn):       Solar Simulator KW/ROI, Phụ trách Báo Cáo Dự Án, Bài tập Mảng/MySQL (25%)
• TV4 (Phan Trần Thịnh):    Quy trình Lắp đặt 4 Giai đoạn, Upload ảnh SHA-256, Bài tập MySQL (20%)
========================================================================================
```
