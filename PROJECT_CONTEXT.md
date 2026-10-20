# TÀI LIỆU BỐI CẢNH DỰ ÁN & HƯỚNG DẪN DÀNH CHO AI (PROJECT CONTEXT FOR AI AGENTS)
> **Dự án:** SOLARIS - Hệ Thống Quản Lý, Tư Vấn, Tính Toán & Lắp Đặt Điện Mặt Trời  
> **Tích hợp:** Cổng 24 Bài Tập Thực Hành Lập Trình Web PHP & MySQL  
> **Phiên bản tài liệu:** v2.1 (Cập nhật chuẩn hóa CSDL & Phân hệ Lắp đặt/Kiểm thử)  
> **Cập nhật lần cuối:** 20/10/2026  
> **Mục đích:** Đính kèm hoặc nạp file này vào ngữ cảnh của AI (ChatGPT, Claude, Gemini, Cursor, Copilot, Antigravity) để AI hỗ trợ lập trình chính xác, đồng bộ 100% với toàn bộ nhóm, không gây xung đột kiến trúc.

---

## 1. THÔNG TIN CHUNG & MÔI TRƯỜNG THỰC THI

* **Ngôn ngữ & Nền tảng:** PHP thuần (Native PHP 8.x) kết hợp kiến trúc mô phỏng Component / MVC linh hoạt, JavaScript thuần (Vanilla JS ES6+), CSS3 Variables.
* **Môi trường máy chủ:** XAMPP Apache trên Windows (`F:\Work\AXMPP\htdocs\PHP\Doan`).
* **Đường dẫn truy cập cục bộ:** `http://localhost/PHP/Doan/` (hoặc `http://localhost/PHP/Doan/index.php`).
* **Git Repository:** `https://github.com/NHDsun/He_Thong_quan-ly-_tu_van_demo_thiet_ke_-lap_dat_nang_luong_mat_troi_Solaris.git`
* **Nhánh làm việc chính:** `main` (hoặc `develop`).

---

## 2. PHÂN CHIA CÔNG VIỆC 4 THÀNH VIÊN (TEAM DIVISION)

Dự án được phân rã thành 5 phân hệ độc lập tương ứng với 4 thành viên. Mỗi thành viên chịu trách nhiệm module của mình và 6 bài tập thực hành:

| Thành viên | Phân hệ phụ trách | Module Controller / Views / Hooks | Phụ trách 24 Bài tập PHP & MySQL |
| :--- | :--- | :--- | :--- |
| **TV1 (Trưởng nhóm)** | Trang chủ (Home), Profile nhóm, Xác thực & Phân quyền RBAC (Session/Cookie) | `app/Modules/Member1_Lead/`<br>`resources/views/pages/home.blade.php`<br>`resources/views/pages/team-profile.blade.php`<br>`app/Hooks/UseAuthSessionState.php` | **Bài 1 đến Bài 6** (Căn bản PHP, Mảng, Chuỗi) |
| **TV2** | Phân hệ Danh mục Sản phẩm & Thiết bị Pin/Inverter, Hệ thống Design System & Components | `app/Modules/Member2_Products/ProductController.php`<br>`resources/views/components/`<br>`resources/views/pages/products.blade.php` | **Bài 7 đến Bài 12** (Form, Ma trận, Xử lý dữ liệu) |
| **TV3** | Bộ công cụ Tính toán Kỹ thuật & Báo giá (Solar Calculator), Quản lý CSDL | `app/Modules/Member3_Simulator/SolarDemoController.php`<br>`app/Hooks/UseSolarCalculator.php`<br>`resources/views/pages/calculator.blade.php` | **Bài 13 đến Bài 18** (OOP, Kết nối MySQL, Đọc ghi bảng) |
| **TV4** | Quy trình Lắp đặt, Khảo sát, Đăng ký Tư vấn & Upload bản vẽ mặt bằng, QA/QC | `app/Modules/Member4_Installation/InstallationController.php`<br>`app/Hooks/UseFileUpload.php`<br>`resources/views/pages/installations.blade.php` | **Bài 19 đến Bài 24** (CSDL Quản lý bán sữa nâng cao) |

