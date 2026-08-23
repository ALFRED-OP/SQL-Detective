<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class AuthMiddleware
{
    public function handle(ServerRequestInterface $request): bool
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['_redirect_after_login'] = $request->getUri()->getPath();
            if ($this->isAjax($request)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Authentication required', 'redirect' => '/login']);
                exit;
            }
            $response = new RedirectResponse('/login');
            $response->getBody()->write('');
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
            http_response_code($response->getStatusCode());
            exit;
        }

        $this->regenerateSessionIfNeeded();
        return true;
    }

    private function isAjax(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->getHeaderLine('Accept'), 'application/json');
    }

    private function regenerateSessionIfNeeded(): void
    {
        $lastRegeneration = $_SESSION['_last_regeneration'] ?? 0;
        if (time() - $lastRegeneration > 300) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }
    }
}