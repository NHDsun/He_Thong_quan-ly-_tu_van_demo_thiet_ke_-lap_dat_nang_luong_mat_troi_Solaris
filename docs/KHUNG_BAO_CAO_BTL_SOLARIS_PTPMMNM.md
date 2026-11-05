# BỘ GIÁO DỤC VÀ ĐÀO TẠO
# TRƯỜNG ĐẠI HỌC NHA TRANG
## KHOA CÔNG NGHỆ THÔNG TIN

---

# BÁO CÁO BÀI TẬP LỚN
## MÔN HỌC: PHÁT TRIỂN PHẦN MỀM MÃ NGUỒN MỞ

---

## **ĐỀ TÀI:**
# XÂY DỰNG HỆ THỐNG QUẢN LÝ, TƯ VẤN, DEMO THIẾT KẾ VÀ QUY TRÌNH LẮP ĐẶT ĐIỆN MẶT TRỜI (SOLARIS)

---

### **SINH VIÊN THỰC HIỆN:**
| Sinh viên thực hiện | MSSV | Vai trò kỹ thuật trong dự án Solaris | Nhiệm vụ bỏ ảnh (67 hình) |
| :--- | :---: | :--- | :---: |
| 1. **Nguyễn Huy Đạt** *(Trưởng nhóm)* | 65130410 | Fullstack Core, Auth OTP, Socket.io, Dual Nav | **0 hình** *(Điều phối tổng thể)* |
| 2. **Lê Chí Hải** | 65130100 | Frontend Lead, Catalog 26 SP Tier-1, Cart | **15 hình** (22.4%) |
| 3. **Mai Đức Toàn** | 65133959 | Simulator & Data, Sizing Engine, SVG 2D, BOM | **37 hình** *(Chiếm phần lớn: 55.2%)* |
| 4. **Phan Trần Thịnh** | 65133649 | Workflow & QA Lead, Lắp đặt EVN, Upload SHA-256 | **15 hình** (22.4%) |

* **Lớp:** `65.CNTT-2`
* **Giảng viên hướng dẫn:** `ThS. Bùi Chí Thành`
* **Khánh Hòa, Năm học 2025 - 2026**

---

\newpage

# BẢNG PHÂN CHIA CÔNG VIỆC HOÀN THIỆN BÁO CÁO NHÓM (CẬP NHẬT THEO BẢN CODE MỚI NHẤT)

