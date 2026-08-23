<?php

namespace App;

class Application
{
    private static ?self $instance = null;
    private array $config = [];
    private ?\PDO $db = null;
    private ?\PDO $investigationDb = null;
    private ?Router $router = null;

    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->initErrorHandling();
        $this->initSession();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');
                if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    private function loadConfiguration(): void
    {
        $configDir = base_path('config/');
        $files = glob($configDir . '*.php');
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $this->config[$name] = require $file;
        }
    }

    private function initErrorHandling(): void
    {
        if (!config('app.debug')) {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', storage_path('logs/php-errors.log'));
        } else {
            ini_set('display_errors', '1');
        }

        error_reporting(E_ALL);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    private function initSession(): void
    {
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
        session_save_path($sessionConfig['files']);
        session_start();
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public function handleException(\Throwable $e): void
    {
        log_error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $code = $e instanceof \HttpException ? $e->getCode() : 500;

        if (is_ajax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            json_response([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
                'errors' => config('app.debug') ? [$e->getTraceAsString()] : [],
            ], $code);
            return;
        }

        http_response_code($code);
        view("errors/$code", [
            'message' => config('app.debug') ? $e->getMessage() : $this->getErrorMessage($code),
            'exception' => config('app.debug') ? $e : null,
        ]);
    }

    public function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    private function getErrorMessage(int $code): string
    {
        return match ($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Case File Not Found',
            405 => 'Method Not Allowed',
            419 => 'Session Expired',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
            default => 'An Error Occurred',
        };
    }

    public function db(): \PDO
    {
        if ($this->db === null) {
            $this->db = $this->createConnection(config('database.connections.mysql'));
        }
        return $this->db;
    }

    public function investigationDb(): \PDO
    {
        if ($this->investigationDb === null) {
            $this->investigationDb = $this->createConnection(config('database.connections.investigation'));
        }
        return $this->investigationDb;
    }

    private function createConnection(array $config): \PDO
    {
        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new \PDO($dsn, $config['username'], $config['password'], $config['options'] ?? []);
        return $pdo;
    }

    public function router(): Router
    {
        if ($this->router === null) {
            $this->router = new Router();
        }
        return $this->router;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        return $value;
    }

    public function run(): void
    {
        $this->router()->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }
}