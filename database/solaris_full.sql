-- =============================================================================
-- SOLARIS ENERGY & ACADEMIC PORTAL - MASTER DATABASE (ALL-IN-ONE)
-- Bao gồm toàn bộ Lược đồ CSDL (17 Bảng) và Dữ liệu mẫu hoàn chỉnh
-- Chỉ cần Import file này vào phpMyAdmin hoặc chạy:
-- mysql -u root -p < database/solaris_full.sql
-- =============================================================================

-- =============================================================================
-- SOLARIS ENERGY & ACADEMIC PORTAL - MASTER DATABASE SCHEMA
-- Hệ thống Quản Lý, Tư Vấn, Tính Toán & Lắp Đặt Điện Mặt Trời SOLARIS
-- Tích hợp CSDL Nghiệp vụ Quản lý Bán Sữa phục vụ 24 Bài tập thực hành
-- Charset: utf8mb4 | Engine: InnoDB
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `solaris` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `solaris`;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- PHẦN 1: CÁC BẢNG HỆ THỐNG NĂNG LƯỢNG MẶT TRỜI (SOLAR CORE)
-- -----------------------------------------------------------------------------

-- 1. Bảng Quản lý Người dùng & Phân quyền RBAC
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `role` ENUM('guest', 'customer', 'technician', 'admin') NOT NULL DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng Danh mục Sản phẩm Thiết bị Điện mặt trời
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Chi tiết Sản phẩm (Pin, Inverter, Lưu trữ, Phụ kiện)
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `product_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `capacity_watts` INT NULL COMMENT 'Công suất danh định Wp/W',
    `price` DECIMAL(15,2) NOT NULL COMMENT 'Đơn giá VNĐ',
    `warranty_years` INT DEFAULT 5 COMMENT 'Thời gian bảo hành (năm)',
    `technical_specs` JSON NULL COMMENT 'Thông số kỹ thuật định dạng JSON',
    `image_url` VARCHAR(255) NULL,
    `stock_quantity` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
    INDEX `idx_products_cat` (`category_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng Dự toán Báo giá (Solar Quotes từ công cụ tính toán)
