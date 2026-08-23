<?php

namespace Database\Migrations;

use PDO;

class CreateChallengeAttemptsTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS challenge_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                challenge_id INT NOT NULL,
                submitted_query TEXT NOT NULL,
                result_status ENUM('success', 'error', 'wrong_result', 'syntax_error', 'timeout') NOT NULL,
                execution_time_ms INT UNSIGNED,
                rows_returned INT UNSIGNED,
                error_message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_challenge_id (challenge_id),
                INDEX idx_created_at (created_at),
                INDEX idx_user_challenge (user_id, challenge_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS challenge_attempts");
    }
}