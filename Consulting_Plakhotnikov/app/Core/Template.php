<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Template
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $rootDir = dirname(__DIR__, 2);
        $viewPath = $rootDir . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            throw new RuntimeException('Шаблон не найден: ' . $view);
        }

        $content = self::renderPartial($viewPath, $data);

        if ($layout === '') {
            echo $content;
            return;
        }

        $layoutPath = $rootDir . '/app/Views/layouts/' . $layout . '.php';
        if (!is_file($layoutPath)) {
            throw new RuntimeException('Layout не найден: ' . $layout);
        }
        $layoutData = array_merge($data, ['content' => $content]);
        echo self::renderPartial($layoutPath, $layoutData);
    }

    public static function partial(string $name, array $data = []): void
    {
        $rootDir = dirname(__DIR__, 2);
        $path = $rootDir . '/app/Views/partials/' . $name . '.php';
        if (!is_file($path)) {
            throw new RuntimeException('Partial не найден: ' . $name);
        }
        echo self::renderPartial($path, $data);
    }

    private static function renderPartial(string $path, array $data): string
    {
        ob_start();
        (static function () use ($path, $data): void {
            extract($data, EXTR_SKIP);
            require $path;
        })();
        return (string)ob_get_clean();
    }
}
