<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;

abstract class Controller
{
    protected Application $app;

    public function __construct()
    {
        $this->app = Application::getInstance();
    }

    protected function view(string $name, array $data = []): HtmlResponse
    {
        $view = new \App\Core\View();
        $content = $view->render($name, $data);
        return new HtmlResponse($content);
    }

    protected function json(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function back(): RedirectResponse
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return new RedirectResponse($referer);
    }

    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $view = new \App\Core\View();
        echo $view->render("errors/$code", ['message' => $message]);
        exit;
    }

    protected function validate(array $data, array $rules, array $messages = []): array
    {
        try {
            $validator = new \App\Validators\Validator($data, $rules, $messages);
            return $validator->validate();
        } catch (\App\Validators\ValidationException $e) {
            if (is_ajax()) {
                json_response(['success' => false, 'errors' => $e->getErrors()]);
            }
            flash('error', $e->getMessage());
            back();
        }
        return [];
    }

    protected function auth(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function user(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function guest(): bool
    {
        return empty($_SESSION['user']);
    }

    protected function csrf_token(): string
    {
        return csrf_token();
    }

    protected function verify_csrf(string $token): bool
    {
        return verify_csrf($token);
    }
}