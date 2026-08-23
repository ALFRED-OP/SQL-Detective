<?php

namespace Database\Migrations;

use PDO;

class CreateQueryHistoryTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS query_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                case_id INT NOT NULL,
                query TEXT NOT NULL,
                status ENUM('success', 'error', 'timeout', 'blocked') NOT NULL,
                execution_time_ms INT UNSIGNED,
                rows_returned INT UNSIGNED,
                error_message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_case_id (case_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS query_history");
    }
}