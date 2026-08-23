<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;

class HomeController extends Controller
{
    public function index(): HtmlResponse
    {
        $db = Application::getInstance()->db();

        $stats = $db->query("
            SELECT
                (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
                (SELECT COUNT(*) FROM cases WHERE status = 'active') as total_cases,
                (SELECT COUNT(*) FROM challenges) as total_challenges,
                (SELECT SUM(xp_reward) FROM cases WHERE status = 'active') as total_xp
        ")->fetch();

        $featuredCases = $db->query("
            SELECT c.*, 
                   COUNT(DISTINCT ch.id) as challenge_count
            FROM cases c
            LEFT JOIN challenges ch ON ch.case_id = c.id
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.difficulty ASC, c.id ASC
            LIMIT 6
        ")->fetchAll();

        $sqlTopics = [
            'Beginner' => ['SELECT', 'WHERE', 'ORDER BY', 'LIMIT', 'DISTINCT', 'LIKE', 'IN', 'BETWEEN'],
            'Intermediate' => ['JOIN', 'GROUP BY', 'HAVING', 'Aggregate Functions', 'Subqueries', 'CASE', 'Date Filtering'],
            'Advanced' => ['CTEs', 'Window Functions', 'Complex Joins', 'Nested Subqueries', 'Data Correlation', 'Analytical Queries'],
        ];

        return $this->view('home', [
            'stats' => $stats,
            'featuredCases' => $featuredCases,
            'sqlTopics' => $sqlTopics,
        ]);
    }

    public function howItWorks(): HtmlResponse
    {
        return $this->view('how-it-works');
    }
}