### Chi tiết 6 Bài tập thực hành của Thành viên 4 (Bài 19 đến Bài 24):
* **Bài 19 (Bài 4 MySQL):** Lưới thông tin sữa có phân trang tự động (`UsePaginationState.php` khi danh sách > 20 dòng).
* **Bài 20 (Bài 5 MySQL):** Lưới sản phẩm sữa hiển thị định dạng thẻ kèm hình ảnh sản phẩm.
* **Bài 21 (Bài 6 MySQL):** Trang xem thông tin chi tiết một sản phẩm sữa (thành phần dinh dưỡng, lợi ích, đơn giá).
* **Bài 22 (Bài 7 MySQL):** Form thêm mới một sản phẩm sữa vào CSDL kèm kiểm tra dữ liệu đầu vào.
* **Bài 23 (Bài 8 MySQL):** Chức năng tìm kiếm sản phẩm sữa đơn giản theo tên sản phẩm.
* **Bài 24 (Bài 9 MySQL):** Chức năng tìm kiếm sản phẩm nâng cao (kết hợp hãng sữa, loại sữa, khoảng đơn giá).

---

## 3. DESIGN SYSTEM & QUY TẮC GIAO DIỆN

* **Theme Design:** Obsidian Void (`#080c14`) kết hợp Solar Corona Gold (`#f59e0b` / `#fbbf24`).
* **File CSS trung tâm:** `resources/css/solaris-theme.css`. Mọi trang, component đều phải kế thừa biến CSS từ file này, tuyệt đối không tự ý dùng màu sắc lệch chuẩn:
  - Nền tối: `--bg-primary: #080c14;`, `--bg-surface: #0f172a;`, `--bg-card: #141e33;`
  - Điểm nhấn: `--accent-gold: #f59e0b;`, `--accent-hover: #d97706;`, `--accent-glow: rgba(245, 158, 11, 0.25);`
  - Chữ: `--text-main: #f8fafc;`, `--text-muted: #94a3b8;`
  - Viền: `--border-glass: rgba(255, 255, 255, 0.08);`
* **Atomic Components hiện có tại `resources/views/components/`:**
  - `button.blade.php`: Hỗ trợ biến thể `primary`, `secondary`, `outline`, `ghost` và kích thước `sm`, `md`, `lg`.
  - `input.blade.php`: Ô nhập liệu glassmorphism kèm icon và trạng thái focus viền vàng.
  - `badge.blade.php`: Nhãn trạng thái (`gold`, `blue`, `green`, `red`, `gray`).
  - `stat-card.blade.php`: Thẻ số liệu thống kê kèm hiệu ứng hover.
  - `header.blade.php`, `navbar.blade.php` (Menu 6 tabs), `footer.blade.php`.
* **Định dạng 3D:** Được xếp vào **Roadmap Tương lai (Phase 2)**. Giai đoạn hiện tại **tuyệt đối không cài đặt thư viện 3D nặng** (Three.js/Babylon.js). Phân hệ Demo/Mô phỏng của TV3 tập trung vào thuật toán tính toán 2D, sơ đồ mặt bằng kỹ thuật (SVG/Canvas) và bảng phân tích hoàn vốn.

---

## 4. QUY CHUẨN KIẾN TRÚC & QUY TẮC BẮT BUỘC (CRITICAL CODING RULES)

1. **Luồng dữ liệu 1 chiều:**
   `Hook (Tính toán / Tiện ích / Logic)` -> `Controller (Điều phối dữ liệu)` -> `Page View (Ghép layout)` -> `Atomic Components (Hiển thị)`.
2. **Quy tắc phân trang (> 20 dòng):**
   Mọi bảng dữ liệu (danh sách sản phẩm, danh sách dự án thi công, bảng bài tập sữa) khi số bản ghi vượt quá **20 dòng** bắt buộc phải sử dụng Hook `app/Hooks/UsePaginationState.php` để chia trang, có nút điều hướng Trước/Sau và hiển thị `Trang X / Y`.
3. **Quy tắc Upload tài liệu kỹ thuật & khảo sát:**
   - Tiếp nhận định dạng: ảnh hiện trường (`.jpg`, `.png`, `.jpeg`) và bản vẽ kỹ thuật (`.pdf`, `.dwg`).
   - Dung lượng tối đa: **5MB** / file.
   - Bắt buộc dùng Hook `app/Hooks/UseFileUpload.php` để kiểm tra MIME-type, phân loại tài liệu theo `document_type` và băm tên file an toàn bằng `hash('sha256', uniqid() . $originalName) . '.' . $ext` lưu vào `assets/uploads/`.
   - **Bắt buộc lưu `file_hash` vào CSDL:** Lưu chuỗi băm SHA-256 vào cột `file_hash` của bảng `project_documents` (hoặc `project_files`) để chống trùng lặp dữ liệu và phục vụ kiểm toán an toàn.
