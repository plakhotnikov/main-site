<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\ServiceCategory;

final class CategoriesController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/categories/index', [
            'title' => 'Категории услуг',
            'items' => ServiceCategory::all([], 'id'),
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        Template::render('admin/categories/form', [
            'title' => $id ? 'Категория' : 'Новая категория',
            'item'  => $id ? ServiceCategory::find($id) : null,
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'code' => trim((string)($_POST['code'] ?? '')),
            'name' => trim((string)($_POST['name'] ?? '')),
        ];
        if ($data['code'] === '' || $data['name'] === '') {
            Helpers::flash('error', 'Код и название обязательны');
            Helpers::redirect('admin_categories_form', $id ? ['id' => $id] : []);
        }
        $id === 0 ? ServiceCategory::create($data) : ServiceCategory::update($id, $data);
        Helpers::flash('success', 'Категория сохранена');
        Helpers::redirect('admin_categories');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        try {
            ServiceCategory::delete((int)($_POST['id'] ?? 0));
            Helpers::flash('success', 'Категория удалена');
        } catch (\Throwable $e) {
            Helpers::flash('error', 'Категория используется услугами (RESTRICT)');
        }
        Helpers::redirect('admin_categories');
    }
}
