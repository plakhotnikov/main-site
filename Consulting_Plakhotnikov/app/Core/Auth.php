<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Auth
{
    private static ?array $user = null;

    public static function login(string $login, string $password, bool $remember = false): bool
    {
        $row = Database::one(
            'SELECT u.*, r.code AS role_code, r.name AS role_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.login = ? AND u.password = ? AND u.is_active = 1
              LIMIT 1',
            [$login, $password]
        );
        if ($row === null) {
            return false;
        }

        $_SESSION['user_id'] = (int)$row['id'];
        self::$user = $row;

        if ($remember) {
            self::storeRememberCookie((int)$row['id']);
        }
        return true;
    }

    public static function logout(): void
    {
        if (!empty($_SESSION['user_id'])) {
            Database::execute(
                'UPDATE users SET remember_token = NULL WHERE id = ?',
                [(int)$_SESSION['user_id']]
            );
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
        setcookie('auth_remember', '', time() - 42000, '/');
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        if (!empty($_SESSION['user_id'])) {
            self::$user = Database::one(
                'SELECT u.*, r.code AS role_code, r.name AS role_name
                   FROM users u
                   JOIN roles r ON r.id = u.role_id
                  WHERE u.id = ? LIMIT 1',
                [(int)$_SESSION['user_id']]
            );
            return self::$user;
        }
        return null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        return $u['role_code'] ?? null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u !== null ? (int)$u['id'] : null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Helpers::flash('error', 'Для доступа к этой странице нужно войти');
            Helpers::redirect('login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            echo '<h1>403 — Доступ запрещён</h1><p>Вашей роли недостаточно для просмотра этой страницы.</p>';
            echo '<p><a href="' . Helpers::baseUrl() . '">На главную</a></p>';
            exit;
        }
    }

    public static function resumeFromCookie(): void
    {
        if (!empty($_SESSION['user_id'])) {
            return;
        }
        $token = $_COOKIE['auth_remember'] ?? null;
        if ($token === null || $token === '') {
            return;
        }
        $row = Database::one(
            'SELECT u.*, r.code AS role_code, r.name AS role_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.remember_token = ? AND u.is_active = 1
              LIMIT 1',
            [$token]
        );
        if ($row !== null) {
            $_SESSION['user_id'] = (int)$row['id'];
            self::$user = $row;
            self::storeRememberCookie((int)$row['id']);
        }
    }

    public static function clientId(): ?int
    {
        if (self::role() !== 'client') {
            return null;
        }
        $row = Database::one('SELECT id FROM clients WHERE user_id = ? LIMIT 1', [self::id()]);
        return $row !== null ? (int)$row['id'] : null;
    }

    public static function consultantId(): ?int
    {
        if (self::role() !== 'consultant') {
            return null;
        }
        $row = Database::one('SELECT id FROM consultants WHERE user_id = ? LIMIT 1', [self::id()]);
        return $row !== null ? (int)$row['id'] : null;
    }

    private static function storeRememberCookie(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        Database::execute('UPDATE users SET remember_token = ? WHERE id = ?', [$token, $userId]);
        $days = (int)($GLOBALS['APP_CONFIG']['app']['remember_days'] ?? 30);
        setcookie('auth_remember', $token, [
            'expires'  => time() + 86400 * $days,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
