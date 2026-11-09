# TÀI LIỆU NGUỒN CẬP NHẬT BÁO CÁO DỰ ÁN SOLARIS (DÙNG CHO GEMINI TRÊN GOOGLE DOCS WEB)
**Phiên bản đồng bộ Codebase:** Nhánh `feature/gmail-phpmailer-integration` (Commit `65e1332`)  
**Đề tài:** Xây dựng Hệ thống Quản lý, Tư vấn, Demo Thiết kế và Quy trình Lắp đặt Điện mặt trời (Solaris)  
**Môn học:** Phát triển Phần mềm Mã nguồn mở — Trường Đại học Nha Trang  

---

## HƯỚNG DẪN SỬ DỤNG VỚI GEMINI TRÊN GOOGLE DOCS WEB

1. **Bước 1:** Tải file `.docx` (hoặc `.md`) này lên **Google Drive** của bạn (nên mở bằng Google Docs và lưu thành 1 file Google Docs tên là `Nguon_Cap_Nhat_Solaris`).
2. **Bước 2:** Mở file **Báo cáo chính** của nhóm trên Google Docs $\rightarrow$ Nhấn biểu tượng **Gemini (Ask Gemini / Help me write)** ở góc phải màn hình.
3. **Bước 3:** Gõ ký tự `@` trong ô chat Gemini của Google Docs để đính kèm file `Nguon_Cap_Nhat_Solaris` vừa tải lên, sau đó **Copy-Paste các câu lệnh (Prompt)** dưới đây:

### Câu lệnh 1 (Cập nhật tổng thể hoặc kiểm tra toàn văn):
> Dựa hoàn toàn vào tài liệu đính kèm `@Nguon_Cap_Nhat_Solaris`, hãy rà soát và cập nhật lại báo cáo hiện tại theo phiên bản mã nguồn mới nhất của dự án Solaris. Lưu ý:
> 1. Tuyệt đối KHÔNG đề cập đến mục "24 bài tập thực hành cá nhân" trong báo cáo dự án.
> 2. Cập nhật cơ chế gửi OTP từ "Sandbox ngoại tuyến" sang "Tích hợp thư viện mã nguồn mở PHPMailer (`MailService.php`) gửi qua Gmail SMTP (`smtp.gmail.com:587`) kèm cơ chế Fallback Sandbox".
> 3. Cập nhật giao diện Trang Chủ (`index.php`) gồm 7 phân khu (đã lược bỏ nút "Khám Phá 26 Vật Tư Quang Điện" ở Hero, giữ và nâng cấp nút "Ước Tính Theo Hóa Đơn Điện").
> 4. Cập nhật Phân hệ 3 (`SolarDemoController.php` & `UseSolarCalculator.php`) với cơ chế tính toán Kép (Dual-Mode): Chế độ Kế hoạch dự định (`source=plan`) và Chế độ Ước tính theo Hóa đơn điện EVN (`source=bill`) hỗ trợ 4 gói giải pháp (`residential`, `hybrid`, `commercial`, `agri`), 3 loại kết cấu mái, kiểm tra đủ diện tích mái (`is_roof_sufficient`) và đồng bộ 1-Click sang giỏ kế hoạch (`apply_bill_to_plan`).
> 5. Cập nhật Phân hệ 2 với bộ 6 Hooks sản phẩm mới (`UseProductFilters`, `UseProductSpecifications`, `UseProductRecommendations`, `UseProductValidation`, `UseProductImageUpload`, `UseProductCsrf`) và Phân hệ 4 với quy trình 6 giai đoạn EVN, quản lý vật tư phát sinh & khóa thanh toán hợp đồng (`UseInstallationWorkflow.php`, `UseProjectBilling.php`, bảng `project_payments`).

### Câu lệnh 2 (Nếu muốn yêu cầu Gemini viết/cập nhật từng chương cho chi tiết, không bị cắt ngắn):
* **Cập nhật Bảng phân công & Chương I:**
  > Dựa vào Mục 1 và Mục 2 trong `@Nguon_Cap_Nhat_Solaris`, hãy tạo/cập nhật lại Bảng phân công nhiệm vụ 4 thành viên và toàn bộ nội dung Chương I (bao gồm các Bảng 1, 2, 3, 4, 5, 6) đúng chuẩn văn phong báo cáo học thuật.
* **Cập nhật Chương II (Thiết kế CSDL, Giao diện & Xử lý):**
  > Dựa vào Mục 3, Mục 4 và Mục 5 trong `@Nguon_Cap_Nhat_Solaris`, hãy cập nhật Chương II: Mục 2.1 (Thiết kế dữ liệu kèm bảng `project_payments`), Mục 2.2 (Giao diện Trang chủ 7 phân khu & Calculator 4 bước) và Mục 2.3 (Thiết kế xử lý chi tiết cho 10 chức năng cốt lõi).
* **Cập nhật Chương III & Chương IV (Cài đặt FOSS, Cấu trúc mã nguồn & Kết luận):**
  > Dựa vào Mục 6 và Mục 7 trong `@Nguon_Cap_Nhat_Solaris`, hãy cập nhật Chương III (Môi trường FOSS bổ sung Composer & PHPMailer, Cấu trúc cây thư mục, Quy trình cài đặt & Kiểm thử tự động 263+ assertions) và Chương IV (Kết quả đạt được, Hạn chế, Hướng phát triển).

