<?php
declare(strict_types=1);

namespace App\Core;

final class Helpers
{
    public static function baseUrl(string $path = ''): string
    {
        $base = rtrim((string)($GLOBALS['APP_CONFIG']['app']['base_url'] ?? ''), '/');
        $path = ltrim($path, '/');
        return $path === '' ? ($base . '/') : ($base . '/' . $path);
    }

    public static function url(string $page, array $params = []): string
    {
        $params = array_merge(['page' => $page], $params);
        return self::baseUrl('index.php?' . http_build_query($params));
    }

    public static function redirect(string $page, array $params = []): never
    {
        header('Location: ' . self::url($page, $params));
        exit;
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $value = $_SESSION['_flash'][$key] ?? null;
        if ($value !== null) {
            unset($_SESSION['_flash'][$key]);
        }
        return $value;
    }
}
