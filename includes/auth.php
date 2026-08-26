<?php

function require_auth() {
    if (empty($_SESSION['user'])) {
        $_SESSION['_redirect_after_login'] = $_SERVER['REQUEST_URI'];
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Authentication required', 'redirect' => '/auth/login'], 401);
        }
        redirect('/auth/login');
    }
    $lastRegen = $_SESSION['_last_regeneration'] ?? 0;
    if (time() - $lastRegen > 300) {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }
}

function require_admin() {
    require_auth();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        abort(403, 'Admin access required.');
    }
}

function require_guest() {
    if (!empty($_SESSION['user'])) {
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Already authenticated', 'redirect' => '/dashboard'], 403);
        }
        redirect('/dashboard');
    }
}
