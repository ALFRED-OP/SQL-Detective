<?php
function auth_show_login() {
    require_guest();
    view('auth.login');
}

function auth_login() {
    require_guest();
    $data = $_POST;
    $validated = validate($data, [
        'email' => 'required|email',
        'password' => 'required',
    ]);
    $db = db();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$validated['email']]);
    $user = $stmt->fetch();
    if (!$user || !verify_password($validated['password'], $user['password_hash'])) {
        audit_log('login_failed', ['email' => $validated['email']]);
        json_response(['success' => false, 'message' => 'Invalid credentials'], 401);
    }
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'],
        'display_name' => $user['display_name'], 'xp' => $user['xp'], 'level' => $user['level'],
        'detective_rank' => $user['detective_rank'], 'role' => $user['role'],
    ];
    $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);
    audit_log('login_success', ['user_id' => $user['id']]);
    $redirect = $_SESSION['_redirect_after_login'] ?? '/dashboard';
    unset($_SESSION['_redirect_after_login']);
    json_response(['success' => true, 'redirect' => $redirect]);
}

function auth_show_register() {
    require_guest();
    view('auth.register');
}

function auth_register() {
    require_guest();
    $data = $_POST;
    $validated = validate($data, [
        'username' => 'required|alpha_dash|min:3|max:50|unique:users,username',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|password|confirmed',
        'display_name' => 'required|min:2|max:100',
    ]);
    $db = db();
    $db->prepare("INSERT INTO users (username, email, password_hash, display_name, role, status, email_verified_at) VALUES (?, ?, ?, ?, 'user', 'active', NOW())")
        ->execute([$validated['username'], $validated['email'], hash_password($validated['password']), $validated['display_name']]);
    $userId = $db->lastInsertId();
    audit_log('registration', ['user_id' => $userId, 'email' => $validated['email']]);
    json_response(['success' => true, 'message' => 'Registration successful', 'redirect' => '/auth/login']);
}

function auth_logout() {
    $userId = $_SESSION['user']['id'] ?? null;
    if ($userId) {
        audit_log('logout', ['user_id' => $userId]);
    }
    session_destroy();
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    redirect('/auth/login');
}

function auth_show_verify_email() { view('auth.verify-email'); }
function auth_verify_email() { json_response(['success' => true, 'message' => 'Email verification not implemented yet']); }
function auth_show_forgot_password() { view('auth.forgot-password'); }
function auth_send_reset_link() { json_response(['success' => true, 'message' => 'Password reset not implemented yet']); }
function auth_show_reset_password($token) { view('auth.reset-password', ['token' => $token]); }
function auth_reset_password($token) { json_response(['success' => true, 'message' => 'Password reset not implemented yet']); }

function audit_log(string $action, array $metadata = []): void {
    try {
        $db = db();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ipHash = hash('sha256', $ip);
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $db->prepare("INSERT INTO audit_logs (user_id, action, ip_hash, user_agent, metadata) VALUES (?, ?, ?, ?, ?)")
            ->execute([$_SESSION['user']['id'] ?? null, $action, $ipHash, $userAgent, json_encode($metadata)]);
    } catch (\Exception $e) {
        // Don't let audit log failures break the app
    }
}