<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class LeaderboardController extends Controller
{
    public function index(): HtmlResponse
    {
        $db = Application::getInstance()->db();

        $leaders = $db->query("
            SELECT u.id, u.username, u.display_name, u.xp, u.level, u.detective_rank,
                   COUNT(DISTINCT ucp.case_id) as cases_completed,
                   COUNT(DISTINCT ua.achievement_id) as achievements_unlocked
            FROM users u
            LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
            LEFT JOIN user_achievements ua ON ua.user_id = u.id
            WHERE u.status = 'active'
            GROUP BY u.id
            ORDER BY u.xp DESC
            LIMIT 50
        ")->fetchAll();

        $userRank = null;
        if ($this->user()) {
            $stmt = $db->prepare("
                SELECT COUNT(*) + 1 as rank
                FROM users
                WHERE xp > (SELECT xp FROM users WHERE id = ?) AND status = 'active'
            ");
            $stmt->execute([$this->user()]);
            $userRank = $stmt->fetchColumn();
        }

        return $this->view('leaderboard.index', [
            'leaders' => $leaders,
            'userRank' => $userRank,
        ]);
    }

    public function api(): JsonResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
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
        ");
        $stmt->execute([$perPage, $offset]);
        $leaders = $stmt->fetchAll();

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
}
