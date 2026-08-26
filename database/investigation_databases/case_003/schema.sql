-- CASE #003: Employee Portal Breach
-- Database: employeeportal
-- Tables: employees, departments, access_logs, login_records, permission_changes, audit_trail, system_events, ip_addresses

CREATE DATABASE IF NOT EXISTS `employeeportal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `employeeportal`;

CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(10) NOT NULL UNIQUE,
    `head_id` INT NULL,
    `budget` DECIMAL(12,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `emp_code` VARCHAR(10) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `department_id` INT NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    `level` ENUM('junior', 'mid', 'senior', 'lead', 'manager', 'director', 'vp', 'c_suite') NOT NULL,
    `hire_date` DATE NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended', 'terminated') DEFAULT 'active',
    `last_login` DATETIME,
    `failed_logins` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `access_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `resource` VARCHAR(200) NOT NULL,
    `resource_type` ENUM('page', 'api', 'file', 'database', 'system', 'admin') NOT NULL,
    `action` ENUM('view', 'create', 'update', 'delete', 'export', 'import', 'execute') NOT NULL,
    `access_time` DATETIME NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `session_id` VARCHAR(100),
    `success` BOOLEAN DEFAULT TRUE,
    `response_code` INT DEFAULT 200,
    `details` JSON,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    INDEX `idx_employee_time` (`employee_id`, `access_time`),
    INDEX `idx_resource` (`resource_type`, `resource`),
    INDEX `idx_action` (`action`),
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_time` (`access_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_records` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `login_time` DATETIME NOT NULL,
    `logout_time` DATETIME NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `location` VARCHAR(100),
    `device_info` VARCHAR(200),
    `session_token` VARCHAR(255),
    `mfa_used` BOOLEAN DEFAULT FALSE,
    `status` ENUM('success', 'failed', 'locked', 'expired', 'suspicious') DEFAULT 'success',
    `failure_reason` VARCHAR(100),
    `geo_location` VARCHAR(100),
    `vpn_used` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    INDEX `idx_employee_time` (`employee_id`, `login_time`),
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_status` (`status`),
    INDEX `idx_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permission_changes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `changed_by` INT NOT NULL,
    `permission_type` ENUM('role', 'access_level', 'data_access', 'admin', 'api_key', 'vpn') NOT NULL,
    `old_value` VARCHAR(100),
    `new_value` VARCHAR(100),
    `change_time` DATETIME NOT NULL,
    `reason` TEXT,
    `approved_by` INT NULL,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    FOREIGN KEY (`changed_by`) REFERENCES `employees`(`id`),
    FOREIGN KEY (`approved_by`) REFERENCES `employees`(`id`),
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_changed_by` (`changed_by`),
    INDEX `idx_time` (`change_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_trail` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `table_name` VARCHAR(100) NOT NULL,
    `record_id` INT NOT NULL,
    `action` ENUM('insert', 'update', 'delete') NOT NULL,
    `old_values` JSON,
    `new_values` JSON,
    `performed_by` INT NOT NULL,
    `performed_at` DATETIME NOT NULL,
    `ip_address` VARCHAR(45),
    `session_id` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`performed_by`) REFERENCES `employees`(`id`),
    INDEX `idx_table_record` (`table_name`, `record_id`),
    INDEX `idx_performed_by` (`performed_by`),
    INDEX `idx_time` (`performed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_events` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_type` ENUM('login', 'logout', 'password_change', 'profile_update', 'data_export', 'admin_action', 'security_alert', 'api_call', 'file_upload', 'file_download') NOT NULL,
    `employee_id` INT,
    `event_time` DATETIME NOT NULL,
    `ip_address` VARCHAR(45),
    `description` TEXT,
    `severity` ENUM('info', 'low', 'medium', 'high', 'critical') DEFAULT 'info',
    `details` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    INDEX `idx_type` (`event_type`),
    INDEX `idx_employee_time` (`employee_id`, `event_time`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_time` (`event_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ip_addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL UNIQUE,
    `ip_type` ENUM('ipv4', 'ipv6') NOT NULL,
    `location` VARCHAR(100),
    `country` VARCHAR(50),
    `isp` VARCHAR(100),
    `is_vpn` BOOLEAN DEFAULT FALSE,
    `is_tor` BOOLEAN DEFAULT FALSE,
    `is_blacklisted` BOOLEAN DEFAULT FALSE,
    `threat_score` INT DEFAULT 0,
    `first_seen` DATETIME,
    `last_seen` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_threat` (`threat_score`),
    INDEX `idx_blacklisted` (`is_blacklisted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;