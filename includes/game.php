<?php

function xp_for_level(int $level): int {
    if ($level <= 1) return 0;
    return (int)(100 * ($level - 1) * $level / 2);
}

function calculate_streak(int $userId): int {
    $db = db();
    $stmt = $db->prepare("SELECT DATE(completed_at) as date FROM user_case_progress WHERE user_id = ? AND completed = 1 ORDER BY completed_at DESC");
    $stmt->execute([$userId]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($dates)) return 0;
    $streak = 0;
    $currentDateStr = (new DateTime())->format('Y-m-d');
    foreach ($dates as $date) {
        if ($date === $currentDateStr || $date === (new DateTime($currentDateStr))->modify('-1 day')->format('Y-m-d')) {
            $streak++;
            $currentDateStr = (new DateTime($date))->modify('-1 day')->format('Y-m-d');
        } else {
            break;
        }
    }
    return $streak;
}

function log_query(int $userId, int $caseId, string $query, string $status, float $executionTime, int $rowCount, string $errorMessage = ''): void {
    $db = db();
    $db->prepare("INSERT INTO query_history (user_id, case_id, query, status, execution_time_ms, rows_returned, error_message) VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$userId, $caseId, $query, $status, $executionTime, $rowCount, $errorMessage]);
}

function sanitize_error(string $message): string {
    $patterns = ['/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', '/password/i', '/secret/i', '/token/i'];
    foreach ($patterns as $pattern) {
        $message = preg_replace($pattern, '[REDACTED]', $message);
    }
    return $message;
}

function award_xp(int $userId, int $xp): void {
    $db = db();
    $db->prepare("UPDATE users SET xp = xp + ? WHERE id = ?")->execute([$xp, $userId]);
    update_level($userId);
}

function update_level(int $userId): void {
    $db = db();
    $stmt = $db->prepare("SELECT xp, level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    $newLevel = calculate_level($user['xp']);
    if ($newLevel > $user['level']) {
        $rank = get_rank_for_level($newLevel);
        $db->prepare("UPDATE users SET level = ?, detective_rank = ? WHERE id = ?")->execute([$newLevel, $rank, $userId]);
    }
}

function calculate_level(int $xp): int {
    if ($xp < 100) return 1;
    $level = 1;
    $required = 100;
    while ($xp >= $required) {
        $level++;
        $required += 100 * $level;
    }
    return $level;
}

function get_rank_for_level(int $level): string {
    if ($level >= 30) return 'SQL Master';
    if ($level >= 20) return 'Senior Investigator';
    if ($level >= 10) return 'Database Detective';
    if ($level >= 5) return 'Query Analyst';
    return 'SQL Rookie';
}

function check_achievements(int $userId): void {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $db->prepare("
        SELECT
            COUNT(DISTINCT ucp.case_id) as cases_completed,
            COUNT(DISTINCT ca.challenge_id) as challenges_solved,
            SUM(CASE WHEN ca.result_status = 'success' AND NOT EXISTS (
                SELECT 1 FROM challenge_attempts ca2
                WHERE ca2.challenge_id = ca.challenge_id AND ca2.user_id = ca.user_id
                AND ca2.result_status != 'success' AND ca2.created_at < ca.created_at
            ) THEN 1 ELSE 0 END) as perfect_challenges,
            SUM(ucp.hints_used) as total_hints_used
        FROM users u
        LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1
        LEFT JOIN challenge_attempts ca ON ca.user_id = u.id AND ca.result_status = 'success'
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch();

    $streak = calculate_streak($userId);

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
            $db->prepare("INSERT IGNORE INTO user_achievements (user_id, achievement_id) VALUES (?, ?)")
                ->execute([$userId, $achievement['id']]);
            if ($achievement['xp_reward'] > 0) {
                award_xp($userId, $achievement['xp_reward']);
            }
        }
    }
}
