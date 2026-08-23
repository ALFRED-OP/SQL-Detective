<?php

namespace Database\Migrations;

use PDO;

class CreateCasesTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS cases (
                id INT AUTO_INCREMENT PRIMARY KEY,
                case_code VARCHAR(20) NOT NULL UNIQUE,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                difficulty ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
                category VARCHAR(100) NOT NULL,
                briefing TEXT,
                objective TEXT,
                expected_result_description TEXT,
                xp_reward INT UNSIGNED NOT NULL DEFAULT 100,
                estimated_minutes INT UNSIGNED NOT NULL DEFAULT 30,
                status ENUM('active', 'inactive', 'archived') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_case_code (case_code),
                INDEX idx_difficulty (difficulty),
                INDEX idx_category (category),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS cases");
    }
}