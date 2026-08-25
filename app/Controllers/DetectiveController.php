<?php

namespace App\Controllers;

use App\Core\Application;
use App\Services\QueryValidator;
use App\Services\ChallengeValidator;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class DetectiveController extends Controller
{
    private QueryValidator $queryValidator;
    private ChallengeValidator $challengeValidator;

    public function __construct()
    {
        parent::__construct();
        $this->queryValidator = new QueryValidator();
        $this->challengeValidator = new ChallengeValidator();
    }

    public function workspace(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();
        $userId = $this->user();

        $stmt = $db->prepare("SELECT * FROM cases WHERE id = ? AND status = 'active'");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        if (!$case) $this->abort(404);

        $stmt = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $databases = $stmt->fetchAll();
        $databaseId = $databases[0]['id'] ?? null;

        $tables = [];
        $relationships = [];
        if ($databaseId) {
            $stmt = $db->prepare("SELECT * FROM database_tables WHERE case_database_id = ? ORDER BY display_order");
            $stmt->execute([$databaseId]);
            $tables = $stmt->fetchAll();
            foreach ($tables as &$table) {
                $stmt = $db->prepare("SELECT * FROM database_columns WHERE table_id = ? ORDER BY display_order");
                $stmt->execute([$table['id']]);
                $table['columns'] = $stmt->fetchAll();
            }

            $stmt = $db->prepare("
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
            ");
            $stmt->execute([$databaseId, $databaseId]);
            $relationships = $stmt->fetchAll();
        }

        $stmt = $db->prepare("SELECT * FROM user_case_progress WHERE user_id = ? AND case_id = ?");
        $stmt->execute([$userId, $caseId]);
        $progress = $stmt->fetch();
        if (!$progress) {
            $db->prepare("INSERT INTO user_case_progress (user_id, case_id, current_challenge_id, progress_percentage) VALUES (?, ?, NULL, 0)")
                ->execute([$userId, $caseId]);
            $progress = ['current_challenge_id' => null, 'progress_percentage' => 0, 'hints_used' => 0];
        }

        $currentChallenge = null;
        if ($progress['current_challenge_id']) {
            $stmt = $db->prepare("SELECT * FROM challenges WHERE id = ?");
            $stmt->execute([$progress['current_challenge_id']]);
            $currentChallenge = $stmt->fetch();
        } else {
            $stmt = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order LIMIT 1");
            $stmt->execute([$caseId]);
            $currentChallenge = $stmt->fetch();
        }

        $stmt = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order");
        $stmt->execute([$caseId]);
        $challenges = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT * FROM query_history
            WHERE user_id = ? AND case_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId, $caseId]);
        $queryHistory = $stmt->fetchAll();

        return $this->view('detective.workspace', [
            'case' => $case,
            'databases' => $databases,
            'tables' => $tables,
            'relationships' => $relationships,
            'progress' => $progress,
            'currentChallenge' => $currentChallenge,
            'challenges' => $challenges,
            'queryHistory' => $queryHistory,
        ]);
    }

    public function schema(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $databases = $stmt->fetchAll();
        $databaseId = $databases[0]['id'] ?? null;

        if (!$databaseId) {
            return $this->json(['tables' => [], 'relationships' => []]);
        }

        $stmt = $db->prepare("SELECT * FROM database_tables WHERE case_database_id = ? ORDER BY display_order");
        $stmt->execute([$databaseId]);
        $tables = $stmt->fetchAll();
        foreach ($tables as &$table) {
            $stmt = $db->prepare("SELECT * FROM database_columns WHERE table_id = ? ORDER BY display_order");
            $stmt->execute([$table['id']]);
            $table['columns'] = $stmt->fetchAll();
        }

        $stmt = $db->prepare("
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
        ");
        $stmt->execute([$databaseId, $databaseId]);
        $relationships = $stmt->fetchAll();

        return $this->json(['tables' => $tables, 'relationships' => $relationships]);
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
            $this->logQuery($userId, $caseId, $query, 'blocked', 0, 0, $validation['message']);
            return $this->json(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400);
        }

        $db = Application::getInstance()->db();
        $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        if (!$case) {
            return $this->json(['success' => false, 'message' => 'Case not found'], 404);
        }

        $stmt = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $databases = $stmt->fetchAll();
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

            $this->logQuery($userId, $caseId, $query, 'success', $executionTime, $rowCount);

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
            $this->logQuery($userId, $caseId, $query, 'error', $executionTime, 0, $message);
            return $this->json(['success' => false, 'message' => $message, 'type' => 'sql_error'], 400);
        }
    }

    public function queryHistory(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("
            SELECT * FROM query_history
            WHERE user_id = ? AND case_id = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$userId, $caseId]);
        $history = $stmt->fetchAll();

        return $this->json(['history' => $history]);
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

        if ($result['success'] && ($result['xp_earned'] ?? 0) > 0) {
            $this->awardXP($userId, $result['xp_earned']);
            $this->checkAchievements($userId);
        }

        return $this->json($result);
    }

    public function useHint(array $vars): JsonResponse
    {
        $userId = $this->user();
        $hintId = (int)$vars['hint'];
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("SELECT * FROM hints WHERE id = ?");
        $stmt->execute([$hintId]);
        $hint = $stmt->fetch();
        if (!$hint) {
            return $this->json(['success' => false, 'message' => 'Hint not found'], 404);
        }

        $stmt = $db->prepare("SELECT * FROM hint_usage WHERE user_id = ? AND hint_id = ?");
        $stmt->execute([$userId, $hintId]);
        $existing = $stmt->fetch();
        if ($existing) {
            return $this->json(['success' => true, 'hint' => $hint, 'already_used' => true]);
        }

        $db->prepare("INSERT INTO hint_usage (user_id, hint_id) VALUES (?, ?)")->execute([$userId, $hintId]);
        $db->prepare("UPDATE user_case_progress SET hints_used = hints_used + 1 WHERE user_id = ? AND case_id = (SELECT case_id FROM challenges WHERE id = ?)")->execute([$userId, $hint['challenge_id']]);

        return $this->json(['success' => true, 'hint' => $hint, 'already_used' => false]);
    }

    private function logQuery(int $userId, int $caseId, string $query, string $status, float $executionTime, int $rowCount, string $errorMessage = ''): void
    {
        $db = Application::getInstance()->db();
        $db->prepare("
            INSERT INTO query_history (user_id, case_id, query, status, execution_time_ms, rows_returned, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$userId, $caseId, $query, $status, $executionTime, $rowCount, $errorMessage]);
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
        $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

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
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $stmt = $db->prepare("
            SELECT
                COUNT(DISTINCT ucp.case_id) as cases_completed,
                COUNT(DISTINCT ca.challenge_id) as challenges_solved,
                SUM(CASE WHEN ca.result_status = 'success' AND NOT EXISTS (SELECT 1 FROM challenge_attempts ca2 WHERE ca2.challenge_id = ca.challenge_id AND ca2.user_id = ca.user_id AND ca2.result_status != 'success' AND ca2.created_at < ca.created_at) THEN 1 ELSE 0 END) as perfect_challenges,
                COUNT(DISTINCT ua.achievement_id) as achievements_unlocked,
                MAX(ucp.hints_used) as max_hints_used,
                SUM(ucp.hints_used) as total_hints_used
            FROM users u
            LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
            LEFT JOIN challenge_attempts ca ON ca.user_id = u.id AND ca.result_status = 'success'
            LEFT JOIN user_achievements ua ON ua.user_id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();

        $streak = $this->calculateStreak($userId);

        $stmt = $db->prepare("SELECT * FROM achievements WHERE id NOT IN (SELECT achievement_id FROM user_achievements WHERE user_id = ?)");
        $stmt->execute([$userId]);
        $achievements = $stmt->fetchAll();

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
                case 'perfect_cases':
                    $unlocked = $stats['perfect_challenges'] >= $achievement['requirement_value'];
                    break;
                case 'no_hints':
                    $unlocked = $stats['total_hints_used'] == 0 && $stats['challenges_solved'] >= 1;
                    break;
                case 'speed':
                    $unlocked = true;
                    break;
                case 'explorer':
                    $unlocked = true;
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
        $stmt = $db->prepare("SELECT DATE(completed_at) as date FROM user_case_progress WHERE user_id = ? AND completed = 1 ORDER BY completed_at DESC");
        $stmt->execute([$userId]);
        $dates = $stmt->fetchAll(\PDO::FETCH_COLUMN);
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