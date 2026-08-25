<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;

class AdminController extends Controller
{
    public function dashboard(): HtmlResponse
    {
        $db = Application::getInstance()->db();

        $stats = $db->query("
            SELECT
                (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
                (SELECT COUNT(*) FROM cases WHERE status = 'active') as total_cases,
                (SELECT COUNT(*) FROM challenges) as total_challenges,
                (SELECT COUNT(*) FROM challenge_attempts WHERE result_status = 'success') as successful_attempts,
                (SELECT COUNT(*) FROM audit_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as recent_logs
        ")->fetch();

        $recentUsers = $db->query("
            SELECT * FROM users ORDER BY created_at DESC LIMIT 10
        ")->fetchAll();

        $recentCases = $db->query("
            SELECT * FROM cases ORDER BY created_at DESC LIMIT 10
        ")->fetchAll();

        return $this->view('admin.dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentCases' => $recentCases,
        ]);
    }

    public function users(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT u.*, COUNT(DISTINCT ucp.case_id) as cases_completed
            FROM users u
            LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $users = $stmt->fetchAll();

        $total = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        return $this->view('admin.users', [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function toggleUser(array $vars): JsonResponse
    {
        $userId = (int)$vars['user'];
        $db = Application::getInstance()->db();

        if ($userId === $this->user()) {
            return $this->json(['success' => false, 'message' => 'Cannot toggle own account'], 400);
        }

        $stmt = $db->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        $db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $userId]);

        return $this->json(['success' => true, 'status' => $newStatus]);
    }

    public function cases(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT c.*, COUNT(DISTINCT ch.id) as challenge_count
            FROM cases c
            LEFT JOIN challenges ch ON ch.case_id = c.id
            GROUP BY c.id
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $cases = $stmt->fetchAll();

        $total = $db->query("SELECT COUNT(*) FROM cases")->fetchColumn();

        return $this->view('admin.cases', [
            'cases' => $cases,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function createCase(): HtmlResponse
    {
        return $this->view('admin.create-case');
    }

    public function storeCase(array $vars, ServerRequestInterface $request): RedirectResponse
    {
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'case_code' => 'required|max:20|unique:cases,case_code',
            'title' => 'required|max:255',
            'description' => 'required',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'category' => 'required|max:100',
            'briefing' => 'required',
            'objective' => 'required',
            'xp_reward' => 'required|integer|min:1',
            'estimated_minutes' => 'required|integer|min:1',
        ]);

        $db = Application::getInstance()->db();
        $db->prepare("
            INSERT INTO cases (case_code, title, description, difficulty, category, briefing, objective, xp_reward, estimated_minutes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ")->execute([
            $validated['case_code'],
            $validated['title'],
            $validated['description'],
            $validated['difficulty'],
            $validated['category'],
            $validated['briefing'],
            $validated['objective'],
            $validated['xp_reward'],
            $validated['estimated_minutes'],
        ]);

        return $this->redirect(route('admin.cases'));
    }

    public function editCase(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();
        if (!$case) $this->abort(404);

        return $this->view('admin.edit-case', ['case' => $case]);
    }

    public function updateCase(array $vars, ServerRequestInterface $request): RedirectResponse
    {
        $caseId = (int)$vars['case'];
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'case_code' => 'required|max:20|unique:cases,case_code,' . $caseId,
            'title' => 'required|max:255',
            'description' => 'required',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'category' => 'required|max:100',
            'briefing' => 'required',
            'objective' => 'required',
            'xp_reward' => 'required|integer|min:1',
            'estimated_minutes' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $db = Application::getInstance()->db();
        $db->prepare("
            UPDATE cases SET
                case_code = ?, title = ?, description = ?, difficulty = ?, category = ?,
                briefing = ?, objective = ?, xp_reward = ?, estimated_minutes = ?, status = ?
            WHERE id = ?
        ")->execute([
            $validated['case_code'],
            $validated['title'],
            $validated['description'],
            $validated['difficulty'],
            $validated['category'],
            $validated['briefing'],
            $validated['objective'],
            $validated['xp_reward'],
            $validated['estimated_minutes'],
            $validated['status'],
            $caseId,
        ]);

        return $this->redirect(route('admin.cases'));
    }

    public function deleteCase(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $db->prepare("DELETE FROM cases WHERE id = ?")->execute([$caseId]);

        return $this->json(['success' => true]);
    }

    public function challenges(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT ch.*, c.title as case_title, c.case_code
            FROM challenges ch
            JOIN cases c ON c.id = ch.case_id
            ORDER BY ch.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $challenges = $stmt->fetchAll();

        $total = $db->query("SELECT COUNT(*) FROM challenges")->fetchColumn();

        return $this->view('admin.challenges', [
            'challenges' => $challenges,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function createChallenge(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $cases = $db->query("SELECT id, case_code, title FROM cases WHERE status = 'active' ORDER BY case_code")->fetchAll();

        return $this->view('admin.create-challenge', ['cases' => $cases]);
    }

    public function storeChallenge(array $vars, ServerRequestInterface $request): RedirectResponse
    {
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'case_id' => 'required|exists:cases,id',
            'title' => 'required|max:255',
            'description' => 'required',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'xp_reward' => 'required|integer|min:1',
            'display_order' => 'required|integer|min:0',
        ]);

        $db = Application::getInstance()->db();
        $db->prepare("
            INSERT INTO challenges (case_id, title, description, difficulty, xp_reward, display_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $validated['case_id'],
            $validated['title'],
            $validated['description'],
            $validated['difficulty'],
            $validated['xp_reward'],
            $validated['display_order'],
        ]);

        return $this->redirect(route('admin.challenges'));
    }

    public function evidence(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $stmt = $db->prepare("
            SELECT e.*, c.title as case_title, c.case_code
            FROM evidence e
            JOIN cases c ON c.id = e.case_id
            ORDER BY e.created_at DESC
        ");
        $stmt->execute();
        $evidence = $stmt->fetchAll();

        return $this->view('admin.evidence', ['evidence' => $evidence]);
    }

    public function suspects(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $stmt = $db->prepare("
            SELECT s.*, c.title as case_title, c.case_code
            FROM suspects s
            JOIN cases c ON c.id = s.case_id
            ORDER BY s.created_at DESC
        ");
        $stmt->execute();
        $suspects = $stmt->fetchAll();

        return $this->view('admin.suspects', ['suspects' => $suspects]);
    }

    public function hints(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $stmt = $db->prepare("
            SELECT h.*, ch.title as challenge_title, c.title as case_title
            FROM hints h
            JOIN challenges ch ON ch.id = h.challenge_id
            JOIN cases c ON c.id = ch.case_id
            ORDER BY h.created_at DESC
        ");
        $stmt->execute();
        $hints = $stmt->fetchAll();

        return $this->view('admin.hints', ['hints' => $hints]);
    }

    public function achievements(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $achievements = $db->query("SELECT * FROM achievements ORDER BY requirement_type, requirement_value")->fetchAll();

        return $this->view('admin.achievements', ['achievements' => $achievements]);
    }

    public function submissions(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT ca.*, u.display_name as user_name, ch.title as challenge_title, c.case_code
            FROM challenge_attempts ca
            JOIN users u ON u.id = ca.user_id
            JOIN challenges ch ON ch.id = ca.challenge_id
            JOIN cases c ON c.id = ch.case_id
            ORDER BY ca.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $submissions = $stmt->fetchAll();

        $total = $db->query("SELECT COUNT(*) FROM challenge_attempts")->fetchColumn();

        return $this->view('admin.submissions', [
            'submissions' => $submissions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function logs(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT al.*, u.display_name as user_name
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $logs = $stmt->fetchAll();

        $total = $db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

        return $this->view('admin.logs', [
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function stats(): HtmlResponse
    {
        $db = Application::getInstance()->db();

        $stats = $db->query("
            SELECT
                (SELECT COUNT(*) FROM users WHERE status = 'active') as active_users,
                (SELECT COUNT(*) FROM users WHERE status = 'inactive') as inactive_users,
                (SELECT COUNT(*) FROM users WHERE status = 'banned') as banned_users,
                (SELECT COUNT(*) FROM cases WHERE status = 'active') as active_cases,
                (SELECT COUNT(*) FROM cases WHERE status = 'inactive') as inactive_cases,
                (SELECT COUNT(*) FROM challenges) as total_challenges,
                (SELECT COUNT(*) FROM challenge_attempts) as total_attempts,
                (SELECT COUNT(*) FROM challenge_attempts WHERE result_status = 'success') as successful_attempts,
                (SELECT COUNT(*) FROM challenge_attempts WHERE result_status = 'error') as error_attempts,
                (SELECT COUNT(*) FROM user_achievements) as total_achievements_unlocked,
                (SELECT SUM(xp) FROM users) as total_xp,
                (SELECT AVG(xp) FROM users) as avg_xp
        ")->fetch();

        $difficultyStats = $db->query("
            SELECT difficulty, COUNT(*) as count
            FROM cases
            WHERE status = 'active'
            GROUP BY difficulty
        ")->fetchAll();

        $categoryStats = $db->query("
            SELECT category, COUNT(*) as count
            FROM cases
            WHERE status = 'active'
            GROUP BY category
            ORDER BY count DESC
        ")->fetchAll();

        return $this->view('admin.stats', [
            'stats' => $stats,
            'difficultyStats' => $difficultyStats,
            'categoryStats' => $categoryStats,
        ]);
    }
}