| Thành viên & MSSV | Phân hệ kỹ thuật phụ trách (Codebase mới nhất) | Nhiệm vụ hoàn thiện Báo cáo (Chương, Bảng, Hình) | Phần việc Bỏ ảnh (67 hình) |
| :--- | :--- | :--- | :---: |
| **Nguyễn Huy Đạt**<br>*(Trưởng nhóm)*<br>`65130410` | • **Hệ thống & Xác thực (Fullstack Core)**:<br>- Front Controller `index.php`, Session Auth bảo mật.<br>- Cổng Gmail OTP Sandbox xác thực mã 6 số ngẫu nhiên ngoại tuyến.<br>- Máy chủ WebSocket Node.js Socket.io Port 3001 kèm **cơ chế giữ vị trí cuộn trang (Scroll Preservation)** khi reload đa tab.<br>- Điều hướng thông minh Dual Navigation (1s) với Sidebar tinh gọn (nút đăng xuất cùng dòng profile, tối ưu badge). | • Soạn thảo Chương I (1.1 Khảo sát hiện trạng, 1.2 Đặc tả hệ thống).<br>• Soạn thảo Mục 3.1 & 3.2 Chương III (Khái quát & Môi trường FOSS).<br>• Hoàn thiện 4 Bảng dữ liệu: Bảng 1, Bảng 2, Bảng 7, Bảng 17.<br>• Điều phối, biên tập và rà soát chuẩn hóa tổng thể tài liệu. | **0 hình**<br>*(Trưởng nhóm điều phối)* |
| **Lê Chí Hải**<br>`65130100` | • **Khách hàng & Thiết bị (Frontend Lead)**:<br>- Design System Obsidian Void & Solar Gold (CSS Variables).<br>- Catalog 26 thiết bị Tier-1 (Canadian Solar, LONGi, Sungrow...).<br>- **Hoàn thiện bộ lọc sản phẩm đa tiêu chí** (công suất, hãng, khoảng giá).<br>- Hook tự động phân trang `UsePaginationState.php`.<br>- Module Giỏ kế hoạch thiết bị (Cart) đồng bộ sang mô phỏng. | • Soạn thảo Mục 2.2 (Thiết kế giao diện người dùng).<br>• Soạn thảo Mục 3.3 (Cấu trúc mã nguồn module hóa).<br>• Hoàn thiện 5 Bảng dữ liệu: Bảng 3, Bảng 6, Bảng 8, Bảng 9, Bảng 18. | **15 hình**<br>*(Hình 1 - 4, 41 - 47, 61, 63 - 65)* |
| **Mai Đức Toàn**<br>`65133959` | • **Simulator & Data (Sizing Engine & Mô phỏng 2D)**:<br>- Bộ máy Solar ROI Engine tính toán kWp, hoàn vốn (`UseSolarCalculator.php`).<br>- Mô phỏng mặt bằng mái 2D SVG động theo hàng x cột và góc đón nắng tối ưu.<br>- Bóc tách vật tư kỹ thuật BOM và **tự động chuyển trạng thái `approved` cho báo giá `solar_quotes`** khi khách xác nhận.<br>- Quản trị CSDL quan hệ 11 bảng cốt lõi, chuẩn hóa utf8mb4. | • Soạn thảo Mục 2.1 (Thiết kế dữ liệu, quan hệ bảng, ERD, DFD).<br>• Soạn thảo Mục 3.4 (Quy trình cài đặt & chạy XAMPP/MySQL/Socket).<br>• Hoàn thiện 5 Bảng dữ liệu: Bảng 10, Bảng 16, Bảng phân công, Mục lục Bảng/Hình. | **37 hình**<br>*(Chiếm phần lớn: Hình 5 - 6, 12 - 40, 57 - 60, 62, 67)* |
| **Phan Trần Thịnh**<br>`65133649` | • **Lắp đặt & Bảo mật Tệp tin (Workflow & QA)**:<br>- Quy trình thi công 4 giai đoạn chuẩn EVN với **cơ chế tự động khởi tạo giai đoạn dự án khi khách hàng xem tiến độ**.<br>- Tra cứu hồ sơ công trình theo mã định danh `SOLAR-YYYY-XXX`.<br>- Hook tải tệp an toàn `UseFileUpload.php` băm SHA-256.<br>- Quản lý an toàn mảng tài liệu hiện trường tránh ngoại lệ runtime.<br>- Kiểm thử QA/QC và thủ tục nghiệm thu đóng điện EVN. | • Soạn thảo Mục 2.3 (Thiết kế xử lý và nghiệp vụ thi công).<br>• Soạn thảo Mục 3.5 (Danh sách module kỹ thuật đã cài đặt).<br>• Soạn thảo Chương IV (Kết luận, hạn chế, hướng mở rộng & 7 TLTK).<br>• Hoàn thiện 5 Bảng dữ liệu: Bảng 4, Bảng 5, Bảng 11 - 15, Bảng 19. | **15 hình**<br>*(Hình 7 - 11, 48 - 56, 66)* |

---

\newpage

# MỤC LỤC CHÍNH

* **CHƯƠNG I: PHÂN TÍCH VÀ ĐẶC TẢ YÊU CẦU**
  * 1.1. Khảo sát hiện trạng
    * 1.1.1. Hiện trạng tổ chức
    * 1.1.2. Hiện trạng nghiệp vụ
    * 1.1.3. Hiện trạng tin học
  * 1.2. Đặc tả hệ thống
    * 1.2.1. Danh sách các chức năng nghiệp vụ yêu cầu
* **CHƯƠNG II: THIẾT KẾ PHẦN MỀM**
  * 2.1. Thiết kế dữ liệu
    * 2.1.1. Các bảng dữ liệu
    * 2.1.2. Mối quan hệ giữa các bảng
    * 2.1.3. Bảng thuộc tính chi tiết (10 bảng cốt lõi)
    * 2.1.4. Sơ đồ thực thể liên kết ERD
    * 2.1.5. Sơ đồ luồng dữ liệu hệ thống (DFD mức 1 & mức 2)
  * 2.2. Thiết kế giao diện
  * 2.3. Thiết kế xử lý (10 chức năng chi tiết cập nhật codebase mới nhất)
