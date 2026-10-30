-- Import vào database mới. Không xóa dữ liệu đã có.
CREATE DATABASE IF NOT EXISTS solaris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE solaris;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL, full_name VARCHAR(100) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE, phone VARCHAR(20) NOT NULL,
 role ENUM('guest','customer','technician','admin') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS categories (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
 slug VARCHAR(100) NOT NULL UNIQUE, description TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS products (
 id INT AUTO_INCREMENT PRIMARY KEY, category_id INT NOT NULL,
 product_code VARCHAR(50) NOT NULL UNIQUE, name VARCHAR(255) NOT NULL,
 capacity_watts INT NULL, price DECIMAL(15,2) NOT NULL, warranty_years INT DEFAULT 5,
 technical_specs JSON NULL, image_url VARCHAR(255) NULL,
 stock_quantity INT DEFAULT 0, is_active TINYINT(1) DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS solar_quotes (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL,
 customer_name VARCHAR(100) NOT NULL, customer_phone VARCHAR(20) NOT NULL,
 customer_email VARCHAR(100) NULL, address VARCHAR(255) NOT NULL,
 monthly_electric_bill DECIMAL(15,2) NOT NULL, roof_area_sqm DECIMAL(8,2) NOT NULL,
 estimated_system_kwp DECIMAL(6,2) NOT NULL, panel_count INT NOT NULL,
 estimated_total_cost DECIMAL(15,2) NOT NULL, payback_years DECIMAL(4,1) NOT NULL,
 notes TEXT NULL, status ENUM('pending','contacted','survey_scheduled','approved','rejected') DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS projects (
 id INT AUTO_INCREMENT PRIMARY KEY, contract_code VARCHAR(20) NOT NULL UNIQUE,
 quote_id INT NULL UNIQUE, user_id INT NOT NULL,
 technician_id INT NULL COMMENT 'Trưởng nhóm; quyền truy cập dựa trên project_assignments',
 project_name VARCHAR(255) NOT NULL, capacity_kwp DECIMAL(6,2) NOT NULL,
 total_amount DECIMAL(15,2) NOT NULL, installation_address VARCHAR(255) NOT NULL,
 status ENUM('new','in_progress','completed','cancelled') NOT NULL DEFAULT 'new',
 current_stage ENUM('survey','design','procurement','installation','grid_connection','handover','cancelled') NOT NULL DEFAULT 'survey',
 notes TEXT NULL, start_date DATE NULL, completion_date DATE NULL,
 planned_end_date DATE NULL, completed_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY (quote_id) REFERENCES solar_quotes(id), FOREIGN KEY (user_id) REFERENCES users(id),
 FOREIGN KEY (technician_id) REFERENCES users(id), INDEX(status, current_stage)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS installation_items (
 id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL, product_id INT NOT NULL,
 quantity INT NOT NULL DEFAULT 1, unit_price DECIMAL(15,2) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
 FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS project_stages (
 id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL,
 stage ENUM('survey','design','procurement','installation','grid_connection','handover') NOT NULL,
 position TINYINT NOT NULL, status ENUM('locked','in_progress','pending_approval','revision_required','approved') NOT NULL DEFAULT 'locked',
 planned_start DATE NULL, planned_end DATE NULL, started_at DATETIME NULL,
 submitted_at DATETIME NULL, submitted_by INT NULL, reviewed_at DATETIME NULL,
 reviewed_by INT NULL, completed_at DATETIME NULL, review_note TEXT NULL,
 UNIQUE(project_id,stage), UNIQUE(project_id,position),
 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
 FOREIGN KEY (submitted_by) REFERENCES users(id), FOREIGN KEY (reviewed_by) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS project_assignments (
 id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL, technician_id INT NOT NULL,
 planned_start DATE NOT NULL, planned_end DATE NOT NULL,
 hours_per_day DECIMAL(4,2) NOT NULL DEFAULT 4,
 assigned_by INT NOT NULL, assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 ended_at DATETIME NULL,
 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
 FOREIGN KEY (technician_id) REFERENCES users(id), FOREIGN KEY (assigned_by) REFERENCES users(id),
 INDEX(technician_id,ended_at,planned_start,planned_end), INDEX(project_id,ended_at),
 CHECK(planned_end >= planned_start), CHECK(hours_per_day > 0 AND hours_per_day <= 8)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS project_requests (
 id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL, stage_id INT NOT NULL,
 request_type ENUM('postpone','replacement') NOT NULL,
 reason TEXT NOT NULL, proposed_start DATE NULL, proposed_end DATE NULL,
 old_technician_id INT NULL, proposed_technician_id INT NULL,
 status ENUM('pending','approved','rejected','superseded') NOT NULL DEFAULT 'pending',
 requested_by INT NOT NULL, requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 reviewed_by INT NULL, reviewed_at DATETIME NULL, review_note TEXT NULL,
 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
 FOREIGN KEY (stage_id) REFERENCES project_stages(id),
 FOREIGN KEY (old_technician_id) REFERENCES users(id), FOREIGN KEY (proposed_technician_id) REFERENCES users(id),
 FOREIGN KEY (requested_by) REFERENCES users(id), FOREIGN KEY (reviewed_by) REFERENCES users(id),
 INDEX(project_id,status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS project_stage_logs (
 id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL,
 stage ENUM('survey','design','procurement','installation','grid_connection','handover') NOT NULL,
 started_at DATETIME NOT NULL, completed_at DATETIME NULL,
 actor_id INT NULL, action VARCHAR(50) NOT NULL, from_status VARCHAR(30) NULL,
 to_status VARCHAR(30) NULL, note TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
 FOREIGN KEY (actor_id) REFERENCES users(id), INDEX(project_id,id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS project_documents (
 id INT AUTO_INCREMENT PRIMARY KEY, project_id INT NOT NULL, stage_id INT NOT NULL,
 document_type ENUM('survey_photo','technical_drawing','evn_permit','procurement_invoice','handover_report','installation_photo','testing_photo','stage_photo') NOT NULL,
 file_name VARCHAR(255) NOT NULL, file_hash CHAR(64) NOT NULL, file_path VARCHAR(255) NOT NULL,
 file_size_kb INT NOT NULL, file_type VARCHAR(50) NOT NULL, uploaded_by INT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
 FOREIGN KEY (stage_id) REFERENCES project_stages(id), FOREIGN KEY (uploaded_by) REFERENCES users(id),
 UNIQUE(stage_id,file_hash), INDEX(project_id,stage_id)
) ENGINE=InnoDB;
