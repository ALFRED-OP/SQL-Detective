<?php

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

function config(string $key, mixed $default = null): mixed
{
    static $config = [];
    if (empty($config)) {
        $config = [];
        $configDir = __DIR__ . '/../../config/';
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

function storage_path(string $path = ''): string
{
    return __DIR__ . '/../../storage/' . ltrim($path, '/');
}

function public_path(string $path = ''): string
{
    return __DIR__ . '/../../public/' . ltrim($path, '/');
}

function base_path(string $path = ''): string
{
    return __DIR__ . '/../../' . ltrim($path, '/');
}

function view(string $name, array $data = []): void
{
    $view = new \App\Core\View();
    echo $view->render($name, $data);
}

function redirect(string $url, int $status = 302): void
{
    header("Location: $url", true, $status);
    exit;
}

function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    redirect($referer);
}

function abort(int $code, string $message = ''): void
{
    http_response_code($code);
    view("errors/$code", ['message' => $message]);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_token'])) {
        $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['_token'] ?? '', $token);
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_now(string $key, mixed $value): void
{
    $_SESSION['_flash_now'][$key] = $value;
}

function get_flash(string $key): mixed
{
    $value = $_SESSION['_flash'][$key] ?? $_SESSION['_flash_now'][$key] ?? null;
    unset($_SESSION['_flash'][$key], $_SESSION['_flash_now'][$key]);
    return $value;
}

function has_flash(string $key): bool
{
    return isset($_SESSION['_flash'][$key]) || isset($_SESSION['_flash_now'][$key]);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dd(mixed ...$vars): void
{
    foreach ($vars as $var) {
        var_dump($var);
    }
    die;
}

function logger(string $level, string $message, array $context = []): void
{
    $logPath = storage_path('logs/app-' . date('Y-m-d') . '.log');
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $entry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
}

function log_info(string $message, array $context = []): void
{
    logger('INFO', $message, $context);
}

function log_warning(string $message, array $context = []): void
{
    logger('WARNING', $message, $context);
}

function log_error(string $message, array $context = []): void
{
    logger('ERROR', $message, $context);
}

function log_debug(string $message, array $context = []): void
{
    if (config('app.debug')) {
        logger('DEBUG', $message, $context);
    }
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_check(): bool
{
    return !empty($_SESSION['user']);
}

function auth_id(): ?int
{
    return $_SESSION['user']['id'] ?? null;
}

function guest(): bool
{
    return !auth_check();
}

function route(string $name, array $params = []): string
{
    $router = \App\Core\Router::getInstance();
    return $router->url($name, $params);
}

function asset(string $path): string
{
    return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
}

function str_slug(string $string): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

function generate_token(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

function hash_password(string $password): string
{
    return password_hash($password, config('security.password.hash_algo'), config('security.password.hash_options'));
}

function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function sanitize_filename(string $filename): string
{
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

function format_bytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function time_ago(string $datetime): string
{
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

function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validate(array $data, array $rules, array $messages = []): array
{
    $validator = new \App\Validators\Validator($data, $rules, $messages);
    return $validator->validate();
}

function rate_limit(string $key, int $max, int $decayMinutes): bool
{
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

function rate_limit_remaining(string $key, int $max, int $decayMinutes): int
{
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