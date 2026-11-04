# BỘ GIÁO DỤC VÀ ĐÀO TẠO
# TRƯỜNG ĐẠI HỌC NHA TRANG
## KHOA CÔNG NGHỆ THÔNG TIN

---

# BÁO CÁO BÀI TẬP LỚN
### MÔN HỌC: PHÁT TRIỂN PHẦN MỀM MÃ NGUỒN MỞ

---

## **ĐỀ TÀI:**
# XÂY DỰNG HỆ THỐNG QUẢN LÝ, TƯ VẤN, DEMO THIẾT KẾ VÀ QUY TRÌNH LẮP ĐẶT ĐIỆN MẶT TRỜI (SOLARIS ENERGY SYSTEM)

---

### **SINH VIÊN THỰC HIỆN:**
1. **Nguyễn Huy Đạt** - MSSV: `65130410` *(Trưởng nhóm)*
2. **Lê Chí Hải** - MSSV: `65130100`
3. **Mai Đức Toàn** - MSSV: `65133959` *(Solar Simulator & Data)*
4. **Phan Trần Thịnh** - MSSV: `65133649`

* **Lớp:** `65.CNTT-2`
* **Giảng viên hướng dẫn:** *ThS. Bùi Chí Thành* *(hoặc Giảng viên phụ trách môn học)*
* **Thời gian thực hiện:** Học kỳ II - Năm học 2025 - 2026 (Khánh Hòa, 2026)

---

\newpage

# BẢNG PHÂN CHIA CÔNG VIỆC VÀ TỶ LỆ ĐÓNG GÓP

| STT | Họ và Tên | MSSV | Vai trò chính trong dự án | Phân hệ / Nhiệm vụ phụ trách | Bài tập PHP & MySQL | Tỷ lệ đóng góp |
|:---:|:---|:---:|:---|:---|:---:|:---:|
| **1** | **Nguyễn Huy Đạt** | **65130410** | **Trưởng nhóm (PM)**<br>Fullstack Core Architect | • Thiết kế kiến trúc điều hướng trung tâm `index.php`, Session Auth, Dual Navigation (Bar ngang $\leftrightarrow$ Sidebar dọc 1s).<br>• Cổng Đăng nhập/Đăng ký Gmail xác thực OTP 6 số + Mail Sandbox.<br>• Máy chủ WebSocket/Socket.io Real-time Port 3001.<br>• Trang Profile cá nhân, hiệu ứng Solar Shockwave. | **Bài 1 - 6**<br>(Cơ bản PHP, Form, Xử lý chuỗi & mảng) | **35%** |
| **2** | **Lê Chí Hải** | **65130100** | **Frontend Lead**<br>Component Architect | • Thiết kế Design System, Obsidian Void Theme & CSS Variables.<br>• Phân hệ Danh mục 26 thiết bị quang điện Tier-1.<br>• Lọc phân trang với Hook `UsePaginationState.php`.<br>• Đồng bộ Giỏ kế hoạch thiết bị dự định (Cart). | **Bài 7 - 12**<br>(Form nâng cao, Ma trận & Mảng 2 chiều) | **20%** |
| **3** | **Mai Đức Toàn** | **65133959** | **Simulator & Data**<br>**Simulator & Sizing Engine** | • Bộ công cụ tính toán quang điện & hoàn vốn `UseSolarCalculator.php`.<br>• Mô phỏng sơ đồ mảng pin 2D/3D & bóc tách vật tư kỹ thuật BOM.<br>• Quản trị CSDL 17 bảng. | **Bài 13 - 18**<br>(OOP, Thao tác kết nối MySQL & Bảng) | **25%** |
| **4** | **Phan Trần Thịnh** | **65133649** | **Workflow & QA Lead** | • Quy trình thi công lắp đặt 4 giai đoạn chuẩn EVN.<br>• Tra cứu hồ sơ công trình mã định danh `SOLAR-YYYY-XXX`.<br>• Bộ tải tệp tin an toàn `UseFileUpload.php` băm SHA-256.<br>• Nghiệm thu kỹ thuật & kiểm thử chất lượng hệ thống (QA/QC). | **Bài 19 - 24**<br>(CSDL Quản lý bán sữa có phân trang, giỏ hàng, tìm kiếm) | **20%** |

---

\newpage

# MỤC LỤC

