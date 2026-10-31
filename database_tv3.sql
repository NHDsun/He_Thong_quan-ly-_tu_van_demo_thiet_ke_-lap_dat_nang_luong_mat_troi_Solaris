-- =========================================================================
-- SOLARIS ENERGY SYSTEM - FULL DATABASE SCHEMA & SEED DATA (14 TABLES)
-- =========================================================================
-- Dự án: SOLARIS - Quản Lý, Tư Vấn & Demo Thiết Kế Năng Lượng Mặt Trời
-- Phụ trách CSDL: Thành viên 3 (Fullstack / Data Hooks)
-- Phiên bản: v2.1 (MySQL 8.x / MariaDB compatible)
-- =========================================================================

CREATE DATABASE IF NOT EXISTS `solaris_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `solaris_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------------------
-- 1. BẢNG USERS (Xác thực và Phân quyền RBAC)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `role` ENUM('guest', 'customer', 'technician', 'admin') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 2. BẢNG CATEGORIES (Danh mục thiết bị Solar)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 3. BẢNG PRODUCTS (Sản phẩm & Thiết bị Pin / Inverter)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `product_code` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `capacity_watts` INT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `warranty_years` INT DEFAULT 5,
    `technical_specs` JSON NULL,
    `image_url` VARCHAR(255) NULL,
    `stock_quantity` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 4. BẢNG SOLAR_QUOTES (Dự Toán Báo Giá Năng Lượng Mặt Trời - THÀNH VIÊN 3)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `solar_quotes`;
CREATE TABLE `solar_quotes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(100) NULL,
    `address` VARCHAR(255) NOT NULL,
    `monthly_electric_bill` DECIMAL(15,2) NOT NULL,
    `roof_area_sqm` DECIMAL(8,2) NOT NULL,
    `estimated_system_kwp` DECIMAL(6,2) NOT NULL,
    `panel_count` INT NOT NULL,
    `estimated_total_cost` DECIMAL(15,2) NOT NULL,
    `payback_years` DECIMAL(4,1) NOT NULL,
    `notes` TEXT NULL,
    `status` ENUM('pending', 'contacted', 'survey_scheduled', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_quotes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 5. BẢNG PROJECTS (Dự án & Hợp đồng Lắp đặt - Tra cứu SOLAR-YYYY-XXX)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contract_code` VARCHAR(20) UNIQUE NOT NULL,
    `quote_id` INT NULL,
    `user_id` INT NOT NULL,
    `technician_id` INT NULL,
    `project_name` VARCHAR(255) NOT NULL,
    `capacity_kwp` DECIMAL(6,2) NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `installation_address` VARCHAR(255) NOT NULL,
    `current_stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover', 'cancelled') DEFAULT 'survey',
    `notes` TEXT NULL,
    `start_date` DATE NULL,
    `completion_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_projects_quote` FOREIGN KEY (`quote_id`) REFERENCES `solar_quotes` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_projects_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_projects_tech` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 6. BẢNG PROJECT_STAGE_LOGS (Lịch sử 6 giai đoạn thi công Timeline)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `project_stage_logs`;
