<?php

function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

function config(string $key, $default = null) {
    static $config = [];
    if (empty($config)) {
        $config = [];
        $configDir = PROJECT_ROOT . '/config/';
        $files = glob($configDir . '*.php');
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $config[$name] = require $file;
        }
    }
    $keys = explode('.', $key);
    $value = $config;
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    return $value;
}

function storage_path(string $path = ''): string {
    return PROJECT_ROOT . '/storage/' . ltrim($path, '/');
}

function public_path(string $path = ''): string {
    return PROJECT_ROOT . '/public/' . ltrim($path, '/');
}

function base_path(string $path = ''): string {
    return PROJECT_ROOT . '/' . ltrim($path, '/');
}

function view(string $name, array $data = []): void {
    extract($data, EXTR_SKIP);
    $viewPath = str_replace('.', '/', $name);
    $viewFile = PROJECT_ROOT . "/views/{$viewPath}.php";
    if (!file_exists($viewFile)) {
        abort(404, "View [{$name}] not found.");
    }
    if (strpos($viewPath, 'layouts/') === 0 || strpos($viewPath, 'errors/') === 0) {
        require $viewFile;
        return;
    }
    ob_start();
    require $viewFile;
    $content = ob_get_clean();
    $flash = [
        'message' => get_flash('message'),
        'error' => get_flash('error'),
    ];
    $flash = array_filter($flash);
    require PROJECT_ROOT . '/views/layouts/app.php';
}

function redirect(string $url, int $status = 302): void {
    header("Location: $url", true, $status);
    exit;
}

function back(): void {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    redirect($referer);
}

function abort(int $code, string $message = ''): void {
    http_response_code($code);
    $errorFile = PROJECT_ROOT . "/views/errors/{$code}.php";
    if (file_exists($errorFile)) {
        require $errorFile;
    } else {
        echo "<h1>{$code}</h1><p>" . htmlspecialchars($message) . "</p>";
    }
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(string $token): bool {
    return hash_equals($_SESSION['_token'] ?? '', $token);
}

function old(string $key, $default = '') {
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, $value): void {
    $_SESSION['_flash'][$key] = $value;
}

function flash_now(string $key, $value): void {
    $_SESSION['_flash_now'][$key] = $value;
}

function get_flash(string $key) {
    $value = $_SESSION['_flash'][$key] ?? $_SESSION['_flash_now'][$key] ?? null;
    unset($_SESSION['_flash'][$key], $_SESSION['_flash_now'][$key]);
    return $value;
}

function has_flash(string $key): bool {
    return isset($_SESSION['_flash'][$key]) || isset($_SESSION['_flash_now'][$key]);
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dd(...$vars): void {
    foreach ($vars as $var) {
        var_dump($var);
    }
    die;
}

function logger(string $level, string $message, array $context = []): void {
    $logPath = storage_path('logs/app-' . date('Y-m-d') . '.log');
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $entry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
}

function log_info(string $message, array $context = []): void {
    logger('INFO', $message, $context);
}

function log_warning(string $message, array $context = []): void {
    logger('WARNING', $message, $context);
}

function log_error(string $message, array $context = []): void {
    logger('ERROR', $message, $context);
}

function log_debug(string $message, array $context = []): void {
    if (config('app.debug')) {
        logger('DEBUG', $message, $context);
    }
}

function auth_user() {
    return $_SESSION['user'] ?? null;
}

function auth_check(): bool {
    return !empty($_SESSION['user']);
}

function auth_id() {
    return $_SESSION['user']['id'] ?? null;
}

function guest(): bool {
    return !auth_check();
}

function route(string $name, array $params = []): string {
    $routes = [
        'home' => '/',
        'how-it-works' => '/how-it-works',
        'login' => '/auth/login',
        'login.post' => '/auth/login',
        'register' => '/auth/register',
        'register.post' => '/auth/register',
        'logout' => '/auth/logout',
        'password.request' => '/auth/password/reset',
        'password.email' => '/auth/password/reset',
        'password.update' => '/auth/password/reset',
        'verification.verify' => '/auth/verify-email',
        'dashboard' => '/dashboard',
        'dashboard.recent-queries' => '/dashboard/recent-queries',
        'cases' => '/cases',
        'cases.show' => '/cases/{case}',
        'cases.briefing' => '/cases/{case}/briefing',
        'cases.evidence' => '/cases/{case}/evidence',
        'cases.suspects' => '/cases/{case}/suspects',
        'detective.workspace' => '/detective/{case}',
        'profile' => '/profile',
        'profile.achievements' => '/profile/achievements',
        'profile.settings' => '/profile/settings',
        'profile.update' => '/profile',
        'profile.password' => '/profile/password',
        'leaderboard' => '/leaderboard',
        'achievements' => '/achievements',
        'admin.dashboard' => '/admin',
        'admin.users' => '/admin/users',
        'admin.cases' => '/admin/cases',
        'admin.cases.create' => '/admin/cases/create',
        'admin.cases.store' => '/admin/cases',
        'admin.cases.edit' => '/admin/cases/{case}/edit',
        'admin.cases.update' => '/admin/cases/{case}',
        'admin.challenges' => '/admin/challenges',
        'admin.challenges.create' => '/admin/challenges/create',
        'admin.challenges.store' => '/admin/challenges',
        'admin.evidence' => '/admin/evidence',
        'admin.suspects' => '/admin/suspects',
        'admin.hints' => '/admin/hints',
        'admin.achievements' => '/admin/achievements',
        'admin.submissions' => '/admin/submissions',
        'admin.logs' => '/admin/logs',
        'admin.stats' => '/admin/stats',
    ];
    $path = $routes[$name] ?? '/';
    foreach ($params as $key => $value) {
        $path = str_replace('{' . $key . '}', $value, $path);
    }
    return $path;
}

function asset(string $path): string {
    return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
}

function str_slug(string $string): string {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

function generate_token(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

function hash_password(string $password): string {
    return password_hash($password, config('security.password.hash_algo'), config('security.password.hash_options'));
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function sanitize_filename(string $filename): string {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

function format_bytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $mins = floor($diff / 60);
        return "$mins minute" . ($mins > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "$hours hour" . ($hours > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return "$days day" . ($days > 1 ? 's' : '') . ' ago';
    }
    return date('M j, Y', $time);
}

function is_ajax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validate(array $data, array $rules, array $messages = []): array {
    try {
        $validator = new Validator($data, $rules, $messages);
        return $validator->validate();
    } catch (ValidationException $e) {
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors], 422);
        }
        abort(422, 'Validation failed: ' . implode(', ', array_map(fn($errs) => implode(' ', $errs), $e->errors)));
    }
}

function rate_limit(string $key, int $max, int $decayMinutes): bool {
    $cacheKey = "ratelimit:$key";
    $cacheFile = storage_path("cache/$cacheKey.json");

    $now = time();
    $window = $decayMinutes * 60;

    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?? [];
    }

    $data = array_filter($data, fn($timestamp) => $now - $timestamp < $window);

    if (count($data) >= $max) {
        return false;
    }

    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    return true;
}

function rate_limit_remaining(string $key, int $max, int $decayMinutes): int {
    $cacheKey = "ratelimit:$key";
    $cacheFile = storage_path("cache/$cacheKey.json");

    $now = time();
    $window = $decayMinutes * 60;

    $data = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true) ?? [];
    }

    $data = array_filter($data, fn($timestamp) => $now - $timestamp < $window);
    return max(0, $max - count($data));
}
