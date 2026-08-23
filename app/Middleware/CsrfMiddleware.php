<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class CsrfMiddleware
{
    public function handle(ServerRequestInterface $request): bool
    {
        if (!config('security.csrf.enabled')) {
            return true;
        }

        $method = $request->getMethod();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $except = config('security.csrf.except', []);
        $path = $request->getUri()->getPath();
        foreach ($except as $pattern) {
            if ($this->matchPattern($pattern, $path)) {
                return true;
            }
        }

        $token = $request->getHeaderLine(config('security.csrf.header_name'));
        if (!$token) {
            $parsedBody = $request->getParsedBody();
            $token = $parsedBody[config('security.csrf.token_name')] ?? '';
        }

        if (!$this->verifyToken($token)) {
            if ($this->isAjax($request)) {
                http_response_code(419);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'CSRF token mismatch. Please refresh the page.',
                    'code' => 'CSRF_TOKEN_MISMATCH',
                ]);
                exit;
            }
            http_response_code(419);
            header('Content-Type: text/html');
            echo '<html><body><h1>419 - CSRF Token Mismatch</h1><p>Please refresh the page and try again.</p></body></html>';
            exit;
        }

        return true;
    }

    private function verifyToken(string $token): bool
    {
        $sessionToken = $_SESSION['_token'] ?? '';
        return hash_equals($sessionToken, $token);
    }

    private function isAjax(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->getHeaderLine('Accept'), 'application/json');
    }

    private function matchPattern(string $pattern, string $path): bool
    {
        $regex = str_replace('*', '.*', preg_quote($pattern, '#'));
        return (bool)preg_match('#^' . $regex . '$#', $path);
    }
}