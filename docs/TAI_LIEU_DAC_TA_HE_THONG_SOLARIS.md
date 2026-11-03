# ☀️ BÁO CÁO ĐẶC TẢ KỸ THUẬT, CƠ SỞ DỮ LIỆU & PHÂN QUYỀN HỆ THỐNG SOLARIS

> **Học phần:** Phát Triển Phần Mềm Mã Nguồn Mở (PTPMMNM)  
> **Đơn vị:** Khoa Công Nghệ Thông Tin - Bộ Môn Công Nghệ Phần Mềm & Hệ Thống Thông Tin  
> **Dự án:** Hệ Thống Quản Lý, Tư Vấn, Mô Phỏng Thiết Kế & Lắp Đặt Năng Lượng Mặt Trời Solaris (Phiên bản v2.1)  
> **Mã nguồn Repository:** `NHDsun/He_Thong_quan-ly-_tu_van_demo_thiet_ke_-lap_dat_nang_luong_mat_troi_Solaris`  
> **Nhánh phát triển chính thức:** `feature/solar-plan-sync`  
> **Cập nhật ngày:** 03/11/2026  

---

## 👥 I. DANH SÁCH THÀNH VIÊN VÀ PHÂN CÔNG NHIỆM VỤ DỰ ÁN

| STT | Thành Viên | MSSV | Vai Trò Chính | Nhiệm Vụ & Phân Hệ Phụ Trách | Tỷ Trọng Đóng Góp |
|:---:|:---|:---:|:---|:---|:---:|
| **1** | **Nguyễn Huy Đạt** | **65130410** | **Trưởng Nhóm & Quản Lý Dự Án (PM)**<br>Fullstack Core & UI/UX | • Lập kế hoạch, theo dõi tiến độ tổng thể (PM), review & hợp nhất mã nguồn Git.<br>• Thiết kế kiến trúc điều phối trung tâm `index.php`, Session Auth bảo mật, cấu trúc Module.<br>• Xây dựng Cổng Đăng Nhập & Đăng Ký Tài Khoản bằng Gmail với xác thực mã OTP 6 số.<br>• Thiết kế giao diện Solar Theme chuẩn Be Vietnam Pro, hiệu ứng quang năng Solar Shockwave Ripple.<br>• Xây dựng phân hệ Hồ sơ Profile người dùng và thanh điều hướng Dual Navigation (Ngang & Dọc 1s).<br>• Tích hợp cổng Socket.io Real-time Port 3001 đồng bộ dữ liệu thời gian thực giữa các tab.<br>• Hoàn thiện phân hệ 6 bài tập Form cốt lõi (Bài 1 - 6). | **35%** |
| **2** | **Lê Chí Hải** | **65130100** | **Frontend Lead & Catalog Thiết Bị** | • Xây dựng CSDL danh mục và 26 thiết bị quang điện thực tế Tier-1 (Longi, Canadian, Sungrow, Huawei...).<br>• Xây dựng bộ lọc tìm kiếm sản phẩm phân trang bằng Hook `UsePaginationState`.<br>• Xây dựng Module Kế hoạch dự định thiết bị (Cart), loại bỏ hiển thị giá tiền VNĐ theo yêu cầu.<br>• Đồng bộ danh sách vật tư đã chọn sang mô phỏng thiết kế.<br>• Hoàn thiện 6 bài tập thực hành (Bài 7, 8 Form + Bài 1 - 4 Mảng). | **20%** |
| **3** | **Mai Đức Toàn** | **65133959** | **Solar Simulator & Data**<br>**Phụ Trách Báo Cáo Dự Án** | • Xây dựng bộ mô phỏng tính toán công suất quang điện và hoàn vốn Hook `UseSolarCalculator.php`.<br>• Dựng giao diện mô phỏng 2D/3D sơ đồ mảng pin và xuất bảng vật tư kỹ thuật BOM.<br>• Phụ trách biên tập và chuẩn hóa Hồ sơ kỹ thuật, Báo cáo đồ án & Tài liệu đặc tả hệ thống.<br>• Chuẩn hóa dữ liệu tính toán bức xạ 3 miền (Bắc, Trung, Nam).<br>• Hoàn thiện 6 bài tập thực hành (Bài 5 - 7 Mảng + Bài 1 - 3 MySQL). | **25%** |
| **4** | **Phan Trần Thịnh** | **65133649** | **Installation Workflow & QA** | • Xây dựng quy trình Quản lý thi công lắp đặt 4 giai đoạn, tra cứu tiến độ công trình `SOLAR-YYYY-XXX`.<br>• Xây dựng Hook tải file an toàn `UseFileUpload.php` chống Web Shell, băm SHA-256.<br>• Phân bổ kỹ thuật viên hiện trường và biên bản nghiệm thu kỹ thuật EVN.<br>• Hoàn thiện 6 bài tập thực hành MySQL nâng cao (Bài 4 - 9 MySQL). | **20%** |

