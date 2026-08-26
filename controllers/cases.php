<?php
function cases_index() {
    require_auth();
    $userId = auth_id();
    $db = db();
    
    $difficulty = $_GET['difficulty'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $where = ['c.status = ?'];
    $params = ['active'];
    
    if ($difficulty) { $where[] = 'c.difficulty = ?'; $params[] = $difficulty; }
    if ($category) { $where[] = 'c.category = ?'; $params[] = $category; }
    if ($search) {
        $where[] = '(c.title LIKE ? OR c.description LIKE ? OR c.case_code LIKE ?)';
        $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    }
    $whereSql = implode(' AND ', $where);
    
    if ($status === 'completed') {
        $stmt = $db->prepare("SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned, COUNT(DISTINCT ch.id) as challenge_count FROM cases c LEFT JOIN challenges ch ON ch.case_id = c.id LEFT JOIN user_case_progress ucp ON ucp.case_id = c.id AND ucp.user_id = ? WHERE $whereSql AND ucp.completed = 1 GROUP BY c.id ORDER BY ucp.completed_at DESC");
    } elseif ($status === 'in_progress') {
        $stmt = $db->prepare("SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned, COUNT(DISTINCT ch.id) as challenge_count FROM cases c LEFT JOIN challenges ch ON ch.case_id = c.id LEFT JOIN user_case_progress ucp ON ucp.case_id = c.id AND ucp.user_id = ? WHERE $whereSql AND ucp.completed = 0 AND ucp.id IS NOT NULL GROUP BY c.id ORDER BY ucp.updated_at DESC");
    } else {
        $stmt = $db->prepare("SELECT c.*, ucp.progress_percentage, ucp.completed, ucp.completed_at, ucp.xp_earned, COUNT(DISTINCT ch.id) as challenge_count FROM cases c LEFT JOIN challenges ch ON ch.case_id = c.id LEFT JOIN user_case_progress ucp ON ucp.case_id = c.id AND ucp.user_id = ? WHERE $whereSql GROUP BY c.id ORDER BY c.difficulty ASC, c.id ASC");
    }
    $stmt->execute(array_merge([$userId], $params));
    $cases = $stmt->fetchAll();
    
    $categories = $db->query("SELECT DISTINCT category FROM cases WHERE status = 'active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
    
    view('cases.index', ['cases' => $cases, 'categories' => $categories, 'filters' => compact('difficulty', 'category', 'status', 'search')]);
}

function cases_show(int $caseId) {
    require_auth();
    $db = db();
    $userId = auth_id();
    
    $stmt = $db->prepare("SELECT c.*, COUNT(DISTINCT ch.id) as challenge_count FROM cases c LEFT JOIN challenges ch ON ch.case_id = c.id WHERE c.id = ? AND c.status = 'active' GROUP BY c.id");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) abort(404);
    
    $stmt = $db->prepare("SELECT * FROM user_case_progress WHERE user_id = ? AND case_id = ?");
    $stmt->execute([$userId, $caseId]);
    $progress = $stmt->fetch();
    
    $stmt = $db->prepare("SELECT * FROM suspects WHERE case_id = ? ORDER BY id");
    $stmt->execute([$caseId]);
    $suspects = $stmt->fetchAll();
    
    $stmt = $db->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id");
    $stmt->execute([$caseId]);
    $evidence = $stmt->fetchAll();
    
    $stmt = $db->prepare("SELECT * FROM challenges WHERE case_id = ? ORDER BY display_order");
    $stmt->execute([$caseId]);
    $challenges = $stmt->fetchAll();
    
    view('cases.show', ['case' => $case, 'progress' => $progress, 'suspects' => $suspects, 'evidence' => $evidence, 'challenges' => $challenges]);
}

function cases_evidence(int $caseId) {
    require_auth();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) abort(404);
    $stmt = $db->prepare("SELECT * FROM evidence WHERE case_id = ? ORDER BY importance DESC, id");
    $stmt->execute([$caseId]);
    view('cases.evidence', ['case' => $case, 'evidence' => $stmt->fetchAll()]);
}

function cases_suspects(int $caseId) {
    require_auth();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) abort(404);
    $stmt = $db->prepare("SELECT * FROM suspects WHERE case_id = ? ORDER BY id");
    $stmt->execute([$caseId]);
    view('cases.suspects', ['case' => $case, 'suspects' => $stmt->fetchAll()]);
}

function cases_briefing(int $caseId) {
    require_auth();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    if (!$case) abort(404);
    view('cases.briefing', ['case' => $case]);
}

function cases_progress(int $caseId) {
    require_auth();
    $userId = auth_id();
    $db = db();
    $stmt = $db->prepare("SELECT * FROM user_case_progress WHERE user_id = ? AND case_id = ?");
    $stmt->execute([$userId, $caseId]);
    json_response(['progress' => $stmt->fetch()]);
}
