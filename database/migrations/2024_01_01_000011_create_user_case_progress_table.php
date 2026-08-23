<?php

namespace Database\Migrations;

use PDO;

class CreateUserCaseProgressTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS user_case_progress (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                case_id INT NOT NULL,
                current_challenge_id INT NULL,
                progress_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                completed BOOLEAN NOT NULL DEFAULT FALSE,
                completed_at TIMESTAMP NULL,
                xp_earned INT UNSIGNED NOT NULL DEFAULT 0,
                hints_used INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
                FOREIGN KEY (current_challenge_id) REFERENCES challenges(id) ON DELETE SET NULL,
                UNIQUE KEY unique_user_case (user_id, case_id),
                INDEX idx_user_id (user_id),
                INDEX idx_case_id (case_id),
                INDEX idx_completed (completed)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS user_case_progress");
    }
}