4. **Quy tắc Tra cứu tiến độ thi công (Tab 4):**
   - Khách hàng tra cứu tiến độ bằng mã hợp đồng thân thiện dạng chuỗi: `contract_code` (ví dụ `SOLAR-2026-001`).
   - Hiển thị dòng thời gian (Timeline) trực quan gồm 6 giai đoạn thi công chuẩn:
     1. Khảo sát hiện trường (`survey`)
     2. Thiết kế kỹ thuật CAD (`design`)
     3. Mua sắm vật tư (`procurement`)
     4. Đang thi công lắp đặt (`installation`)
     5. Thỏa thuận nối lưới điện EVN / Nghiệm thu (`grid_connection`)
     6. Bàn giao & Vận hành bảo trì (`handover`)
   - Mọi lần thay đổi trạng thái đều được ghi nhật ký vào bảng `project_stage_logs` để hiển thị thời điểm bắt đầu/hoàn tất từng khâu.
5. **Cơ chế Phân quyền (RBAC):**
   Hệ thống có 4 vai trò rõ ràng:
   - `guest` (Khách vãng lai): Xem Home, xem sản phẩm, dùng thử công cụ tính toán nhanh, tra cứu tiến độ công khai bằng `contract_code`.
   - `customer` (Khách hàng): Đăng ký tư vấn, gửi yêu cầu khảo sát, xem lịch sử các báo giá (`user_id` trong `solar_quotes`), theo dõi dự án của chính mình (`user_id` trong `projects`).
   - `technician` (Kỹ thuật viên): Quản lý danh sách các dự án được phân công trực tiếp (`technician_id` trong `projects`), cập nhật tiến độ thi công, upload bản vẽ CAD, ảnh hiện trường và thỏa thuận EVN.
   - `admin` (Quản trị viên): Toàn quyền quản trị hệ thống, phân công kỹ thuật viên cho dự án, phê duyệt báo giá, quản lý sản phẩm và toàn quyền chấm 24 bài tập.
6. **Chính sách sạch mã (Zero AI Trace Policy):**
   - Không sinh các đoạn văn sáo rỗng ("Tóm lại", "Hơn nữa", "Dưới đây là mã nguồn bạn yêu cầu", v.v.).
   - Comment trong code ngắn gọn, thuần kỹ thuật, không để lộ prompt của AI.
   - Không đặt commit message có tiền tố kiểu máy móc (ví dụ không dùng `feat:`, chỉ dùng tiếng Việt mô tả công việc cụ thể).

---

## 5. THIẾT KẾ CƠ SỞ DỮ LIỆU ĐỒNG BỘ (12 BẢNG)

Hệ thống sử dụng CSDL MySQL bao gồm 2 nhóm bảng rõ ràng với đầy đủ khóa ngoại, chỉ mục và timestamps:

### A. Nhóm bảng Năng lượng Mặt trời (6 bảng lõi)
1. `users`
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `username` VARCHAR(50) UNIQUE NOT NULL
   - `password_hash` VARCHAR(255) NOT NULL
   - `full_name` VARCHAR(100) NOT NULL
   - `email` VARCHAR(100) UNIQUE NOT NULL
   - `phone` VARCHAR(20) NOT NULL
   - `role` ENUM('guest', 'customer', 'technician', 'admin') DEFAULT 'customer'
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

2. `categories`
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `name` VARCHAR(100) NOT NULL
   - `slug` VARCHAR(100) UNIQUE NOT NULL
   - `description` TEXT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

3. `products`
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `category_id` INT NOT NULL, FK → `categories.id`
   - `product_code` VARCHAR(50) UNIQUE NOT NULL
   - `name` VARCHAR(255) NOT NULL
   - `capacity_watts` INT NULL
   - `price` DECIMAL(15,2) NOT NULL
   - `warranty_years` INT DEFAULT 5
   - `technical_specs` JSON NULL
   - `image_url` VARCHAR(255) NULL
   - `stock_quantity` INT DEFAULT 0
   - `is_active` TINYINT(1) DEFAULT 1
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

