<?php

namespace Database\Migrations;

use PDO;

class CreateAchievementsTable
{
    public function __construct(private PDO $db) {}

    public function up(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS achievements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT NOT NULL,
                icon VARCHAR(100) NOT NULL,
                requirement_type ENUM('cases_completed', 'challenges_solved', 'perfect_cases', 'no_hints', 'speed', 'streak', 'xp_milestone', 'level_milestone', 'explorer', 'first_case', 'difficulty_complete', 'total_queries') NOT NULL,
                requirement_value INT UNSIGNED NOT NULL DEFAULT 1,
                xp_reward INT UNSIGNED NOT NULL DEFAULT 0,
                is_secret BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_requirement (requirement_type, requirement_value)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS achievements");
    }
}