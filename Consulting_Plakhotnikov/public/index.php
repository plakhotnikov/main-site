<?php
/**
 * Front controller курсового проекта
 * «Консалтинговая компания» — Плахотников Владимир
 */

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Router;

session_start();

$rootDir = dirname(__DIR__);
require $rootDir . '/../vendor/autoload.php';

// PSR-4-подобный автолоадер для App\*
spl_autoload_register(static function (string $class) use ($rootDir): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $relative = substr($class, 4);
    $path = $rootDir . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$config = require $rootDir . '/config/config.php';
$GLOBALS['APP_CONFIG'] = $config;

require $rootDir . '/app/Core/functions.php';

// Авторизация по cookie auth_remember (если сессии нет)
Auth::resumeFromCookie();

$page = isset($_GET['page']) ? (string)$_GET['page'] : 'home';

try {
    Router::dispatch($page);
} catch (Throwable $e) {
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<h1>Ошибка приложения</h1><pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</pre>';
}