---

# NỘI DUNG CHUẨN ĐỂ GEMINI TRÍCH XUẤT VÀ CẬP NHẬT VÀO BÁO CÁO

---

## MỤC 1: BẢNG PHÂN CÔNG NHIỆM VỤ & TỶ TRỌNG ĐÓNG GÓP NHÓM (ĐỒNG BỘ CODEBASE MỚI NHẤT)

| Sinh viên thực hiện | MSSV | Vai trò kỹ thuật trong dự án Solaris | Đóng góp Dự án | Nhiệm vụ bỏ ảnh (67 hình) |
| :--- | :---: | :--- | :---: | :---: |
| 1. **Nguyễn Huy Đạt** *(Trưởng nhóm)* | `65130410` | Fullstack Core, Auth Gmail SMTP (PHPMailer), Trang Chủ 7 Phân Khu, Socket.io, Dual Nav | **30%** | **0 hình** *(Điều phối tổng thể)* |
| 2. **Lê Chí Hải** | `65130100` | Frontend Lead, Catalog 26 SP Tier-1, Bộ 6 Hooks Sản phẩm (`UseProduct*`), Cart | **25%** | **15 hình** (22.4%) |
| 3. **Mai Đức Toàn** | `65133959` | Simulator & Data, Dual-Mode Sizing Engine (`source=plan` & `source=bill`), SVG 2D, BOM | **20%** | **37 hình** *(Chiếm phần lớn: 55.2%)* |
| 4. **Phan Trần Thịnh** | `65133649` | Workflow & QA Lead, Lắp đặt EVN 6 Giai đoạn, Thanh toán & Phát sinh vật tư (`UseProjectBilling`) | **25%** | **15 hình** (22.4%) |

### Bảng Chi Tiết Phân Chia Công Việc Kỹ Thuật & Soạn Thảo Báo Cáo:

