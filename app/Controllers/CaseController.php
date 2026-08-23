<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class CaseController extends Controller
{
    public function index(): HtmlResponse
    {
        $db = Application::getInstance()->db();
        $userId = $this->user();

        $difficulty = $_GET['difficulty'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = ['c.status = ?'];
        $params = ['active'];

        if ($difficulty) {
            $where[] = 'c.difficulty = ?';
            $params[] = $difficulty;
        }
        if ($category) {
            $where[] = 'c.category = ?';
            $params[] = $category;
        }
        if ($search) {
            $where[] = '(c.title LIKE ? OR c.description LIKE ? OR c.case_code LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = implode(' AND ', $where);

        if ($status === 'completed') {
            $cases = $db->prepare("
                SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned,
                       COUNT(DISTINCT ch.id) as challenge_count
                FROM cases c
                LEFT JOIN challenges ch ON ch.case_id = c.id
                LEFT JOIN user_case_progress ucp ON ucp.case_id = c.id AND ucp.user_id = ?
                WHERE $whereSql AND ucp.completed = 1
                GROUP BY c.id
                ORDER BY ucp.completed_at DESC
            ")->execute(array_merge([$userId], $params))->fetchAll();
        } elseif ($status === 'in_progress') {
            $cases = $db->prepare("
                SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned,
                       COUNT(DISTINCT ch.id) as challenge_count
                FROM cases c
                LEFT JOIN challenges ch ON ch.case_id = c.id
                LEFT JOIN user_case_progress ucp ON ucp.case_id = c.id AND ucp.user_id = ?
                WHERE $whereSql AND ucp.completed = 0 AND ucp.id IS NOT NULL
                GROUP BY c.id
                ORDER BY ucp.updated_at DESC
            ")->execute(array_merge([$userId], $params))->fetchAll();
        } else {
            $cases = $db->prepare("
                SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned,
                       COUNT(DISTINCT ch.id) as challenge_count
                FROM cases c
                LEFT JOIN challenges ch ON ch.case_id = c.id
                LEFT JOIN user_case_progress ucp ON ucp.case_id = c.id AND ucp.user_id = ?
                WHERE $whereSql
                GROUP BY c.id
                ORDER BY c.difficulty ASC, c.id ASC
            ")->execute(array_merge([$userId], $params))->fetchAll();
        }

        $categories = $db->query("SELECT DISTINCT category FROM cases WHERE status = 'active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

        return $this->view('cases.index', [
            'cases' => $cases,
            'categories' => $categories,
            'filters' => compact('difficulty', 'category', 'status', 'search'),
        ]);
    }

    public function show(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();
        $userId = $this->user();

        $case = $db->prepare("
            SELECT c.*, COUNT(DISTINCT ch.id) as challenge_count
            FROM cases c
            LEFT JOIN challenges ch ON ch.case_id = c.id
            WHERE c.id = ? AND c.status = 'active'
            GROUP BY c.id
        ")->execute([$caseId])->fetch();

        if (!$case) {
            $this->abort(404);
        }

        $progress = $db->prepare("
            SELECT * FROM user_case_progress
            WHERE user_id = ? AND case_id = ?
        ")->execute([$userId, $caseId])->fetch();

        $suspects = $db->prepare("SELECT * FROM suspects WHERE case_id = ? ORDER BY id")->execute([$caseId])->fetchAll();
        $evidence = $db->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id")->execute([$caseId])->fetchAll();
        $challenges = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order")->execute([$caseId])->fetchAll();

        return $this->view('cases.show', [
            'case' => $case,
            'progress' => $progress,
            'suspects' => $suspects,
            'evidence' => $evidence,
            'challenges' => $challenges,
        ]);
    }

    public function evidence(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $case = $db->prepare("SELECT * FROM cases WHERE id = ?")->execute([$caseId])->fetch();
        if (!$case) $this->abort(404);

        $evidence = $db->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id")->execute([$caseId])->fetchAll();

        return $this->view('cases.evidence', [
            'case' => $case,
            'evidence' => $evidence,
        ]);
    }

    public function suspects(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $case = $db->prepare("SELECT * FROM cases WHERE id = ?")->execute([$caseId])->fetch();
        if (!$case) $this->abort(404);

        $suspects = $db->prepare("SELECT * FROM suspects WHERE case_id = ? ORDER BY id")->execute([$caseId])->fetchAll();

        return $this->view('cases.suspects', [
            'case' => $case,
            'suspects' => $suspects,
        ]);
    }

    public function briefing(array $vars): HtmlResponse
    {
        $caseId = (int)$vars['case'];
        $db = Application::getInstance()->db();

        $case = $db->prepare("SELECT * FROM cases WHERE id = ?")->execute([$caseId])->fetch();
        if (!$case) $this->abort(404);

        return $this->view('cases.briefing', ['case' => $case]);
    }

    public function progress(array $vars): JsonResponse
    {
        $caseId = (int)$vars['case'];
        $userId = $this->user();
        $db = Application::getInstance()->db();

        $progress = $db->prepare("
            SELECT * FROM user_case_progress
            WHERE user_id = ? AND case_id = ?
        ")->execute([$userId, $caseId])->fetch();

        return $this->json(['progress' => $progress]);
    }
}