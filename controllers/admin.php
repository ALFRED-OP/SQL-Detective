<?php
function admin_dashboard() {
    require_admin();
    $db = db();
    $stats = $db->query("SELECT (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users, (SELECT COUNT(*) FROM cases WHERE status = 'active') as total_cases, (SELECT COUNT(*) FROM challenges) as total_challenges, (SELECT COUNT(*) FROM challenge_attempts WHERE result_status = 'success') as successful_attempts, (SELECT COUNT(*) FROM audit_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as recent_logs")->fetch();
    $recentUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
    $recentCases = $db->query("SELECT * FROM cases ORDER BY created_at DESC LIMIT 10")->fetchAll();
    view('admin.dashboard', ['stats' => $stats, 'recentUsers' => $recentUsers, 'recentCases' => $recentCases]);
}

function admin_users() {
    require_admin();
    $db = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20; $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT u.*, COUNT(DISTINCT ucp.case_id) as cases_completed FROM users u LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1 GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $users = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    view('admin.users', ['users' => $users, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]]);
}

function admin_toggle_user(int $userId) {
    require_admin();
    if ($userId === auth_id()) { json_response(['success' => false, 'message' => 'Cannot toggle own account'], 400); return; }
    $db = db();
    $stmt = $db->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) { json_response(['success' => false, 'message' => 'User not found'], 404); return; }
    $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
    $db->prepare("UPDATE users SET status = ? WHERE id = ?")->execute([$newStatus, $userId]);
    json_response(['success' => true, 'status' => $newStatus]);
}

function admin_cases() {
    require_admin();
    $db = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20; $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT c.*, COUNT(DISTINCT ch.id) as challenge_count FROM cases c LEFT JOIN challenges ch ON ch.case_id = c.id GROUP BY c.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $cases = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM cases")->fetchColumn();
    view('admin.cases', ['cases' => $cases, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]]);
}

function admin_create_case() {
    require_admin();
    view('admin.create-case');
}

