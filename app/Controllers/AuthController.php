<?php

namespace App\Controllers;

use App\Core\Application;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    public function showLogin(): HtmlResponse
    {
        return $this->view('auth.login');
    }

    public function login(array $vars, ServerRequestInterface $request): JsonResponse|RedirectResponse
    {
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $db = Application::getInstance()->db();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$validated['email']]);
        $user = $stmt->fetch();

        if (!$user || !verify_password($validated['password'], $user['password_hash'])) {
            $this->logAudit('login_failed', ['email' => $validated['email']]);
            return $this->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'display_name' => $user['display_name'],
            'xp' => $user['xp'],
            'level' => $user['level'],
            'detective_rank' => $user['detective_rank'],
            'role' => $user['role'],
        ];

        $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$user['id']]);
        $this->logAudit('login_success', ['user_id' => $user['id']]);

        $redirect = $_SESSION['_redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['_redirect_after_login']);

        return $this->json(['success' => true, 'redirect' => $redirect]);
    }

    public function showRegister(): HtmlResponse
    {
        return $this->view('auth.register');
    }

    public function register(array $vars, ServerRequestInterface $request): JsonResponse|RedirectResponse
    {
        $data = $request->getParsedBody();
        $validated = $this->validate($data, [
            'username' => 'required|alpha_dash|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|password|confirmed',
            'display_name' => 'required|min:2|max:100',
        ]);

        $db = Application::getInstance()->db();
        $userId = $db->prepare("
            INSERT INTO users (username, email, password_hash, display_name, role, status, email_verified_at)
            VALUES (?, ?, ?, ?, 'user', 'active', NOW())
        ")->execute([
            $validated['username'],
            $validated['email'],
            hash_password($validated['password']),
            $validated['display_name'],
        ]);

        $this->logAudit('registration', ['user_id' => $db->lastInsertId(), 'email' => $validated['email']]);

        return $this->json(['success' => true, 'message' => 'Registration successful', 'redirect' => '/login']);
    }

    public function logout(): RedirectResponse
    {
        $userId = $this->user();
        if ($userId) {
            $this->logAudit('logout', ['user_id' => $userId]);
        }
        session_destroy();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        return $this->redirect('/login');
    }

    public function showVerifyEmail(): HtmlResponse
    {
        return $this->view('auth.verify-email');
    }

    public function verifyEmail(array $vars, ServerRequestInterface $request): JsonResponse
    {
        return $this->json(['success' => true, 'message' => 'Email verification not implemented yet']);
    }

    public function showForgotPassword(): HtmlResponse
    {
        return $this->view('auth.forgot-password');
    }

    public function sendResetLink(array $vars, ServerRequestInterface $request): JsonResponse
    {
        return $this->json(['success' => true, 'message' => 'Password reset not implemented yet']);
    }

    public function showResetPassword(array $vars): HtmlResponse
    {
        return $this->view('auth.reset-password', ['token' => $vars['token']]);
    }

    public function resetPassword(array $vars, ServerRequestInterface $request): JsonResponse
    {
        return $this->json(['success' => true, 'message' => 'Password reset not implemented yet']);
    }

    private function logAudit(string $action, array $metadata = []): void
    {
        $db = Application::getInstance()->db();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ipHash = hash('sha256', $ip);
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $db->prepare("
            INSERT INTO audit_logs (user_id, action, ip_hash, user_agent, metadata)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $_SESSION['user']['id'] ?? null,
            $action,
            $ipHash,
            $userAgent,
            json_encode($metadata),
        ]);
    }
}