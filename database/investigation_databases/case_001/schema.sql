-- CASE #001: The Missing Million
-- Database: corporatefinance
-- Tables: employees, departments, transactions, bank_accounts, login_logs, access_logs, devices, locations

CREATE DATABASE IF NOT EXISTS `corporatefinance` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `corporatefinance`;

-- Departments table
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `description` TEXT,
    `budget` DECIMAL(15,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employees table
CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_code` VARCHAR(20) NOT NULL UNIQUE,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20),
    `department_id` INT NOT NULL,
    `position` VARCHAR(100) NOT NULL,
    `hire_date` DATE NOT NULL,
    `salary` DECIMAL(12,2) NOT NULL,
    `manager_id` INT NULL,
    `status` ENUM('active', 'inactive', 'terminated', 'on_leave') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`),
    FOREIGN KEY (`manager_id`) REFERENCES `employees`(`id`),
    INDEX `idx_department` (`department_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_manager` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locations table
CREATE TABLE `locations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `address` TEXT,
    `city` VARCHAR(50),
    `state` VARCHAR(50),
    `country` VARCHAR(50) DEFAULT 'India',
    `ip_range_start` VARCHAR(45),
    `ip_range_end` VARCHAR(45),
    `timezone` VARCHAR(50) DEFAULT 'Asia/Kolkata',
    `is_office` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank accounts table
CREATE TABLE `bank_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `account_number` VARCHAR(30) NOT NULL UNIQUE,
    `account_type` ENUM('checking', 'savings', 'investment', 'operating', 'payroll') NOT NULL,
    `account_name` VARCHAR(100) NOT NULL,
    `owner_type` ENUM('company', 'employee', 'vendor', 'external') NOT NULL,
    `owner_id` INT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `branch` VARCHAR(100),
    `currency` CHAR(3) DEFAULT 'INR',
    `balance` DECIMAL(15,2) DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_owner` (`owner_type`, `owner_id`),
    INDEX `idx_type` (`account_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` VARCHAR(30) NOT NULL UNIQUE,
    `transaction_date` DATETIME NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` CHAR(3) DEFAULT 'INR',
    `transaction_type` ENUM('transfer', 'deposit', 'withdrawal', 'payment', 'refund', 'fee', 'adjustment') NOT NULL,
    `status` ENUM('pending', 'completed', 'failed', 'reversed', 'flagged') DEFAULT 'completed',
    `from_account_id` INT NOT NULL,
    `to_account_id` INT NOT NULL,
    `initiated_by` INT NULL,
    `approved_by` INT NULL,
    `description` TEXT,
    `reference` VARCHAR(100),
    `ip_address` VARCHAR(45),
    `device_id` VARCHAR(50),
    `location_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`from_account_id`) REFERENCES `bank_accounts`(`id`),
    FOREIGN KEY (`to_account_id`) REFERENCES `bank_accounts`(`id`),
    FOREIGN KEY (`initiated_by`) REFERENCES `employees`(`id`),
    FOREIGN KEY (`approved_by`) REFERENCES `employees`(`id`),
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`),
    INDEX `idx_date` (`transaction_date`),
    INDEX `idx_amount` (`amount`),
    INDEX `idx_status` (`status`),
    INDEX `idx_initiated_by` (`initiated_by`),
    INDEX `idx_accounts` (`from_account_id`, `to_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login logs table
CREATE TABLE `login_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `login_time` DATETIME NOT NULL,
    `logout_time` DATETIME NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `device_id` VARCHAR(50),
    `location_id` INT NULL,
    `status` ENUM('success', 'failed', 'locked_out', 'expired') DEFAULT 'success',
    `failure_reason` VARCHAR(100),
    `session_id` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`),
    INDEX `idx_employee_time` (`employee_id`, `login_time`),
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_status` (`status`),
    INDEX `idx_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Access logs table
CREATE TABLE `access_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `resource_type` ENUM('system', 'database', 'file', 'application', 'api', 'server') NOT NULL,
    `resource_name` VARCHAR(100) NOT NULL,
    `action` ENUM('read', 'write', 'delete', 'execute', 'export', 'import', 'admin') NOT NULL,
    `access_time` DATETIME NOT NULL,
    `ip_address` VARCHAR(45),
    `device_id` VARCHAR(50),
    `location_id` INT NULL,
    `success` BOOLEAN DEFAULT TRUE,
    `error_message` TEXT,
    `details` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`),
    INDEX `idx_employee_time` (`employee_id`, `access_time`),
    INDEX `idx_resource` (`resource_type`, `resource_name`),
    INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Devices table
CREATE TABLE `devices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `device_id` VARCHAR(50) NOT NULL UNIQUE,
    `employee_id` INT NULL,
    `device_type` ENUM('laptop', 'desktop', 'mobile', 'tablet', 'server', 'unknown') NOT NULL,
    `os` VARCHAR(50),
    `browser` VARCHAR(50),
    `mac_address` VARCHAR(17),
    `is_company_issued` BOOLEAN DEFAULT FALSE,
    `first_seen` DATETIME NOT NULL,
    `last_seen` DATETIME NOT NULL,
    `status` ENUM('active', 'inactive', 'lost', 'stolen', 'retired') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`),
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs for transactions
CREATE TABLE `transaction_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT NOT NULL,
    `action` ENUM('created', 'modified', 'approved', 'reversed', 'flagged', 'investigated') NOT NULL,
    `performed_by` INT NOT NULL,
    `old_values` JSON,
    `new_values` JSON,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`),
    FOREIGN KEY (`performed_by`) REFERENCES `employees`(`id`),
    INDEX `idx_transaction` (`transaction_id`),
    INDEX `idx_performed_by` (`performed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;