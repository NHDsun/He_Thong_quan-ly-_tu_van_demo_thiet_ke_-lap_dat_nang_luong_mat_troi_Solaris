# ☀️ SOLARIS - HỆ THỐNG QUẢN LÝ, TƯ VẤN, DEMO THIẾT KẾ & LẮP ĐẶT ĐIỆN MẶT TRỜI (v2.1)

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/MySQL-InnoDB_utf8mb4-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Realtime](https://img.shields.io/badge/Socket.io-Port_3001-010101?style=flat&logo=socket.io&logoColor=white)](https://socket.io/)
[![Environment](https://img.shields.io/badge/XAMPP-Windows-FB7A24?style=flat&logo=apache&logoColor=white)](https://www.apachefriends.org/)
[![Branch](https://img.shields.io/badge/Branch-feature%2Fsolar--plan--sync-f59e0b?style=flat&logo=git&logoColor=white)](https://github.com/NHDsun/He_Thong_quan-ly-_tu_van_demo_thiet_ke_-lap_dat_nang_luong_mat_troi_Solaris/tree/feature/solar-plan-sync)

> **Môn học:** Phát Triển Phần Mềm Mã Nguồn Mở (PTPMMNM)  
> **Đơn vị:** Khoa Công Nghệ Thông Tin - Trường Đại Học Nha Trang (NTU)  
> **Phiên bản:** Solaris Enterprise v2.1  
> **Repository:** [NHDsun/Solaris Energy System](https://github.com/NHDsun/He_Thong_quan-ly-_tu_van_demo_thiet_ke_-lap_dat_nang_luong_mat_troi_Solaris)  

---

## 👥 I. DANH SÁCH THÀNH VIÊN & TỶ TRỌNG ĐÓNG GÓP (WBS 100%)

| STT | Họ Và Tên | MSSV | Vai Trò | Phân Hệ Phụ Trách Chính | Đóng Góp |
|:---:|:---|:---:|:---|:---|:---:|
| **1** | **Nguyễn Huy Đạt** | **65130410** | **Trưởng Nhóm / PM**<br>Fullstack Core | • Kiến trúc trung tâm `index.php`, Session Auth, Dual Nav (Ngang $\leftrightarrow$ Dọc 1s).<br>• Cổng Đăng nhập & Đăng ký Gmail xác thực mã OTP 6 số.<br>• Tích hợp máy chủ Socket.io Real-time Port 3001, hiệu ứng Solar Shockwave.<br>• Quản lý Profile người dùng thực tế & 6 Bài tập Form (Bài 1 - 6). | **35%** |
| **2** | **Lê Chí Hải** | **65130100** | **Frontend Lead** | • CSDL danh mục và 26 thiết bị quang điện Tier-1 (Canadian, LONGi, Sungrow...).<br>• Bộ lọc phân trang Hook `UsePaginationState`.<br>• Kế hoạch dự định thiết bị (Cart) & 6 Bài tập Form/Mảng (Bài 7 - 12). | **20%** |
| **3** | **Mai Đức Toàn** | **65133959** | **Simulator & Data**<br>**Phụ Trách Báo Cáo** | • Bộ mô phỏng tính toán công suất quang điện và hoàn vốn `UseSolarCalculator.php`.<br>• Sơ đồ mảng pin 2D/3D & bảng bóc tách vật tư kỹ thuật BOM.<br>• Biên soạn Hồ sơ kỹ thuật, Báo cáo & 6 Bài tập Mảng/MySQL (Bài 13 - 18). | **25%** |
| **4** | **Phan Trần Thịnh** | **65133649** | **Workflow & QA** | • Quy trình thi công lắp đặt 4 giai đoạn, tra cứu mã công trình `SOLAR-YYYY-XXX`.<br>• Tải ảnh an toàn `UseFileUpload.php` băm SHA-256.<br>• Nghiệm thu kỹ thuật EVN & 6 Bài tập MySQL nâng cao (Bài 19 - 24). | **20%** |

```
========================================================================================
TIẾN ĐỘ VÀ CƠ CẤU TỶ TRỌNG ĐÓNG GÓP XÂY DỰNG DỰ ÁN SOLARIS (HOÀN THÀNH 100%)
========================================================================================
[==================================== 100% TỔNG KHỐI LƯỢNG ====================================]
[ TV1: Nguyễn Huy Đạt (35%) ][ TV2: Lê Chí Hải (20%) ][ TV3: Mai Đức Toàn (25%) ][ TV4: Thịnh (20%) ]
========================================================================================
```

---

## 🌟 II. CÁC TÍNH NĂNG CỐT LÕI ĐỘT PHÁ (V2.1)

### 1. 🔑 Cổng Đăng Nhập & Đăng Ký Tài Khoản Xác Thực Bằng Gmail (OTP Verification)
* **Tự động điều hướng khi vừa vào dự án:** Khách truy cập chưa đăng nhập sẽ thấy ngay cổng Auth hiện đại.
* **Cơ chế xác thực qua Gmail:**
  - Nhập thông tin và địa chỉ Gmail.
  - Tự động sinh mã xác nhận OTP 6 số (hiệu lực 10 phút) gửi qua email HTML.
  - **Hộp Thư Ảo Gmail Sandbox:** Hiển thị trực quan bức thư Gmail ngay trên web để kiểm thử offline trên localhost mà không sợ lỗi chặn SMTP hay mất mạng.
  - Giao diện 6 ô nhập mã OTP hiện đại: tự động nhảy ô khi gõ số, phím Backspace tự động lùi về ô trước, tự động paste từ clipboard, đếm ngược thời gian thực.
  - Nhập đúng OTP $\rightarrow$ Lưu vào MySQL `users` $\rightarrow$ Tự động đăng nhập $\rightarrow$ Điều hướng về Trang Chủ kèm banner chúc mừng.
* **Đăng nhập nhanh 1-Click Tester Accounts:** 3 nút truy cập trực tiếp vai trò Admin, Technician, Customer dành cho Hội đồng chấm thi.
* **Nút Đăng xuất an toàn (`🚪`):** Bổ sung trên cả thanh Menu ngang và Menu dọc bên trái.

### 2. ⚡ Đồng Bộ Thời Gian Thực Socket.io Real-time Gateway (Port 3001)
* Máy chủ Node.js chạy độc lập phát sóng các sự kiện `solar:action`, `solar:slide_change`, `solar:plan_update`.
* Tự động đồng bộ các tab trình duyệt và hiển thị trạng thái kết nối trực tiếp trên giao diện.

### 3. 🌊 Hiệu Ứng Quang Năng Solar Shockwave Ripple & Giao Diện Hiện Đại
* Sóng năng lượng vàng rực rỡ tỏa ra từ tâm điểm click chuột.
* Dải thông số bức xạ Live Irradiance Ticker mô phỏng cường độ nắng theo thời gian thực.
* Slider 4 giải pháp: Hòa lưới (On-grid), Lưu trữ (Hybrid ESS), Nhà xưởng (C&I Rooftop), Nông nghiệp (Agri-Solar).
* Bộ tính toán Solar ROI Sizing 3 miền: Bắc (3.8h), Trung (4.8h), Nam (5.2h).
* Quy trình 5 bước thi công chuẩn EVN: Khảo sát Drone $\rightarrow$ Demo 3D & BOM $\rightarrow$ Lắp khung giàn $\rightarrow$ Đấu nối Inverter & Pin $\rightarrow$ Nghiệm thu đóng điện EVN.

### 4. 🔀 Thanh Điều Hướng Thông Minh Dual Navigation
* **Trang Chủ:** Navigation Bar ngang full-width rộng thoáng, loại bỏ tên phiên bản thừa, nổi bật nút Profile màu vàng rực rỡ.
* **Các Trang Con:** Tự động chuyển đổi thành Thanh Menu Dọc Bên Trái (Left Sidebar) với hiệu ứng trượt mượt mà trong đúng 1 giây (`cubic-bezier(0.16, 1, 0.3, 1)`).

### 5. 🛒 Đồng Bộ Kế Hoạch Dự Định Thiết Bị (Cart) Sang Demo Thiết Kế
* Người dùng tự chọn tấm pin, inverter, khối pin lưu trữ trong Catalog.
* Chuyển 1-click sang công cụ tính toán mô phỏng hoàn vốn và bảng vật tư kỹ thuật BOM.

### 6. 👤 Hồ Sơ Cá Nhân Dành Cho Người Dùng & Modal DOC Nghiệm Thu
* Giao diện Profile cá nhân hiển thị công trình thực tế, lịch bảo dưỡng, sổ tay bảo hành điện tử.
* Dữ liệu tài khoản kiểm thử được đưa vào **Modal DOC** chuyên nghiệp với bảng hướng dẫn nghiệm thu và nút 1-click chuyển vai trò.

### 7. 📚 Cổng 24 Bài Tập Thực Hành PHP & MySQL
* Tích hợp toàn diện 24 bài tập thực hành trên lớp: Bài 1-6 (Form cơ bản), Bài 7-12 (Form nâng cao & Mảng), Bài 13-18 (Mảng & MySQL cơ bản), Bài 19-24 (MySQL quản lý bán sữa nâng cao có phân trang, giỏ hàng, tìm kiếm).

---

## 🚀 III. HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN

### 1. Yêu Cầu Môi Trường
* **XAMPP** (Apache 2.4+, MySQL/MariaDB 10.4+, PHP 8.1 trở lên).
* **Node.js** (Phiên bản 18+ để chạy máy chủ Socket.io).

### 2. Cài Đặt Thư Mục Dự Án
Đặt thư mục dự án vào thư mục `htdocs` của XAMPP:
```
F:\Work\AXMPP\htdocs\PHP\Doan\
```

### 3. Đồng Bộ Cơ Sở Dữ Liệu MySQL (17 Bảng)
Chọn 1 trong 2 cách:
* **Cách 1 (Dòng lệnh):**
  ```bash
  mysql -u root -p solaris < database/solaris_data_insert.sql
  ```
* **Cách 2 (phpMyAdmin):**
  Truy cập [http://localhost/phpmyadmin](http://localhost/phpmyadmin) $\rightarrow$ Tạo CSDL `solaris` (Collation: `utf8mb4_unicode_ci`) $\rightarrow$ Chọn Tab **Import** $\rightarrow$ Chọn file [`database/solaris_data_insert.sql`](database/solaris_data_insert.sql) $\rightarrow$ Nhấn **Go**.

### 4. Khởi Động Máy Chủ Socket.io Real-time
Mở cửa sổ dòng lệnh trong thư mục `socket_server`:
```bash
cd socket_server
node server.js
```
*(Máy chủ socket sẽ lắng nghe tại cổng `http://localhost:3001`)*

---

## 🌐 IV. DANH MỤC ĐƯỜNG LINK TRUY CẬP

### 1. Ứng Dụng Web Localhost
* 🏠 **Trang Chủ (Home):** [http://localhost/PHP/Doan/index.php?page=home](http://localhost/PHP/Doan/index.php?page=home)
* 🔑 **Cổng Đăng Nhập & Đăng Ký (Auth):** [http://localhost/PHP/Doan/index.php?page=auth](http://localhost/PHP/Doan/index.php?page=auth)
* ☀️ **Danh Mục Sản Phẩm (Products):** [http://localhost/PHP/Doan/index.php?page=products](http://localhost/PHP/Doan/index.php?page=products)
* 🛒 **Kế Hoạch Dự Định (Cart):** [http://localhost/PHP/Doan/index.php?page=cart](http://localhost/PHP/Doan/index.php?page=cart)
* 🧮 **Demo Thiết Kế & Hoàn Vốn (Calculator):** [http://localhost/PHP/Doan/index.php?page=calculator](http://localhost/PHP/Doan/index.php?page=calculator)
* 📋 **Quản Lý Lắp Đặt & Khảo Sát (Installations):** [http://localhost/PHP/Doan/index.php?page=installations](http://localhost/PHP/Doan/index.php?page=installations)
* 👤 **Hồ Sơ Cá Nhân & Modal DOC (Profile):** [http://localhost/PHP/Doan/index.php?page=profile](http://localhost/PHP/Doan/index.php?page=profile)
* 👥 **Hồ Sơ 4 Thành Viên & WBS (Team):** [http://localhost/PHP/Doan/index.php?page=profile&tab=team](http://localhost/PHP/Doan/index.php?page=profile&tab=team)
* 📚 **Cổng 24 Bài Tập Thực Hành (Exercises):** [http://localhost/PHP/Doan/index.php?page=exercises](http://localhost/PHP/Doan/index.php?page=exercises)

### 2. Liên Kết Đăng Nhập Nhanh 1-Click (Dành Cho Giảng Viên / Hội Đồng)
* 🛡️ **Quản Trị Viên (Admin Level):** [http://localhost/PHP/Doan/index.php?page=auth&switch_account=admin_pro](http://localhost/PHP/Doan/index.php?page=auth&switch_account=admin_pro) (`admin_pro` / `Solaris@2026`)
* 🔧 **Kỹ Thuật Viên (Technician Level):** [http://localhost/PHP/Doan/index.php?page=auth&switch_account=tech_pro](http://localhost/PHP/Doan/index.php?page=auth&switch_account=tech_pro) (`tech_pro` / `Solaris@2026`)
* 👤 **Khách Hàng (Customer Level):** [http://localhost/PHP/Doan/index.php?page=auth&switch_account=customer_pro](http://localhost/PHP/Doan/index.php?page=auth&switch_account=customer_pro) (`customer_pro` / `Solaris@2026`)

### 3. Cổng Socket.io Real-time
* **Kiểm tra trạng thái máy chủ (Health Check):** [http://localhost:3001/health](http://localhost:3001/health)
* **Kích hoạt phát tín hiệu Real-time test:** [http://localhost:3001/broadcast?event=solar:action&type=test](http://localhost:3001/broadcast?event=solar:action&type=test)

---

## 📁 V. CẤU TRÚC THƯ MỤC DỰ ÁN

```
PHP/Doan/
├── app/
│   ├── Config/
│   │   └── Database.php             # Singleton PDO kết nối CSDL MySQL solaris
│   ├── Hooks/
│   │   ├── UseAuthSessionState.php  # Quản lý phiên làm việc & phân quyền RBAC
│   │   ├── UseFileUpload.php        # Upload file an toàn, băm SHA-256
│   │   ├── UsePaginationState.php   # Phân trang tự động danh sách
│   │   └── UseSolarCalculator.php   # Thuật toán tính toán quang điện, hoàn vốn & ghép đôi
│   └── Modules/
│       ├── Member1_Lead/            # TV1 Đạt: AuthController, ProfileController, HomeController
│       ├── Member2_Products/        # TV2 Hải: ProductController, Giỏ hàng Kế hoạch
│       ├── Member3_Simulator/       # TV3 Toàn: SolarDemoController, Mô phỏng 2D/3D
│       └── Member4_Installation/    # TV4 Thịnh: InstallationController, 4 giai đoạn thi công
├── database/
│   ├── solaris_data_insert.sql      # File INSERT đồng bộ dữ liệu mẫu 17 bảng (KHUYẾN NGHỊ)
│   ├── solaris_full.sql             # Toàn bộ Schema + Dữ liệu mẫu hoàn chỉnh
│   └── solaris_seed.sql             # Dữ liệu seed chuẩn hóa
├── docs/
│   ├── TAI_LIEU_DAC_TA_HE_THONG_SOLARIS.md # Báo cáo đặc tả kỹ thuật chi tiết
│   ├── TV4_INSTALLATION.md
│   └── TV4_THAY_DOI_CHUC_NANG_LUONG_CODE.md
├── resources/
│   ├── css/
│   │   └── solaris-theme.css        # Hệ thống Design System biến CSS, animations, glassmorphism
│   └── views/
│       ├── components/              # Các UI atomic & composite components
│       └── pages/                   # Giao diện auth, home, products, cart, calculator, installations, profile
├── socket_server/
│   ├── server.js                    # Máy chủ Socket.io Real-time cổng 3001
│   └── package.json
├── index.php                        # Bộ điều phối trung tâm toàn bộ ứng dụng
├── solaris_data_insert.sql          # Bản sao file insert dữ liệu tại thư mục gốc
├── TAI_LIEU_DAC_TA_HE_THONG_SOLARIS.doc # File Word nộp bài của nhóm
└── README.md                        # Hướng dẫn tổng quan dự án trên GitHub
```

---
© 2026 **SOLARIS Energy System**. Giữ toàn quyền sở hữu trí tuệ đồ án học phần Phát Triển Phần Mềm Mã Nguồn Mở.
