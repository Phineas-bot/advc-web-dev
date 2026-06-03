CREATE DATABASE IF NOT EXISTS cimen_hrms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cimen_hrms;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS department_records;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  department_name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_departments_department_name (department_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(180) NOT NULL,
  address VARCHAR(255) NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(30) NULL,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'admin', 'employee') NOT NULL DEFAULT 'employee',
  department_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_username (username),
  KEY idx_users_role (role),
  KEY idx_users_department_id (department_id),
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE department_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  department_id INT UNSIGNED NOT NULL,
  field1 VARCHAR(255) NOT NULL,
  field2 VARCHAR(255) NOT NULL,
  field3 VARCHAR(255) NOT NULL,
  field4 VARCHAR(255) NOT NULL,
  field5 VARCHAR(255) NOT NULL,
  field6 VARCHAR(255) NOT NULL,
  field7 VARCHAR(255) NOT NULL,
  field8 VARCHAR(255) NOT NULL,
  field9 VARCHAR(255) NOT NULL,
  field10 VARCHAR(255) NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_department_records_department_id (department_id),
  KEY idx_department_records_created_by (created_by),
  CONSTRAINT fk_department_records_department FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_department_records_created_by FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE remember_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  selector VARCHAR(24) NOT NULL,
  validator_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_remember_tokens_selector (selector),
  KEY idx_remember_tokens_user_id (user_id),
  CONSTRAINT fk_remember_tokens_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_password_resets_user_id (user_id),
  KEY idx_password_resets_token_hash (token_hash),
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO departments (department_name, description) VALUES
('Human Resources', 'Hiring, employee relations, and internal people operations.'),
('Finance', 'Budgeting, payroll, and financial controls.'),
('Engineering', 'Technical delivery, maintenance, and project execution.'),
('Information Technology', 'Infrastructure, systems support, and business applications.'),
('Procurement', 'Vendor management and purchasing operations.'),
('Marketing', 'Brand visibility, campaigns, and market communications.'),
('Operations', 'Daily site and business operations management.'),
('Logistics', 'Fleet, delivery, and warehouse coordination.'),
('Legal', 'Compliance, contracts, and governance support.'),
('Administration', 'Administrative coordination and executive support.');

INSERT INTO users (full_name, address, email, phone, username, password, role, department_id) VALUES
('System Super Admin', 'CIMEN Head Office', 'superadmin@cimenlimited.com', '+233 555 000 001', 'superadmin', '$2b$12$fGjrqJiqFWyXwAACAPr9T.WRYxt4fo6CoMS1zEknunLs.OFPipkrq', 'super_admin', 10);

DELIMITER $$

CREATE TRIGGER trg_prevent_super_admin_delete
BEFORE DELETE ON users
FOR EACH ROW
BEGIN
  IF OLD.role = 'super_admin' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Super admin cannot be deleted';
  END IF;
END$$

DELIMITER ;
