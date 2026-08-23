<?php

namespace App\Controllers;

use App\Core\Application;
use App\Services\QueryValidator;
use App\Services\ChallengeValidator;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ApiController extends Controller
{
    private QueryValidator $queryValidator;
    private ChallengeValidator $challengeValidator;

    public function __construct()
    {
        parent::__construct();
        $this->queryValidator = new QueryValidator();
        $this->challengeValidator = new ChallengeValidator();
    }

    public function executeQuery(array $vars, ServerRequestInterface $request): JsonResponse
    {
        $userId = $this->user();
        $data = $request->getParsedBody();
        $query = trim($data['query'] ?? '');
        $caseId = (int)($data['case_id'] ?? 0);

        if (!$query) {
            return $this->json(['success' => false, 'message' => 'Query is required'], 400);
        }

        $validation = $this->queryValidator->validate($query);
        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400);
        }

        $db = Application::getInstance()->db();
        $case = $db->prepare("SELECT * FROM cases WHERE id = ?")->execute([$caseId])->fetch();
        if (!$case) {
            return $this->json(['success' => false, 'message' => 'Case not found'], 404);
        }

        $databases = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?")->execute([$caseId])->fetchAll();
        $databaseId = $databases[0]['id'] ?? null;

        if (!$databaseId) {
            return $this->json(['success' => false, 'message' => 'No investigation database configured'], 400);
        }

        $investigationDb = Application::getInstance()->investigationDb();
        $startTime = microtime(true);

        try {
            $stmt = $investigationDb->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $rowCount = count($rows);

            $maxRows = config('security.query_execution.max_result_rows', 1000);
            if ($rowCount > $maxRows) {
                $rows = array_slice($rows, 0, $maxRows);
            }

            $db->prepare("
                INSERT INTO query_history (user_id, case_id, query, status, execution_time_ms, rows_returned)
                VALUES (?, ?, ?, 'success', ?, ?)
            ")->execute([$userId, $caseId, $query, $executionTime, $rowCount]);

            return $this->json([
                'success' => true,
                'rows' => $rows,
                'row_count' => $rowCount,
                'execution_time_ms' => $executionTime,
                'truncated' => $rowCount > $maxRows,
            ]);
        } catch (\PDOException $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $message = $this->sanitizeError($e->getMessage());
            $db->prepare("
                INSERT INTO query_history (user_id, case_id, query, status, execution_time_ms, rows_returned, error_message)
                VALUES (?, ?, ?, 'error', ?, 0, ?)
            ")->execute([$userId, $caseId, $query, $executionTime, $message]);
            return $this->json(['success' => false, 'message' => $message, 'type' => 'sql_error'], 400);
        }
    }

    public function caseSchema(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $databases = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?")->execute([$caseId])->fetchAll();
        $databaseId = $databases[0]['id'] ?? null;

        if (!$databaseId) {
            return $this->json(['tables' => [], 'relationships' => []]);
        }

        $tables = $db->prepare("SELECT * FROM database_tables WHERE case_database_id = ? ORDER BY display_order")->execute([$databaseId])->fetchAll();
        foreach ($tables as &$table) {
            $table['columns'] = $db->prepare("SELECT * FROM database_columns WHERE table_id = ? ORDER BY display_order")->execute([$table['id']])->fetchAll();
        }

        $relationships = $db->prepare("
            SELECT
                dt1.table_name as from_table,
                dc1.column_name as from_column,
                dt2.table_name as to_table,
                dc2.column_name as to_column
            FROM database_columns dc1
            JOIN database_tables dt1 ON dt1.id = dc1.table_id
            JOIN database_columns dc2 ON dc2.column_name = dc1.column_name AND dc2.table_id != dc1.table_id
            JOIN database_tables dt2 ON dt2.id = dc2.table_id
            WHERE (dc1.is_primary_key = 1 OR dc2.is_primary_key = 1)
            AND dt1.case_database_id = ? AND dt2.case_database_id = ?
        ")->execute([$databaseId, $databaseId])->fetchAll();

        return $this->json(['tables' => $tables, 'relationships' => $relationships]);
    }

    public function caseEvidence(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $evidence = $db->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id")->execute([$caseId])->fetchAll();
        $suspects = $db->prepare("SELECT * FROM suspects WHERE case_id = ? ORDER BY id")->execute([$caseId])->fetchAll();

        return $this->json(['evidence' => $evidence, 'suspects' => $suspects]);
    }

    public function caseChallenges(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $challenges = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order")->execute([$caseId])->fetchAll();

        $progress = $db->prepare("SELECT current_challenge_id FROM user_case_progress WHERE user_id = ? AND case_id = ?")->execute([$userId, $caseId])->fetch();
        $currentChallengeId = $progress['current_challenge_id'] ?? null;

        $attempts = [];
        if ($userId) {
            $attempts = $db->prepare("
                SELECT challenge_id, COUNT(*) as attempts, MAX(CASE WHEN result_status = 'success' THEN 1 ELSE 0 END) as solved
                FROM challenge_attempts
                WHERE user_id = ? AND challenge_id IN (" . implode(',', array_fill(0, count($challenges), '?')) . ")
                GROUP BY challenge_id
            ")->execute(array_merge([$userId], array_column($challenges, 'id')))->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        foreach ($challenges as &$challenge) {
            $challenge['hints'] = $db->prepare("SELECT * FROM hints WHERE challenge_id = ? ORDER BY hint_level")->execute([$challenge['id']])->fetchAll();
            $challenge['is_current'] = $challenge['id'] == $currentChallengeId;
            $challenge['attempts'] = $attempts[$challenge['id']] ?? ['attempts' => 0, 'solved' => 0];
        }

        return $this->json(['challenges' => $challenges]);
    }

    public function submitChallenge(array $vars, ServerRequestInterface $request): JsonResponse
    {
        $userId = $this->user();
        $challengeId = (int)$vars['challenge'];
        $data = $request->getParsedBody();
        $query = trim($data['query'] ?? '');

        if (!$query) {
            return $this->json(['success' => false, 'message' => 'Query is required'], 400);
        }

        $validation = $this->queryValidator->validate($query);
        if (!$validation['valid']) {
            return $this->json(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400);
        }

        $result = $this->challengeValidator->validate($userId, $challengeId, $query);

        if ($result['success']) {
            $this->awardXP($userId, $result['xp_earned']);
            $this->checkAchievements($userId);
        }

        return $this->json($result);
    }

    public function leaderboard(): JsonResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $leaders = $db->prepare("
            SELECT u.id, u.username, u.display_name, u.xp, u.level, u.detective_rank,
                   COUNT(DISTINCT ucp.case_id) as cases_completed,
                   COUNT(DISTINCT ua.achievement_id) as achievements_unlocked
            FROM users u
            LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
            LEFT JOIN user_achievements ua ON ua.user_id = u.id
            WHERE u.status = 'active'
            GROUP BY u.id
            ORDER BY u.xp DESC
            LIMIT ? OFFSET ?
        ")->execute([$perPage, $offset])->fetchAll();

        $total = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();

        return $this->json([
            'data' => $leaders,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ]
        ]);
    }

    public function profile(): JsonResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $user = $db->prepare("SELECT id, username, email, display_name, xp, level, detective_rank, role, created_at FROM users WHERE id = ?")->execute([$userId])->fetch();

        return $this->json(['user' => $user]);
    }

    public function achievements(): JsonResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $achievements = $db->prepare("
            SELECT a.*, ua.unlocked_at,
                   CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as unlocked
            FROM achievements a
            LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
            ORDER BY a.requirement_type, a.requirement_value
        ")->execute([$userId])->fetchAll();

        return $this->json(['achievements' => $achievements]);
    }

    private function sanitizeError(string $message): string
    {
        $patterns = [
            '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/',
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
            '/password/i',
            '/secret/i',
            '/token/i',
        ];
        foreach ($patterns as $pattern) {
            $message = preg_replace($pattern, '[REDACTED]', $message);
        }
        return $message;
    }

    private function awardXP(int $userId, int $xp): void
    {
        $db = Application::getInstance()->db();
        $db->prepare("UPDATE users SET xp = xp + ? WHERE id = ?")->execute([$xp, $userId]);
        $this->updateLevel($userId);
    }

    private function updateLevel(int $userId): void
    {
        $db = Application::getInstance()->db();
        $user = $db->prepare("SELECT xp, level FROM users WHERE id = ?")->execute([$userId])->fetch();

        $newLevel = $this->calculateLevel($user['xp']);
        if ($newLevel > $user['level']) {
            $rank = $this->getRankForLevel($newLevel);
            $db->prepare("UPDATE users SET level = ?, detective_rank = ? WHERE id = ?")->execute([$newLevel, $rank, $userId]);
        }
    }

    private function calculateLevel(int $xp): int
    {
        if ($xp < 100) return 1;
        $level = 1;
        $required = 100;
        while ($xp >= $required) {
            $level++;
            $required += 100 * $level;
        }
        return $level;
    }

    private function getRankForLevel(int $level): string
    {
        if ($level >= 30) return 'SQL Master';
        if ($level >= 20) return 'Senior Investigator';
        if ($level >= 10) return 'Database Detective';
        if ($level >= 5) return 'Query Analyst';
        return 'SQL Rookie';
    }

    private function checkAchievements(int $userId): void
    {
        $db = Application::getInstance()->db();
        $user = $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$userId])->fetch();

        $stats = $db->prepare("
            SELECT
                COUNT(DISTINCT ucp.case_id) as cases_completed,
                COUNT(DISTINCT ca.challenge_id) as challenges_solved,
                SUM(ucp.hints_used) as total_hints_used
            FROM users u
            LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
            LEFT JOIN challenge_attempts ca ON ca.user_id = u.id AND ca.result_status = 'success'
            WHERE u.id = ?
        ")->execute([$userId])->fetch();

        $streak = $this->calculateStreak($userId);

        $achievements = $db->prepare("SELECT * FROM achievements WHERE id NOT IN (SELECT achievement_id FROM user_achievements WHERE user_id = ?)")->execute([$userId])->fetchAll();

        foreach ($achievements as $achievement) {
            $unlocked = false;
            switch ($achievement['requirement_type']) {
                case 'first_case':
                    $unlocked = $stats['cases_completed'] >= 1;
                    break;
                case 'cases_completed':
                    $unlocked = $stats['cases_completed'] >= $achievement['requirement_value'];
                    break;
                case 'challenges_solved':
                    $unlocked = $stats['challenges_solved'] >= $achievement['requirement_value'];
                    break;
                case 'no_hints':
                    $unlocked = $stats['total_hints_used'] == 0 && $stats['challenges_solved'] >= 1;
                    break;
                case 'streak':
                    $unlocked = $streak >= $achievement['requirement_value'];
                    break;
                case 'level_milestone':
                    $unlocked = $user['level'] >= $achievement['requirement_value'];
                    break;
                case 'xp_milestone':
                    $unlocked = $user['xp'] >= $achievement['requirement_value'];
                    break;
            }

            if ($unlocked) {
                $db->prepare("INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)")->execute([$userId, $achievement['id']]);
                if ($achievement['xp_reward'] > 0) {
                    $this->awardXP($userId, $achievement['xp_reward']);
                }
            }
        }
    }

    private function calculateStreak(int $userId): int
    {
        $db = Application::getInstance()->db();
        $dates = $db->prepare("SELECT DATE(completed_at) as date FROM user_case_progress WHERE user_id = ? AND completed = 1 ORDER BY completed_at DESC")->execute([$userId])->fetchAll(PDO::FETCH_COLUMN);
        if (empty($dates)) return 0;

        $streak = 0;
        $currentDate = new \DateTime();
        $currentDateStr = $currentDate->format('Y-m-d');

        foreach ($dates as $date) {
            if ($date === $currentDateStr || $date === (new \DateTime($currentDateStr))->modify('-1 day')->format('Y-m-d')) {
                $streak++;
                $currentDateStr = (new \DateTime($date))->modify('-1 day')->format('Y-m-d');
            } else {
                break;
            }
        }
        return $streak;
    }
}