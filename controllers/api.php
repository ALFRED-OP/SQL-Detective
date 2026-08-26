<?php
require_once __DIR__ . '/../includes/game.php';

function api_execute_query() {
    require_auth();
    $userId = auth_id();
    $data = $_POST;
    $query = trim($data['query'] ?? '');
    $caseId = (int)($data['case_id'] ?? 0);
    if (!$query) { json_response(['success' => false, 'message' => 'Query is required'], 400); return; }

    $qv = new QueryValidator();
    $validation = $qv->validate($query);
    if (!$validation['valid']) { json_response(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400); return; }

    $db = db();
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) { json_response(['success' => false, 'message' => 'Case not found'], 404); return; }

    $stmt = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
    $stmt->execute([$caseId]);
    $databases = $stmt->fetchAll();
    $databaseName = $databases[0]['database_name'] ?? null;
    if (!$databaseName) { json_response(['success' => false, 'message' => 'No investigation database configured'], 400); return; }

    $investigationDb = investigationDbFor($databaseName);
    $startTime = microtime(true);
    try {
        $stmt = $investigationDb->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $rowCount = count($rows);
        $maxRows = config('security.query_execution.max_result_rows', 1000);
        if ($rowCount > $maxRows) { $rows = array_slice($rows, 0, $maxRows); }
        $db->prepare("INSERT INTO query_history (user_id, case_id, query, status, execution_time_ms, rows_returned) VALUES (?, ?, ?, 'success', ?, ?)")
            ->execute([$userId, $caseId, $query, $executionTime, $rowCount]);
        json_response(['success' => true, 'rows' => $rows, 'row_count' => $rowCount, 'execution_time_ms' => $executionTime, 'truncated' => $rowCount > $maxRows]);
    } catch (PDOException $e) {
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $message = sanitize_error($e->getMessage());
        $db->prepare("INSERT INTO query_history (user_id, case_id, query, status, execution_time_ms, rows_returned, error_message) VALUES (?, ?, ?, 'error', ?, 0, ?)")
            ->execute([$userId, $caseId, $query, $executionTime, $message]);
        json_response(['success' => false, 'message' => $message, 'type' => 'sql_error'], 400);
    }
}

function api_case_schema(int $caseId) {
    require_auth();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
    $stmt->execute([$caseId]);
    $databases = $stmt->fetchAll();
    $databaseId = $databases[0]['id'] ?? null;
    if (!$databaseId) { json_response(['tables' => [], 'relationships' => []]); return; }
    $stmt = $db->prepare("SELECT * FROM database_tables WHERE case_database_id = ? ORDER BY display_order");
    $stmt->execute([$databaseId]);
    $tables = $stmt->fetchAll();
    foreach ($tables as &$table) {
        $colStmt = $db->prepare("SELECT * FROM database_columns WHERE table_id = ? ORDER BY display_order");
        $colStmt->execute([$table['id']]);
        $table['columns'] = $colStmt->fetchAll();
    }
    $stmt = $db->prepare("SELECT dt1.table_name as from_table, dc1.column_name as from_column, dt2.table_name as to_table, dc2.column_name as to_column FROM database_columns dc1 JOIN database_tables dt1 ON dt1.id = dc1.table_id JOIN database_columns dc2 ON dc2.column_name = dc1.column_name AND dc2.table_id != dc1.table_id JOIN database_tables dt2 ON dt2.id = dc2.table_id WHERE (dc1.is_primary_key = 1 OR dc2.is_primary_key = 1) AND dt1.case_database_id = ? AND dt2.case_database_id = ?");
    $stmt->execute([$databaseId, $databaseId]);
    json_response(['tables' => $tables, 'relationships' => $stmt->fetchAll()]);
}

function api_case_evidence(int $caseId) {
    require_auth();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id");
    $stmt->execute([$caseId]);
    $evidence = $stmt->fetchAll();
    $stmt = $db->prepare("SELECT * FROM suspects WHERE case_id = ? ORDER BY id");
    $stmt->execute([$caseId]);
    json_response(['evidence' => $evidence, 'suspects' => $stmt->fetchAll()]);
}

function api_case_challenges(int $caseId) {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order");
    $stmt->execute([$caseId]);
    $challenges = $stmt->fetchAll();
    $stmt = $db->prepare("SELECT current_challenge_id FROM user_case_progress WHERE user_id = ? AND case_id = ?");
    $stmt->execute([$userId, $caseId]);
    $progress = $stmt->fetch();
    $currentChallengeId = $progress['current_challenge_id'] ?? null;
    $attempts = [];
    if ($userId && !empty($challenges)) {
        $placeholders = implode(',', array_fill(0, count($challenges), '?'));
        $stmt = $db->prepare("SELECT challenge_id, COUNT(*) as attempts, MAX(CASE WHEN result_status = 'success' THEN 1 ELSE 0 END) as solved FROM challenge_attempts WHERE user_id = ? AND challenge_id IN ($placeholders) GROUP BY challenge_id");
        $stmt->execute(array_merge([$userId], array_column($challenges, 'id')));
        $attempts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    foreach ($challenges as &$challenge) {
        $hintStmt = $db->prepare("SELECT * FROM hints WHERE challenge_id = ? ORDER BY hint_level");
        $hintStmt->execute([$challenge['id']]);
        $challenge['hints'] = $hintStmt->fetchAll();
        $challenge['is_current'] = $challenge['id'] == $currentChallengeId;
        $challenge['attempts'] = $attempts[$challenge['id']] ?? ['attempts' => 0, 'solved' => 0];
    }
    json_response(['challenges' => $challenges]);
}

function api_submit_challenge(int $challengeId) {
    require_auth();
    $userId = auth_id();
    $data = $_POST;
    $query = trim($data['query'] ?? '');
    if (!$query) { json_response(['success' => false, 'message' => 'Query is required'], 400); return; }
    $qv = new QueryValidator();
    $validation = $qv->validate($query);
    if (!$validation['valid']) { json_response(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400); return; }
    $cv = new ChallengeValidator();
    $result = $cv->validate($userId, $challengeId, $query);
    if ($result['success'] && ($result['xp_earned'] ?? 0) > 0) {
        award_xp($userId, $result['xp_earned']);
        check_achievements($userId);
    }
    json_response($result);
}

function api_leaderboard() {
    $db = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT u.id, u.username, u.display_name, u.xp, u.level, u.detective_rank, COUNT(DISTINCT ucp.case_id) as cases_completed, COUNT(DISTINCT ua.achievement_id) as achievements_unlocked FROM users u LEFT JOIN user_case_progress ucp ON ucp.user_id = u.id AND ucp.completed = 1 LEFT JOIN user_achievements ua ON ua.user_id = u.id WHERE u.status = 'active' GROUP BY u.id ORDER BY u.xp DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $leaders = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    json_response(['data' => $leaders, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]]);
}

function api_profile() {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT id, username, email, display_name, xp, level, detective_rank, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    json_response(['user' => $stmt->fetch()]);
}

function api_achievements() {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT a.*, ua.unlocked_at, CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as unlocked FROM achievements a LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ? ORDER BY a.requirement_type, a.requirement_value");
    $stmt->execute([$userId]);
    json_response(['achievements' => $stmt->fetchAll()]);
}


