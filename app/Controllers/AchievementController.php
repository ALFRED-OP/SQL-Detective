<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;

class AchievementController extends Controller
{
    public function index(): HtmlResponse
    {
        $db = Application::getInstance()->db();

        $achievements = $db->query("SELECT * FROM achievements ORDER BY requirement_type, requirement_value")->fetchAll();

        $userUnlocked = [];
        if ($this->user()) {
            $userUnlocked = $db->prepare("SELECT achievement_id FROM user_achievements WHERE user_id = ?")->execute([$this->user()])->fetchAll(PDO::FETCH_COLUMN);
        }

        return $this->view('achievements.index', [
            'achievements' => $achievements,
            'userUnlocked' => $userUnlocked,
        ]);
    }
}