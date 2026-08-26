<?php
require_once __DIR__ . '/../includes/init.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$uri = rtrim($uri, '/') ?: '/';

// Support _method override for PATCH/DELETE via HTML forms
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
    if (!in_array($method, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
        $method = 'POST';
    }
}

// --- HEALTH ---
if ($uri === '/health') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

// --- DIAGNOSTIC (remove after debugging) ---
if ($uri === '/diag') {
    header('Content-Type: text/plain');
    echo "URI: {$uri}\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "PHP SAPI: " . php_sapi_name() . "\n";
    echo "PHP VERSION: " . phpversion() . "\n";
    echo "PROJECT_ROOT: " . PROJECT_ROOT . "\n";
    echo "Session active: " . (session_status() === PHP_SESSION_ACTIVE ? 'yes' : 'no') . "\n";
    echo "Storage writable: " . (is_writable(storage_path()) ? 'yes' : 'no') . "\n";
    $envExists = file_exists(PROJECT_ROOT . '/.env');
    echo ".env exists: " . ($envExists ? 'yes' : 'no') . "\n";
    if ($envExists) {
        $dbUser = env('DB_USER', '(not set)');
        $dbRoot = env('DB_ROOT_USER', '(not set)');
        echo "DB_USER: {$dbUser}\n";
        echo "DB_ROOT_USER: {$dbRoot}\n";
        echo "DB_NAME: " . env('DB_NAME', '(not set)') . "\n";
    }
    try {
        $db = db();
        echo "DB connection: OK\n";
        $tables = [];
        $rows = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) { $tables[] = $row[0]; }
        echo "Tables: " . implode(', ', $tables) . "\n";
    } catch (\Throwable $e) {
        echo "DB connection FAILED: " . $e->getMessage() . "\n";
    }
    exit;
}

// --- HOME ---
if ($uri === '/' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/home.php';
    home_index();
    exit;
}
if ($uri === '/how-it-works' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/home.php';
    home_how_it_works();
    exit;
}

