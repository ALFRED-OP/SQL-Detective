<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class DashboardController extends Controller
{
    public function index(): HtmlResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $xpForNextLevel = $this->xpForLevel($user['level'] + 1);
        $xpForCurrentLevel = $this->xpForLevel($user['level']);
        $progressXp = $user['xp'] - $xpForCurrentLevel;
        $requiredXp = $xpForNextLevel - $xpForCurrentLevel;
        $progressPercent = $requiredXp > 0 ? min(100, ($progressXp / $requiredXp) * 100) : 100;

        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total_cases,
                SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_cases,
                SUM(CASE WHEN NOT completed THEN 1 ELSE 0 END) as remaining_cases
            FROM user_case_progress
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $caseStats = $stmt->fetch();

        $stmt = $db->prepare("
            SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned
            FROM user_case_progress ucp
            JOIN cases c ON c.id = ucp.case_id
            WHERE ucp.user_id = ?
            ORDER BY ucp.updated_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentCases = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT a.*, ua.unlocked_at
            FROM user_achievements ua
            JOIN achievements a ON a.id = ua.achievement_id
            WHERE ua.user_id = ?
            ORDER BY ua.unlocked_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentAchievements = $stmt->fetchAll();

        $stmt = $db->prepare("
            SELECT COUNT(*) + 1 as rank
            FROM users
            WHERE xp > ? AND status = 'active'
        ");
        $stmt->execute([$user['xp']]);
        $leaderboardPos = $stmt->fetchColumn();

        $stmt = $db->prepare("
            SELECT qh.*, c.title as case_title, c.case_code
            FROM query_history qh
            JOIN cases c ON c.id = qh.case_id
            WHERE qh.user_id = ?
            ORDER BY qh.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $recentQueries = $stmt->fetchAll();

        $streak = $this->calculateStreak($userId);

        return $this->view('dashboard.index', [
            'user' => $user,
            'xpForNextLevel' => $xpForNextLevel,
            'xpForCurrentLevel' => $xpForCurrentLevel,
            'progressPercent' => $progressPercent,
            'caseStats' => $caseStats,
            'recentCases' => $recentCases,
            'recentAchievements' => $recentAchievements,
            'leaderboardPos' => $leaderboardPos,
            'recentQueries' => $recentQueries,
            'streak' => $streak,
        ]);
    }

    public function continueCase(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['caseId'];
        return $this->redirect(route('detective.workspace', ['case' => $caseId]));
    }

    public function xpHistory(): JsonResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("
            SELECT ucp.xp_earned, ucp.completed_at, c.title, c.case_code
            FROM user_case_progress ucp
            JOIN cases c ON c.id = ucp.case_id
            WHERE ucp.user_id = ? AND ucp.completed = 1
            ORDER BY ucp.completed_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();

        return $this->json(['history' => $history]);
    }

    public function recentQueries(): JsonResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("
            SELECT qh.*, c.title as case_title, c.case_code
            FROM query_history qh
            JOIN cases c ON c.id = qh.case_id
            WHERE qh.user_id = ?
            ORDER BY qh.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $queries = $stmt->fetchAll();

        return $this->json(['queries' => $queries]);
    }

    public function stats(): JsonResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("
            SELECT
                u.xp,
                u.level,
                u.detective_rank,
                COUNT(DISTINCT ucp.case_id) as cases_completed,
                COUNT(DISTINCT ca.challenge_id) as challenges_solved,
                COUNT(DISTINCT ua.achievement_id) as achievements_unlocked,
                SUM(ucp.hints_used) as hints_used
            FROM users u
            LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
            LEFT JOIN challenge_attempts ca ON ca.user_id = u.id AND ca.result_status = 'success'
            LEFT JOIN user_achievements ua ON ua.user_id = u.id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();

        return $this->json(['stats' => $stats]);
    }

    private function xpForLevel(int $level): int
    {
        if ($level <= 1) return 0;
        return 100 * ($level - 1) * $level / 2;
    }

    private function calculateStreak(int $userId): int
    {
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("
            SELECT DATE(completed_at) as date
            FROM user_case_progress
            WHERE user_id = ? AND completed = 1
            ORDER BY completed_at DESC
        ");
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