* **CHƯƠNG III: CÀI ĐẶT PHẦN MỀM (TRỌNG TÂM MÔN PTPMMNM)**
  * 3.1. Khái quát cài đặt phần mềm
  * 3.2. Lựa chọn ngôn ngữ và môi trường cài đặt FOSS
  * 3.3. Cấu trúc mã nguồn đã cài đặt
  * 3.4. Quy trình cài đặt và chạy project
  * 3.5. Danh sách module đã cài đặt
* **CHƯƠNG IV: KẾT LUẬN**
  * 4.1. Kết quả đạt được
  * 4.2. Hạn chế
  * 4.3. Hướng phát triển và Kết luận
* **TÀI LIỆU THAM KHẢO** (7 tài liệu chuẩn IEEE)

---

\newpage

# MỤC LỤC BẢNG (19 BẢNG THUẦN SOLARIS)
* **Bảng 1:** Danh sách chức năng Phân hệ Hệ thống & Xác thực (Nguyễn Huy Đạt)
* **Bảng 2:** Danh sách chức năng Phân hệ Quản trị & Điều hành dự án (Nguyễn Huy Đạt)
* **Bảng 3:** Danh sách chức năng Phân hệ Khách hàng & Tư vấn thiết bị (Lê Chí Hải)
* **Bảng 4:** Danh sách chức năng Phân hệ Kỹ thuật viên & Lắp đặt công trình (Mai Đức Toàn & Phan Trần Thịnh)
* **Bảng 5:** Yêu cầu chức năng nghiệp vụ Phân hệ Quản lý & Giám sát
* **Bảng 6:** Yêu cầu chức năng nghiệp vụ Phân hệ Khách hàng & Tư vấn
* **Bảng 7:** Bảng thuộc tính `users` (Quản lý tài khoản, phân quyền RBAC, OTP)
* **Bảng 8:** Bảng thuộc tính `categories` (Phân loại thiết bị quang điện)
* **Bảng 9:** Bảng thuộc tính `products` (Catalog 26 thiết bị quang điện Tier-1)
* **Bảng 10:** Bảng thuộc tính `solar_quotes` (Dự toán báo giá năng lượng mặt trời)
* **Bảng 11:** Bảng thuộc tính `projects` (Quản lý hồ sơ công trình & hợp đồng lắp đặt)
* **Bảng 12:** Bảng thuộc tính `project_stages` (18 bước quy trình 4 giai đoạn EVN)
* **Bảng 13:** Bảng thuộc tính `project_assignments` (Phân công kỹ thuật viên phụ trách)
* **Bảng 14:** Bảng thuộc tính `project_stage_logs` (Lịch sử timeline 4 giai đoạn chuẩn EVN)
* **Bảng 15:** Bảng thuộc tính `project_documents` (Tài liệu khảo sát, ảnh hiện trường băm SHA-256)
* **Bảng 16:** Bảng thuộc tính `installation_items` (Bóc tách khối lượng vật tư kỹ thuật BOM)
* **Bảng 17:** Danh sách màn hình vai trò Quản trị viên (Admin)
* **Bảng 18:** Danh sách màn hình vai trò Khách hàng (Customer)
* **Bảng 19:** Danh sách màn hình vai trò Kỹ thuật viên (Technician)

---

\newpage

# MỤC LỤC HÌNH (67 HÌNH KÈM PHÂN CÔNG BỎ ẢNH)