CREATE TABLE `project_stage_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `stage` ENUM('survey', 'design', 'procurement', 'installation', 'grid_connection', 'handover') NOT NULL,
    `started_at` DATETIME NOT NULL,
    `completed_at` DATETIME NULL,
    `note` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_logs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 7. BẢNG PROJECT_DOCUMENTS (Tài liệu kỹ thuật & Ảnh khảo sát mã hóa Hash)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `project_documents`;
CREATE TABLE `project_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `document_type` ENUM('survey_photo', 'technical_drawing', 'evn_permit', 'procurement_invoice', 'handover_report') NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_hash` VARCHAR(64) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size_kb` INT NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_docs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_docs_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------------------
-- 8. BẢNG INSTALLATION_ITEMS (Bóc tách vật tư xuất kho công trình)
-- -------------------------------------------------------------------------
DROP TABLE IF EXISTS `installation_items`;
CREATE TABLE `installation_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(15,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_items_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================================================================
-- DỮ LIỆU MẪU (SEED DATA)
-- =========================================================================

-- Dữ liệu Users
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Solaris System Administrator', 'admin@solaris.vn', '0909888888', 'admin'),
(2, 'tech_dat', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Tấn Đạt (Kỹ sư trưởng)', 'dat.nguyen@solaris.vn', '0912345678', 'technician'),
(3, 'khachhang1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Văn Hoàng (Villa Thảo Điền)', 'hoang.tran@gmail.com', '0988123456', 'customer');

-- Dữ liệu Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Tấm Pin Năng Lượng Mặt Trời', 'tam-pin-solar', 'Các dòng pin Mono Perc Half-cell Tier-1 hiệu suất cao'),
(2, 'Biến Tần Inverter Hòa Lưới & Hybrid', 'bien-tan-inverter', 'Bộ biến đổi điện năng IP65 từ 3kW đến 100kW'),
(3, 'Pin Lưu Trữ Lithium ESS', 'pin-luu-tru-ess', 'Hệ thống lưu trữ điện mặt trời ban đêm và chống mất điện'),
(4, 'Phụ Kiện Cơ Điện & Khung Giá Đỡ', 'phu-kien-khung-gia', 'Ray nhôm Anode, kẹp sóng tôn, cáp DC chống tia cực tím');

-- Dữ liệu Products
INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `capacity_watts`, `price`, `warranty_years`, `stock_quantity`) VALUES
(1, 1, 'PANEL-LONGI-550', 'Tấm Pin Longi Solar Hi-MO 5 550Wp Mono Half-cell', 550, 2450000, 25, 250),
(2, 1, 'PANEL-JINKO-550', 'Tấm Pin Jinko Tiger Pro 550Wp Hai Mặt Kính', 550, 2580000, 25, 180),
(3, 2, 'INV-GROWATT-5K', 'Biến tần Inverter Hòa Lưới Growatt MIN 5000TL-X (1 Pha)', 5000, 14200000, 5, 45),
(4, 2, 'INV-HUAWEI-10K', 'Biến tần Inverter Huawei SUN2000-10KTL-M1 (3 Pha)', 10000, 29500000, 5, 30),
(5, 3, 'ESS-DEYE-5KWH', 'Pin Lưu Trữ Lithium LiFePO4 Deye 5.12kWh 51.2V 100Ah', 5120, 36000000, 10, 20);

-- Dữ liệu Solar Quotes (Báo giá Thành viên 3)
INSERT INTO `solar_quotes` (`id`, `customer_name`, `customer_phone`, `customer_email`, `address`, `monthly_electric_bill`, `roof_area_sqm`, `estimated_system_kwp`, `panel_count`, `estimated_total_cost`, `payback_years`, `status`) VALUES
(1, 'Nguyễn Văn An', '0912345678', 'an.nguyen@gmail.com', '124 Nguyễn Thị Minh Khai, Q.3, TP.HCM', 3000000, 35.0, 7.7, 14, 107800000, 3.3, 'approved'),
(2, 'Công Ty May Mặc Tân Bình', '02838445566', 'contact@tanbinhgarment.vn', 'KCN Tân Bình, Tây Thạnh, Tân Phú', 18000000, 210.0, 46.2, 84, 600600000, 3.1, 'survey_scheduled'),
(3, 'Lê Thị Thu Thảo', '0908765432', 'thuthao.le@gmail.com', '88 Trần Phú, Vĩnh Nguyên, Nha Trang', 4500000, 50.0, 11.0, 20, 154000000, 3.2, 'contacted'),
(4, 'Phạm Quốc Hùng', '0933557799', 'quochung.pham@hotmail.com', 'Villa 15 Khu Đô Thị An Viên, Nha Trang', 7000000, 85.0, 18.15, 33, 399300000, 5.3, 'pending');
