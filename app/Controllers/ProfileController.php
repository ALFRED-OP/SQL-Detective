<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController extends Controller
{
    public function index(): HtmlResponse
    {
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $user = $db->prepare("SELECT * FROM users WHERE id = ?")->execute([$userId])->fetch();

        $xpForNextLevel = $this->xpForLevel($user['level'] + 1);
        $xpForCurrentLevel = $this->xpForLevel($user['level']);
        $progressXp = $user['xp'] - $xpForCurrentLevel;
        $requiredXp = $xpForNextLevel - $xpForCurrentLevel;
        $progressPercent = $requiredXp > 0 ? min(100, ($progressXp / $requiredXp) * 100) : 100;

        $caseStats = $db->prepare("
            SELECT
                COUNT(*) as total_cases,
                SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_cases,
                SUM(CASE WHEN NOT completed THEN 1 ELSE 0 END) as remaining_cases,
                SUM(xp_earned) as total_xp_earned,
                SUM(hints_used) as total_hints_used
            FROM user_case_progress
            WHERE user_id = ?
        ")->execute([$userId])->fetch();

        $challengeStats = $db->prepare("
            SELECT
                COUNT(DISTINCT challenge_id) as challenges_solved,
                COUNT(*) as total_attempts,
                SUM(CASE WHEN result_status = 'success' THEN 1 ELSE 0 END) as successful_attempts
            FROM challenge_attempts
            WHERE user_id = ?
        ")->execute([$userId])->fetch();

        $recentCases = $db->prepare("
            SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned
            FROM user_case_progress ucp
            JOIN cases c ON c.id = ucp.case_id
            WHERE ucp.user_id = ?
            ORDER BY ucp.updated_at DESC
            LIMIT 10
        ")->execute([$userId])->fetchAll();

        $achievements = $db->prepare("
            SELECT a.*, ua.unlocked_at,
                   CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as unlocked
            FROM achievements a
            LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
            ORDER BY a.requirement_type, a.requirement_value
        ")->execute([$userId])->fetchAll();

        $streak = $this->calculateStreak($userId);

        return $this->view('profile.index', [
            'user' => $user,
            'xpForNextLevel' => $xpForNextLevel,
            'xpForCurrentLevel' => $xpForCurrentLevel,
            'progressPercent' => $progressPercent,
            'caseStats' => $caseStats,
            'challengeStats' => $challengeStats,
            'recentCases' => $recentCases,
            'achievements' => $achievements,
            'streak' => $streak,
        ]);
    }

    public function update(array $vars, ServerRequestInterface $request): JsonResponse
    {
        $userId = $this->user();
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'display_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email,' . $userId,
        ]);

        $db = Application::getInstance()->db();
        $db->prepare("UPDATE users SET display_name = ?, email = ? WHERE id = ?")->execute([$validated['display_name'], $validated['email'], $userId]);

        $_SESSION['user']['display_name'] = $validated['display_name'];
        $_SESSION['user']['email'] = $validated['email'];

        return $this->json(['success' => true, 'message' => 'Profile updated']);
    }

    public function updatePassword(array $vars, ServerRequestInterface $request): JsonResponse
    {
        $userId = $this->user();
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'current_password' => 'required',
            'password' => 'required|password|confirmed',
        ]);

        $db = Application::getInstance()->db();
        $user = $db->prepare("SELECT password_hash FROM users WHERE id = ?")->execute([$userId])->fetch();

        if (!verify_password($validated['current_password'], $user['password_hash'])) {
            return $this->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([hash_password($validated['password']), $userId]);

        return $this->json(['success' => true, 'message' => 'Password updated']);
    }

    public function achievements(): HtmlResponse
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

        $unlockedCount = $db->prepare("SELECT COUNT(*) FROM user_achievements WHERE user_id = ?")->execute([$userId])->fetchColumn();
        $totalCount = count($achievements);

        return $this->view('profile.achievements', [
            'achievements' => $achievements,
            'unlockedCount' => $unlockedCount,
            'totalCount' => $totalCount,
        ]);
    }

    public function settings(): HtmlResponse
    {
        return $this->view('profile.settings');
    }

    private function xpForLevel(int $level): int
    {
        if ($level <= 1) return 0;
        return 100 * ($level - 1) * $level / 2;
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