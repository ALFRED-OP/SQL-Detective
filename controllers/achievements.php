<?php
function achievements_index() {
    $db = db();
    $achievements = $db->query("SELECT * FROM achievements ORDER BY requirement_type, requirement_value")->fetchAll();
    $userUnlocked = [];
    if (auth_check()) {
        $stmt = $db->prepare("SELECT achievement_id FROM user_achievements WHERE user_id = ?");
        $stmt->execute([auth_id()]);
        $userUnlocked = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    view('achievements.index', ['achievements' => $achievements, 'userUnlocked' => $userUnlocked]);
}