---

## 🏗️ II. KIẾN TRÚC HỆ THỐNG & ĐẶC TẢ CÁC TÍNH NĂNG MỚI NHẤT (V2.1)

### 1. Cổng Xác Thực Đăng Nhập & Đăng Ký Tài Khoản Bằng Gmail (OTP Verification)
* **Tự động điều hướng khi vừa vào dự án:** Người dùng chưa đăng nhập khi truy cập website (`index.php`) sẽ tự động được đưa đến cổng **Auth Portal** với 2 tab chuyển đổi mượt mà giữa Đăng Nhập và Đăng Ký.
* **Cơ chế xác thực qua Gmail với OTP 6 số:**
  1. Người dùng nhập thông tin họ tên, Gmail nhận mã, số điện thoại, mật khẩu và vai trò (Khách hàng hoặc Kỹ thuật viên).
  2. Hệ thống kiểm tra trùng lặp email/username trong CSDL, tạo mã xác thực 6 số ngẫu nhiên có hiệu lực 10 phút, gửi email qua hàm `mail()` chuẩn HTML.
  3. Cung cấp **Hộp Thư Ảo Gmail Sandbox (Modal)** trực quan trên giao diện hiển thị bức thư mô phỏng giúp giảng viên và sinh viên kiểm thử tức thì ngay trên localhost mà không phụ thuộc vào Internet hay SMTP bên ngoài.
  4. Giao diện 6 ô nhập mã OTP hiện đại tự động nhảy focus khi nhập số, phím Backspace tự động lùi về ô trước, tự động paste từ clipboard, đếm ngược thời gian thực và cho phép gửi lại mã khi cần.
  5. Khi nhập đúng mã OTP: Hệ thống lưu tài khoản vào bảng `users` trong MySQL `solaris`, tự động tạo phiên Session đăng nhập và chuyển hướng về Trang Chủ kèm banner chúc mừng kích hoạt thành công.
* **Hỗ trợ Đăng nhập 1-Click Tester Accounts:** Giữ nút truy cập nhanh 3 vai trò mẫu (Admin, Tech, Customer) để Giảng viên và Hội đồng chấm điểm có thể kiểm thử toàn diện mọi luồng nghiệp vụ không cần gõ mật khẩu.
* **Nút Đăng xuất an toàn (`🚪`):** Bổ sung nút đăng xuất nhanh ở cả thanh Navigation Bar ngang (Trang Chủ) và Menu dọc bên trái (các trang con).