| Thành viên & MSSV | Phân hệ kỹ thuật phụ trách (Codebase mới nhất) | Nhiệm vụ hoàn thiện Báo cáo (Chương, Bảng, Hình) | Đóng góp Dự án | Phần việc Bỏ ảnh (67 hình) |
| :--- | :--- | :--- | :---: | :---: |
| **Nguyễn Huy Đạt**<br>*(Trưởng nhóm)*<br>`65130410` | • **Hệ thống, Xác thực & Trang chủ (Fullstack Core)**:<br>- Front Controller `index.php`, Session Auth bảo mật, PSR-4 Autoloader & Composer.<br>- **Tích hợp PHPMailer (`MailService.php`) gửi mã OTP 6 số qua Gmail SMTP (`smtp.gmail.com:587`)** kèm cơ chế Fallback Sandbox thông minh.<br>- **Remake Trang Chủ (`index.php`) gồm 7 phân khu Enterprise**: Hero + 4 huy hiệu tín nhiệm (đã lược bỏ nút *"Khám Phá 26 Vật Tư Quang Điện"*), 5 bộ đếm chỉ số động, Carousel 4 gói giải pháp, Công cụ tính nhanh ROI theo 3 vùng nắng, Quy trình 6 bước EVN, Bảng đối sánh EVN vs. Solaris, Showcase dự án.<br>- Máy chủ WebSocket Node.js Socket.io Port 3001 kèm **Scroll Preservation**.<br>- Điều hướng thông minh Dual Navigation (1s) với Sidebar tinh gọn. | • Soạn thảo Chương I (1.1 Khảo sát hiện trạng, 1.2 Đặc tả hệ thống).<br>• Soạn thảo Mục 3.1 & 3.2 Chương III (Khái quát & Môi trường FOSS: PHP, MySQL, Composer, PHPMailer, Node.js).<br>• Hoàn thiện 4 Bảng dữ liệu: Bảng 1, Bảng 2, Bảng 7, Bảng 17.<br>• Điều phối, biên tập và rà soát chuẩn hóa tổng thể tài liệu. | **30%** | **0 hình**<br>*(Trưởng nhóm điều phối)* |
| **Lê Chí Hải**<br>`65130100` | • **Khách hàng & Thiết bị (Frontend Lead)**:<br>- Design System Obsidian Void & Solar Gold (CSS Variables).<br>- Catalog 26 thiết bị Tier-1 (Canadian Solar, LONGi, Sungrow, Huawei, Growatt...).<br>- **Xây dựng bộ 6 Hooks chuyên biệt cho thiết bị (`app/Hooks/UseProduct*.php`)**: `UseProductFilters` (lọc đa tiêu chí, hãng theo danh mục), `UseProductSpecifications` (chuẩn hóa thông số JSON), `UseProductRecommendations` (gợi ý phối ghép), `UseProductValidation`, `UseProductImageUpload`, `UseProductCsrf` (quản trị CRUD sản phẩm cho Admin & so sánh trực quan).<br>- Hook tự động phân trang `UsePaginationState.php`.<br>- Module Giỏ kế hoạch thiết bị (`CartController.php`) đồng bộ sang mô phỏng thiết kế. | • Soạn thảo Mục 2.2 (Thiết kế giao diện người dùng).<br>• Soạn thảo Mục 3.3 (Cấu trúc mã nguồn module hóa).<br>• Hoàn thiện 5 Bảng dữ liệu: Bảng 3, Bảng 6, Bảng 8, Bảng 9, Bảng 18. | **25%** | **15 hình**<br>*(Hình 1 - 4, 41 - 47, 61, 63 - 65)* |
| **Mai Đức Toàn**<br>`65133959` | • **Simulator & Data (Dual-Mode Sizing Engine & Mô phỏng 2D)**:<br>- **Bộ máy tính toán Kép (`UseSolarCalculator.php` & `SolarDemoController.php`)**:<br>  1. *Chế độ Kế hoạch dự định (`source=plan`)*: Ma trận tương thích 7 nhóm thiết bị, đánh giá tỷ lệ DC/AC, nút **1-Click Bổ sung trọn bộ 6 nhóm chuẩn (`fill_full_system`)**.<br>  2. *Chế độ Ước tính theo Hóa đơn điện EVN (`source=bill`)*: Hỗ trợ **4 gói giải pháp** (`Dân dụng`, `Hybrid ESS`, `Thương mại C&I`, `Nông nghiệp Bơm nước Agri-Solar`), **3 kết cấu mái** (`Mái tôn`, `Mái ngói`, `Mái bê tông`), 6 nút chọn nhanh mức hóa đơn, Live Preview công suất $kWp$, cảnh báo thiếu diện tích mái (`is_roof_sufficient`), và nút **1-Click Đưa cấu hình hóa đơn vào Kế hoạch (`apply_bill_to_plan`)**.<br>- Mô phỏng mặt bằng mái & Sơ đồ nguyên lý đấu nối 2D SVG động (tự động kích hoạt khối Pin Lưu Trữ Lithium ESS khi chọn gói Hybrid).<br>- Bóc tách vật tư BOM đầy đủ (kèm Smart Meter 2 chiều) và quy trình lưu/gửi duyệt báo giá `solar_quotes`. | • Soạn thảo Mục 2.1 (Thiết kế dữ liệu, quan hệ bảng, ERD, DFD).<br>• Soạn thảo Mục 3.4 (Quy trình cài đặt & chạy XAMPP/MySQL/Composer/Socket).<br>• Hoàn thiện 5 Bảng dữ liệu: Bảng 10, Bảng 16, Bảng phân công, Mục lục Bảng/Hình. | **20%** | **37 hình**<br>*(Chiếm phần lớn: Hình 5 - 6, 12 - 40, 57 - 60, 62, 67)* |
| **Phan Trần Thịnh**<br>`65133959` / `65133649` | • **Lắp đặt, Thanh toán Dự án & Bảo mật Tệp tin (Workflow & QA)**:<br>- **Quy trình thi công 6 giai đoạn chuẩn EVN** (`UseInstallationWorkflow.php`): Khảo sát $\rightarrow$ Thiết kế $\rightarrow$ Mua sắm $\rightarrow$ Lắp đặt $\rightarrow$ Nối lưới EVN $\rightarrow$ Bàn giao.<br>- **Quản lý Tài chính, Hóa đơn & Vật tư phát sinh (`UseProjectBilling.php`)**: Bảng giao dịch `project_payments`, ghi nhận vật tư phát sinh trong giai đoạn khảo sát/mua sắm (bắt buộc lý do, khóa chỉnh sửa khi chờ duyệt), tính toán số dư chính xác, xuất bản in hóa đơn dự án, và **chặn duyệt bước Mua sắm nếu khách hàng chưa thanh toán đủ hợp đồng & phát sinh**.<br>- Bộ lọc đa trạng thái, sắp xếp `PROJECT_SORTS` và tìm kiếm theo mã hợp đồng `SOLAR-YYYY-XXX`.<br>- Tải tệp an toàn `UseFileUpload.php` kiểm tra MIME và băm SHA-256. | • Soạn thảo Mục 2.3 (Thiết kế xử lý và nghiệp vụ thi công).<br>• Soạn thảo Mục 3.5 (Danh sách module kỹ thuật & Kiểm thử tự động).<br>• Soạn thảo Chương IV (Kết luận, hạn chế, hướng mở rộng & 7 TLTK).<br>• Hoàn thiện 5 Bảng dữ liệu: Bảng 4, Bảng 5, Bảng 11 - 15, Bảng 19. | **25%** | **15 hình**<br>*(Hình 7 - 11, 48 - 56, 66)* |

---

## MỤC 2: NỘI DUNG CẬP NHẬT CHƯƠNG I — ĐẶC TẢ CHỨC NĂNG HỆ THỐNG

