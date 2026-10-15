# TÀI LIỆU BỐI CẢNH DỰ ÁN & HƯỚNG DẪN DÀNH CHO AI (PROJECT CONTEXT FOR AI AGENTS)
> **Dự án:** SOLARIS - Hệ Thống Quản Lý, Tư Vấn, Tính Toán & Lắp Đặt Điện Mặt Trời  
> **Tích hợp:** Cổng 24 Bài Tập Thực Hành Lập Trình Web PHP  
> **Cập nhật lần cuối:** 15/10/2026  
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

| Thành viên | Phân hệ phụ trách | Module Controller / Views | Bài tập (24 bài) |
| :--- | :--- | :--- | :--- |
| **TV1 (Trưởng nhóm)** | Trang chủ (Home), Profile nhóm, Xác thực & Phân quyền RBAC (Session/Cookie) | `app/Modules/Member1_Lead/`<br>`resources/views/pages/home.blade.php`<br>`resources/views/pages/team-profile.blade.php` | **Bài 1 đến Bài 6** |
| **TV2** | Phân hệ Danh mục Sản phẩm & Thiết bị Pin/Inverter, Hệ thống Design System & Components | `app/Modules/Member2_Products/`<br>`resources/views/components/` | **Bài 7 đến Bài 12** |
| **TV3** | Bộ công cụ Tính toán Kỹ thuật & Báo giá (Solar Calculator), Quản lý CSDL | `app/Modules/Member3_Simulator/`<br>`app/Hooks/UseSolarCalculator.php` | **Bài 13 đến Bài 18** |
| **TV4** | Quy trình Lắp đặt, Khảo sát, Đăng ký Tư vấn & Upload bản vẽ mặt bằng, QA/Test | `app/Modules/Member4_Installation/`<br>`app/Hooks/UseFileUpload.php` | **Bài 19 đến Bài 24** |

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
   Mọi bảng dữ liệu (danh sách sản phẩm, hồ sơ dự án, danh sách khách hàng, danh sách bài tập) khi số bản ghi vượt quá **20 dòng** bắt buộc phải sử dụng Hook `app/Hooks/UsePaginationState.php` để chia trang, có nút điều hướng Trước/Sau và hiển thị `Trang X / Y`.
3. **Quy tắc Upload tài liệu khảo sát:**
   - Chỉ chấp nhận file tài liệu/bản vẽ mặt bằng định dạng: `pdf`, `png`, `jpg`, `jpeg`.
   - Dung lượng tối đa: **5MB** / file.
   - Bắt buộc dùng Hook `app/Hooks/UseFileUpload.php` để kiểm tra MIME-type và mã hóa tên file bằng chuỗi băm ngẫu nhiên `hash('sha256', uniqid() . $originalName) . '.' . $ext` nhằm tránh trùng lặp và ngăn chặn lỗ hổng Upload Shell.
4. **Cơ chế Phân quyền (RBAC):**
   Hệ thống có 4 vai trò rõ ràng:
   - `guest` (Khách vãng lai): Xem Home, xem sản phẩm, dùng thử công cụ tính toán nhanh, xem danh sách bài tập.
   - `customer` (Khách hàng): Đăng ký tư vấn, gửi yêu cầu lắp đặt, tải lên bản vẽ khảo sát mặt bằng, xem tiến độ dự án của chính mình.
   - `technician` (Kỹ thuật viên): Tiếp nhận khảo sát, upload bản vẽ thiết kế kỹ thuật, cập nhật trạng thái lắp đặt.
   - `admin` (Quản trị viên): Toàn quyền quản lý người dùng, sản phẩm, phê duyệt báo giá, chấm điểm/chạy toàn bộ 24 bài tập.
5. **Chính sách sạch mã (Zero AI Trace Policy):**
   - Không sinh các đoạn văn sáo rỗng ("Tóm lại", "Hơn nữa", "Dưới đây là mã nguồn bạn yêu cầu", v.v.).
   - Comment trong code ngắn gọn, thuần kỹ thuật, không để lộ prompt của AI.
   - Không đặt commit message có tiền tố kiểu máy móc (ví dụ không dùng `feat:`, chỉ dùng tiếng Việt mô tả công việc cụ thể).

---

## 5. THIẾT KẾ CƠ SỞ DỮ LIỆU (11 BẢNG)

Hệ thống sử dụng CSDL MySQL bao gồm 2 nhóm bảng rõ ràng:

### A. Nhóm bảng Năng lượng Mặt trời (5 bảng)
1. `users` (id, username, password_hash, full_name, email, phone, role, created_at, updated_at)
   - `role`: ENUM('guest', 'customer', 'technician', 'admin') DEFAULT 'customer'
2. `products` (id, product_code, name, category, capacity_watts, price, warranty_years, technical_specs, image_url, stock_quantity, status, created_at)
   - `category`: ENUM('solar_panel', 'inverter', 'battery_storage', 'accessories')
3. `solar_quotes` (id, customer_name, customer_phone, customer_email, address, monthly_electric_bill, roof_area_sqm, estimated_system_kwp, panel_count, estimated_total_cost, payback_years, notes, status, created_at)
   - `status`: ENUM('pending', 'contacted', 'survey_scheduled', 'approved', 'rejected')
4. `projects` (id, quote_id, user_id, project_name, capacity_kwp, total_amount, installation_address, current_stage, notes, start_date, completion_date, created_at)
   - `current_stage`: ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover')