### 2. Đồng bộ Kế Hoạch Dự Định sang Demo Thiết Kế & Ghép Đôi Thiết Bị
* **Cơ chế hoạt động:** Cho phép người dùng chuyển toàn bộ thiết bị đã chọn trong Kế hoạch dự định (`page=cart`) sang phân hệ Demo Thiết Kế (`page=calculator&source=plan`).
* **Công thức tính toán thời gian thực:**
  - $\text{Tổng Công Suất (kWp)} = \frac{\sum (\text{Số lượng tấm pin} \times \text{Công suất W})}{1.000}$
  - $\text{Điện Năng Sinh Ra (kWh/tháng)} = \text{kWp} \times \text{Số Giờ Nắng Đỉnh} \times 30 \times 0.85$
  - $\text{Tiền Tiết Kiệm/Tháng (VNĐ)} = \text{Điện Năng/Tháng} \times 3.000\text{ đ/kWh}$
  - $\text{Thời Gian Gỡ Vốn (Năm)} = \frac{\text{Tổng Chi Phí Đầu Tư}}{\text{Tiền Tiết Kiệm Hàng Năm}}$
* **Ma trận ghép đôi tương thích tự động:** Đánh giá độ tương thích điện áp và công suất giữa Tấm pin $\leftrightarrow$ Inverter hòa lưới $\leftrightarrow$ Tủ điện bảo vệ ATS $\leftrightarrow$ Pin Lithium ESS lưu trữ.

### 3. Hệ Thống Phân Quyền 3 Mức Tài Khoản & Profile Người Dùng
* **Mức 1 - Quản trị viên (Admin):** `admin_pro` / `Solaris@2026`. Quản trị danh mục, giám sát toàn sàn, phê duyệt báo giá và phân công nhân sự.
* **Mức 2 - Kỹ thuật viên (Technician):** `tech_pro` / `Solaris@2026`. Quản lý tiến độ 4 giai đoạn lắp đặt, tải ảnh hiện trường, checklist kiểm tra an toàn điện lực.
* **Mức 3 - Khách hàng thành viên (Customer):** `customer_pro` / `Solaris@2026`. Xem thông số cá nhân, kế hoạch thiết bị dự định, dự toán tiền tiết kiệm, sổ tay bảo hành điện tử.
* **Tài liệu kiểm thử chuyên biệt:** Toàn bộ thông tin tài khoản mẫu và hướng dẫn nghiệm thu được tách riêng vào **Modal DOC** chuyên nghiệp có nút 1-click chuyển vai trò, giữ cho giao diện Profile chính hoàn toàn trong sạch phục vụ người dùng thực tế.

### 4. Thanh Điều Hướng Thông Minh 2 Chế Độ (Dual Navigation)
* **Trang Chủ (Landing Page):** Thanh điều hướng ngang full-width rộng thoáng, loại bỏ nhãn phiên bản thừa, làm nổi bật nút `Profile` màu vàng rực rỡ bo góc 12px theo đúng mẫu thiết kế.
* **Các Trang Con (App Workspace):** Tự động chuyển đổi thành Thanh Menu Dọc Bên Trái (Left Sidebar) với hiệu ứng trượt mượt mà trong đúng 1 giây (`cubic-bezier(0.16, 1, 0.3, 1)`), tối ưu không gian làm việc chuyên nghiệp cho kỹ sư và quản trị viên.

### 5. Trang Chủ Năng Lượng Đẳng Cấp & Socket.io Realtime Gateway
* **Cổng Socket.io Real-time (Port 3001):** Máy chủ Node.js phát sóng các sự kiện `solar:action`, `solar:slide_change`, `solar:plan_update` giúp đồng bộ dữ liệu tức thì giữa các tab và người dùng.
* **Hiệu ứng quang năng Solar Shockwave Ripple:** Sóng năng lượng vàng rực rỡ tỏa ra từ điểm nhấp chuột trên màn hình.
* **Slider 4 giải pháp hệ thống siêu mượt:** Hòa lưới (On-grid), Lưu trữ (Hybrid ESS), Doanh nghiệp (C&I Rooftop), Nông nghiệp bơm nước (Agri-Solar).
* **Bộ tính toán Solar ROI Sizing 3 miền:** Tùy biến số giờ nắng theo Miền Bắc (3.8h), Miền Trung (4.8h), Miền Nam (5.2h).
* **Quy trình 5 bước thi công chuẩn EVN:** Khảo sát Drone 3D $\rightarrow$ Demo 3D & BOM $\rightarrow$ Thi công khung giàn $\rightarrow$ Đấu nối Inverter & Pin $\rightarrow$ Nghiệm thu đóng điện EVN & SCADA App.