function admin_store_case() {
    require_admin();
    $validated = validate($_POST, [
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
    $db = db();
    $db->prepare("INSERT INTO cases (case_code, title, description, difficulty, category, briefing, objective, xp_reward, estimated_minutes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')")
        ->execute([$validated['case_code'], $validated['title'], $validated['description'], $validated['difficulty'], $validated['category'], $validated['briefing'], $validated['objective'], $validated['xp_reward'], $validated['estimated_minutes']]);
    redirect(route('admin.dashboard'));
}

function admin_edit_case(int $caseId) {
    require_admin();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) abort(404);
    view('admin.edit-case', ['case' => $case]);
}

function admin_update_case(int $caseId) {
    require_admin();
    $validated = validate($_POST, [
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
    $db = db();
    $db->prepare("UPDATE cases SET case_code = ?, title = ?, description = ?, difficulty = ?, category = ?, briefing = ?, objective = ?, xp_reward = ?, estimated_minutes = ?, status = ? WHERE id = ?")
        ->execute([$validated['case_code'], $validated['title'], $validated['description'], $validated['difficulty'], $validated['category'], $validated['briefing'], $validated['objective'], $validated['xp_reward'], $validated['estimated_minutes'], $validated['status'], $caseId]);
    redirect(route('admin.dashboard'));
}

function admin_delete_case(int $caseId) {
    require_admin();
    $db = db();
    $db->prepare("DELETE FROM cases WHERE id = ?")->execute([$caseId]);
    json_response(['success' => true]);
}

function admin_challenges() {
    require_admin();
    $db = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20; $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT ch.*, c.title as case_title, c.case_code FROM challenges ch JOIN cases c ON c.id = ch.case_id ORDER BY ch.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $challenges = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM challenges")->fetchColumn();
    view('admin.challenges', ['challenges' => $challenges, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]]);
}

function admin_create_challenge() {
    require_admin();
    $db = db();
    $cases = $db->query("SELECT id, case_code, title FROM cases WHERE status = 'active' ORDER BY case_code")->fetchAll();
    view('admin.create-challenge', ['cases' => $cases]);
}

function admin_store_challenge() {
    require_admin();
    $validated = validate($_POST, [
        'case_id' => 'required|exists:cases,id',
        'title' => 'required|max:255',
        'description' => 'required',
        'difficulty' => 'required|in:beginner,intermediate,advanced',
        'xp_reward' => 'required|integer|min:1',
        'display_order' => 'required|integer|min:0',
    ]);
    $db = db();
    $db->prepare("INSERT INTO challenges (case_id, title, description, difficulty, xp_reward, display_order) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$validated['case_id'], $validated['title'], $validated['description'], $validated['difficulty'], $validated['xp_reward'], $validated['display_order']]);
    redirect(route('admin.dashboard'));
}

function admin_evidence() {
    require_admin();
    $db = db();
    $evidence = $db->prepare("SELECT e.*, c.title as case_title, c.case_code FROM evidence e JOIN cases c ON c.id = e.case_id ORDER BY e.created_at DESC");
    $evidence->execute();
    view('admin.evidence', ['evidence' => $evidence->fetchAll()]);
}

function admin_suspects() {
    require_admin();
    $db = db();
    $suspects = $db->prepare("SELECT s.*, c.title as case_title, c.case_code FROM suspects s JOIN cases c ON c.id = s.case_id ORDER BY s.created_at DESC");
    $suspects->execute();
    view('admin.suspects', ['suspects' => $suspects->fetchAll()]);
}

function admin_hints() {
    require_admin();
    $db = db();
    $hints = $db->prepare("SELECT h.*, ch.title as challenge_title, c.title as case_title FROM hints h JOIN challenges ch ON ch.id = h.challenge_id JOIN cases c ON c.id = ch.case_id ORDER BY h.created_at DESC");
    $hints->execute();
    view('admin.hints', ['hints' => $hints->fetchAll()]);
}

function admin_achievements_list() {
    require_admin();
    $db = db();
    $achievements = $db->query("SELECT * FROM achievements ORDER BY requirement_type, requirement_value")->fetchAll();
    view('admin.achievements', ['achievements' => $achievements]);
}

function admin_submissions() {
    require_admin();
    $db = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50; $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT ca.*, u.display_name as user_name, ch.title as challenge_title, c.case_code FROM challenge_attempts ca JOIN users u ON u.id = ca.user_id JOIN challenges ch ON ch.id = ca.challenge_id JOIN cases c ON c.id = ch.case_id ORDER BY ca.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $submissions = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM challenge_attempts")->fetchColumn();
    view('admin.submissions', ['submissions' => $submissions, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]]);
}

function admin_logs() {
    require_admin();
    $db = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50; $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT al.*, u.display_name as user_name FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $logs = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    view('admin.logs', ['logs' => $logs, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]]);
}

function admin_stats() {
    require_admin();
    $db = db();
    $stats = $db->query("SELECT (SELECT COUNT(*) FROM users WHERE status = 'active') as active_users, (SELECT COUNT(*) FROM users WHERE status = 'inactive') as inactive_users, (SELECT COUNT(*) FROM users WHERE status = 'banned') as banned_users, (SELECT COUNT(*) FROM cases WHERE status = 'active') as active_cases, (SELECT COUNT(*) FROM cases WHERE status = 'inactive') as inactive_cases, (SELECT COUNT(*) FROM challenges) as total_challenges, (SELECT COUNT(*) FROM challenge_attempts) as total_attempts, (SELECT COUNT(*) FROM challenge_attempts WHERE result_status = 'success') as successful_attempts, (SELECT COUNT(*) FROM challenge_attempts WHERE result_status = 'error') as error_attempts, (SELECT COUNT(*) FROM user_achievements) as total_achievements_unlocked, (SELECT SUM(xp) FROM users) as total_xp, (SELECT AVG(xp) FROM users) as avg_xp")->fetch();
    $difficultyStats = $db->query("SELECT difficulty, COUNT(*) as count FROM cases WHERE status = 'active' GROUP BY difficulty")->fetchAll();
    $categoryStats = $db->query("SELECT category, COUNT(*) as count FROM cases WHERE status = 'active' GROUP BY category ORDER BY count DESC")->fetchAll();
    view('admin.stats', ['stats' => $stats, 'difficultyStats' => $difficultyStats, 'categoryStats' => $categoryStats]);
}