- [CHƯƠNG I: PHÂN TÍCH VÀ ĐẶC TẢ YÊU CẦU](#chương-i-phân-tích-và-đặc-tả-yêu-cầu)
  - [1.1. Khảo sát hiện trạng](#11-khảo-sát-hiện-trạng)
    - [1.1.1. Hiện trạng tổ chức doanh nghiệp năng lượng](#111-hiện-trạng-tổ-chức-doanh-nghiệp-năng-lượng)
    - [1.1.2. Hiện trạng nghiệp vụ tư vấn, thiết kế và thi công điện mặt trời](#112-hiện-trạng-nghiệp-vụ-tư-vấn-thiết-kế-và-thi-công-điện-mặt-trời)
    - [1.1.3. Hiện trạng tin học hóa và nhu cầu ứng dụng mã nguồn mở](#113-hiện-trạng-tin-học-hóa-và-nhu-cầu-ứng-dụng-mã-nguồn-mở)
  - [1.2. Đặc tả hệ thống](#12-đặc-tả-hệ-thống)
    - [1.2.1. Yêu cầu chức năng theo từng phân hệ](#121-yêu-cầu-chức-năng-theo-từng-phân-hệ)
    - [1.2.2. Ma trận chức năng chi tiết và quy định nghiệp vụ](#122-ma-trận-chức-năng-chi-tiết-và-quy-định-nghiệp-vụ)
    - [1.2.3. Yêu cầu phi chức năng và tiêu chí mã nguồn mở](#123-yêu-cầu-phi-chức-năng-và-tiêu-chí-mã-nguồn-mở)
- [CHƯƠNG II: THIẾT KẾ PHẦN MỀM](#chương-ii-thiết-kế-phần-mềm)
  - [2.1. Thiết kế dữ liệu](#21-thiết-kế-dữ-liệu)
    - [2.1.1. Danh sách các bảng dữ liệu (Core Schema & Thực hành)](#211-danh-sách-các-bảng-dữ-liệu-core-schema--thực-hành)
    - [2.1.2. Mối quan hệ giữa các bảng](#212-mối-quan-hệ-giữa-các-bảng)
    - [2.1.3. Từ điển dữ liệu (Bảng thuộc tính chi tiết)](#213-từ-điển-dữ-liệu-bảng-thuộc-tính-chi-tiết)
    - [2.1.4. Sơ đồ thực thể liên kết (ERD)](#214-sơ-đồ-thực-thể-liên-kết-erd)
    - [2.1.5. Sơ đồ luồng dữ liệu (DFD) và Ca sử dụng (Use Case)](#215-sơ-đồ-luồng-dữ-liệu-dfd-và-ca-sử-dụng-use-case)
  - [2.2. Thiết kế giao diện](#22-thiết-kế-giao-diện)
    - [2.2.1. Ngôn ngữ thiết kế (Design System: Obsidian Void & Solar Gold)](#221-ngôn-ngữ-thiết-kế-design-system-obsidian-void--solar-gold)
    - [2.2.2. Thiết kế giao diện chi tiết các phân hệ](#222-thiết-kế-giao-diện-chi-tiết-các-phân-hệ)
  - [2.3. Thiết kế xử lý thuật toán cốt lõi](#23-thiết-kế-xử-lý-thuật-toán-cốt-lõi)
    - [2.3.1. Thuật toán tính toán phụ tải, diện tích mái và hoàn vốn (Solar Engine)](#231-thuật-toán-tính-toán-phụ-tải-diện-tích-mái-và-hoàn-vốn-solar-engine)
    - [2.3.2. Thuật toán xác thực OTP 6 số qua Mail Sandbox và bảo mật Session](#232-thuật-toán-xác-thực-otp-6-số-qua-mail-sandbox-và-bảo-mật-session)
    - [2.3.3. Cơ chế giao tiếp Real-time qua WebSocket / Socket.io](#233-cơ-chế-giao-tiếp-real-time-qua-websocket--socketio)
    - [2.3.4. Cơ chế tải tệp tin an toàn băm SHA-256](#234-cơ-chế-tải-tệp-tin-an-toàn-băm-sha-256)
- [CHƯƠNG III: CÀI ĐẶT PHẦN MỀM](#chương-iii-cài-đặt-phần-mềm)
  - [3.1. Khái quát cài đặt phần mềm](#31-khái-quát-cài-đặt-phần-mềm)
  - [3.2. Lựa chọn ngôn ngữ, công nghệ và môi trường cài đặt](#32-lựa-chọn-ngôn-ngữ-công-nghệ-và-môi-trường-cài-đặt)
  - [3.3. Cấu trúc mã nguồn mã nguồn mở](#33-cấu-trúc-mã-nguồn-mã-nguồn-mở)
  - [3.4. Quy trình cài đặt, triển khai và chạy hệ thống](#34-quy-trình-cài-đặt-triển-khai-và-chạy-hệ-thống)
  - [3.5. Danh mục các module đã cài đặt](#35-danh-mục-các-module-đã-cài-đặt)
  - [3.6. Tích hợp phân hệ 24 bài tập thực hành PHP & MySQL](#36-tích-hợp-phân-hệ-24-bài-tập-thực-hành-php--mysql)
- [CHƯƠNG IV: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN](#chương-iv-kết-luận-và-hướng-phát-triển)
  - [4.1. Kết quả đạt được](#41-kết-quả-đạt-được)
  - [4.2. Hạn chế](#42-hạn-chế)
  - [4.3. Hướng phát triển trong tương lai](#43-hướng-phát-triển-trong-tương-lai)
- [TÀI LIỆU THAM KHẢO](#tài-liệu-tham-khảo)

---

\newpage

# NỘI DUNG CHI TIẾT CÁC CHƯƠNG

## CHƯƠNG I: PHÂN TÍCH VÀ ĐẶC TẢ YÊU CẦU

### 1.1. Khảo sát hiện trạng
* **1.1.1. Hiện trạng tổ chức doanh nghiệp năng lượng:**
  * Giới thiệu mô hình kinh doanh của các đơn vị EPC (Engineering, Procurement, Construction) điện mặt trời mái nhà và công nghiệp tại Việt Nam.
  * Cơ cấu tổ chức: Ban Giám đốc $\rightarrow$ Phòng Kinh doanh/Tư vấn $\rightarrow$ Phòng Kỹ thuật/Thiết kế $\rightarrow$ Đội ngũ Thi công/Khảo sát thực địa.
* **1.1.2. Hiện trạng nghiệp vụ tư vấn, thiết kế và thi công điện mặt trời:**
  * Khó khăn trong khâu tiếp cận khách hàng: Khách hàng chưa nắm rõ công suất cần lắp, thời gian hoàn vốn (ROI), số lượng tấm pin phù hợp với hóa đơn tiền điện hàng tháng.
  * Thiếu công cụ demo trực quan thời gian thực: Việc gửi bảng vẽ CAD, bóc tách khối lượng (BOM) và báo giá qua email/zalo mất nhiều thời gian, dễ thất lạc.
  * Theo dõi tiến độ lắp đặt thủ công: 4 giai đoạn chuẩn EVN (Khảo sát $\rightarrow$ Demo BOM $\rightarrow$ Thi công khung giàn & đấu nối $\rightarrow$ Nghiệm thu đóng điện) chưa được số hóa minh bạch.
* **1.1.3. Hiện trạng tin học hóa và nhu cầu ứng dụng mã nguồn mở:**
  * Chi phí bản quyền phần mềm đóng (proprietary software) cho ngành điện mặt trời (như PVsyst, AutoCAD) rất đắt đỏ, không phù hợp cho khách hàng phổ thông tự tra cứu.
  * Xu hướng xây dựng giải pháp mã nguồn mở (FOSS): Sử dụng PHP Native, MySQL/MariaDB, Apache và WebSocket giúp hệ thống triển khai linh hoạt trên mọi máy chủ, giảm tối đa chi phí bản quyền và dễ dàng bảo trì, mở rộng.

---

### 1.2. Đặc tả hệ thống
#### 1.2.1. Yêu cầu chức năng theo từng phân hệ
* **Phân hệ 1: Xác thực & Điều hướng trung tâm (Trưởng nhóm Nguyễn Huy Đạt)**
  * Đăng nhập, Đăng ký xác thực bằng mã OTP 6 số gửi qua Gmail.
  * Cơ chế Mail Sandbox mô phỏng trực tiếp trên giao diện dành cho môi trường Localhost/Kiểm thử.
  * Nút truy cập nhanh 1-Click Tester Accounts (Admin, Technician, Customer).
  * Thanh điều hướng thông minh Dual Navigation (Header ngang ở Trang chủ $\leftrightarrow$ Sidebar dọc mượt mà ở Trang chức năng).
  * Socket.io Real-time Gateway (Port 3001) đồng bộ hành động và trạng thái người dùng trên nhiều tab.
* **Phân hệ 2: Danh mục & Kế hoạch thiết bị quang điện (Lê Chí Hải)**
  * Quản lý danh mục 26 thiết bị Tier-1 (Tấm pin năng lượng, Biến tần Inverter, Pin lưu trữ Hybrid ESS).
  * Lọc theo danh mục, công suất, hãng sản xuất, giá thành.
  * Phân trang tự động bằng Custom Hook `UsePaginationState.php`.
  * Giỏ kế hoạch thiết bị dự định (Equipment Planning Cart) chuyển nhanh 1-click sang bài toán tính toán hoàn vốn.
* **Phân hệ 3: Tư vấn kỹ thuật, Mô phỏng & Báo giá BOM (Mai Đức Toàn)**
  * Bộ công cụ tính toán Solar ROI Engine dựa trên hóa đơn tiền điện (VNĐ/tháng), diện tích mái khả dụng ($m^2$) và bức xạ 3 miền (Bắc 3.8h, Trung 4.8h, Nam 5.2h).
  * Tự động sinh công suất khuyến nghị ($kWp$), số lượng tấm pin, số biến tần, diện tích chiếm dụng và biểu đồ hoàn vốn đầu tư.
  * Xuất bảng bóc tách vật tư kỹ thuật (BOM - Bill of Materials).
  * Lưu trữ dự toán vào CSDL `solar_quotes`.
* **Phân hệ 4: Quản lý quy trình lắp đặt & Khảo sát hiện trường (Phan Trần Thịnh)**
  * Quản lý tiến trình công trình theo 4 giai đoạn chuẩn hóa EVN:
    1. Tiếp nhận & Khảo sát thực địa (Upload ảnh mái nhà băm SHA-256).
    2. Thiết kế Demo 2D/3D & Duyệt BOM vật tư.
    3. Thi công khung giàn & Đấu nối Inverter/Pin lưu trữ.
    4. Nghiệm thu kỹ thuật, đo kiểm an toàn điện & Đóng điện hòa lưới EVN.
  * Tra cứu nhanh trạng thái dự án bằng mã công trình `SOLAR-YYYY-XXX`.
* **Phân hệ 5: Cổng 24 Bài tập thực hành PHP & MySQL (Toàn bộ 4 thành viên)**
  * Tích hợp đầy đủ 24 bài tập lập trình mã nguồn mở của môn học trực tiếp vào menu hệ thống:
    * *Bài 1 - 6 (TV1 Đạt):* Các bài toán xử lý Form, mảng, chuỗi, tính toán cơ bản.
    * *Bài 7 - 12 (TV2 Hải):* Form nâng cao, ma trận số, kết hợp dữ liệu.
    * *Bài 13 - 18 (TV3 Toàn):* Lập trình hướng đối tượng OOP, kết nối CSDL MySQL cơ bản.
    * *Bài 19 - 24 (TV4 Thịnh):* CSDL Quản lý bán sữa nâng cao (Phân trang, xem chi tiết, giỏ hàng, tìm kiếm đơn giản & nâng cao).

#### 1.2.2. Ma trận chức năng chi tiết (Format chuẩn BTL_BaoCao)

| STT | Tên công việc / Nghiệp vụ | Loại công việc | Thành viên phụ trách | Quy định / Biểu mẫu liên quan | Diễn giải chức năng |
|:---:|:---|:---:|:---|:---|:---|
| 1 | Xác thực tài khoản qua OTP Gmail | Xử lý / Lưu trữ | Nguyễn Huy Đạt | QĐ_AUTH_01<br>BM_OTP_MAIL | Sinh mã OTP ngẫu nhiên 6 số, gửi email HTML và mở Mail Sandbox nếu kiểm thử offline. |
| 2 | Chuyển đổi giao diện Dual Navigation | Điều hướng | Nguyễn Huy Đạt | QĐ_UI_NAV | Chuyển mượt mà từ thanh ngang sang thanh dọc khi vào sâu các trang chức năng. |
| 3 | Phát sóng thời gian thực Socket.io | Truyền thông | Nguyễn Huy Đạt | QĐ_SOCKET_01 | Phát các sự kiện `solar:action`, đồng bộ tab client qua Port 3001. |
| 4 | Xem danh mục thiết bị Tier-1 | Tra cứu | Lê Chí Hải | BM_PROD_01 | Hiển thị 26 sản phẩm gồm pin Canadian/LONGi, Inverter Sungrow, pin lưu trữ. |
| 5 | Lọc và phân trang thiết bị | Tính toán / Tra cứu | Lê Chí Hải | QĐ_PAGINATE | Phân trang tự động bằng `UsePaginationState.php`, giới hạn số dòng cấu hình động. |
| 6 | Quản lý Giỏ dự định thiết bị | Lưu trữ tạm | Lê Chí Hải | BM_CART_01 | Thêm/xóa sản phẩm, chuyển cấu hình thiết bị sang công cụ tính toán Solar Demo. |
| 7 | Tính toán phụ tải và thời gian hoàn vốn | Tính toán | Mai Đức Toàn | QĐ_CALC_SOLAR<br>BM_QUOTE_01 | Dựa trên số tiền điện, vị trí địa lý 3 miền để tính $kWp$, số tấm pin, năm hoàn vốn. |
| 8 | Bóc tách vật tư kỹ thuật BOM | Tính toán / Báo cáo | Mai Đức Toàn | BM_BOM_01 | Liệt kê chi tiết pin, inverter, giàn khung, cáp chuyên dụng DC/AC, tủ điện. |
| 9 | Quản trị CSDL và lưu trữ báo giá | Lưu trữ | Mai Đức Toàn | QĐ_DB_SCHEMA | Ghi nhận thông tin dự toán vào bảng `solar_quotes` và đồng bộ tài khoản người dùng. |
| 10 | Đăng ký khảo sát & tải ảnh mặt bằng | Lưu trữ / Upload | Phan Trần Thịnh | QĐ_UPLOAD_FILE<br>BM_SURVEY_01 | Upload file thiết kế, ảnh chụp hiện trạng mái, kiểm tra mã MIME và băm SHA-256. |
| 11 | Cập nhật tiến độ 4 giai đoạn EVN | Quản lý tiến trình | Phan Trần Thịnh | QĐ_EVN_STAGE | Cập nhật từ Giai đoạn 1 (Khảo sát) đến Giai đoạn 4 (Nghiệm thu đóng điện). |
| 12 | Tra cứu hồ sơ theo mã SOLAR-YYYY-XXX | Tra cứu | Phan Trần Thịnh | BM_SEARCH_PROJ | Nhập mã định danh dự án để xem biên bản kỹ thuật, nhật ký thi công và ảnh bàn giao. |
| 13 | Thực thi 24 bài tập thực hành PHP & MySQL | Nghiệp vụ học tập | Cả 4 thành viên | BM_LAB_01 $\rightarrow$ 24 | Chạy trực tiếp các bài tập môn học từ giao diện điều hướng của đồ án. |

#### 1.2.3. Yêu cầu phi chức năng và tiêu chí mã nguồn mở
* **Tính tuân thủ mã nguồn mở (Open Source Compliance):**
  * Sử dụng 100% công nghệ nền tảng mã nguồn mở (Native PHP 8.2, Apache 2.4, MariaDB 10.4, Node.js, Linux/XAMPP).
  * Công bố mã nguồn trên GitHub với Giấy phép phần mềm tự do nguồn mở (GNU General Public License v3.0 / MIT License).
* **Hiệu năng & Tối ưu hóa:**
  * Thời gian phản hồi trang dưới 500ms đối với Native PHP.
  * Không phụ thuộc vào các framework nặng nề bên ngoài, tận dụng Native Component Pattern.
* **Bảo mật & Toàn vẹn dữ liệu:**
  * Phòng chống tấn công SQL Injection thông qua Prepared Statements (PDO / MySQLi).
  * Phòng chống XSS bằng hàm lọc dữ liệu đầu ra `htmlspecialchars()`.
  * Upload file an toàn: Giới hạn định dạng MIME (image/jpeg, png, pdf), kiểm tra dung lượng tối đa và đổi tên tệp tin bằng mã băm SHA-256.
  * Bảo vệ phiên người dùng: Chống session hijacking, cookie `HttpOnly`.

---

## CHƯƠNG II: THIẾT KẾ PHẦN MỀM

### 2.1. Thiết kế dữ liệu
#### 2.1.1. Danh sách các bảng dữ liệu (Core Schema & Hệ thống Bán sữa)
Hệ thống Solaris bao gồm **17 bảng dữ liệu** được chuẩn hóa ở dạng chuẩn 3NF:
* **Nhóm 1: Phân hệ Lõi Năng Lượng Mặt Trời (Solaris Core - 6 bảng):**
  1. `users`: Lưu trữ thông tin tài khoản, mật khẩu băm, quyền hạn (`admin`, `technician`, `customer`), mã OTP.
  2. `products`: Lưu trữ danh mục tấm pin năng lượng, biến tần inverter, pin lưu trữ dung lượng cao.
  3. `solar_quotes`: Lưu trữ kết quả tính toán mô phỏng hệ thống điện mặt trời, chi phí dự kiến, số tiền tiết kiệm.
  4. `projects`: Lưu trữ hồ sơ công trình lắp đặt thực tế, tiến độ thi công, khách hàng đại diện, công suất hợp đồng.
  5. `project_stage_logs`: Lưu lịch sử chuyển đổi 4 giai đoạn thi công chuẩn hóa EVN, ghi chú kỹ thuật viên.
  6. `project_documents`: Lưu trữ chứng chỉ an toàn, tệp CAD mặt bằng, ảnh chụp hiện trạng mái nhà trước và sau thi công.
* **Nhóm 2: Phân hệ CSDL Thực hành Quản lý Bán sữa (Phục vụ Bài tập 19 - 24):**
  7. `hang_sua`: Hãng sản xuất (Vinamilk, Nutifood, Dutch Lady, Abbott...).
  8. `loai_sua`: Phân loại sữa (Sữa tươi, sữa bột, sữa chua...).
  9. `sua`: Danh mục sản phẩm sữa, đơn giá, trọng lượng, thành phần dinh dưỡng, hình ảnh.
  10. `khach_hang`: Danh sách khách hàng và thông tin liên hệ.
  11. `hoa_don`: Thông tin hóa đơn bán hàng.
  12. `ct_hoadon`: Chi tiết mặt hàng trong hóa đơn.
  *(và các bảng danh mục phụ trợ khác)*.

#### 2.1.2. Mối quan hệ giữa các bảng
* `users` $(1) \longleftrightarrow (N)$ `solar_quotes` (Một khách hàng có thể tạo nhiều dự toán tính toán).
* `users` $(1) \longleftrightarrow (N)$ `projects` (Một khách hàng sở hữu nhiều dự án công trình).
* `projects` $(1) \longleftrightarrow (N)$ `project_stage_logs` (Một công trình có nhiều nhật ký ghi nhận tiến độ qua các giai đoạn).
* `projects` $(1) \longleftrightarrow (N)$ `project_documents` (Một công trình có nhiều hồ sơ, ảnh hiện trường và biên bản kỹ thuật).
* `hang_sua` $(1) \longleftrightarrow (N)$ `sua` & `loai_sua` $(1) \longleftrightarrow (N)$ `sua` (Quan hệ phân loại sản phẩm trong CSDL thực hành).

#### 2.1.3. Từ điển dữ liệu (Bảng thuộc tính mẫu chi tiết)

##### Bảng `users` (Quản lý tài khoản & Xác thực)
| Tên thuộc tính | Kiểu dữ liệu | Khóa | Ràng buộc | Diễn giải |
|:---|:---|:---:|:---|:---|
| `id` | `INT` | **PK** | AUTO_INCREMENT | Mã định danh tài khoản duy nhất |
| `username` | `VARCHAR(50)` | | UNIQUE, NOT NULL | Tên đăng nhập |
| `email` | `VARCHAR(100)` | | UNIQUE, NOT NULL | Địa chỉ Gmail nhận mã xác thực OTP |
| `password` | `VARCHAR(255)` | | NOT NULL | Mật khẩu tài khoản (băm bcrypt) |
| `full_name` | `VARCHAR(100)` | | NOT NULL | Họ và tên người dùng |
| `phone` | `VARCHAR(20)` | | NULL | Số điện thoại liên hệ |
| `role` | `ENUM` | | 'admin', 'technician', 'customer' | Quyền hạn tài khoản trong hệ thống |
| `otp_code` | `VARCHAR(6)` | | NULL | Mã OTP 6 chữ số dùng cho xác thực Gmail |
| `otp_expires_at`| `DATETIME` | | NULL | Thời gian hết hạn của mã OTP (10 phút) |
| `created_at` | `TIMESTAMP` | | DEFAULT CURRENT_TIMESTAMP | Thời gian tạo tài khoản |

##### Bảng `solar_quotes` (Dự toán & Báo giá điện mặt trời)
| Tên thuộc tính | Kiểu dữ liệu | Khóa | Ràng buộc | Diễn giải |
|:---|:---|:---:|:---|:---|
| `quote_id` | `INT` | **PK** | AUTO_INCREMENT | Mã báo giá dự toán |
| `user_id` | `INT` | **FK** | REFERENCES `users(id)` | Mã khách hàng yêu cầu báo giá |
| `monthly_bill` | `DECIMAL(15,2)`| | NOT NULL | Số tiền điện chi trả hàng tháng (VNĐ) |
| `region` | `VARCHAR(50)` | | NOT NULL | Miền lắp đặt ('bac', 'trung', 'nam') |
| `roof_area` | `FLOAT` | | NOT NULL | Diện tích mái khả dụng ($m^2$) |
| `recommended_kwp`| `FLOAT` | | NOT NULL | Công suất khuyến nghị lắp đặt ($kWp$) |
| `total_investment`| `DECIMAL(15,2)`| | NOT NULL | Tổng mức vốn đầu tư ước tính (VNĐ) |
| `payback_years` | `FLOAT` | | NOT NULL | Thời gian hoàn vốn dự kiến (Số năm) |
| `created_at` | `TIMESTAMP` | | DEFAULT CURRENT_TIMESTAMP | Ngày tạo dự toán |

##### Bảng `projects` (Quản lý dự án thi công lắp đặt)
| Tên thuộc tính | Kiểu dữ liệu | Khóa | Ràng buộc | Diễn giải |
|:---|:---|:---:|:---|:---|
| `project_id` | `INT` | **PK** | AUTO_INCREMENT | ID dự án |
| `project_code` | `VARCHAR(50)` | | UNIQUE, NOT NULL | Mã công trình chuẩn hóa `SOLAR-YYYY-XXX` |
| `customer_id` | `INT` | **FK** | REFERENCES `users(id)` | Mã khách hàng chủ đầu tư |
| `system_capacity`| `FLOAT` | | NOT NULL | Công suất hệ thống lắp đặt ($kWp$) |
| `current_stage` | `INT` | | DEFAULT 1 (1 - 4) | Giai đoạn hiện tại theo quy trình EVN |
| `status` | `ENUM` | | 'survey', 'design', 'install', 'completed' | Trạng thái dự án |
| `installed_address`| `TEXT` | | NOT NULL | Địa chỉ thi công công trình |
| `created_at` | `TIMESTAMP` | | DEFAULT CURRENT_TIMESTAMP | Thời điểm khởi tạo dự án |

#### 2.1.4. Sơ đồ thực thể liên kết (ERD)
*(Xem trực quan trong sơ đồ Mermaid ở trên hoặc tài liệu đính kèm)*

---

### 2.2. Thiết kế giao diện
* **2.2.1. Ngôn ngữ thiết kế (Design System):**
  * Chủ đề: **Obsidian Void & Solar Gold**
  * Mã màu CSS Variables trung tâm (`resources/css/solaris-theme.css`):
    * Nền chính: `--bg-primary: #080c14` (Không gian vũ trụ sâu thẳm).
    * Mặt phẳng nổi: `--bg-surface: #0f172a`, Thẻ hiển thị: `--bg-card: #141e33`.
    * Điểm nhấn: `--accent-gold: #f59e0b` (Màu quang năng rực rỡ), hiệu ứng sáng: `--accent-glow`.
    * Màu chữ: `--text-main: #f8fafc`, `--text-muted: #94a3b8`.
* **2.2.2. Các màn hình chức năng chính:**
  * Màn hình Đăng nhập & Xác thực Gmail OTP Sandbox.
  * Trang chủ & Thanh điều hướng Dual Navigation.
  * Màn hình Danh mục thiết bị Tier-1 & Giỏ kế hoạch.
  * Màn hình Solar Calculator & Sơ đồ mảng pin 2D/3D.
  * Màn hình Quy trình thi công lắp đặt 4 giai đoạn.
  * Trang Hồ sơ cá nhân (Profile) & Modal nghiệm thu DOC.
  * Cổng thực hành 24 bài tập PHP & MySQL.

---

### 2.3. Thiết kế xử lý thuật toán cốt lõi
* **2.3.1. Thuật toán tính toán phụ tải và hoàn vốn (Solar Engine):**
  * Xác định sản lượng điện: $E_{month} = \frac{\text{Tiền điện}}{\text{Đơn giá}}$.
  * Công suất lắp đặt: $P_{kWp} = \frac{E_{month}}{30 \times H_{sun} \times \eta}$.
  * Số lượng tấm pin và năm hoàn vốn dự kiến.
* **2.3.2. Thuật toán xác thực OTP 6 số qua Mail Sandbox.**
* **2.3.3. Cơ chế đồng bộ Real-time qua Socket.io (Port 3001).**
* **2.3.4. Cơ chế tải tệp tin an toàn băm SHA-256.**

---

## CHƯƠNG III: CÀI ĐẶT PHẦN MỀM

### 3.1. Khái quát cài đặt phần mềm
* Dự án xây dựng trên nền tảng **PHP Native 8.2** với mô hình Component / MVC linh hoạt kết hợp Custom Hooks, đảm bảo nhẹ, độc lập và tuân thủ các quy tắc mã nguồn mở.

### 3.2. Lựa chọn ngôn ngữ, công nghệ và môi trường cài đặt (Đặc thù môn PTPMMNM)
* PHP 8.2+, MySQL/MariaDB 10.4+, Apache HTTP Server 2.4, Node.js (Socket.io), XAMPP Windows, Git & GitHub, Giấy phép GNU GPLv3.

### 3.3. Cấu trúc mã nguồn đã cài đặt
* Thư mục phân chia rõ ràng theo từng thành viên (`Member1_Lead`, `Member2_Products`, `Member3_Simulator`, `Member4_Installation`), thư mục `Hooks/`, `resources/views/components/`, `database/`, `socket_server/`.

### 3.4. Quy trình cài đặt và chạy project
* 5 bước chuẩn: Cài môi trường $\rightarrow$ Triển khai mã nguồn $\rightarrow$ Import CSDL `solaris_data_insert.sql` $\rightarrow$ Chạy Socket Server (`node server.js`) $\rightarrow$ Truy cập và kiểm thử 1-Click Tester Accounts.

### 3.5. Danh mục các module đã cài đặt
* Bảng tổng hợp chi tiết 10 module từ Xác thực OTP, Dual Nav, Socket Real-time, Catalog thiết bị, Hook phân trang, Solar Calculator, Bóc tách BOM, Workflow lắp đặt 4 giai đoạn, Upload file SHA-256 đến Cổng 24 bài tập PHP.

### 3.6. Tích hợp phân hệ 24 bài tập thực hành PHP & MySQL
* Phân bổ cụ thể theo 4 thành viên: Bài 1-6 (Đạt), Bài 7-12 (Hải), Bài 13-18 (Toàn), Bài 19-24 (Thịnh - CSDL Quản lý bán sữa nâng cao).

---

## CHƯƠNG IV: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN
* **4.1. Kết quả đạt được:** Hoàn thành 100% mục tiêu mã nguồn mở, vận hành ổn định trên XAMPP, đột phá giao diện Dual Nav & OTP Mail Sandbox.
* **4.2. Hạn chế:** Dữ liệu bức xạ chưa tích hợp API thời tiết vệ tinh thời gian thực, chưa có cổng thanh toán online.
* **4.3. Hướng phát triển:** AI phân tích mặt bằng mái qua ảnh vệ tinh, kết nối IoT Inverter, đóng gói Docker Container (`docker-compose`).

---

## TÀI LIỆU THAM KHẢO
1. PHP Documentation Group (PHP 8.2+).
2. The Apache Software Foundation (Apache 2.4).
3. MariaDB / MySQL Reference Manual.
4. Socket.IO Official Documentation.
5. Quy chuẩn kỹ thuật quốc gia về an toàn điện & Đấu nối điện mặt trời (Bộ Công Thương).
6. GNU General Public License v3 (FSF).
7. Bài giảng Thực hành Phát triển phần mềm mã nguồn mở (Khoa CNTT - ĐH Nha Trang).
