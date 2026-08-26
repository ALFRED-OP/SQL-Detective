<?php
require_once __DIR__ . '/../includes/game.php';

function detective_workspace(int $caseId) {
    require_auth();
    $userId = auth_id();
    $db = db();
    
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ? AND status = 'active'");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) abort(404);
    
    $stmt = $db->prepare("SELECT * FROM case_databases WHERE case_id = ?");
    $stmt->execute([$caseId]);
    $databases = $stmt->fetchAll();
    $databaseId = $databases[0]['id'] ?? null;
    
    $tables = [];
    $relationships = [];
    if ($databaseId) {
        $stmt = $db->prepare("SELECT * FROM database_tables WHERE case_database_id = ? ORDER BY display_order");
        $stmt->execute([$databaseId]);
        $tables = $stmt->fetchAll();
        foreach ($tables as &$table) {
            $stmt = $db->prepare("SELECT * FROM database_columns WHERE table_id = ? ORDER BY display_order");
            $stmt->execute([$table['id']]);
            $table['columns'] = $stmt->fetchAll();
        }
        $stmt = $db->prepare("SELECT dt1.table_name as from_table, dc1.column_name as from_column, dt2.table_name as to_table, dc2.column_name as to_column FROM database_columns dc1 JOIN database_tables dt1 ON dt1.id = dc1.table_id JOIN database_columns dc2 ON dc2.column_name = dc1.column_name AND dc2.table_id != dc1.table_id JOIN database_tables dt2 ON dt2.id = dc2.table_id WHERE (dc1.is_primary_key = 1 OR dc2.is_primary_key = 1) AND dt1.case_database_id = ? AND dt2.case_database_id = ?");
        $stmt->execute([$databaseId, $databaseId]);
        $relationships = $stmt->fetchAll();
    }
    
    $stmt = $db->prepare("SELECT * FROM user_case_progress WHERE user_id = ? AND case_id = ?");
    $stmt->execute([$userId, $caseId]);
    $progress = $stmt->fetch();
    if (!$progress) {
        $db->prepare("INSERT INTO user_case_progress (user_id, case_id, current_challenge_id, progress_percentage) VALUES (?, ?, NULL, 0)")->execute([$userId, $caseId]);
        $progress = ['current_challenge_id' => null, 'progress_percentage' => 0, 'hints_used' => 0];
    }
    
    $currentChallenge = null;
    if ($progress['current_challenge_id']) {
        $stmt = $db->prepare("SELECT * FROM challenges WHERE id = ?");
        $stmt->execute([$progress['current_challenge_id']]);
        $currentChallenge = $stmt->fetch();
    } else {
        $stmt = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order LIMIT 1");
        $stmt->execute([$caseId]);
        $currentChallenge = $stmt->fetch();
    }
    
    $stmt = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order");
    $stmt->execute([$caseId]);
    $challenges = $stmt->fetchAll();
    
    $stmt = $db->prepare("SELECT * FROM query_history WHERE user_id = ? AND case_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId, $caseId]);
    $queryHistory = $stmt->fetchAll();
    
    view('detective.workspace', [
        'case' => $case, 'databases' => $databases, 'tables' => $tables, 'relationships' => $relationships,
        'progress' => $progress, 'currentChallenge' => $currentChallenge, 'challenges' => $challenges,
        'queryHistory' => $queryHistory,
    ]);
}

function detective_schema(int $caseId) {
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
        $stmt = $db->prepare("SELECT * FROM database_columns WHERE table_id = ? ORDER BY display_order");
        $stmt->execute([$table['id']]);
        $table['columns'] = $stmt->fetchAll();
    }
    $stmt = $db->prepare("SELECT dt1.table_name as from_table, dc1.column_name as from_column, dt2.table_name as to_table, dc2.column_name as to_column FROM database_columns dc1 JOIN database_tables dt1 ON dt1.id = dc1.table_id JOIN database_columns dc2 ON dc2.column_name = dc1.column_name AND dc2.table_id != dc1.table_id JOIN database_tables dt2 ON dt2.id = dc2.table_id WHERE (dc1.is_primary_key = 1 OR dc2.is_primary_key = 1) AND dt1.case_database_id = ? AND dt2.case_database_id = ?");
    $stmt->execute([$databaseId, $databaseId]);
    json_response(['tables' => $tables, 'relationships' => $stmt->fetchAll()]);
}

function detective_execute_query() {
    require_auth();
    $userId = auth_id();
    $data = $_POST;
    $query = trim($data['query'] ?? '');
    $caseId = (int)($data['case_id'] ?? 0);
    if (!$query) { json_response(['success' => false, 'message' => 'Query is required'], 400); return; }
    
    $qv = new QueryValidator();
    $validation = $qv->validate($query);
    if (!$validation['valid']) {
        log_query($userId, $caseId, $query, 'blocked', 0, 0, $validation['message']);
        json_response(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400);
        return;
    }
    
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
        log_query($userId, $caseId, $query, 'success', $executionTime, $rowCount);
        json_response(['success' => true, 'rows' => $rows, 'row_count' => $rowCount, 'execution_time_ms' => $executionTime, 'truncated' => $rowCount > $maxRows]);
    } catch (PDOException $e) {
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $message = sanitize_error($e->getMessage());
        log_query($userId, $caseId, $query, 'error', $executionTime, 0, $message);
        json_response(['success' => false, 'message' => $message, 'type' => 'sql_error'], 400);
    }
}

function detective_query_history(int $caseId) {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM query_history WHERE user_id = ? AND case_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$userId, $caseId]);
    json_response(['history' => $stmt->fetchAll()]);
}

function detective_submit_challenge(int $caseId, int $challengeId) {
    require_auth();
    $userId = auth_id();
    $data = $_POST;
    $query = trim($data['query'] ?? '');
    if (!$query) { json_response(['success' => false, 'message' => 'Query is required'], 400); return; }
    
    $qv = new QueryValidator();
    $validation = $qv->validate($query);
    if (!$validation['valid']) {
        json_response(['success' => false, 'message' => $validation['message'], 'type' => 'validation_error'], 400);
        return;
    }
    
    $cv = new ChallengeValidator();
    $result = $cv->validate($userId, $challengeId, $query);
    
    if ($result['success'] && ($result['xp_earned'] ?? 0) > 0) {
        award_xp($userId, $result['xp_earned']);
        check_achievements($userId);
    }
    json_response($result);
}

function detective_use_hint(int $caseId, int $hintId) {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM hints WHERE id = ?");
    $stmt->execute([$hintId]);
    $hint = $stmt->fetch();
    if (!$hint) { json_response(['success' => false, 'message' => 'Hint not found'], 404); return; }
    $stmt = $db->prepare("SELECT * FROM hint_usage WHERE user_id = ? AND hint_id = ?");
    $stmt->execute([$userId, $hintId]);
    $existing = $stmt->fetch();
    if ($existing) { json_response(['success' => true, 'hint' => $hint, 'already_used' => true]); return; }
    $db->prepare("INSERT INTO hint_usage (user_id, hint_id) VALUES (?, ?)")->execute([$userId, $hintId]);
    $db->prepare("UPDATE user_case_progress SET hints_used = hints_used + 1 WHERE user_id = ? AND case_id = (SELECT case_id FROM challenges WHERE id = ?)")->execute([$userId, $hint['challenge_id']]);
    json_response(['success' => true, 'hint' => $hint, 'already_used' => false]);
}


