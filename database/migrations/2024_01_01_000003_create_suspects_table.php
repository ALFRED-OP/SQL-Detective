<?php

namespace Database\Migrations;

use PDO;

class CreateSuspectsTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS suspects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                case_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                age INT UNSIGNED,
                occupation VARCHAR(100),
                description TEXT,
                alibi TEXT,
                risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'low',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
                INDEX idx_case_id (case_id),
                INDEX idx_risk_level (risk_level)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS suspects");
    }
}