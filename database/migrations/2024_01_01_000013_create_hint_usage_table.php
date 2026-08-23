<?php

namespace Database\Migrations;

use PDO;

class CreateHintUsageTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hint_usage (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                hint_id INT NOT NULL,
                used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (hint_id) REFERENCES hints(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_hint (user_id, hint_id),
                INDEX idx_user_id (user_id),
                INDEX idx_hint_id (hint_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS hint_usage");
    }
}