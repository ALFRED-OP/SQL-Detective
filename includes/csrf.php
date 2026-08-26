<?php

function csrf_check() {
    if (!config('security.csrf.enabled')) return;

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) return;

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $except = config('security.csrf.except', []);
    foreach ($except as $pattern) {
        $regex = str_replace('*', '.*', preg_quote($pattern, '#'));
        if (preg_match('#^' . $regex . '$#', $path)) return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['_token'] ?? '', $token)) {
        if (is_ajax()) {
            http_response_code(419);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.', 'code' => 'CSRF_TOKEN_MISMATCH']);
            exit;
        }
        abort(419, 'CSRF token mismatch. Please refresh the page.');
    }
}