---

## 📊 III. BIỂU ĐỒ CƠ CẤU ĐÓNG GÓP TOÀN DỰ ÁN (WBS WORK BREAKDOWN)

```
========================================================================================
TIẾN ĐỘ VÀ CƠ CẤU TỶ TRỌNG ĐÓNG GÓP XÂY DỰNG DỰ ÁN SOLARIS (HOÀN THÀNH 100%)
========================================================================================
[==================================== 100% TỔNG KHỐI LƯỢNG ====================================]
[ TV1: Nguyễn Huy Đạt (35%) ][ TV2: Lê Chí Hải (20%) ][ TV3: Mai Đức Toàn (25%) ][ TV4: Thịnh (20%) ]
----------------------------------------------------------------------------------------
• TV1 (PM Nguyễn Huy Đạt):  Core Arch, Auth Gmail OTP, Dual Nav, Profile, 6 Bài Form (35%)
• TV2 (Lê Chí Hải):         Catalog 26 SP, Kế hoạch dự định (Cart), Ghép đôi tương thích (20%)
• TV3 (Mai Đức Toàn):       Solar Simulator KW/ROI, Phụ trách Báo Cáo Dự Án, Bài Mảng/MySQL (25%)
• TV4 (Phan Trần Thịnh):    Quy trình Lắp đặt 4 Giai đoạn, Upload ảnh SHA-256, Bài MySQL (20%)
========================================================================================
```

---

## 💾 IV. HỆ THỐNG DỮ LIỆU ĐỒNG BỘ CHO NHÓM (DATABASE SYNC SPECIFICATION)

Để đảm bảo toàn bộ 4 thành viên (Đạt, Hải, Toàn, Thịnh) và Hội đồng chấm điểm có dữ liệu đồng nhất 100%, hệ thống cung cấp file đồng bộ dữ liệu mẫu:

