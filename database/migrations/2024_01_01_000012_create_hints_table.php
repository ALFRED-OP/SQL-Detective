<?php

namespace Database\Migrations;

use PDO;

class CreateHintsTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS hints (
                id INT AUTO_INCREMENT PRIMARY KEY,
                challenge_id INT NOT NULL,
                hint_text TEXT NOT NULL,
                hint_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
                xp_penalty INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
                INDEX idx_challenge_id (challenge_id),
                UNIQUE KEY unique_challenge_level (challenge_id, hint_level)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS hints");
    }
}