DROP TABLE IF EXISTS `solar_quotes`;
CREATE TABLE `solar_quotes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL COMMENT 'FK tới users.id nếu khách hàng đã đăng nhập',
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(100) NULL,
    `address` VARCHAR(255) NOT NULL,
    `monthly_electric_bill` DECIMAL(15,2) NOT NULL COMMENT 'Tiền điện hàng tháng VNĐ',
    `roof_area_sqm` DECIMAL(8,2) NOT NULL COMMENT 'Diện tích mái khả dụng m2',
    `estimated_system_kwp` DECIMAL(6,2) NOT NULL COMMENT 'Công suất tối ưu đề xuất kWp',
    `panel_count` INT NOT NULL COMMENT 'Số lượng tấm pin',
    `estimated_total_cost` DECIMAL(15,2) NOT NULL COMMENT 'Tổng chi phí đầu tư dự kiến VNĐ',
    `payback_years` DECIMAL(4,1) NOT NULL COMMENT 'Thời gian hoàn vốn ước tính (năm)',
    `notes` TEXT NULL,
    `status` ENUM('pending', 'contacted', 'survey_scheduled', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_quotes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng Hợp đồng & Dự án Thi công Lắp đặt thực tế
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contract_code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã hợp đồng chuẩn SOLAR-YYYY-XXX',
    `quote_id` INT NULL UNIQUE COMMENT 'Liên kết 1:1 với báo giá đã duyệt',
    `user_id` INT NOT NULL COMMENT 'Khách hàng sở hữu dự án',
    `technician_id` INT NULL COMMENT 'Kỹ thuật viên trưởng nhóm phụ trách',
    `project_name` VARCHAR(255) NOT NULL,
    `capacity_kwp` DECIMAL(6,2) NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `installation_address` VARCHAR(255) NOT NULL,
    `status` ENUM('new', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
    `current_stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover', 'cancelled') NOT NULL DEFAULT 'survey',
    `notes` TEXT NULL,
    `start_date` DATE NULL,
    `completion_date` DATE NULL,
    `planned_end_date` DATE NULL,
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`quote_id`) REFERENCES `solar_quotes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_projects_lookup` (`contract_code`, `status`, `current_stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng Vật tư thiết bị xuất kho lắp đặt cho công trình
DROP TABLE IF EXISTS `installation_items`;
CREATE TABLE `installation_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng Quản lý Chi tiết 6 Giai đoạn của từng dự án
DROP TABLE IF EXISTS `project_stages`;
CREATE TABLE `project_stages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover') NOT NULL,
    `position` TINYINT NOT NULL COMMENT 'Thứ tự từ 1 đến 6',
    `status` ENUM('locked', 'in_progress', 'pending_approval', 'revision_required', 'approved') NOT NULL DEFAULT 'locked',
    `planned_start` DATE NULL,
    `planned_end` DATE NULL,
    `started_at` DATETIME NULL,
    `submitted_at` DATETIME NULL,
    `submitted_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    `reviewed_by` INT NULL,
    `completed_at` DATETIME NULL,
    `review_note` TEXT NULL,
    UNIQUE KEY `uk_project_stage` (`project_id`, `stage`),
    UNIQUE KEY `uk_project_position` (`project_id`, `position`),
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`submitted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng Phân công Nhân sự Kỹ thuật & Khối lượng công việc
DROP TABLE IF EXISTS `project_assignments`;
CREATE TABLE `project_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `technician_id` INT NOT NULL,
    `planned_start` DATE NOT NULL,
    `planned_end` DATE NOT NULL,
    `hours_per_day` DECIMAL(4,2) NOT NULL DEFAULT 4.00,
    `assigned_by` INT NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ended_at` DATETIME NULL,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_assign_workload` (`technician_id`, `ended_at`, `planned_start`, `planned_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng Yêu cầu Xử lý Tiến độ (Hoãn tiến độ / Thay thế kỹ thuật viên)
DROP TABLE IF EXISTS `project_requests`;
CREATE TABLE `project_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `stage_id` INT NOT NULL,
    `request_type` ENUM('postpone', 'replacement') NOT NULL,
    `reason` TEXT NOT NULL,
    `proposed_start` DATE NULL,
    `proposed_end` DATE NULL,
    `old_technician_id` INT NULL,
    `proposed_technician_id` INT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'superseded') NOT NULL DEFAULT 'pending',
    `requested_by` INT NOT NULL,
    `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    `review_note` TEXT NULL,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stage_id`) REFERENCES `project_stages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`old_technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`proposed_technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_requests_status` (`project_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng Nhật ký Lịch sử Chuyển đổi Giai đoạn thi công (Timeline History)
DROP TABLE IF EXISTS `project_stage_logs`;
CREATE TABLE `project_stage_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover') NOT NULL,
    `started_at` DATETIME NOT NULL,
    `completed_at` DATETIME NULL,
    `actor_id` INT NULL,
    `action` VARCHAR(50) NOT NULL,
    `from_status` VARCHAR(30) NULL,
    `to_status` VARCHAR(30) NULL,
    `note` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`actor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_stage_logs` (`project_id`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bảng Hồ sơ Kỹ thuật, Bản vẽ CAD & Ảnh khảo sát mặt bằng
DROP TABLE IF EXISTS `project_documents`;
CREATE TABLE `project_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `stage_id` INT NOT NULL,
    `document_type` ENUM(
        'survey_photo',
        'technical_drawing',
        'evn_permit',
        'procurement_invoice',
        'handover_report',
        'installation_photo',
        'testing_photo',
        'stage_photo'
    ) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Tên tệp gốc',
    `file_hash` CHAR(64) NOT NULL COMMENT 'Chuỗi băm SHA-256 chống trùng lặp',
    `file_path` VARCHAR(255) NOT NULL COMMENT 'Đường dẫn lưu trên disk',
    `file_size_kb` INT NOT NULL,
    `file_type` VARCHAR(50) NOT NULL COMMENT 'MIME-type',
    `uploaded_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stage_id`) REFERENCES `project_stages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY `uk_stage_filehash` (`stage_id`, `file_hash`),
    INDEX `idx_project_docs` (`project_id`, `stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- PHẦN 2: CÁC BẢNG NGHIỆP VỤ QUẢN LÝ BÁN SỮA (PHỤC VỤ 24 BÀI TẬP THỰC HÀNH)
-- -----------------------------------------------------------------------------

-- 12. Bảng Hãng Sữa
DROP TABLE IF EXISTS `hang_sua`;
CREATE TABLE `hang_sua` (
    `Ma_hang_sua` VARCHAR(20) NOT NULL PRIMARY KEY,
    `Ten_hang_sua` VARCHAR(100) NOT NULL,
    `Dia_chi` VARCHAR(200) NULL,
    `Dien_thoai` VARCHAR(20) NULL,
    `Email` VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Bảng Loại Sữa
DROP TABLE IF EXISTS `loai_sua`;
CREATE TABLE `loai_sua` (
    `Ma_loai_sua` VARCHAR(20) NOT NULL PRIMARY KEY,
    `Ten_loai` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Bảng Sản phẩm Sữa (Trọng tâm Bài 19 đến Bài 24)
DROP TABLE IF EXISTS `sua`;
CREATE TABLE `sua` (
    `Ma_sua` VARCHAR(20) NOT NULL PRIMARY KEY,
    `Ten_sua` VARCHAR(100) NOT NULL,
    `Ma_hang_sua` VARCHAR(20) NOT NULL,
    `Ma_loai_sua` VARCHAR(20) NOT NULL,
    `Trong_luong` INT NOT NULL COMMENT 'Trọng lượng (gram hoặc ml)',
    `Don_gia` DECIMAL(15,2) NOT NULL COMMENT 'Đơn giá (VNĐ)',
    `TP_Dinh_Duong` TEXT NULL COMMENT 'Thành phần dinh dưỡng',
    `Loi_ich` TEXT NULL COMMENT 'Lợi ích sức khỏe',
    `Hinh` VARCHAR(255) NULL COMMENT 'Tên file hình ảnh sản phẩm',
    FOREIGN KEY (`Ma_hang_sua`) REFERENCES `hang_sua`(`Ma_hang_sua`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`Ma_loai_sua`) REFERENCES `loai_sua`(`Ma_loai_sua`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_sua_search` (`Ten_sua`, `Don_gia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Bảng Khách Hàng mua sữa
DROP TABLE IF EXISTS `khach_hang`;
CREATE TABLE `khach_hang` (
    `Ma_khach_hang` VARCHAR(20) NOT NULL PRIMARY KEY,
    `Ten_khach_hang` VARCHAR(100) NOT NULL,
    `Phai` TINYINT(1) DEFAULT 0 COMMENT '0: Nam, 1: Nữ',
    `Dia_chi` VARCHAR(200) NULL,
    `Dien_thoai` VARCHAR(20) NULL,
    `Email` VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Bảng Hóa Đơn bán sữa
DROP TABLE IF EXISTS `hoa_don`;
CREATE TABLE `hoa_don` (
    `So_hoa_don` VARCHAR(20) NOT NULL PRIMARY KEY,
    `Ngay_HD` DATE NOT NULL,
    `Ma_khach_hang` VARCHAR(20) NOT NULL,
    `Tri_gia` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (`Ma_khach_hang`) REFERENCES `khach_hang`(`Ma_khach_hang`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Bảng Chi Tiết Hóa Đơn
DROP TABLE IF EXISTS `ct_hoadon`;
CREATE TABLE `ct_hoadon` (
    `So_hoa_don` VARCHAR(20) NOT NULL,
    `Ma_sua` VARCHAR(20) NOT NULL,
    `So_luong` INT NOT NULL DEFAULT 1,
    `Don_gia` DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (`So_hoa_don`, `Ma_sua`),
    FOREIGN KEY (`So_hoa_don`) REFERENCES `hoa_don`(`So_hoa_don`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`Ma_sua`) REFERENCES `sua`(`Ma_sua`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;


-- =============================================================================
-- SOLARIS ENERGY & ACADEMIC PORTAL - MASTER SEED DATA
-- Dữ liệu mẫu chuẩn hóa cho Hệ thống Solaris & 24 Bài tập thực hành PHP
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. BẢNG USERS (Mật khẩu chung cho tất cả tài khoản mẫu: Solaris@2026)
-- Hash BCrypt: $2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `role`) VALUES
(1, 'admin', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Nguyễn Minh Quân (Quản Trị)', 'admin@solaris.vn', '0909888001', 'admin'),
(2, 'admin.tv4', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Phan Văn Thịnh (QA & Lắp Đặt)', 'thinh.pv@solaris.vn', '0909888004', 'admin'),
(3, 'kythuat.an', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Trần Hoàng An', 'an.th@solaris.vn', '0909777001', 'technician'),
(4, 'kythuat.binh', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Lê Thanh Bình', 'binh.lt@solaris.vn', '0909777002', 'technician'),
(5, 'kythuat.chi', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Phạm Ngọc Chi', 'chi.pn@solaris.vn', '0909777003', 'technician'),
(6, 'kythuat.dung', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Vũ Anh Dũng', 'dung.va@solaris.vn', '0909777004', 'technician'),
(7, 'khachhang.demo', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Nguyễn Văn Hải', 'hai.nv@gmail.com', '0912345678', 'customer'),
(8, 'khachhang.mai', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Trần Thị Mai', 'mai.tt@gmail.com', '0987654321', 'customer'),
(9, 'khachhang.tuan', '$2y$10$tsxq5r3F0X9FthiMHAtTseampqPy9M8GscvTcVgDD1KkKdPf.et7m', 'Đặng Quốc Tuấn', 'tuan.dq@gmail.com', '0933221100', 'customer');

-- -----------------------------------------------------------------------------
-- 2. BẢNG CATEGORIES
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `categories`;
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Tấm Pin Năng Lượng Mặt Trời', 'tam-pin-mat-troi', 'Các dòng pin quang điện Mono Crystalline, N-Type TOPCon hiệu suất cao từ các hãng Tier-1 thế giới.'),
(2, 'Biến Tần Inverter Hòa Lưới & Hybrid', 'bien-tan-inverter', 'Bộ biến đổi điện một chiều DC sang xoay chiều AC hòa lưới và lưu trữ dự phòng thông minh.'),
(3, 'Pin Lưu Trữ Lithium (BESS)', 'pin-luu-tru-lithium', 'Hệ thống acquy Lithium Iron Phosphate (LiFePO4) chu kỳ nạp xả 6000 lần, độ bền vượt trội.'),
(4, 'Phụ Kiện Khung Nhôm & Tủ Đóng Cắt', 'phu-kien-lap-dat', 'Khung đỡ nhôm anode AL6005-T5, kẹp pin, cáp năng lượng mặt trời DC chuyên dụng và tủ điện ATS.');

-- -----------------------------------------------------------------------------
-- 3. BẢNG PRODUCTS (26 sản phẩm > 20 dòng để kiểm thử phân trang)
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `products`;
INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `capacity_watts`, `price`, `warranty_years`, `technical_specs`, `image_url`, `stock_quantity`, `is_active`) VALUES
(1, 1, 'SOL-PAN-001', 'Tấm Pin Canadian Solar 550W HiKu6 Mono', 550, 2450000.00, 12, '{"efficiency": "21.5%", "cell_type": "Mono Crystalline", "weight_kg": 27.6}', 'assets/img/products/canadian-550w.jpg', 150, 1),
(2, 1, 'SOL-PAN-002', 'Tấm Pin LONGi Hi-MO 6 Explorer 580W', 580, 2750000.00, 15, '{"efficiency": "22.5%", "cell_type": "HPBC N-Type", "weight_kg": 27.5}', 'assets/img/products/longi-580w.jpg', 120, 1),
(3, 1, 'SOL-PAN-003', 'Tấm Pin Jinko Solar Tiger Neo 600W N-Type', 600, 2950000.00, 15, '{"efficiency": "22.7%", "cell_type": "TOPCon N-Type", "weight_kg": 28.0}', 'assets/img/products/jinko-600w.jpg', 90, 1),
(4, 1, 'SOL-PAN-004', 'Tấm Pin JA Solar 545W DeepBlue 3.0 Pro', 545, 2380000.00, 12, '{"efficiency": "21.3%", "cell_type": "Mono PERC", "weight_kg": 27.2}', 'assets/img/products/ja-545w.jpg', 80, 1),
(5, 1, 'SOL-PAN-005', 'Tấm Pin Trina Solar Vertex 670W 210mm', 670, 3400000.00, 12, '{"efficiency": "21.6%", "cell_type": "Ultra-High Power 210mm", "weight_kg": 33.7}', 'assets/img/products/trina-670w.jpg', 65, 1),
(6, 1, 'SOL-PAN-006', 'Tấm Pin AE Solar 550W Đức - Aurora Series', 550, 2600000.00, 15, '{"efficiency": "21.4%", "technology": "German Engineering", "weight_kg": 28.0}', 'assets/img/products/ae-550w.jpg', 45, 1),
(7, 1, 'SOL-PAN-007', 'Tấm Pin Risen Energy 500W Titan', 500, 2150000.00, 12, '{"efficiency": "20.9%", "cell_type": "Mono PERC 132 cells", "weight_kg": 25.0}', 'assets/img/products/risen-500w.jpg', 55, 1),
(8, 2, 'SOL-INV-001', 'Biến Tần Hòa Lưới Sungrow 5kW 1 Pha SG5.0RS', 5000, 15500000.00, 5, '{"mppt": 2, "max_efficiency": "97.7%", "cooling": "Natural convection"}', 'assets/img/products/sungrow-5kw.jpg', 40, 1),
(9, 2, 'SOL-INV-002', 'Biến Tần Hòa Lưới Huawei 5kW SUN2000-5KTL-L1', 5000, 18200000.00, 5, '{"mppt": 2, "max_efficiency": "98.4%", "ai_afci": true}', 'assets/img/products/huawei-5kw.jpg', 35, 1),
(10, 2, 'SOL-INV-003', 'Biến Tần Hòa Lưới Huawei 10kW SUN2000-10KTL-M1', 10000, 26500000.00, 5, '{"phase": "3 Pha", "mppt": 2, "max_efficiency": "98.6%"}', 'assets/img/products/huawei-10kw.jpg', 25, 1),
(11, 2, 'SOL-INV-004', 'Biến Tần Hòa Lưới Growatt 6kW MIN 6000TL-X', 6000, 14200000.00, 5, '{"mppt": 2, "dc_ac_ratio": 1.4, "oled_display": true}', 'assets/img/products/growatt-6kw.jpg', 50, 1),
(12, 2, 'SOL-INV-005', 'Biến Tần Hybrid Deye 8kW 1 Pha SUN-8K-SG01LP1', 8000, 31500000.00, 5, '{"battery_voltage": "48V", "ups_switch_ms": 4, "generator_input": true}', 'assets/img/products/deye-8kw.jpg', 20, 1),
(13, 2, 'SOL-INV-006', 'Biến Tần Hybrid GoodWe 10kW 3 Pha ET Series', 10000, 42000000.00, 5, '{"battery_voltage": "High Voltage 180-600V", "efficiency": "98.2%"}', 'assets/img/products/goodwe-10kw.jpg', 15, 1),
(14, 2, 'SOL-INV-007', 'Biến Tần Hòa Lưới SMA Sunny Boy 5.0 (Đức)', 5000, 24000000.00, 10, '{"origin": "Germany", "webconnect_integrated": true}', 'assets/img/products/sma-5kw.jpg', 10, 1),
(15, 3, 'SOL-BAT-001', 'Pin Lưu Trữ Lithium Pylontech US5000 4.8kWh', 4800, 36000000.00, 10, '{"voltage": "48V", "dod": "95%", "cycle_life": 6000}', 'assets/img/products/pylontech-48.jpg', 25, 1),
(16, 3, 'SOL-BAT-002', 'Pin Lưu Trữ Lithium Deye 5.12kWh LiFePO4', 5120, 33500000.00, 10, '{"voltage": "51.2V", "bms": "Integrated Smart BMS", "wall_mounted": true}', 'assets/img/products/deye-5kwh.jpg', 30, 1),
(17, 3, 'SOL-BAT-003', 'Pin Lưu Trữ Huawei LUNA2000 10kWh Cao Áp', 10000, 89000000.00, 10, '{"modular": true, "high_voltage": true, "safe_chemistry": "LFP"}', 'assets/img/products/huawei-luna-10k.jpg', 12, 1),
(18, 3, 'SOL-BAT-004', 'Pin Lưu Trữ GoodWe Lynx Home U 5.4kWh', 5400, 37500000.00, 10, '{"capacity_ah": 105, "ip_rating": "IP65 Outdoor"}', 'assets/img/products/goodwe-lynx.jpg', 18, 1),
(19, 4, 'SOL-ACC-001', 'Thanh Rail Nhôm Chuyên Dụng AL6005-T5 4.2m', NULL, 280000.00, 10, '{"material": "Aluminium Anodized AL6005-T5", "length_m": 4.2}', 'assets/img/products/rail-42m.jpg', 500, 1),
(20, 4, 'SOL-ACC-002', 'Kẹp Giữa Chữ U Kèm Bulong Inox 304 (Bộ)', NULL, 15000.00, 10, '{"material": "Inox 304 & AL6005"}', 'assets/img/products/mid-clamp.jpg', 2000, 1),
(21, 4, 'SOL-ACC-003', 'Kẹp Biên Chữ Z Điều Chỉnh 30-40mm (Bộ)', NULL, 16000.00, 10, '{"material": "Inox 304 & AL6005"}', 'assets/img/products/end-clamp.jpg', 2000, 1),
(22, 4, 'SOL-ACC-004', 'Cuộn Cáp Điện Mặt Trời DC Chuyên Dụng 4.0mm2 (100m)', NULL, 1450000.00, 25, '{"standard": "EN50618", "copper": "Tinned Copper", "uv_resistant": true}', 'assets/img/products/cable-dc-4mm.jpg', 80, 1),
(23, 4, 'SOL-ACC-005', 'Cặp Đầu Nối Chống Nước MC4 1500VDC', NULL, 22000.00, 5, '{"ip_rating": "IP68", "rated_voltage": "1500V DC"}', 'assets/img/products/mc4.jpg', 1500, 1),
(24, 4, 'SOL-ACC-006', 'Tủ Điện Đóng Cắt DC/AC & Chống Sét Lan Truyền 5kW', NULL, 4800000.00, 2, '{"components": "Schneider & Suntree SPD 1000V"}', 'assets/img/products/electrical-box-5kw.jpg', 35, 1),
(25, 4, 'SOL-ACC-007', 'Tủ Điện Đóng Cắt 3 Pha 10kW Tích Hợp ATS Dự Phòng', NULL, 8500000.00, 2, '{"features": "Tự động đảo nguồn lưới - pin lưu trữ <10ms"}', 'assets/img/products/electrical-box-10kw.jpg', 20, 1),
(26, 4, 'SOL-ACC-008', 'Bộ Đồng Hồ Đo Đếm Năng Lượng Smart Meter Chint 1 Pha', NULL, 1250000.00, 2, '{"protocol": "RS485 Modbus RTU", "zero_export_control": true}', 'assets/img/products/smart-meter.jpg', 60, 1);

-- -----------------------------------------------------------------------------
-- 4. BẢNG SOLAR_QUOTES
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `solar_quotes`;
INSERT INTO `solar_quotes` (`id`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `address`, `monthly_electric_bill`, `roof_area_sqm`, `estimated_system_kwp`, `panel_count`, `estimated_total_cost`, `payback_years`, `notes`, `status`) VALUES
(1, 7, 'Nguyễn Văn Hải', '0912345678', 'hai.nv@gmail.com', '12 Nguyễn Văn Hưởng, Thảo Điền, TP. Thủ Đức, TP.HCM', 3500000.00, 48.00, 7.70, 14, 115000000.00, 4.2, 'Mái ngói Thái hướng Nam, có nhu cầu lắp thêm lưu trữ sau 6 tháng.', 'approved'),
(2, 8, 'Trần Thị Mai', '0987654321', 'mai.tt@gmail.com', '145 Bạch Đằng, Phường 2, Quận Bình Thạnh, TP.HCM', 2400000.00, 35.00, 5.50, 10, 85000000.00, 4.5, 'Mái tôn bằng phẳng, yêu cầu thi công gọn gàng trong ngày thứ 7.', 'approved'),
(3, 9, 'Đặng Quốc Tuấn', '0933221100', 'tuan.dq@gmail.com', '78 Cộng Hòa, Phường 4, Quận Tân Bình, TP.HCM', 6800000.00, 110.00, 15.00, 26, 210000000.00, 3.8, 'Xưởng may thời trang, tiêu thụ điện chủ yếu ban ngày từ 8h đến 17h.', 'approved'),
(4, NULL, 'Lê Hoàng Long', '0903112233', 'long.lh@yahoo.com', 'Khu biệt thự Nam Long, Quận 7, TP.HCM', 4500000.00, 65.00, 10.00, 18, 148000000.00, 4.1, 'Khách hàng vãng lai để lại yêu cầu tính toán trên website.', 'survey_scheduled'),
(5, NULL, 'Công Ty TNHH Logistics Á Châu', '0283889988', 'info@achau-logistics.vn', 'KCN Sóng Thần 2, Dĩ An, Bình Dương', 25000000.00, 450.00, 50.00, 90, 680000000.00, 3.4, 'Dự án áp mái nhà kho xưởng công nghiệp.', 'contacted'),
(6, NULL, 'Phạm Gia Khiêm', '0944556677', 'khiem.pg@gmail.com', '24 Lê Lợi, Phường Bến Nghé, Quận 1, TP.HCM', 1800000.00, 25.00, 3.30, 6, 52000000.00, 4.8, 'Nhà phố trung tâm, diện tích mái hạn chế.', 'pending');

-- -----------------------------------------------------------------------------
-- 5. BẢNG PROJECTS
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `projects`;
INSERT INTO `projects` (`id`, `contract_code`, `quote_id`, `user_id`, `technician_id`, `project_name`, `capacity_kwp`, `total_amount`, `installation_address`, `status`, `current_stage`, `notes`, `start_date`, `completion_date`, `planned_end_date`) VALUES
(1, 'SOLAR-2026-001', 1, 7, 3, 'Điện mặt trời Biệt thự Thảo Điền - Anh Hải', 7.70, 115000000.00, '12 Nguyễn Văn Hưởng, Thảo Điền, TP. Thủ Đức, TP.HCM', 'in_progress', 'installation', 'Hợp đồng trọn gói bao gồm 14 tấm pin Canadian 550W và Inverter Sungrow 8kW.', '2026-10-15', NULL, '2026-11-15'),
(2, 'SOLAR-2026-002', 2, 8, 4, 'Điện mặt trời Nhà phố Bình Thạnh - Chị Mai', 5.50, 85000000.00, '145 Bạch Đằng, Phường 2, Quận Bình Thạnh, TP.HCM', 'completed', 'handover', 'Hệ thống đã nghiệm thu đóng điện hòa lưới thành công.', '2026-10-01', '2026-10-20', '2026-10-22'),
(3, 'SOLAR-2026-003', 3, 9, 3, 'Hệ thống Điện mặt trời Xưởng May Tân Bình', 15.00, 210000000.00, '78 Cộng Hòa, Phường 4, Quận Tân Bình, TP.HCM', 'new', 'survey', 'Chờ phê duyệt lịch khảo sát kết cấu xà gồ mái tôn xưởng.', '2026-10-28', NULL, '2026-12-05');

-- -----------------------------------------------------------------------------
-- 6. BẢNG PROJECT_STAGES (Chi tiết 6 bước của 3 dự án)
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `project_stages`;
-- Dự án 1 (Đang ở bước 4: Thi công lắp đặt)
INSERT INTO `project_stages` (`id`, `project_id`, `stage`, `position`, `status`, `planned_start`, `planned_end`, `started_at`, `completed_at`, `review_note`) VALUES
(1, 1, 'survey', 1, 'approved', '2026-10-15', '2026-10-18', '2026-10-15 08:30:00', '2026-10-17 16:00:00', 'Mặt bằng đạt tiêu chuẩn chịu tải mái ngói.'),
(2, 1, 'design', 2, 'approved', '2026-10-19', '2026-10-24', '2026-10-19 09:00:00', '2026-10-23 15:30:00', 'Bản vẽ CAD phương án đi dây đạt chuẩn PCCC.'),
(3, 1, 'procurement', 3, 'approved', '2026-10-25', '2026-10-29', '2026-10-25 08:00:00', '2026-10-28 17:00:00', 'Đã xuất kho đầy đủ 14 tấm Canadian và Inverter.'),
(4, 1, 'installation', 4, 'in_progress', '2026-10-30', '2026-11-06', '2026-10-30 08:00:00', NULL, 'Đang tiến hành lắp ray nhôm và gắn tấm pin lên mái.'),
(5, 1, 'grid_connection', 5, 'locked', '2026-11-07', '2026-11-11', NULL, NULL, NULL),
(6, 1, 'handover', 6, 'locked', '2026-11-12', '2026-11-15', NULL, NULL, NULL),

-- Dự án 2 (Đã hoàn tất cả 6 bước)
(7, 2, 'survey', 1, 'approved', '2026-10-01', '2026-10-03', '2026-10-01 08:00:00', '2026-10-03 16:00:00', 'Khảo sát tốt.'),
(8, 2, 'design', 2, 'approved', '2026-10-04', '2026-10-07', '2026-10-04 09:00:00', '2026-10-06 17:00:00', 'Thiết kế duyệt.'),
(9, 2, 'procurement', 3, 'approved', '2026-10-08', '2026-10-10', '2026-10-08 08:00:00', '2026-10-10 16:30:00', 'Vật tư đủ.'),
(10, 2, 'installation', 4, 'approved', '2026-10-11', '2026-10-15', '2026-10-11 08:00:00', '2026-10-14 17:00:00', 'Lắp đặt an toàn.'),
(11, 2, 'grid_connection', 5, 'approved', '2026-10-16', '2026-10-18', '2026-10-16 08:30:00', '2026-10-18 15:00:00', 'Điện lực EVN đã gắn công tơ 2 chiều.'),
(12, 2, 'handover', 6, 'approved', '2026-10-19', '2026-10-20', '2026-10-19 09:00:00', '2026-10-20 11:30:00', 'Bàn giao app giám sát và biên bản bảo hành 5 năm.'),

-- Dự án 3 (Mới khởi tạo - Đang ở bước 1: Khảo sát)
(13, 3, 'survey', 1, 'in_progress', '2026-10-28', '2026-11-02', '2026-10-28 14:00:00', NULL, NULL),
(14, 3, 'design', 2, 'locked', '2026-11-03', '2026-11-08', NULL, NULL, NULL),
(15, 3, 'procurement', 3, 'locked', '2026-11-09', '2026-11-15', NULL, NULL, NULL),
(16, 3, 'installation', 4, 'locked', '2026-11-16', '2026-11-25', NULL, NULL, NULL),
(17, 3, 'grid_connection', 5, 'locked', '2026-11-26', '2026-11-30', NULL, NULL, NULL),
(18, 3, 'handover', 6, 'locked', '2026-12-01', '2026-12-05', NULL, NULL, NULL);

-- -----------------------------------------------------------------------------
-- 7. BẢNG PROJECT_ASSIGNMENTS (Phân công kỹ thuật)
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `project_assignments`;
INSERT INTO `project_assignments` (`id`, `project_id`, `technician_id`, `planned_start`, `planned_end`, `hours_per_day`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 3, '2026-10-15', '2026-11-15', 4.00, 1, '2026-10-15 08:00:00'),
(2, 1, 4, '2026-10-30', '2026-11-06', 4.00, 1, '2026-10-29 16:00:00'),
(3, 2, 4, '2026-10-01', '2026-10-20', 6.00, 1, '2026-10-01 07:30:00'),
(4, 3, 3, '2026-10-28', '2026-12-05', 4.00, 1, '2026-10-28 09:00:00'),
(5, 3, 5, '2026-10-28', '2026-11-02', 4.00, 1, '2026-10-28 09:00:00');

-- -----------------------------------------------------------------------------
-- 8. BẢNG PROJECT_STAGE_LOGS (Lịch sử tiến độ)
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `project_stage_logs`;
INSERT INTO `project_stage_logs` (`id`, `project_id`, `stage`, `started_at`, `actor_id`, `action`, `from_status`, `to_status`, `note`) VALUES
(1, 1, 'survey', '2026-10-15 08:00:00', 1, 'project_created', NULL, 'new', 'Khởi tạo từ báo giá đã duyệt #1 (SOLAR-2026-001)'),
(2, 1, 'survey', '2026-10-17 16:00:00', 3, 'stage_approved', 'in_progress', 'approved', 'Hoàn tất khảo sát đo vẽ thực địa mái'),
(3, 1, 'design', '2026-10-23 15:30:00', 1, 'stage_approved', 'pending_approval', 'approved', 'Phê duyệt bản vẽ CAD và hồ sơ thỏa thuận đấu nối'),
(4, 1, 'procurement', '2026-10-28 17:00:00', 1, 'stage_approved', 'pending_approval', 'approved', 'Hoàn tất thủ tục xuất kho 14 tấm pin và phụ kiện'),
(5, 1, 'installation', '2026-10-30 08:00:00', 3, 'stage_started', 'locked', 'in_progress', 'Bắt đầu triển khai lắp đặt hệ thống cơ điện trên mái');


-- -----------------------------------------------------------------------------
-- PHẦN 2: DỮ LIỆU MẪU QUẢN LÝ BÁN SỮA (PHỤC VỤ 24 BÀI TẬP THỰC HÀNH)
-- -----------------------------------------------------------------------------

-- 9. BẢNG HÃNG SỮA
TRUNCATE TABLE `hang_sua`;
INSERT INTO `hang_sua` (`Ma_hang_sua`, `Ten_hang_sua`, `Dia_chi`, `Dien_thoai`, `Email`) VALUES
('VNM', 'Vinamilk - CTCP Sữa Việt Nam', '10 Tân Trào, P. Tân Phú, Quận 7, TP.HCM', '02854155555', 'vinamilk@vinamilk.com.vn'),
('NTF', 'Nutifood Nutrition Food', '283 Đỗ Xuân Hợp, P. Phước Long B, TP. Thủ Đức, TP.HCM', '02838267999', 'nutifood@nutifood.com.vn'),
('DL', 'Dutch Lady - Cô Gái Hà Lan', 'Xã Bình Hòa, Thị xã Thuận An, Tỉnh Bình Dương', '02743755455', 'cskh@dutchlady.com.vn'),
('TH', 'TH True Milk - Tập đoàn TH', '166 Nguyễn Thái Học, P. Quang Trung, TP. Vinh, Nghệ An', '1800545440', 'chamsockhachhang@thmilk.vn'),
('AB', 'Abbott Nutrition Việt Nam', 'Tầng 12 Mê Linh Point Tower, 2 Ngô Đức Kế, Quận 1, TP.HCM', '02838242400', 'abbott_vietnam@abbott.com'),
('MJ', 'Mead Johnson Nutrition', 'Tầng 4 Diamond Plaza, 34 Lê Duẩn, Quận 1, TP.HCM', '02838245050', 'contact@meadjohnson.com');

-- 10. BẢNG LOẠI SỮA
TRUNCATE TABLE `loai_sua`;
INSERT INTO `loai_sua` (`Ma_loai_sua`, `Ten_loai`) VALUES
('SB', 'Sữa bột dinh dưỡng'),
('ST', 'Sữa tươi tiệt trùng'),
('SC', 'Sữa chua ăn & uống'),
('SD', 'Sữa đặc có đường');

-- 11. BẢNG SỮA (25 sản phẩm phong phú > 20 dòng để làm Bài 19 Phân trang)
TRUNCATE TABLE `sua`;
INSERT INTO `sua` (`Ma_sua`, `Ten_sua`, `Ma_hang_sua`, `Ma_loai_sua`, `Trong_luong`, `Don_gia`, `TP_Dinh_Duong`, `Loi_ich`, `Hinh`) VALUES
('SUA001', 'Sữa Bột Dielac Alpha Gold 1', 'VNM', 'SB', 900, 285000.00, 'DHA, Choline, Sắt, Kẽm, Vitamin A, C, D', 'Phát triển não bộ và thị giác cho trẻ từ 0 - 6 tháng', 'dielac-alpha-1.jpg'),
('SUA002', 'Sữa Bột Dielac Alpha Gold 2', 'VNM', 'SB', 900, 295000.00, 'DHA, ARA, Lutein, Canxi, Photpho', 'Tăng cường sức đề kháng và chiều cao cho trẻ 6 - 12 tháng', 'dielac-alpha-2.jpg'),
('SUA003', 'Sữa Bột Dielac Alpha Gold 3', 'VNM', 'SB', 900, 315000.00, 'Canxi, Vitamin K2, Chất xơ FOS, Probiotics', 'Hỗ trợ tiêu hóa khỏe mạnh, hấp thu tốt từ 1 - 2 tuổi', 'dielac-alpha-3.jpg'),
('SUA004', 'Sữa Tươi Tiệt Trùng Vinamilk 100% Có Đường (Lốc 4x180ml)', 'VNM', 'ST', 720, 32000.00, 'Sữa bò tươi nguyên chất, Vitamin D3, Canxi, Selen', 'Bổ sung năng lượng dồi dào và tăng cường miễn dịch', 'vinamilk-st-180.jpg'),
('SUA005', 'Sữa Tươi Tiệt Trùng Vinamilk 100% Không Đường (1 Lít)', 'VNM', 'ST', 1000, 36000.00, '100% Sữa tươi sạch, không bổ sung đường', 'Giữ dáng, bổ sung canxi tự nhiên cho cả gia đình', 'vinamilk-st-1l.jpg'),
('SUA006', 'Sữa Đặc Có Đường Ông Thọ Nhãn Xanh Hộp Thiếc', 'VNM', 'SD', 380, 26000.00, 'Đường, sữa, chất béo sữa, đạm sữa', 'Hương vị béo ngậy thơm ngon, làm bánh và pha cà phê', 'ong-tho-xanh.jpg'),
('SUA007', 'Sữa Đặc Có Đường Phương Nam Hộp Giấy', 'VNM', 'SD', 1284, 68000.00, 'Đường tinh luyện, sữa bột, dầu thực vật', 'Tiết kiệm tối ưu cho quán pha chế sinh tố và trà sữa', 'phuong-nam.jpg'),
('SUA008', 'Sữa Chua Ăn Vinamilk Có Đường (Lốc 4 hộp x 100g)', 'VNM', 'SC', 400, 28000.00, 'Lên men tự nhiên từ sữa tươi, chủng men Bulgaricus', 'Hỗ trợ đường ruột khỏe mạnh và làn da mịn màng', 'sua-chua-vnm.jpg'),
('SUA009', 'Sữa Bột Nutifood GrowPLUS+ Đỏ', 'NTF', 'SB', 900, 365000.00, 'FOS/Inulin, Lysin, Sắt, Kẽm, Vitamin nhóm B', 'Đặc chế cho trẻ suy dinh dưỡng, thấp còi, biếng ăn', 'growplus-do.jpg'),
('SUA010', 'Sữa Bột Nutifood GrowPLUS+ Xanh', 'NTF', 'SB', 900, 245000.00, 'MCT, Canxi, Photpho, Vitamin D3, Selen', 'Giúp trẻ tăng cân đều đặn và phát triển thể chất', 'growplus-xanh.jpg'),
('SUA011', 'Sữa Bột Nuvita Grow Diamond', 'NTF', 'SB', 900, 330000.00, 'Nano Canxi, Vitamin K2, D3, DHA từ tảo Thụy Điển', 'Công thức đột phá phát triển chiều cao tối ưu', 'nuvita-diamond.jpg'),
('SUA012', 'Sữa Bột Dutch Lady Khám Phá 2-4 Tuổi', 'DL', 'SB', 900, 275000.00, 'Cholin, Magie, Vitamin B1, B2, B12, I-ốt', 'Giúp trẻ tò mò khám phá và học hỏi thế giới xung quanh', 'dutchlady-khampha.jpg'),
('SUA013', 'Sữa Tươi Cô Gái Hà Lan Cao Khỏe Có Đường (Lốc 4x180ml)', 'DL', 'ST', 720, 31000.00, 'Bộ đôi Canxi và Phốt pho, Vitamin D, Protein', 'Cho hệ xương chắc khỏe và vóc dáng cao lớn', 'dutchlady-caokhoe.jpg'),
('SUA014', 'Sữa Bột Dutch Lady Sáng Tạo 4-6 Tuổi', 'DL', 'SB', 900, 285000.00, 'DHA, Sắt, Axit Folic, Kẽm, Vitamin C', 'Phát triển tư duy logic và khả năng sáng tạo', 'dutchlady-sangtao.jpg'),
('SUA015', 'Sữa Tươi Sạch TH True Milk Nguyên Chất Có Đường (Lốc 4x180ml)', 'TH', 'ST', 720, 35000.00, 'Sữa bò tươi sạch nguyên chất của trang trại TH', 'Vị sữa thanh mát, giàu khoáng chất tự nhiên', 'th-coduong-180.jpg'),
('SUA016', 'Sữa Tươi Sạch TH True Milk Ít Đường (Lốc 4x180ml)', 'TH', 'ST', 720, 35000.00, 'Giảm 25% đường, thơm béo thanh nhẹ', 'Hạn chế vị ngọt, thích hợp người ăn kiêng đường', 'th-itduong-180.jpg'),
('SUA017', 'Sữa Chua Uống Men Sống TH True Yogurt Hương Cam', 'TH', 'SC', 400, 30000.00, 'Khoảng 1 tỷ lợi khuẩn TH.paracasei, hương cam tự nhiên', 'Cân bằng hệ vi sinh đường ruột và tiêu hóa êm dịu', 'th-yogurt-cam.jpg'),
('SUA018', 'Sữa Bột Ensure Gold Hương Vani', 'AB', 'SB', 850, 890000.00, 'HMB, 28 Vitamin và khoáng chất, đạm chất lượng cao', 'Bồi bổ sức khỏe toàn diện cho người lớn tuổi và người bệnh', 'ensure-gold-850.jpg'),
('SUA019', 'Sữa Bột Glucerna Cho Người Đái Tháo Đường', 'AB', 'SB', 850, 920000.00, 'Hệ bột đường giải phóng chậm Fibersol, Omega 3, MUFA', 'Kiểm soát đường huyết và tốt cho hệ tim mạch', 'glucerna-850.jpg'),
('SUA020', 'Sữa Bột PediaSure BA Cho Trẻ Biếng Ăn', 'AB', 'SB', 850, 680000.00, 'Hệ 3 phức hợp đạm, 37 dưỡng chất, vi sinh vật có lợi', 'Giúp trẻ biếng ăn bắt kịp đà tăng trưởng chuẩn', 'pediasure-850.jpg'),
('SUA021', 'Sữa Bột Similac 5G Số 1', 'AB', 'SB', 900, 580000.00, 'Phức hợp 5 loại HMOs, Gangliosides, BB-12', 'Hỗ trợ miễn dịch và phát triển não bộ 5 phương diện', 'similac-5g-1.jpg'),
('SUA022', 'Sữa Bột Similac 5G Số 2', 'AB', 'SB', 900, 570000.00, '5 loại HMOs, Lutein, Vitamin E tự nhiên, DHA', 'Cho trẻ từ 6 đến 12 tháng tuổi phát triển trí tuệ', 'similac-5g-2.jpg'),
('SUA023', 'Sữa Bột Enfamil A+ NeuroPro Số 1', 'MJ', 'SB', 830, 620000.00, 'Bộ đôi dưỡng chất vàng MFGM và DHA', 'Hỗ trợ phát triển tư duy cảm xúc và trí tuệ sớm', 'enfamil-neuro-1.jpg'),
('SUA024', 'Sữa Bột Enfamil A+ NeuroPro Số 2', 'MJ', 'SB', 830, 615000.00, 'Hệ chất xơ 2’-FL HMO và FOS, MFGM, DHA', 'Tăng cường sức đề kháng đường ruột cho trẻ 6-12 tháng', 'enfamil-neuro-2.jpg'),
('SUA025', 'Sữa Bột Enfagrow A+ NeuroPro Số 3', 'MJ', 'SB', 830, 550000.00, 'Hàm lượng DHA chuẩn khuyến nghị FAO/WHO', 'Nuôi dưỡng trí não và chiều cao cho trẻ 1 - 3 tuổi', 'enfagrow-neuro-3.jpg');

-- -----------------------------------------------------------------------------
-- 12. BẢNG KHÁCH HÀNG
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `khach_hang`;
INSERT INTO `khach_hang` (`Ma_khach_hang`, `Ten_khach_hang`, `Phai`, `Dia_chi`, `Dien_thoai`, `Email`) VALUES
('KH001', 'Nguyễn Thị Bích Thủy', 1, '120 Nguyễn Đình Chiểu, Quận 3, TP.HCM', '0903123456', 'thuy.ntb@gmail.com'),
('KH002', 'Trần Văn Hưng', 0, '45 Lê Văn Sỹ, Phường 13, Quận Phú Nhuận, TP.HCM', '0918234567', 'hung.tv@yahoo.com'),
('KH003', 'Lê Hoàng Nam', 0, '88 Võ Văn Tần, Phường 6, Quận 3, TP.HCM', '0937345678', 'nam.lh@gmail.com'),
('KH004', 'Phạm Thị Mỹ Linh', 1, '15/2 Cách Mạng Tháng 8, Quận 10, TP.HCM', '0989456789', 'linh.ptm@hotmail.com'),
('KH005', 'Đặng Ngọc Cẩm Tú', 1, '67 Hai Bà Trưng, Phường Bến Nghé, Quận 1, TP.HCM', '0908567890', 'tu.dnc@gmail.com'),
('KH006', 'Vũ Đức Thịnh', 0, '204 Hoàng Hoa Thám, Quận Bình Thạnh, TP.HCM', '0913678901', 'thinh.vd@gmail.com'),
('KH007', 'Hoàng Minh Châu', 1, '33 Nguyễn Thị Minh Khai, Quận 1, TP.HCM', '0945789012', 'chau.hm@gmail.com'),
('KH008', 'Đỗ Quang Dũng', 0, '51 Điện Biên Phủ, Phường Đa Kao, Quận 1, TP.HCM', '0978890123', 'dung.dq@gmail.com');

-- -----------------------------------------------------------------------------
-- 13. BẢNG HÓA ĐƠN & CHI TIẾT HÓA ĐƠN
-- -----------------------------------------------------------------------------
TRUNCATE TABLE `hoa_don`;
INSERT INTO `hoa_don` (`So_hoa_don`, `Ngay_HD`, `Ma_khach_hang`, `Tri_gia`) VALUES
('HD001', '2026-10-10', 'KH001', 570000.00),
('HD002', '2026-10-12', 'KH002', 890000.00),
('HD003', '2026-10-15', 'KH003', 730000.00),
('HD004', '2026-10-18', 'KH004', 1235000.00),
('HD005', '2026-10-20', 'KH005', 380000.00);

TRUNCATE TABLE `ct_hoadon`;
INSERT INTO `ct_hoadon` (`So_hoa_don`, `Ma_sua`, `So_luong`, `Don_gia`) VALUES
('HD001', 'SUA001', 2, 285000.00),
('HD002', 'SUA018', 1, 890000.00),
('HD003', 'SUA009', 2, 365000.00),
('HD004', 'SUA018', 1, 890000.00),
('HD004', 'SUA011', 1, 330000.00),
('HD004', 'SUA004', 1, 15000.00),
('HD005', 'SUA006', 10, 26000.00),
('HD005', 'SUA008', 4, 30000.00);

SET FOREIGN_KEY_CHECKS = 1;