* **File Dữ Liệu Đồng Bộ Độc Lập:** [`database/solaris_data_insert.sql`](file:///F:/Work/AXMPP/htdocs/PHP/Doan/database/solaris_data_insert.sql) (hoặc file gốc tại thư mục dự án: [`solaris_data_insert.sql`](file:///F:/Work/AXMPP/htdocs/PHP/Doan/solaris_data_insert.sql))
* **File CSDL Trọn Gói (Schema + Data):** [`database/solaris_full.sql`](file:///F:/Work/AXMPP/htdocs/PHP/Doan/database/solaris_full.sql)

### 1. Phạm Vi 17 Bảng Dữ Liệu Đồng Bộ
1. **Quản lý Tài khoản & Phân quyền:** `users` (12 tài khoản: Admin, Technician, Customer, Tester)
2. **Danh mục & Thiết bị quang điện:** `categories` (4 danh mục), `products` (26 sản phẩm Tier-1)
3. **Báo giá & Dự án Solar:** `solar_quotes` (6 báo giá), `projects` (3 hợp đồng thực tế)
4. **Vật tư & Giai đoạn thi công:** `installation_items` (15 hạng mục BOM), `project_stages` (18 bước quy trình), `project_assignments` (5 phân công kỹ thuật), `project_stage_logs` (5 nhật ký thi công)
5. **Hồ sơ nghiệm thu & Yêu cầu:** `project_documents` (6 ảnh khảo sát, bản vẽ CAD, EVN), `project_requests` (2 yêu cầu gia hạn/thay kỹ thuật)
6. **Portal 24 Bài tập PHP Bán sữa:** `hang_sua` (6 hãng), `loai_sua` (4 loại), `sua` (25 sản phẩm phân trang), `khach_hang` (8 khách hàng), `hoa_don` (5 hóa đơn), `ct_hoadon` (8 chi tiết)

### 2. Cách Thực Hiện Đồng Bộ
```bash
# Cách 1: Qua dòng lệnh MySQL/XAMPP Shell:
mysql -u root -p solaris < database/solaris_data_insert.sql

# Cách 2: Qua giao diện phpMyAdmin:
# Truy cập http://localhost/phpmyadmin -> Chọn CSDL solaris -> Tab "Import" -> Chọn file solaris_data_insert.sql -> Bấm "Go"
```

---

## 🌐 V. DANH MỤC ĐƯỜNG LINK TRUY CẬP HỆ THỐNG

### 1. Liên kết Ứng dụng Web Cục bộ (Localhost)
* 🏠 **Trang Chủ (Home):** [http://localhost/PHP/Doan/index.php?page=home](http://localhost/PHP/Doan/index.php?page=home)
* 🔑 **Cổng Đăng Nhập & Đăng Ký (Auth):** [http://localhost/PHP/Doan/index.php?page=auth](http://localhost/PHP/Doan/index.php?page=auth)
* ☀️ **Danh Mục Sản Phẩm (Products):** [http://localhost/PHP/Doan/index.php?page=products](http://localhost/PHP/Doan/index.php?page=products)
* 🛒 **Kế Hoạch Dự Định (Cart):** [http://localhost/PHP/Doan/index.php?page=cart](http://localhost/PHP/Doan/index.php?page=cart)
* 🧮 **Demo Thiết Kế & Hoàn Vốn (Calculator):** [http://localhost/PHP/Doan/index.php?page=calculator](http://localhost/PHP/Doan/index.php?page=calculator)
* 📋 **Quản Lý Lắp Đặt & Khảo Sát (Installations):** [http://localhost/PHP/Doan/index.php?page=installations](http://localhost/PHP/Doan/index.php?page=installations)
* 👤 **Hồ Sơ Cá Nhân & Modal DOC (Profile):** [http://localhost/PHP/Doan/index.php?page=profile](http://localhost/PHP/Doan/index.php?page=profile)
* 👥 **Hồ Sơ 4 Thành Viên & WBS (Team):** [http://localhost/PHP/Doan/index.php?page=profile&tab=team](http://localhost/PHP/Doan/index.php?page=profile&tab=team)
* 📚 **Cổng 24 Bài Tập Thực Hành (Exercises):** [http://localhost/PHP/Doan/index.php?page=exercises](http://localhost/PHP/Doan/index.php?page=exercises)

### 2. Đăng Nhập 1-Click 3 Cấp Phân Quyền Kiểm Thử
* 🛡️ **Quản trị viên (Admin Level):** [http://localhost/PHP/Doan/index.php?page=auth&switch_account=admin_pro](http://localhost/PHP/Doan/index.php?page=auth&switch_account=admin_pro) (`admin_pro` / `Solaris@2026`)
* 🔧 **Kỹ thuật viên (Technician Level):** [http://localhost/PHP/Doan/index.php?page=auth&switch_account=tech_pro](http://localhost/PHP/Doan/index.php?page=auth&switch_account=tech_pro) (`tech_pro` / `Solaris@2026`)
* 👤 **Khách hàng (Customer Level):** [http://localhost/PHP/Doan/index.php?page=auth&switch_account=customer_pro](http://localhost/PHP/Doan/index.php?page=auth&switch_account=customer_pro) (`customer_pro` / `Solaris@2026`)

### 3. Cổng Socket.io Real-time (Port 3001)
* **Kiểm tra trạng thái máy chủ (Health Check):** [http://localhost:3001/health](http://localhost:3001/health)
* **Kích hoạt Broadcast Test:** [http://localhost:3001/broadcast?event=solar:action&type=test](http://localhost:3001/broadcast?event=solar:action&type=test)
