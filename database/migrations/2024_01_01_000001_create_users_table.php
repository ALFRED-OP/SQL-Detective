<?php

namespace Database\Migrations;

use PDO;

class CreateUsersTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(100) NOT NULL,
                xp INT UNSIGNED NOT NULL DEFAULT 0,
                level INT UNSIGNED NOT NULL DEFAULT 1,
                detective_rank VARCHAR(50) NOT NULL DEFAULT 'SQL Rookie',
                role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
                email_verified_at TIMESTAMP NULL,
                last_login_at TIMESTAMP NULL,
                status ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_username (username),
                INDEX idx_xp (xp DESC),
                INDEX idx_level (level DESC),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS users");
    }
}