### Nhóm do Mai Đức Toàn chèn ảnh (37 hình - Chiếm phần lớn):
* **Hình 5:** Biểu mẫu Phiếu dự toán bóc tách vật tư kỹ thuật (BOM - Bill of Materials)
* **Hình 6:** Biểu mẫu Báo giá & Phân tích thời gian hoàn vốn đầu tư (ROI Quotation)
* **Hình 12:** Sơ đồ thực thể liên kết ERD toàn diện của hệ thống Solaris (11 bảng cốt lõi)
* **Hình 13:** Sơ đồ luồng dữ liệu (DFD) chức năng Đăng ký / Đăng nhập xác thực Gmail OTP
* **Hình 14:** Sơ đồ luồng dữ liệu (DFD) chức năng Phân quyền hệ thống & 1-Click Tester
* **Hình 15:** Sơ đồ luồng dữ liệu (DFD) chức năng Quản lý danh mục & Lọc thiết bị quang điện
* **Hình 16:** Sơ đồ luồng dữ liệu (DFD) chức năng Tính toán phụ tải và mô phỏng Solar Engine
* **Hình 17:** Sơ đồ luồng dữ liệu (DFD) chức năng Bóc tách vật tư kỹ thuật BOM và lập dự toán
* **Hình 18:** Sơ đồ luồng dữ liệu (DFD) chức năng Lưu trữ và tra cứu hồ sơ dự toán (`solar_quotes`)
* **Hình 19:** Sơ đồ luồng dữ liệu (DFD) chức năng Tra cứu công trình theo mã `SOLAR-YYYY-XXX`
* **Hình 20:** Sơ đồ luồng dữ liệu (DFD) chức năng Đăng ký khảo sát hiện trường và tư vấn
* **Hình 21:** Sơ đồ luồng dữ liệu (DFD) chức năng Cập nhật tiến độ 4 giai đoạn chuẩn EVN
* **Hình 22:** Sơ đồ luồng dữ liệu (DFD) chức năng Tải lên tệp tin thiết kế/ảnh băm SHA-256
* **Hình 23:** Sơ đồ luồng dữ liệu (DFD) chức năng Xuất biên bản kỹ thuật và hồ sơ nghiệm thu
* **Hình 24:** Sơ đồ luồng dữ liệu (DFD) chức năng Đồng bộ hóa thời gian thực Socket.io Port 3001
* **Hình 25:** Sơ đồ luồng dữ liệu (DFD) chức năng Điều phối giao diện Dual Navigation (Ngang $\leftrightarrow$ Dọc)
* **Hình 26:** Sơ đồ luồng dữ liệu (DFD) chức năng Quản lý phân công kỹ thuật viên công trình
* **Hình 27:** Sơ đồ logic chức năng Xác thực Gmail OTP Sandbox
* **Hình 28:** Màn hình giao diện Đăng nhập / Đăng ký 6 ô OTP
* **Hình 29:** Hàm sinh mã OTP ngẫu nhiên và gửi mail HTML trong `AuthController.php`
* **Hình 30:** Hàm kiểm tra mã OTP và xác thực kích hoạt tài khoản
* **Hình 31:** Sơ đồ logic chức năng Tính toán phụ tải và Solar ROI Engine
* **Hình 32:** Màn hình giao diện Bộ công cụ tính toán Solar Calculator
* **Hình 33:** Hàm tính toán công suất kWp và số lượng tấm pin trong `UseSolarCalculator.php` (Phần 1)
* **Hình 34:** Hàm tính toán tài chính và số năm hoàn vốn trong `UseSolarCalculator.php` (Phần 2)
* **Hình 35:** Sơ đồ logic chức năng Bóc tách vật tư BOM & Lưu dự toán
* **Hình 36:** Giao diện hiển thị Bảng BOM vật tư và sơ đồ mảng pin 2D/3D
* **Hình 37:** Hàm bóc tách danh mục thiết bị tự động theo kWp
* **Hình 38:** Hàm lưu thông tin dự toán vào CSDL `solar_quotes` (tự duyệt approved khi xác nhận)
* **Hình 39:** Giao diện Trang chủ với hiệu ứng sóng năng lượng Solar Shockwave
* **Hình 40:** Hàm phát sóng sự kiện thời gian thực trong `socket_server/server.js` (kèm Scroll Preservation)
* **Hình 57:** Sơ đồ logic chuyển đổi giao diện Dual Navigation (Ngang $\leftrightarrow$ Dọc 1s)
* **Hình 58:** Màn hình giao diện Sidebar dọc trượt mượt mà trên các trang chức năng (nút đăng xuất cùng hàng)
* **Hình 59:** Hàm điều phối Route trung tâm và kiểm tra phiên trong `index.php`
* **Hình 60:** Sơ đồ logic phân quyền RBAC và các tài khoản 1-Click Tester
* **Hình 62:** Sơ đồ kiến trúc liên kết phân hệ kỹ thuật và luồng dữ liệu Solaris
* **Hình 67:** Màn hình tổng quan Dashboard quản trị hệ thống Solaris

