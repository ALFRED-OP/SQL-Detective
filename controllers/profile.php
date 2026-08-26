<?php
require_once __DIR__ . '/../includes/game.php';

function profile_index() {
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
    $stmt = $db->prepare("SELECT COUNT(*) as total_cases, SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_cases, SUM(CASE WHEN NOT completed THEN 1 ELSE 0 END) as remaining_cases, SUM(xp_earned) as total_xp_earned, SUM(hints_used) as total_hints_used FROM user_case_progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $caseStats = $stmt->fetch();
    $stmt = $db->prepare("SELECT COUNT(DISTINCT challenge_id) as challenges_solved, COUNT(*) as total_attempts, SUM(CASE WHEN result_status = 'success' THEN 1 ELSE 0 END) as successful_attempts FROM challenge_attempts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $challengeStats = $stmt->fetch();
    $stmt = $db->prepare("SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned FROM user_case_progress ucp JOIN cases c ON c.id = ucp.case_id WHERE ucp.user_id = ? ORDER BY ucp.updated_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $recentCases = $stmt->fetchAll();
    $stmt = $db->prepare("SELECT a.*, ua.unlocked_at, CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as unlocked FROM achievements a LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ? ORDER BY a.requirement_type, a.requirement_value");
    $stmt->execute([$userId]);
    $achievements = $stmt->fetchAll();
    $streak = calculate_streak($userId);
    view('profile.index', [
        'user' => $user, 'xpForNextLevel' => $xpForNextLevel, 'xpForCurrentLevel' => $xpForCurrentLevel,
        'progressPercent' => $progressPercent, 'caseStats' => $caseStats, 'challengeStats' => $challengeStats,
        'recentCases' => $recentCases, 'achievements' => $achievements, 'streak' => $streak,
    ]);
}

function profile_update() {
    require_auth();
    $userId = auth_id();
    $validated = validate($_POST, [
        'display_name' => 'required|min:2|max:100',
        'email' => 'required|email|unique:users,email,' . $userId,
    ]);
    $db = db();
    $db->prepare("UPDATE users SET display_name = ?, email = ? WHERE id = ?")->execute([$validated['display_name'], $validated['email'], $userId]);
    $_SESSION['user']['display_name'] = $validated['display_name'];
    $_SESSION['user']['email'] = $validated['email'];
    json_response(['success' => true, 'message' => 'Profile updated']);
}

function profile_update_password() {
    require_auth();
    $userId = auth_id();
    $validated = validate($_POST, [
        'current_password' => 'required',
        'password' => 'required|password|confirmed',
    ]);
    $db = db();
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!verify_password($validated['current_password'], $user['password_hash'])) {
        json_response(['success' => false, 'message' => 'Current password is incorrect'], 400);
        return;
    }
    $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([hash_password($validated['password']), $userId]);
    json_response(['success' => true, 'message' => 'Password updated']);
}

function profile_achievements() {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT a.*, ua.unlocked_at, CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as unlocked FROM achievements a LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ? ORDER BY a.requirement_type, a.requirement_value");
    $stmt->execute([$userId]);
    $achievements = $stmt->fetchAll();
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_achievements WHERE user_id = ?");
    $stmt->execute([$userId]);
    $unlockedCount = $stmt->fetchColumn();
    view('profile.achievements', ['achievements' => $achievements, 'unlockedCount' => $unlockedCount, 'totalCount' => count($achievements)]);
}

function profile_settings() {
    require_auth();
    view('profile.settings');
}


