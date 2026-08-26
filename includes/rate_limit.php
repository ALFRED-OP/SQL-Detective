<?php

function rate_limit_check(string $key): void {
    $config = config("security.rate_limiting.$key", []);
    $max = $config['max_attempts'] ?? 60;
    $decay = $config['decay_minutes'] ?? 1;

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ip = explode(',', $ip)[0];
    $userId = $_SESSION['user']['id'] ?? 'anonymous';
    $cacheKey = "ratelimit:$key:$ip:$userId";
    $cacheFile = storage_path("cache/$cacheKey.json");

    $now = time();
    $window = $decay * 60;
    $data = [];

    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?? [];
    }

    $data = array_values(array_filter($data, fn($t) => $now - $t < $window));

    if (count($data) >= $max) {
        $retryAfter = max(1, min($data) + $window - $now);
        if (is_ajax()) {
            http_response_code(429);
            header('Content-Type: application/json');
            header("Retry-After: $retryAfter");
            echo json_encode(['success' => false, 'message' => 'Too many requests.', 'retry_after' => $retryAfter]);
            exit;
        }
        abort(429, 'Too many requests. Please wait.');
    }

    $data[] = $now;

    $dir = dirname($cacheFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($cacheFile, json_encode($data), LOCK_EX);

    $remaining = max(0, $max - count($data));
    header("X-RateLimit-Limit: $max");
    header("X-RateLimit-Remaining: $remaining");
    header("X-RateLimit-Reset: " . (min($data) + $window));
}