5. `project_documents` (id, project_id, file_name, file_hash, file_path, file_size_kb, file_type, uploaded_by, created_at)
   - Lưu trữ bản vẽ khảo sát, giấy phép thỏa thuận nối lưới EVN.

### B. Nhóm bảng Nghiệp vụ Bán sữa phục vụ 24 bài tập thực hành (6 bảng)
6. `hang_sua` (Ma_hang_sua [PK], Ten_hang_sua, Dia_chi, Dien_thoai, Email)
7. `loai_sua` (Ma_loai_sua [PK], Ten_loai)
8. `sua` (Ma_sua [PK], Ten_sua, Ma_hang_sua [FK], Ma_loai_sua [FK], Trong_luong, Don_gia, TP_Dinh_Duong, Loi_ich, Hinh)
9. `khach_hang` (Ma_khach_hang [PK], Ten_khach_hang, Phai, Dia_chi, Dien_thoai, Email)
10. `hoa_don` (So_hoa_don [PK], Ngay_HD, Ma_khach_hang [FK], Tri_gia)
11. `ct_hoadon` (So_hoa_don [PK, FK], Ma_sua [PK, FK], So_luong, Don_gia)

---

## 6. CẤU TRÚC THƯ MỤC CHUẨN

```text
Doan/
├── app/
│   ├── Config/
│   │   ├── Database.php          # Kết nối CSDL MySQL (PDO)
│   │   └── AppConfig.php         # Hằng số toàn cục và đường dẫn
│   ├── Hooks/                    # Reusable Logic Hooks (Single Source of Truth)
│   │   ├── UseAuthSessionState.php   # Quản lý Đăng nhập, Session, RBAC Middleware
│   │   ├── UseSolarCalculator.php    # Thuật toán tính toán điện mặt trời (TV3)
│   │   ├── UsePaginationState.php    # Phân trang tự động khi số dòng > 20
│   │   └── UseFileUpload.php         # Kiểm tra dung lượng 5MB và băm tên file an toàn (TV4)
│   └── Modules/
│       ├── Member1_Lead/         # TV1: HomeController, AuthController, ProfileController
│       ├── Member2_Products/     # TV2: ProductController, CategoryController
│       ├── Member3_Simulator/    # TV3: SolarDemoController, QuoteCalculationController
│       └── Member4_Installation/ # TV4: InstallationController, SurveyUploadController
├── assets/
│   ├── solar-logo.svg            # Logo vector chính thức của dự án
│   └── uploads/                  # Thư mục chứa tài liệu khảo sát được mã hóa hash
├── resources/
│   ├── css/
│   │   └── solaris-theme.css     # Obsidian & Solar Corona Gold Theme
│   ├── js/
│   │   ├── main.js               # Khởi tạo giao diện, dropdown, modal
│   │   └── solar-calculator.js   # Script tương tác tính toán nhanh phía Client
│   └── views/
│       ├── components/           # Atomic Components (Button, Input, Badge, Header,...)
│       ├── layouts/              # Master Layouts
│       └── pages/                # Các trang chính của 6 Tab
│           ├── home.blade.php
│           ├── team-profile.blade.php
│           └── exercises/
│               └── portal.blade.php  # Cổng 24 bài tập chia đều 4 thành viên
├── index.php                     # Điểm vào chính của ứng dụng
├── TAI_LIEU_DAC_TA_HE_THONG_SOLARIS.doc # Tài liệu đặc tả hệ thống hoàn chỉnh
└── PROJECT_CONTEXT.md            # File ngữ cảnh này dành cho Dev và AI Agents
```

---

## 7. MẪU LỆNH (PROMPT TEMPLATE) DÀNH CHO THÀNH VIÊN KHI LÀM VIỆC VỚI AI

Khi bắt đầu làm việc, thành viên trong nhóm chỉ cần sao chép đoạn prompt dưới đây kèm file `PROJECT_CONTEXT.md` gửi cho AI của mình:

```markdown
Chào AI, tôi là [Thành viên 1 / Thành viên 2 / Thành viên 3 / Thành viên 4] của dự án SOLARIS.
Tôi đang phụ trách phân hệ: [Tên phân hệ của bạn, ví dụ: Module Tính toán Solar & CSDL] và các bài tập từ Bài [X] đến Bài [Y].

Hãy đọc kỹ file PROJECT_CONTEXT.md được đính kèm ở trên và tuân thủ tuyệt đối các nguyên tắc sau:
1. Chỉ viết hoặc chỉnh sửa code trong phân hệ được giao (không sửa đè code của thành viên khác).
2. Dùng đúng bảng màu Obsidian Void (#080c14) và Solar Gold (#f59e0b) trong `resources/css/solaris-theme.css`.
3. Tái sử dụng các Atomic Components trong `resources/views/components/`.
4. Khi danh sách dữ liệu > 20 dòng, bắt buộc dùng `app/Hooks/UsePaginationState.php`.
5. Nếu có chức năng upload, bắt buộc kiểm tra max 5MB và băm tên file bằng `app/Hooks/UseFileUpload.php`.
6. Tạm thời không cài thư viện 3D; tập trung vào tính toán 2D và luồng nghiệp vụ.
7. Giữ code sạch, không ghi chú thích lộ dấu vết AI.

Nhiệm vụ cụ thể của tôi hôm nay là: [Mô tả công việc bạn cần làm, ví dụ: Viết file Database.php và tạo bảng CSDL cho hệ thống]. Hãy hướng dẫn và viết mã nguồn chuẩn xác giúp tôi!
```
