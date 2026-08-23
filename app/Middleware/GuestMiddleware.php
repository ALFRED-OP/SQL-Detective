<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class GuestMiddleware
{
    public function handle(ServerRequestInterface $request): bool
    {
        if (!empty($_SESSION['user'])) {
            if ($this->isAjax($request)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Already authenticated', 'redirect' => '/dashboard']);
                exit;
            }
            $response = new RedirectResponse('/dashboard');
            $this->sendResponse($response);
            exit;
        }
        return true;
    }

    private function isAjax(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->getHeaderLine('Accept'), 'application/json');
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