### Nhóm do Lê Chí Hải chèn ảnh (15 hình):
* **Hình 1:** Biểu mẫu Hóa đơn tiền điện hàng tháng của khách hàng (EVN Bill)
* **Hình 2:** Biểu mẫu Phiếu khảo sát hiện trạng kết cấu mái nhà & diện tích khả dụng
* **Hình 3:** Biểu mẫu Danh mục thông số kỹ thuật tấm pin & Biến tần (Datasheet Tier-1)
* **Hình 4:** Biểu mẫu Thư xác thực OTP gửi qua Gmail (Gmail Sandbox Notification)
* **Hình 41:** Sơ đồ logic chức năng Quản lý danh mục thiết bị và Lọc phân trang
* **Hình 42:** Màn hình giao diện Danh mục 26 thiết bị quang điện Tier-1
* **Hình 43:** Hàm lọc sản phẩm đa tiêu chí trong `ProductController.php`
* **Hình 44:** Hàm phân trang tự động trong Hook `UsePaginationState.php`
* **Hình 45:** Sơ đồ logic chức năng Giỏ dự định thiết bị (Cart) và đồng bộ sang Demo
* **Hình 46:** Màn hình giao diện Giỏ kế hoạch thiết bị quang điện
* **Hình 47:** Hàm đồng bộ cấu hình thiết bị sang công cụ tính toán
* **Hình 61:** Màn hình Trang Profile cá nhân và Modal DOC hướng dẫn nghiệm thu
* **Hình 63:** Màn hình giao diện Lọc nâng cao thiết bị quang điện theo công suất và hãng
* **Hình 64:** Màn hình giao diện Chi tiết thông số kỹ thuật tấm pin / Inverter
* **Hình 65:** Màn hình giao diện Form đăng ký tư vấn & Khảo sát hiện trường mái nhà

### Nhóm do Phan Trần Thịnh chèn ảnh (15 hình):
* **Hình 7:** Biểu mẫu Hợp đồng nguyên tắc thi công lắp đặt điện mặt trời
* **Hình 8:** Biểu mẫu Phiếu tiếp nhận mặt bằng & Khảo sát drone hiện trường
* **Hình 9:** Biểu mẫu Biên bản cập nhật tiến độ giai đoạn thi công khung giàn
* **Hình 10:** Biểu mẫu Biên bản thử nghiệm đo kiểm cách điện và an toàn hòa lưới
* **Hình 11:** Biểu mẫu Biên bản nghiệm thu đóng điện & Bàn giao hệ thống điện mặt trời EVN
* **Hình 48:** Sơ đồ logic quy trình quản lý thi công 4 giai đoạn chuẩn EVN
* **Hình 49:** Màn hình giao diện Timeline tiến độ lắp đặt và tra cứu mã `SOLAR-YYYY-XXX`
* **Hình 50:** Hàm tra cứu hồ sơ công trình theo mã dự án trong `InstallationController.php` (tự tạo giai đoạn)
* **Hình 51:** Hàm cập nhật bước thi công và ghi nhật ký vào `project_stage_logs`
* **Hình 52:** Hàm chuyển trạng thái công trình sang Nghiệm thu đóng điện EVN
* **Hình 53:** Sơ đồ logic cơ chế tải tệp tin an toàn băm SHA-256
* **Hình 54:** Màn hình giao diện Tải ảnh hiện trường và tài liệu thiết kế CAD
* **Hình 55:** Hàm kiểm tra MIME và băm mã SHA-256 trong `UseFileUpload.php` (Phần 1)
* **Hình 56:** Hàm lưu trữ bản ghi tệp tin vào bảng `project_documents` (Phần 2)
* **Hình 66:** Màn hình giao diện Biên bản nghiệm thu kỹ thuật và đóng điện EVN
