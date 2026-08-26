-- CASE #002: The Digital Trail
-- Database: digitalforensics
-- Tables: devices, users, activities, files, network_logs, emails, logs, timestamps

CREATE DATABASE IF NOT EXISTS `digitalforensics` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `digitalforensics`;

CREATE TABLE `devices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `device_name` VARCHAR(100) NOT NULL,
    `device_type` ENUM('server', 'workstation', 'laptop', 'mobile', 'network_device', 'iot') NOT NULL,
    `ip_address` VARCHAR(45),
    `mac_address` VARCHAR(17),
    `os` VARCHAR(50),
    `location` VARCHAR(100),
    `owner_id` INT,
    `status` ENUM('active', 'inactive', 'compromised', 'quarantined') DEFAULT 'active',
    `first_seen` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `last_seen` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `department` VARCHAR(50),
    `role` ENUM('admin', 'user', 'guest', 'service_account') DEFAULT 'user',
    `status` ENUM('active', 'inactive', 'locked', 'deleted') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activities` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `device_id` INT,
    `action_type` ENUM('login', 'logout', 'file_access', 'email_send', 'email_receive', 'network_connect', 'process_start', 'process_end', 'database_query', 'config_change', 'privilege_escalation') NOT NULL,
    `description` TEXT,
    `timestamp` DATETIME NOT NULL,
    `ip_address` VARCHAR(45),
    `success` BOOLEAN DEFAULT TRUE,
    `details` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`),
    INDEX `idx_user_time` (`user_id`, `timestamp`),
    INDEX `idx_device_time` (`device_id`, `timestamp`),
    INDEX `idx_action` (`action_type`),
    INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `file_path` VARCHAR(500) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50),
    `file_size` BIGINT DEFAULT 0,
    `owner_id` INT,
    `device_id` INT,
    `created_at` DATETIME NOT NULL,
    `modified_at` DATETIME NOT NULL,
    `accessed_at` DATETIME,
    `is_deleted` BOOLEAN DEFAULT FALSE,
    `deleted_at` DATETIME,
    `hash_md5` VARCHAR(32),
    `hash_sha256` VARCHAR(64),
    `permissions` VARCHAR(10),
    `content_preview` TEXT,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`),
    INDEX `idx_owner` (`owner_id`),
    INDEX `idx_device` (`device_id`),
    INDEX `idx_type` (`file_type`),
    INDEX `idx_name` (`file_name`),
    INDEX `idx_path` (`file_path`(255)),
    FULLTEXT INDEX `idx_content` (`content_preview`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `network_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `source_ip` VARCHAR(45) NOT NULL,
    `destination_ip` VARCHAR(45) NOT NULL,
    `source_port` INT,
    `destination_port` INT,
    `protocol` ENUM('tcp', 'udp', 'icmp', 'http', 'https', 'ssh', 'ftp', 'dns', 'smtp', 'pop3') NOT NULL,
    `timestamp` DATETIME NOT NULL,
    `bytes_sent` BIGINT DEFAULT 0,
    `bytes_received` BIGINT DEFAULT 0,
    `duration` INT DEFAULT 0,
    `status` ENUM('allowed', 'blocked', 'suspicious', 'failed') DEFAULT 'allowed',
    `device_id` INT,
    `user_id` INT,
    `details` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    INDEX `idx_source` (`source_ip`),
    INDEX `idx_dest` (`destination_ip`),
    INDEX `idx_time` (`timestamp`),
    INDEX `idx_protocol` (`protocol`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emails` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `message_id` VARCHAR(255) NOT NULL UNIQUE,
    `sender_id` INT,
    `recipient_ids` JSON,
    `subject` VARCHAR(500) NOT NULL,
    `body` TEXT,
    `attachments` JSON,
    `sent_at` DATETIME NOT NULL,
    `received_at` DATETIME,
    `is_read` BOOLEAN DEFAULT FALSE,
    `is_deleted` BOOLEAN DEFAULT FALSE,
    `folder` ENUM('inbox', 'sent', 'drafts', 'trash', 'spam', 'archived') DEFAULT 'inbox',
    `importance` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `has_attachments` BOOLEAN DEFAULT FALSE,
    `size_bytes` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`),
    INDEX `idx_sender` (`sender_id`),
    INDEX `idx_sent` (`sent_at`),
    INDEX `idx_subject` (`subject`),
    FULLTEXT INDEX `idx_body` (`body`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `log_type` ENUM('system', 'application', 'security', 'audit', 'error') NOT NULL,
    `level` ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL,
    `source` VARCHAR(100) NOT NULL,
    `message` TEXT NOT NULL,
    `user_id` INT,
    `device_id` INT,
    `ip_address` VARCHAR(45),
    `timestamp` DATETIME NOT NULL,
    `details` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`),
    INDEX `idx_type_level` (`log_type`, `level`),
    INDEX `idx_time` (`timestamp`),
    INDEX `idx_source` (`source`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `timestamps` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(100) NOT NULL,
    `event_data` JSON,
    `recorded_at` DATETIME(3) NOT NULL,
    `source` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event` (`event_type`),
    INDEX `idx_time` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;