4. `solar_quotes`
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `user_id` INT NULL, FK → `users.id` *(Khách đã đăng nhập xem lại lịch sử)*
   - `customer_name` VARCHAR(100) NOT NULL
   - `customer_phone` VARCHAR(20) NOT NULL
   - `customer_email` VARCHAR(100) NULL
   - `address` VARCHAR(255) NOT NULL
   - `monthly_electric_bill` DECIMAL(15,2) NOT NULL
   - `roof_area_sqm` DECIMAL(8,2) NOT NULL
   - `estimated_system_kwp` DECIMAL(6,2) NOT NULL
   - `panel_count` INT NOT NULL
   - `estimated_total_cost` DECIMAL(15,2) NOT NULL
   - `payback_years` DECIMAL(4,1) NOT NULL
   - `notes` TEXT NULL
   - `status` ENUM('pending', 'contacted', 'survey_scheduled', 'approved', 'rejected') DEFAULT 'pending'
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

5. `projects`
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `contract_code` VARCHAR(20) UNIQUE NOT NULL *(Mã hợp đồng: SOLAR-YYYY-XXX tra cứu tiến độ)*
   - `quote_id` INT NULL, FK → `solar_quotes.id`
   - `user_id` INT NOT NULL, FK → `users.id` *(Khách hàng sở hữu dự án)*
   - `technician_id` INT NULL, FK → `users.id` *(Kỹ thuật viên phụ trách thi công - RBAC)*
   - `project_name` VARCHAR(255) NOT NULL
   - `capacity_kwp` DECIMAL(6,2) NOT NULL
   - `total_amount` DECIMAL(15,2) NOT NULL
   - `installation_address` VARCHAR(255) NOT NULL
   - `current_stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover', 'cancelled') DEFAULT 'survey'
   - `notes` TEXT NULL
   - `start_date` DATE NULL
   - `completion_date` DATE NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

6. `project_stage_logs` *(Lưu lịch sử timeline từng giai đoạn thi công)*
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `project_id` INT NOT NULL, FK → `projects.id` ON DELETE CASCADE
   - `stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover') NOT NULL
   - `started_at` DATETIME NOT NULL
   - `completed_at` DATETIME NULL
   - `note` TEXT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

7. `project_documents` *(hoặc `project_files` - Tài liệu khảo sát & kỹ thuật)*
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `project_id` INT NOT NULL, FK → `projects.id` ON DELETE CASCADE
   - `document_type` ENUM('survey_photo', 'technical_drawing', 'evn_permit', 'procurement_invoice', 'handover_report') NOT NULL
   - `file_name` VARCHAR(255) NOT NULL *(Tên gốc)*
   - `file_hash` VARCHAR(64) NOT NULL *(Chuỗi SHA-256 băm an toàn trên disk)*
   - `file_path` VARCHAR(255) NOT NULL
   - `file_size_kb` INT NOT NULL
   - `file_type` VARCHAR(50) NOT NULL *(MIME-type kỹ thuật)*
   - `uploaded_by` INT NOT NULL, FK → `users.id`
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

8. `installation_items` *(Vật tư xuất kho thi công cho dự án)*
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `project_id` INT NOT NULL, FK → `projects.id` ON DELETE CASCADE
   - `product_id` INT NOT NULL, FK → `products.id`
   - `quantity` INT NOT NULL DEFAULT 1
   - `unit_price` DECIMAL(15,2) NOT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### B. Nhóm bảng Nghiệp vụ Bán sữa phục vụ 24 bài tập thực hành (6 bảng)
9. `hang_sua` (Ma_hang_sua [PK], Ten_hang_sua, Dia_chi, Dien_thoai, Email)
10. `loai_sua` (Ma_loai_sua [PK], Ten_loai)
11. `sua` (Ma_sua [PK], Ten_sua, Ma_hang_sua [FK], Ma_loai_sua [FK], Trong_luong, Don_gia, TP_Dinh_Duong, Loi_ich, Hinh)
12. `khach_hang` (Ma_khach_hang [PK], Ten_khach_hang, Phai, Dia_chi, Dien_thoai, Email)
13. `hoa_don` (So_hoa_don [PK], Ngay_HD, Ma_khach_hang [FK], Tri_gia)
14. `ct_hoadon` (So_hoa_don [PK, FK], Ma_sua [PK, FK], So_luong, Don_gia)

---

## 6. CẤU TRÚC THƯ MỤC CHUẨN

