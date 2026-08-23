<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class AdminMiddleware
{
    public function handle(ServerRequestInterface $request): bool
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['_redirect_after_login'] = $request->getUri()->getPath();
            $response = new RedirectResponse('/login');
            $this->sendResponse($response);
            return false;
        }

        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            header('Content-Type: text/html');
            echo '<html><body><h1>403 - Forbidden</h1><p>Admin access required.</p></body></html>';
            exit;
        }

        return true;
    }

    private function sendResponse(\Laminas\Diactoros\Response\ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        http_response_code($response->getStatusCode());
        echo $response->getBody();
    }
}