### 1. Phân hệ Hệ thống, Xác thực & Trang Chủ (Bảng 1 & Bảng 2)
* **Điều phối trung tâm (`index.php`):** Quản lý định tuyến (Routing) cho các trang `home`, `auth`, `products`, `cart`, `calculator`, `installations`, `profile`; tự động nạp Composer Autoloader (`vendor/autoload.php`) và PSR-4 Autoloader cho không gian tên `App\`.
* **Xác thực đa nhân tố qua Gmail SMTP (`AuthController.php` & `MailService.php`):**
  * Đăng ký tài khoản với mật khẩu băm `password_hash(PASSWORD_DEFAULT)`.
  * Sinh mã OTP 6 chữ số ngẫu nhiên (`random_int(100000, 999999)`) có thời hạn 5 phút.
  * Sử dụng thư viện mã nguồn mở **PHPMailer** gửi email HTML giao diện thương hiệu Solaris qua máy chủ **Gmail SMTP (`smtp.gmail.com`, cổng `587` STARTTLS hoặc `465` SMTPS)**.
  * Trường hợp môi trường máy trạm chưa cấu hình `env.php` hoặc mất kết nối Internet, hệ thống tự động kích hoạt chế độ **Fallback Sandbox** lưu nội dung thư vào Session để phục vụ kiểm thử không gián đoạn.
* **Trang chủ 7 Phân khu (`index.php`):**
  1. **Hero Section & 4 Huy hiệu tín nhiệm:** Trình bày thông điệp cốt lõi, 4 cam kết kỹ thuật (Tier-1, IEC 61730/UL, Đấu nối EVN 2 chiều, Bảo hành 25–30 năm) và nút điều hướng trực tiếp sang trang Tư vấn & Demo thiết kế (đã tinh gọn, lược bỏ nút *"Khám Phá 26 Vật Tư Quang Điện"* ở Hero).
  2. **Thanh 5 Chỉ số Năng lực Thực thi:** Hiệu ứng đếm số mượt mà (`IntersectionObserver`) cho Công suất lắp đặt, Tỷ lệ giảm hóa đơn, Tổng công trình, Thời gian hoàn vốn và Chuẩn kháng nước IP65/IP68.
  3. **Carousel 4 Gói Giải Pháp Chuyên Biệt:** Trượt ngang tự động giữa 4 gói (`Hòa Lưới Dân Dụng`, `Lưu Trữ Hybrid ESS`, `Thương Mại & Nhà Xưởng C&I`, `Bơm Nước Nông Nghiệp Agri-Solar`), mỗi gói gắn liên kết mở trực tiếp chế độ tính theo hóa đơn điện (`source=bill`).
  4. **Công cụ Tính nhanh Công suất & Hoàn vốn (Home ROI Calculator):** Cho phép chọn 1 trong 3 vùng bức xạ nắng (`Miền Bắc 3.8h`, `Miền Trung 4.8h`, `Miền Nam 5.2h`) và kéo thanh trượt hóa đơn điện (`500.000đ` – `20.000.000đ`) để tính tức thì công suất $kWp$, số tấm pin Mono 550W, diện tích mái, tiền tiết kiệm và thời gian hoàn vốn, kèm nút chuyển thẳng thông số sang trang Mô phỏng 2D (`source=bill`).
  5. **Sơ đồ 6 Giai đoạn Lắp đặt & Bàn giao Chuẩn EVN:** Tóm tắt trực quan lộ trình từ Khảo sát Drone 3D đến Nghiệm thu công tơ 2 chiều.
  6. **Bảng Đối sánh Hiệu quả Đầu tư:** So sánh 5 tiêu chí giữa sử dụng 100% điện lưới EVN và đầu tư hệ thống điện mặt trời Solaris.
  7. **Showcase Dự án Thực tế & Đối tác Tier-1:** Trưng bày các công trình tiêu biểu tại Khánh Hòa, TP.HCM, Bình Thuận, Đắk Lắk và các thương hiệu đối tác (Canadian Solar, LONGi, Jinko, Huawei, Sungrow, Growatt).

### 2. Phân hệ Danh mục Thiết bị & Kế hoạch Dự định (Bảng 3 & Bảng 6)
* **Danh mục 26 Thiết bị Quang điện Tier-1 (`ProductController.php`):** Phân loại theo 7 nhóm thiết bị cốt lõi (Tấm pin mặt trời, Biến tần Inverter, Pin lưu trữ Lithium ESS, Tủ điện bảo vệ AC/DC, Khung giá đỡ & Ray nhôm, Dây cáp DC & Đầu nối MC4, Đồng hồ đo đếm Smart Meter 2 chiều).
* **Bộ lọc Đa tiêu chí & So sánh Thiết bị (`UseProductFilters.php`, `UseProductSpecifications.php`):** Tìm kiếm theo từ khóa, lọc theo danh mục, lọc theo thương hiệu thuộc từng danh mục, lọc theo khoảng giá và công suất định danh ($W$); so sánh trực quan thông số kỹ thuật giữa các thiết bị.
* **Quản trị Sản phẩm dành cho Admin (`UseProductValidation.php`, `UseProductImageUpload.php`, `UseProductCsrf.php`):** Thêm mới, cập nhật, ẩn/khôi phục sản phẩm, tải ảnh thiết bị an toàn và kiểm tra mã chống giả mạo yêu cầu CSRF.
* **Giỏ Kế hoạch Dự định (`CartController.php`):** Cho phép khách hàng thêm thiết bị, điều chỉnh số lượng, chọn danh sách thiết bị đưa vào mô phỏng (`solar_demo_selection`) và chuyển tiếp sang Phân hệ 3.

### 3. Phân hệ Tư vấn, Mô phỏng 2D & Bóc tách Vật tư BOM (Bảng 4 & Bảng 6)
* **Cơ chế Tính toán Kép (Dual-Mode Calculator) trên trang `index.php?page=calculator`:**
  * **Chế độ 1 — Theo Kế Hoạch Dự Định (`source=plan`):**
    * Đọc danh sách thiết bị từ `$_SESSION['solar_cart']`, phân tích ma trận tương thích 7 nhóm thiết bị (`UseSolarCalculator::analyzeCompatibility()`), tính điểm hoàn thiện cấu hình (`completion_score` %) và đánh giá tỷ lệ công suất $DC/AC$ giữa giàn pin và Inverter.
    * Kiểm tra bắt buộc đủ 6 nhóm phần cứng thiết yếu trước khi mở khóa Bước 2–4; cung cấp nút **1-Click "Bổ Sung Đầy Đủ Trọn Bộ 6 Nhóm Chuẩn" (`action=fill_full_system`)** để tự động điền các thiết bị còn thiếu từ CSDL.
  * **Chế độ 2 — Ước Tính Theo Hóa Đơn Điện (`source=bill`):**
    * Giữ nguyên nút chuyển chế độ **`⚙️ Ước Tính Theo Hóa Đơn Điện`** trên thanh công cụ đầu trang.
    * Cho phép nhập hóa đơn tiền điện hàng tháng (`monthly_bill` từ `500.000 VNĐ` trở lên) hoặc bấm chọn nhanh 6 mức tiêu thụ phổ biến (`1.5 Triệu`, `3 Triệu`, `5 Triệu`, `8 Triệu`, `15 Triệu`, `30 Triệu C&I`) kèm **Live Preview** cập nhật tức thì công suất $kWp$ và số tấm pin ngay khi gõ.
    * Cho phép chọn diện tích mái khả dụng (`roof_area` $m^2$), **4 gói giải pháp** (`Hòa Lưới Dân Dụng - 14Tr/kWp`, `Lưu Trữ Hybrid ESS - 22Tr/kWp`, `Thương Mại & Công Nghiệp C&I - 13Tr/kWp`, `Nông Nghiệp Bơm Nước Agri-Solar - 13.5Tr/kWp`) và **3 loại kết cấu mái** (`Mái tôn sóng / Seamlock`, `Mái ngói Thái / Nhật`, `Sân thượng Bê tông cốt thép`).
    * Tự động hiển thị ngay toàn bộ 4 bước mô phỏng (`$showStepsFrom2 = true`), cảnh báo trực quan nếu diện tích mái khai báo nhỏ hơn diện tích yêu cầu (`is_roof_sufficient === false`, tính số tấm pin tối đa lắp được `max_panels_by_roof`).
    * Cung cấp nút **1-Click "Đưa Bộ Thiết Bị Đề Xuất Này Vào Kế Hoạch Lắp Đặt" (`action=apply_bill_to_plan`)** để đồng bộ toàn bộ cấu hình ước tính từ hóa đơn vào giỏ hàng thiết bị thật.

### 4. Phân hệ Quản lý Thi công 6 Giai đoạn EVN & Thanh toán Dự án (Bảng 4 & Bảng 5)
* **Quy trình 6 Giai đoạn (18 bước chi tiết) chuẩn EVN (`UseInstallationWorkflow.php`):**
  1. Khảo sát hiện trường (Survey)
  2. Thiết kế kỹ thuật (Design)
  3. Mua sắm & Tập kết vật tư (Procurement)
  4. Thi công lắp đặt cơ điện (Installation)
  5. Kiểm định & Đấu nối công tơ 2 chiều EVN (Grid Connection)
  6. Nghiệm thu & Bàn giao vận hành (Handover)
* **Quản lý Vật tư Phát sinh & Thanh toán Hợp đồng (`UseProjectBilling.php`):**
  * Kỹ thuật viên được phép cập nhật vật tư phát sinh thực tế trong giai đoạn Khảo sát / Mua sắm (bắt buộc nhập lý do phát sinh, số lượng nguyên từ `1` đến `10.000`, đơn giá lấy trực tiếp từ máy chủ để chống giả mạo).
  * Tự động khóa chỉnh sửa vật tư khi giai đoạn đang ở trạng thái chờ quản lý duyệt (`submitted`).
  * Khách hàng thực hiện thanh toán trực tuyến giá trị hợp đồng gốc và khoản phát sinh (chống gửi lặp giao dịch bằng `request_key`, ghi lịch sử vào bảng `project_payments`).
  * **Quy tắc khóa nghiệm thu Mua sắm:** Hệ thống chặn phê duyệt hoàn tất giai đoạn Mua sắm nếu tổng số tiền khách hàng đã thanh toán nhỏ hơn tổng giá trị hợp đồng cộng vật tư phát sinh (chính xác đến `0,01 đ`).
  * Hỗ trợ xuất **Bản in Hóa đơn & Quyết toán Vật tư Dự án** (tách bạch bảng sản phẩm ban đầu và bảng vật tư phát sinh, chống lỗi IDOR giữa các khách hàng).

---

## MỤC 3: NỘI DUNG CẬP NHẬT CHƯƠNG II (MỤC 2.1) — THIẾT KẾ CƠ SỞ DỮ LIỆU

Ngoài 11 bảng cốt lõi ban đầu (`users`, `categories`, `products`, `solar_quotes`, `solar_quote_items`, `projects`, `project_stages`, `project_assignments`, `project_stage_logs`, `project_documents`, `installation_items`), cơ sở dữ liệu mới nhất đã được mở rộng và chuẩn hóa qua [`tools/migrate-project-workflow.php`](file:///f:/Work/AXMPP/htdocs/PHP/Doan/tools/migrate-project-workflow.php) với các điểm cập nhật quan trọng:

1. **Bảng `solar_quotes` (Dự toán & Báo giá khảo sát):**
   * Quản lý cả 2 nguồn tạo dự toán qua cột `source` (`'plan'` từ Kế hoạch dự định hoặc `'bill'` từ Hóa đơn tiền điện).
   * Chu trình trạng thái (`status`): `draft` (Nháp) $\rightarrow$ `submitted` (Đã gửi yêu cầu khảo sát) $\rightarrow$ `approved` (Đã duyệt chuyển thành công trình thi công) / `rejected` (Từ chối kèm `review_note`).
2. **Bảng `projects` (Hồ sơ công trình thi công):**
   * Bổ sung các trường quản lý hợp đồng và tài chính: `contract_code` (Mã hợp đồng định danh), `quote_id` (Liên kết báo giá gốc), `contract_value` (Giá trị hợp đồng gốc), `extra_cost` (Tổng chi phí vật tư phát sinh), `paid_amount` (Tổng số tiền khách hàng đã thanh toán), `payment_status` (`unpaid`, `partial`, `paid`).
3. **Bảng `installation_items` (Bóc tách vật tư & Vật tư phát sinh công trình):**
   * Lưu trữ danh sách vật tư lắp đặt thực tế tại công trường kèm đơn giá lịch sử (`unit_price`), thành tiền (`total_price`), cờ phân biệt vật tư gốc/phát sinh (`is_extra`), lý do phát sinh (`reason`) và người ghi nhận (`created_by`).
4. **Bảng `project_payments` (Lịch sử giao dịch thanh toán dự án):**
   * Lưu trữ từng đợt thanh toán của khách hàng cho công trình: `id`, `project_id`, `user_id`, `amount` (`DECIMAL(15,2)`), `payment_method`, `request_key` (`UNIQUE` chống thanh toán trùng lặp khi mạng trễ), `note`, `created_at`.

---

## MỤC 4: NỘI DUNG CẬP NHẬT CHƯƠNG II (MỤC 2.3) — THIẾT KẾ XỬ LÝ CHI TIẾT CÁC HÀM MỚI

### 1. Dịch vụ Gửi Thư Xác Thực OTP qua Gmail SMTP (`app/Services/MailService.php`)
* **Phương thức chính:** `MailService::sendOtpEmail(string $toEmail, string $toName, string $otpCode): array`
* **Luồng xử lý:**
  1. Đọc cấu hình SMTP từ `AppConfig` (`env.php`), bao gồm `mail_host` (`smtp.gmail.com`), `mail_port` (`587`), `mail_username`, `mail_password` (16 ký tự Gmail App Password), `mail_encryption` (`tls`).
  2. Kiểm tra điều kiện sẵn sàng của thư viện `PHPMailer\PHPMailer\PHPMailer` và thông tin xác thực SMTP (`MailService::isSmtpConfigured()`).
  3. Khởi tạo đối tượng `PHPMailer(true)`, thiết lập mã hóa `UTF-8`, giao thức `isSMTP()`, `SMTPAuth = true`, `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`, và tạo nội dung thư HTML chuẩn thương hiệu Solaris chứa mã OTP 6 chữ số cùng cảnh báo hiệu lực 5 phút.
  4. Thực thi `$mail->send()`. Nếu gửi thành công qua Gmail SMTP, trả về `['sent_via' => 'smtp', 'success' => true]`. Nếu chưa cấu hình SMTP hoặc lỗi mạng cục bộ, tự động chuyển sang chế độ Sandbox dự phòng (`sent_via => 'sandbox'`) để đảm bảo luồng kiểm thử luôn thông suốt.

### 2. Thuật Toán Tính Toán Năng Lượng Kép (`app/Hooks/UseSolarCalculator.php` & `SolarDemoController.php`)
* **Phương thức 1 — Tính từ Hóa đơn điện (`UseSolarCalculator::calculate($monthlyBill, $roofArea, $packageKey, $roofType)`):**
  1. **Chuẩn hóa đầu vào:** Đảm bảo `$monthlyBill >= 500.000 VNĐ`, kiểm tra `$packageKey` thuộc 1 trong 4 gói định giá trong hằng số `PACKAGE_PRICING`:
     * `'residential'` (Hòa Lưới Dân Dụng): `14.000.000 VNĐ/kWp`
     * `'commercial'` (Thương Mại & Công Nghiệp C&I): `13.000.000 VNĐ/kWp`
     * `'hybrid'` (Lưu Trữ Hybrid ESS có Pin Lithium): `22.000.000 VNĐ/kWp`
     * `'agri'` (Nông Nghiệp Bơm Nước Agri-Solar): `13.500.000 VNĐ/kWp`
  2. **Tính sản lượng tiêu thụ & Công suất giàn pin:**
     $$\text{kWh}_{\text{tháng}} = \frac{\text{Hóa đơn tháng}}{3.000 \text{ VNĐ/kWh}}, \quad \text{kWp}_{\text{đề xuất}} = \frac{\text{kWh}_{\text{tháng}}}{4,5 \text{ giờ nắng} \times 30 \text{ ngày}}$$
  3. **Quy đổi số lượng tấm pin Mono Half-Cell 550W & Diện tích mái:**
     $$\text{Số tấm pin} = \max\left(4, \left\lceil \frac{\text{kWp}_{\text{đề xuất}} \times 1000}{550} \right\rceil\right), \quad \text{kWp}_{\text{thực tế}} = \frac{\text{Số tấm pin} \times 550}{1000}$$
     $$\text{Diện tích mái yêu cầu } (m^2) = \text{Số tấm pin} \times 2,5\,m^2$$
     Nếu người dùng nhập diện tích mái khả dụng `$roofArea` nhỏ hơn diện tích yêu cầu, hàm đặt cờ `is_roof_sufficient = false` và tính số tấm tối đa lắp được trên mặt phẳng đơn `max_panels_by_roof = floor($roofArea / 2.5)`.
  4. **Dự toán đầu tư, Thời gian hoàn vốn & Chỉ số ESG 25 năm:**
     * Tổng mức đầu tư = $\text{kWp}_{\text{thực tế}} \times \text{Đơn giá gói}$. Tách bạch chi phí thiết bị (`device_cost` = 88%) và chi phí nhân công/vật tư phụ (`install_cost` = 12%).
     * Tiền điện tiết kiệm hàng tháng = $\text{Hóa đơn tháng} \times 90\%$.
     * Thời gian hoàn vốn được quy đổi chính xác ra chuỗi năm và tháng (`payback_text`, ví dụ: `"3 năm 11 tháng"`).
     * Sản lượng điện thực tế hàng năm (hiệu suất chuỗi $PR = 0,82$) và lượng giảm phát thải $\text{CO}_2$ ($0,7221 \text{ kg CO}_2/\text{kWh}$).
  5. **Sinh cấu hình đại diện cho Sơ đồ 2D SVG & Bảng BOM (`generateBOM()`):**
     * Tự động khởi tạo dữ liệu cho 5 khối trên bản vẽ kỹ thuật 2D (`primary_panel`, `primary_inverter`, `primary_battery` — bật sáng khối Pin Lithium ESS khi chọn gói `hybrid`, `primary_box`, `primary_meter`).
     * Bóc tách đầy đủ các hạng mục vật tư chính kèm **Đồng hồ đo đếm năng lượng Smart Meter 2 Chiều** và gắn nhãn `● Chuẩn Gói Hóa Đơn` (`is_from_bill => true`).
* **Phương thức 2 — Đồng bộ 1-Click từ Hóa đơn sang Kế hoạch (`SolarDemoController::handleApplyBillToPlan()`):**
  * Dựa trên công suất $kWp$, số tấm pin và gói giải pháp khách hàng đang chọn ở chế độ hóa đơn (`source=bill`), truy vấn CSDL bảng `products` để nạp tự động các thiết bị thực tế tương ứng (`SOL-PAN-001`, Inverter Hòa lưới hoặc Inverter Hybrid `SOL-INV-005`/`006`, Pin lưu trữ Lithium `SOL-BAT-002`/`003` nếu là gói Hybrid, Tủ điện `SOL-ACC-006`/`007`, Ray nhôm `SOL-ACC-001`, Cáp DC `SOL-ACC-004`, Smart Meter `SOL-ACC-008`) vào `$_SESSION['solar_cart']` và chuyển sang chế độ Kế hoạch dự định.

---

## MỤC 5: NỘI DUNG CẬP NHẬT CHƯƠNG III — MÔI TRƯỜNG FOSS, CẤU TRÚC MÃ NGUỒN & KIỂM THỬ

### 1. Mục 3.2 — Lựa chọn Ngôn ngữ và Môi trường Cài đặt Mã Nguồn Mở (FOSS)
Bổ sung thêm 2 thành phần mã nguồn mở quan trọng vào bảng công nghệ của dự án:
* **Trình quản lý gói Composer (`getcomposer.org` — Giấy phép MIT):** Quản lý phụ thuộc thư viện PHP và cơ chế Autoloading chuẩn PSR-4.
* **Thư viện PHPMailer (`phpmailer/phpmailer` v6.9 — Giấy phép LGPL-2.1):** Thư viện gửi thư điện tử mã nguồn mở tiêu chuẩn trong học phần Phát triển Phần mềm Mã nguồn mở, hỗ trợ xác thực SMTP qua TLS/SSL để gửi mã OTP kích hoạt tài khoản.

### 2. Mục 3.3 — Cấu trúc Mã nguồn Đã Cài đặt (Cây thư mục chuẩn mới nhất)
```text
Doan/
├── app/
│   ├── Config/
│   │   ├── AppConfig.php                 # Quản lý cấu hình hệ thống & nạp biến môi trường env.php
│   │   └── Database.php                  # Singleton PDO kết nối MySQL (utf8mb4)
│   ├── Hooks/
│   │   ├── UseFileUpload.php             # Kiểm tra MIME & băm SHA-256 tài liệu công trình
│   │   ├── UseInstallationWorkflow.php   # Điều phối quy trình 6 giai đoạn EVN & duyệt báo giá
│   │   ├── UsePaginationState.php        # Hook phân trang tự động dữ liệu lớn
│   │   ├── UseProductCsrf.php            # Bảo vệ CSRF cho biểu mẫu quản trị sản phẩm
│   │   ├── UseProductFilters.php         # Bộ lọc sản phẩm đa tiêu chí & hãng theo danh mục
│   │   ├── UseProductImageUpload.php     # Tải lên & kiểm tra ảnh thiết bị quang điện
│   │   ├── UseProductRecommendations.php # Gợi ý phối ghép thiết bị đồng bộ công suất
│   │   ├── UseProductSpecifications.php  # Chuẩn hóa & trích xuất thông số kỹ thuật JSON
│   │   ├── UseProductValidation.php      # Kiểm tra hợp lệ dữ liệu CRUD thiết bị
│   │   ├── UseProjectBilling.php         # Quản lý vật tư phát sinh, thanh toán & hóa đơn dự án
│   │   └── UseSolarCalculator.php        # Bộ máy tính toán năng lượng Kép (Plan & EVN Bill)
│   ├── Modules/
│   │   ├── Member1_Lead/                 # TV1: AuthController.php, HomeController.php
│   │   ├── Member2_Products/             # TV2: ProductController.php, CartController.php
│   │   ├── Member3_Simulator/            # TV3: SolarDemoController.php
│   │   └── Member4_Installation/         # TV4: InstallationController.php, View.php
│   └── Services/
│       └── MailService.php               # Dịch vụ gửi OTP qua Gmail SMTP bằng PHPMailer
├── database/
│   ├── schema.sql                        # Cấu trúc các bảng CSDL cốt lõi
│   └── seed.sql                          # Dữ liệu mẫu 26 thiết bị Tier-1, tài khoản & dự án
├── docs/                                 # Hồ sơ đặc tả kỹ thuật & Báo cáo Bài tập lớn
├── resources/
│   ├── css/                              # Hệ thống CSS Obsidian Void & Solar Gold
│   └── views/                            # Giao diện Blade-style (auth, products, cart, calculator, installations...)
├── socket_server/
│   ├── package.json                      # Cấu hình Node.js & Socket.io
│   └── server.js                         # Máy chủ WebSocket Real-time Port 3001
├── tests/                                # Bộ kiểm thử tự động (Automated Test Suites)
│   ├── project-workflow.php              # 169 assertions kiểm thử luồng dự án & 6 giai đoạn EVN
│   ├── tv2-workflows.php                 # 43 assertions kiểm thử danh mục, giỏ hàng & phân quyền TV2
│   ├── tv4-billing.php                   # 51 assertions kiểm thử phát sinh vật tư, thanh toán & hóa đơn TV4
│   └── test-mail-service.php             # Kiểm thử dịch vụ gửi OTP PHPMailer & Fallback Sandbox
├── tools/
│   ├── migrate-project-workflow.php      # Script đồng bộ schema CSDL an toàn (Idempotent Migration)
│   └── test-gmail-smtp.php               # Công cụ CLI kiểm tra kết nối Gmail SMTP trực tiếp
├── composer.json                         # Khai báo phụ thuộc phpmailer/phpmailer
├── env.example.php                       # Mẫu cấu hình kết nối CSDL & Gmail App Password
└── index.php                             # Front Controller điều phối toàn cục & Giao diện Trang Chủ 7 phân khu
```

### 3. Mục 3.5 & Chương IV — Bổ sung Kết quả Kiểm thử Tự động (Automated QA)
* Hệ thống được kiểm chứng chất lượng qua bộ kiểm thử tự động độc lập trong thư mục `tests/` (tự động khởi tạo CSDL kiểm thử cô lập, không ảnh hưởng dữ liệu thật):
  * `php tests/project-workflow.php`: **Đạt 169/169 kiểm tra (assertions)** về luồng tạo báo giá, duyệt khảo sát, phân công kỹ thuật viên và chuyển tiếp 6 giai đoạn lắp đặt EVN.
  * `php tests/tv2-workflows.php`: **Đạt 43/43 kiểm tra** về bảo vệ CSRF, lọc đa tiêu chí, hãng theo danh mục, so sánh thông số và phân quyền quản trị thiết bị.
  * `php tests/tv4-billing.php`: **Đạt 51/51 kiểm tra** về ràng buộc vật tư phát sinh, chống giả mạo đơn giá, chống thanh toán trùng lặp, khóa duyệt mua sắm khi chưa thanh toán đủ (chính xác đến `0,01 đ`) và chống lỗi IDOR khi xuất hóa đơn.
  * `php tests/test-mail-service.php`: **Đạt 100% kiểm tra** cơ chế gửi OTP qua PHPMailer Gmail SMTP và cơ chế dự phòng Sandbox.
