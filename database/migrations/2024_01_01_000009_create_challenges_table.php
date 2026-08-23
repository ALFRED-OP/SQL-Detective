<?php

namespace Database\Migrations;

use PDO;

class CreateChallengesTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS challenges (
                id INT AUTO_INCREMENT PRIMARY KEY,
                case_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                challenge_type ENUM('query', 'analysis', 'identification', 'correlation') NOT NULL DEFAULT 'query',
                difficulty ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
                expected_query_type VARCHAR(100),
                expected_result_hash VARCHAR(64),
                validation_rules JSON,
                xp_reward INT UNSIGNED NOT NULL DEFAULT 50,
                display_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
                INDEX idx_case_id (case_id),
                INDEX idx_display_order (case_id, display_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS challenges");
    }
}