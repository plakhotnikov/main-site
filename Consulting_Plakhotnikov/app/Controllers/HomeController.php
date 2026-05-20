<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Template;
use App\Models\Service;
use App\Models\ServiceCategory;

final class HomeController
{
    public function index(): void
    {
        $categories = ServiceCategory::all([], 'id');
        $services   = Service::active();
        Template::render('home/index', [
            'title'      => 'Никс Менеджмент — Консалтинговая компания',
            'categories' => $categories,
            'services'   => $services,
        ]);
    }

    public function services(): void
    {
        $categories = ServiceCategory::all([], 'id');
        $services   = Service::active();
        Template::render('home/services', [
            'title'      => 'Услуги',
            'categories' => $categories,
            'services'   => $services,
        ]);
    }

    public function about(): void
    {
        Template::render('home/about', ['title' => 'О компании']);
    }

    public function contacts(): void
    {
        Template::render('home/contacts', ['title' => 'Контакты']);
    }
}