```text
Doan/
├── app/
│   ├── Config/
│   │   ├── Database.php              # Kết nối CSDL MySQL (PDO)
│   │   └── AppConfig.php             # Hằng số toàn cục và đường dẫn
│   ├── Hooks/                        # Reusable Logic Hooks (Single Source of Truth)
│   │   ├── UseAuthSessionState.php   # Quản lý Đăng nhập, Session, RBAC Middleware
│   │   ├── UseSolarCalculator.php    # Thuật toán tính toán điện mặt trời (TV3)
│   │   ├── UsePaginationState.php    # Phân trang tự động khi số dòng > 20
│   │   └── UseFileUpload.php         # Kiểm tra 5MB, phân loại document_type, hash tên file (TV4)
│   └── Modules/
│       ├── Member1_Lead/             # TV1: HomeController, AuthController, ProfileController
│       ├── Member2_Products/         # TV2: ProductController, CategoryController
│       ├── Member3_Simulator/        # TV3: SolarDemoController, QuoteCalculationController
│       └── Member4_Installation/     # TV4: InstallationController, SurveyUploadController
├── assets/
│   ├── solar-logo.svg                # Logo vector chính thức của dự án
│   └── uploads/                      # Thư mục chứa tài liệu khảo sát được mã hóa hash SHA-256
├── resources/
│   ├── css/
│   │   └── solaris-theme.css         # Obsidian & Solar Corona Gold Theme
│   ├── js/
│   │   ├── main.js                   # Khởi tạo giao diện, dropdown, modal
│   │   └── solar-calculator.js       # Script tương tác tính toán nhanh phía Client
│   └── views/
│       ├── components/               # Atomic Components (Button, Input, Badge, Header,...)
│       ├── layouts/                  # Master Layouts
│       └── pages/                    # Các trang chính của 6 Tab
│           ├── home.blade.php
│           ├── products.blade.php
│           ├── calculator.blade.php
│           ├── installations.blade.php # Giao diện tra cứu tiến độ SOLAR-YYYY-XXX và upload hồ sơ
│           ├── team-profile.blade.php
│           └── exercises/
│               └── portal.blade.php  # Cổng 24 bài tập chia đều 4 thành viên
├── index.php                         # Điểm vào chính của ứng dụng
├── TAI_LIEU_DAC_TA_HE_THONG_SOLARIS.doc # Tài liệu đặc tả hệ thống hoàn chỉnh
└── PROJECT_CONTEXT.md                # File ngữ cảnh này dành cho Dev và AI Agents
```

---

## 7. MẪU LỆNH (PROMPT TEMPLATE) DÀNH CHO THÀNH VIÊN KHI LÀM VIỆC VỚI AI

Khi bắt đầu làm việc, thành viên trong nhóm chỉ cần sao chép đoạn prompt dưới đây kèm file `PROJECT_CONTEXT.md` gửi cho AI của mình:

```markdown
Chào AI, tôi là [Thành viên 1 / Thành viên 2 / Thành viên 3 / Thành viên 4] của dự án SOLARIS.
Tôi đang phụ trách phân hệ: [Tên phân hệ của bạn, ví dụ: Module Quản lý Lắp đặt & QA/QC] và các bài tập từ Bài [X] đến Bài [Y].

Hãy đọc kỹ file PROJECT_CONTEXT.md được đính kèm ở trên và tuân thủ tuyệt đối các nguyên tắc sau:
1. Chỉ viết hoặc chỉnh sửa code trong phân hệ được giao (không sửa đè code của thành viên khác).
2. Dùng đúng bảng màu Obsidian Void (#080c14) và Solar Gold (#f59e0b) trong `resources/css/solaris-theme.css`.
3. Tái sử dụng các Atomic Components trong `resources/views/components/`.
4. Khi danh sách dữ liệu > 20 dòng, bắt buộc dùng `app/Hooks/UsePaginationState.php`.
5. Nếu có chức năng upload, bắt buộc kiểm tra max 5MB, phân loại document_type và băm tên file bằng `app/Hooks/UseFileUpload.php` (lưu file_hash vào CSDL).
6. Phân hệ Lắp đặt tra cứu tiến độ bằng mã hợp đồng `contract_code` (SOLAR-YYYY-XXX), hiển thị timeline 6 giai đoạn và ghi lịch sử vào `project_stage_logs`.
7. Kỹ thuật viên chỉ thao tác trên các dự án được gán theo `technician_id`.
8. Tạm thời không cài thư viện 3D; tập trung vào tính toán 2D và luồng nghiệp vụ.
9. Giữ code sạch, không ghi chú thích lộ dấu vết AI.

Nhiệm vụ cụ thể của tôi hôm nay là: [Mô tả công việc bạn cần làm]. Hãy hướng dẫn và viết mã nguồn chuẩn xác giúp tôi!
```
