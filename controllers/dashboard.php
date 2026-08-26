<?php
require_once __DIR__ . '/../includes/game.php';

function dashboard_index() {
    require_auth();
    $userId = auth_id();
    $db = db();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    $xpForNextLevel = xp_for_level($user['level'] + 1);
    $xpForCurrentLevel = xp_for_level($user['level']);
    $progressXp = $user['xp'] - $xpForCurrentLevel;
    $requiredXp = $xpForNextLevel - $xpForCurrentLevel;
    $progressPercent = $requiredXp > 0 ? min(100, ($progressXp / $requiredXp) * 100) : 100;
    
    $stmt = $db->prepare("SELECT COUNT(*) as total_cases, SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_cases, SUM(CASE WHEN NOT completed THEN 1 ELSE 0 END) as remaining_cases FROM user_case_progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $caseStats = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned FROM user_case_progress ucp JOIN cases c ON c.id = ucp.case_id WHERE ucp.user_id = ? ORDER BY ucp.updated_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentCases = $stmt->fetchAll();
    
    $stmt = $db->prepare("SELECT a.*, ua.unlocked_at FROM user_achievements ua JOIN achievements a ON a.id = ua.achievement_id WHERE ua.user_id = ? ORDER BY ua.unlocked_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentAchievements = $stmt->fetchAll();
    
    $stmt = $db->prepare("SELECT COUNT(*) + 1 as rank FROM users WHERE xp > ? AND status = 'active'");
    $stmt->execute([$user['xp']]);
    $leaderboardPos = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT qh.*, c.title as case_title, c.case_code FROM query_history qh JOIN cases c ON c.id = qh.case_id WHERE qh.user_id = ? ORDER BY qh.created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $recentQueries = $stmt->fetchAll();
    
    $streak = calculate_streak($userId);
    
    view('dashboard.index', [
        'user' => $user, 'xpForNextLevel' => $xpForNextLevel, 'xpForCurrentLevel' => $xpForCurrentLevel,
        'progressPercent' => $progressPercent, 'caseStats' => $caseStats, 'recentCases' => $recentCases,
        'recentAchievements' => $recentAchievements, 'leaderboardPos' => $leaderboardPos,
        'recentQueries' => $recentQueries, 'streak' => $streak,
    ]);
}

function dashboard_continue_case(int $caseId) {
    require_auth();
    redirect("/detective/$caseId");
}

function dashboard_xp_history() {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT ucp.xp_earned, ucp.completed_at, c.title, c.case_code FROM user_case_progress ucp JOIN cases c ON c.id = ucp.case_id WHERE ucp.user_id = ? AND ucp.completed = 1 ORDER BY ucp.completed_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    json_response(['history' => $stmt->fetchAll()]);
}

function dashboard_recent_queries() {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT qh.*, c.title as case_title, c.case_code FROM query_history qh JOIN cases c ON c.id = qh.case_id WHERE qh.user_id = ? ORDER BY qh.created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    json_response(['queries' => $stmt->fetchAll()]);
}

function dashboard_stats() {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT u.xp, u.level, u.detective_rank, COUNT(DISTINCT ucp.case_id) as cases_completed, COUNT(DISTINCT ca.challenge_id) as challenges_solved, COUNT(DISTINCT ua.achievement_id) as achievements_unlocked, SUM(ucp.hints_used) as hints_used FROM users u LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1 LEFT JOIN challenge_attempts ca ON ca.user_id = u.id AND ca.result_status = 'success' LEFT JOIN user_achievements ua ON ua.user_id = u.id WHERE u.id = ? GROUP BY u.id");
    $stmt->execute([$userId]);
    json_response(['stats' => $stmt->fetch()]);
}