// --- AUTH ---
if ($uri === '/auth/login' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_show_login();
    exit;
}
if ($uri === '/auth/login' && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('login');
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_login();
    exit;
}
if ($uri === '/auth/register' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_show_register();
    exit;
}
if ($uri === '/auth/register' && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('registration');
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_register();
    exit;
}
if ($uri === '/auth/logout' && $method === 'POST') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_logout();
    exit;
}
if ($uri === '/auth/verify-email' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_show_verify_email();
    exit;
}
if ($uri === '/auth/verify-email' && $method === 'POST') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_verify_email();
    exit;
}
if ($uri === '/auth/password/reset' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_show_forgot_password();
    exit;
}
if ($uri === '/auth/password/reset' && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('login');
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_send_reset_link();
    exit;
}
if (preg_match('#^/auth/password/reset/([\w\-]+)$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_show_reset_password($m[1]);
    exit;
}
if (preg_match('#^/auth/password/reset/([\w\-]+)$#', $uri, $m) && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('login');
    require PROJECT_ROOT . '/controllers/auth.php';
    auth_reset_password($m[1]);
    exit;
}

// --- DASHBOARD ---
if ($uri === '/dashboard' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/dashboard.php';
    dashboard_index();
    exit;
}
if (preg_match('#^/dashboard/continue/(\d+)$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/dashboard.php';
    dashboard_continue_case((int)$m[1]);
    exit;
}
if ($uri === '/dashboard/xp-history' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/dashboard.php';
    dashboard_xp_history();
    exit;
}
if ($uri === '/dashboard/recent-queries' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/dashboard.php';
    dashboard_recent_queries();
    exit;
}
if ($uri === '/dashboard/stats' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/dashboard.php';
    dashboard_stats();
    exit;
}

// --- CASES ---
if ($uri === '/cases' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/cases.php';
    cases_index();
    exit;
}
if (preg_match('#^/cases/(\d+)$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/cases.php';
    cases_show((int)$m[1]);
    exit;
}
if (preg_match('#^/cases/(\d+)/evidence$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/cases.php';
    cases_evidence((int)$m[1]);
    exit;
}
if (preg_match('#^/cases/(\d+)/suspects$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/cases.php';
    cases_suspects((int)$m[1]);
    exit;
}
if (preg_match('#^/cases/(\d+)/briefing$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/cases.php';
    cases_briefing((int)$m[1]);
    exit;
}
if (preg_match('#^/cases/(\d+)/progress$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/cases.php';
    cases_progress((int)$m[1]);
    exit;
}

// --- DETECTIVE ---
if (preg_match('#^/detective/(\d+)$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/detective.php';
    detective_workspace((int)$m[1]);
    exit;
}
if (preg_match('#^/detective/(\d+)/schema$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/detective.php';
    detective_schema((int)$m[1]);
    exit;
}
if ($uri === '/detective/query/execute' && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('query_execution');
    require PROJECT_ROOT . '/controllers/detective.php';
    detective_execute_query();
    exit;
}
if (preg_match('#^/detective/(\d+)/history$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/detective.php';
    detective_query_history((int)$m[1]);
    exit;
}
if (preg_match('#^/detective/(\d+)/challenge/(\d+)$#', $uri, $m) && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('challenge_submission');
    require PROJECT_ROOT . '/controllers/detective.php';
    detective_submit_challenge((int)$m[1], (int)$m[2]);
    exit;
}
if (preg_match('#^/detective/(\d+)/hint/(\d+)$#', $uri, $m) && $method === 'POST') {
    require PROJECT_ROOT . '/controllers/detective.php';
    detective_use_hint((int)$m[1], (int)$m[2]);
    exit;
}

// --- PROFILE ---
if ($uri === '/profile' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/profile.php';
    profile_index();
    exit;
}
if ($uri === '/profile' && $method === 'PATCH') {
    require PROJECT_ROOT . '/controllers/profile.php';
    profile_update();
    exit;
}
if ($uri === '/profile/password' && $method === 'PATCH') {
    require PROJECT_ROOT . '/controllers/profile.php';
    profile_update_password();
    exit;
}
if ($uri === '/profile/achievements' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/profile.php';
    profile_achievements();
    exit;
}
if ($uri === '/profile/settings' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/profile.php';
    profile_settings();
    exit;
}

// --- LEADERBOARD ---
if ($uri === '/leaderboard' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/leaderboard.php';
    leaderboard_index();
    exit;
}
if ($uri === '/leaderboard/api' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/leaderboard.php';
    leaderboard_api();
    exit;
}

// --- ACHIEVEMENTS ---
if ($uri === '/achievements' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/achievements.php';
    achievements_index();
    exit;
}

// --- ADMIN ---
if ($uri === '/admin' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_dashboard();
    exit;
}
if ($uri === '/admin/users' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_users();
    exit;
}
if (preg_match('#^/admin/users/(\d+)/toggle$#', $uri, $m) && $method === 'POST') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_toggle_user((int)$m[1]);
    exit;
}
if ($uri === '/admin/cases' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_cases();
    exit;
}
if ($uri === '/admin/cases/create' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_create_case();
    exit;
}
if ($uri === '/admin/cases' && $method === 'POST') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_store_case();
    exit;
}
if (preg_match('#^/admin/cases/(\d+)/edit$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_edit_case((int)$m[1]);
    exit;
}
if (preg_match('#^/admin/cases/(\d+)$#', $uri, $m) && $method === 'PATCH') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_update_case((int)$m[1]);
    exit;
}
if (preg_match('#^/admin/cases/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_delete_case((int)$m[1]);
    exit;
}
if ($uri === '/admin/challenges' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_challenges();
    exit;
}
if ($uri === '/admin/challenges/create' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_create_challenge();
    exit;
}
if ($uri === '/admin/challenges' && $method === 'POST') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_store_challenge();
    exit;
}
if ($uri === '/admin/evidence' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_evidence();
    exit;
}
if ($uri === '/admin/suspects' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_suspects();
    exit;
}
if ($uri === '/admin/hints' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_hints();
    exit;
}
if ($uri === '/admin/achievements' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_achievements_list();
    exit;
}
if ($uri === '/admin/submissions' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_submissions();
    exit;
}
if ($uri === '/admin/logs' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_logs();
    exit;
}
if ($uri === '/admin/stats' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/admin.php';
    admin_stats();
    exit;
}

// --- API ---
if ($uri === '/api/query/execute' && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('query_execution');
    require PROJECT_ROOT . '/controllers/api.php';
    api_execute_query();
    exit;
}
if (preg_match('#^/api/cases/(\d+)/schema$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/api.php';
    api_case_schema((int)$m[1]);
    exit;
}
if (preg_match('#^/api/cases/(\d+)/evidence$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/api.php';
    api_case_evidence((int)$m[1]);
    exit;
}
if (preg_match('#^/api/cases/(\d+)/challenges$#', $uri, $m) && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/api.php';
    api_case_challenges((int)$m[1]);
    exit;
}
if (preg_match('#^/api/challenges/(\d+)/submit$#', $uri, $m) && $method === 'POST') {
    require PROJECT_ROOT . '/includes/rate_limit.php';
    rate_limit_check('challenge_submission');
    require PROJECT_ROOT . '/controllers/api.php';
    api_submit_challenge((int)$m[1]);
    exit;
}
if ($uri === '/api/leaderboard' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/api.php';
    api_leaderboard();
    exit;
}
if ($uri === '/api/profile' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/api.php';
    api_profile();
    exit;
}
if ($uri === '/api/achievements' && $method === 'GET') {
    require PROJECT_ROOT . '/controllers/api.php';
    api_achievements();
    exit;
}

// --- 404 ---
http_response_code(404);
if (is_ajax()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Page not found']);
} else {
    view('errors/404', ['message' => 'Page not found']);
}
exit;
