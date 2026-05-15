<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Template;

final class ErrorController
{
    public function notFound(string $page = ''): void
    {
        http_response_code(404);
        Template::render('errors/404', ['title' => 'Страница не найдена', 'page' => $page]);
    }
}
