<?php
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

function env_load($file) {
    if (!file_exists($file)) return;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), '"\'');
        if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
env_load(PROJECT_ROOT . '/.env');

require_once PROJECT_ROOT . '/includes/helpers.php';
require_once PROJECT_ROOT . '/includes/auth.php';

if (php_sapi_name() !== 'cli') {
    $sessionConfig = config('security.session');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
    ini_set('session.cookie_samesite', $sessionConfig['same_site']);
    ini_set('session.gc_maxlifetime', (string)$sessionConfig['lifetime'] * 60);
    ini_set('session.cookie_lifetime', (string)$sessionConfig['lifetime'] * 60);
    if ($sessionConfig['domain']) {
        ini_set('session.cookie_domain', $sessionConfig['domain']);
    }
    session_name($sessionConfig['cookie']);
    $sessionPath = $sessionConfig['files'];
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    session_save_path($sessionPath);
    if (!is_dir(storage_path('logs'))) {
        mkdir(storage_path('logs'), 0755, true);
    }
    if (!is_dir(storage_path('cache'))) {
        mkdir(storage_path('cache'), 0755, true);
    }
    session_start();
}

$headers = config('security.headers', []);
foreach ($headers as $name => $value) {
    header("$name: $value");
}

if (!config('app.debug')) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', storage_path('logs/php-errors.log'));
} else {
    ini_set('display_errors', '1');
}
error_reporting(E_ALL);
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

require_once PROJECT_ROOT . '/includes/db.php';
require_once PROJECT_ROOT . '/includes/validator.php';
require_once PROJECT_ROOT . '/includes/csrf.php';

csrf_check();
