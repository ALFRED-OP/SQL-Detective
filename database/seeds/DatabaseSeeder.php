<?php

namespace Database\Seeds;

use PDO;

class DatabaseSeeder
{
    private PDO $db;

    public function __construct()
    {
        $config = require base_path('config/database.php');
        $conn = $config['connections'][$config['default']];

        $dsn = "{$conn['driver']}:host={$conn['host']};port={$conn['port']};dbname={$conn['database']};charset={$conn['charset']}";
        $this->db = new PDO($dsn, $conn['username'], $conn['password'], $conn['options']);
    }

    public function run(): void
    {
        $this->seedUsers();
        $this->seedCases();
        $this->seedAchievements();
        echo "Database seeding completed\n";
    }

    private function seedUsers(): void
    {
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@sqldetective.local';
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'SecurePass123!';
        $demoEmail = $_ENV['DEMO_EMAIL'] ?? 'demo@sqldetective.local';
        $demoPassword = $_ENV['DEMO_PASSWORD'] ?? 'DemoPass123!';

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        if ($stmt->fetchColumn() === 0) {
            $this->db->prepare("
                INSERT INTO users (username, email, password_hash, display_name, xp, level, detective_rank, role, email_verified_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ")->execute([
                'admin',
                $adminEmail,
                password_hash($adminPassword, PASSWORD_DEFAULT),
                'Administrator',
                0,
                1,
                'SQL Rookie',
                'admin',
            ]);
            echo "Admin user created\n";
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$demoEmail]);
        if ($stmt->fetchColumn() === 0) {
            $this->db->prepare("
                INSERT INTO users (username, email, password_hash, display_name, xp, level, detective_rank, role, email_verified_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
            ")->execute([
                'demo',
                $demoEmail,
                password_hash($demoPassword, PASSWORD_DEFAULT),
                'Demo Detective',
                0,
                1,
                'SQL Rookie',
                'user',
            ]);
            echo "Demo user created\n";
        }
    }

    private function seedCases(): void
    {
        $cases = [
            [
                'case_code' => 'CASE-001',
                'title' => 'The Missing Million',
                'description' => 'A company reports an unauthorized transfer of ₹10,00,000. Investigate the transaction logs to find the culprit.',
                'difficulty' => 'beginner',
                'category' => 'Financial Crime',
                'briefing' => 'On March 15th, 2024, at 14:32, an unauthorized wire transfer of ₹10,00,000 was initiated from the company\'s main operating account. The transfer was sent to an external account not recognized by the finance department. Your task is to analyze the transaction logs, employee access records, and login history to identify who initiated this transfer.',
                'objective' => 'Determine which employee initiated the unauthorized transaction and provide the evidence.',
                'expected_result_description' => 'The query should return the employee ID, name, and timestamp of the suspicious transaction.',
                'xp_reward' => 150,
                'estimated_minutes' => 20,
            ],
            [
                'case_code' => 'CASE-002',
                'title' => 'Data Leak Investigation',
                'description' => 'Sensitive customer data was found on a public forum. Trace the leak back to its source.',
                'difficulty' => 'intermediate',
                'category' => 'Data Leak',
                'briefing' => 'A security researcher discovered a dataset containing 50,000 customer records on a hacker forum. The data includes names, emails, phone numbers, and partial credit card information. The company\'s database shows no signs of external breach. Investigate internal access logs, export activities, and employee behavior to find the source.',
                'objective' => 'Identify the employee responsible for the data export and the method used.',
                'expected_result_description' => 'The query should return the employee details and export timestamp.',
                'xp_reward' => 250,
                'estimated_minutes' => 35,
            ],
            [
                'case_code' => 'CASE-003',
                'title' => 'The Phantom Employee',
                'description' => 'A ghost employee has been collecting payroll for months. Find the fake record in the HR database.',
                'difficulty' => 'beginner',
                'category' => 'Fraud Investigation',
                'briefing' => 'The payroll department noticed an anomaly: an employee who has never been seen in the office has been receiving monthly salary deposits for 8 months. The employee ID exists in the system but no one recognizes the name. Investigate the employees table, department assignments, and login records to uncover the truth.',
                'objective' => 'Find the ghost employee record and identify who created it.',
                'expected_result_description' => 'The query should return the ghost employee details and creation timestamp.',
                'xp_reward' => 150,
                'estimated_minutes' => 25,
            ],
        ];

        foreach ($cases as $case) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cases WHERE case_code = ?");
            $stmt->execute([$case['case_code']]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO cases (case_code, title, description, difficulty, category, briefing, objective, expected_result_description, xp_reward, estimated_minutes, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ")->execute(array_values($case));
                echo "Case {$case['case_code']} created\n";
            }
        }
    }

    private function seedAchievements(): void
    {
        $achievements = [
            ['First Investigation', 'Complete your first case.', '🔍', 'first_case', 1, 50],
            ['Clean Query', 'Solve a challenge without using any hints.', '💎', 'no_hints', 1, 100],
            ['SQL Master', 'Complete 10 advanced challenges.', '🏆', 'challenges_solved', 10, 500],
            ['No Errors', 'Complete a case without any incorrect queries.', '🎯', 'perfect_cases', 1, 200],
            ['Speed Detective', 'Solve a case under the target time.', '⚡', 'speed', 1, 150],
            ['Database Explorer', 'Inspect every table in a case.', '🗄️', 'explorer', 1, 100],
            ['Persistent Investigator', 'Complete 7 cases.', '📋', 'cases_completed', 7, 300],
            ['Perfect Investigation', 'Complete a case with maximum XP.', '✨', 'perfect_cases', 1, 250],
            ['Level 5 Reached', 'Reach level 5.', '⭐', 'level_milestone', 5, 100],
            ['Level 10 Reached', 'Reach level 10.', '⭐⭐', 'level_milestone', 10, 250],
            ['Level 20 Reached', 'Reach level 20.', '⭐⭐⭐', 'level_milestone', 20, 500],
            ['XP Milestone: 1000', 'Earn 1000 total XP.', '💰', 'xp_milestone', 1000, 100],
            ['XP Milestone: 5000', 'Earn 5000 total XP.', '💰💰', 'xp_milestone', 5000, 500],
            ['7-Day Streak', 'Maintain a 7-day investigation streak.', '🔥', 'streak', 7, 200],
            ['30-Day Streak', 'Maintain a 30-day investigation streak.', '🔥🔥', 'streak', 30, 1000],
        ];

        foreach ($achievements as $achievement) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM achievements WHERE name = ?");
            $stmt->execute([$achievement[0]]);
            if ($stmt->fetchColumn() === 0) {
                $this->db->prepare("
                    INSERT INTO achievements (name, description, icon, requirement_type, requirement_value, xp_reward)
                    VALUES (?, ?, ?, ?, ?, ?)
                ")->execute($achievement);
                echo "Achievement '{$achievement[0]}' created\n";
            }
        }
    }
}