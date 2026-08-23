<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class RateLimitMiddleware
{
    private string $limiter;
    private int $maxAttempts;
    private int $decayMinutes;
    private string $keyPrefix;

    public function __construct(string $limiter = 'login')
    {
        $this->limiter = $limiter;
        $config = config("security.rate_limiting.$limiter", []);
        $this->maxAttempts = $config['max_attempts'] ?? 60;
        $this->decayMinutes = $config['decay_minutes'] ?? 1;
        $this->keyPrefix = "ratelimit:$limiter:";
    }

    public function handle(ServerRequestInterface $request): bool
    {
        $key = $this->getKey($request);
        $allowed = $this->checkLimit($key);

        $remaining = $this->getRemaining($key);
        $resetTime = $this->getResetTime($key);

        $this->setHeaders($remaining, $resetTime);

        if (!$allowed) {
            $retryAfter = $this->getRetryAfter($resetTime);
            if ($this->isAjax($request)) {
                http_response_code(429);
                header('Content-Type: application/json');
                header("Retry-After: $retryAfter");
                echo json_encode([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $retryAfter,
                ]);
                exit;
            }
            http_response_code(429);
            header('Content-Type: text/html');
            header("Retry-After: $retryAfter");
            echo '<html><body><h1>429 - Too Many Requests</h1><p>Please wait before trying again.</p></body></html>';
            exit;
        }

        return true;
    }

    private function getKey(ServerRequestInterface $request): string
    {
        $ip = $this->getClientIp($request);
        $userId = $_SESSION['user']['id'] ?? 'anonymous';
        return $this->keyPrefix . $ip . ':' . $userId;
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $headers = ['X-Forwarded-For', 'X-Real-IP', 'Client-IP'];
        foreach ($headers as $header) {
            $value = $request->getHeaderLine($header);
            if ($value) {
                return explode(',', $value)[0];
            }
        }
        return $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
    }

    private function checkLimit(string $key): bool
    {
        $cacheFile = storage_path("cache/$key.json");
        $now = time();
        $window = $this->decayMinutes * 60;

        $data = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true) ?? [];
        }

        $data = array_filter($data, fn($timestamp) => $now - $timestamp < $window);

        if (count($data) >= $this->maxAttempts) {
            return false;
        }

        $data[] = $now;
        file_put_contents($cacheFile, json_encode($data), LOCK_EX);
        return true;
    }

    private function getRemaining(string $key): int
    {
        $cacheFile = storage_path("cache/$key.json");
        $now = time();
        $window = $this->decayMinutes * 60;

        $data = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true) ?? [];
        }

        $data = array_filter($data, fn($timestamp) => $now - $timestamp < $window);
        return max(0, $this->maxAttempts - count($data));
    }

    private function getResetTime(string $key): int
    {
        $cacheFile = storage_path("cache/$key.json");
        $now = time();
        $window = $this->decayMinutes * 60;

        $data = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true) ?? [];
        }

        $data = array_filter($data, fn($timestamp) => $now - $timestamp < $window);
        if (empty($data)) {
            return $now + $window;
        }
        return min($data) + $window;
    }

    private function getRetryAfter(int $resetTime): int
    {
        return max(1, $resetTime - time());
    }

    private function setHeaders(int $remaining, int $resetTime): void
    {
        header("X-RateLimit-Limit: {$this->maxAttempts}");
        header("X-RateLimit-Remaining: $remaining");
        header("X-RateLimit-Reset: $resetTime");
    }

    private function isAjax(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->getHeaderLine('Accept'), 'application/json');
    }
}