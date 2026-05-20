<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Service;
use App\Models\ServiceCategory;

final class ServicesController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/services/index', [
            'title'    => 'Услуги',
            'services' => Service::withCategory(),
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        Template::render('admin/services/form', [
            'title'      => $id ? 'Редактирование услуги' : 'Новая услуга',
            'service'    => $id ? Service::find($id) : null,
            'categories' => ServiceCategory::all([], 'name'),
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'category_id'    => (int)($_POST['category_id'] ?? 0),
            'name'           => trim((string)($_POST['name'] ?? '')),
            'description'    => trim((string)($_POST['description'] ?? '')) ?: null,
            'price'          => (float)str_replace(',', '.', (string)($_POST['price'] ?? '0')),
            'duration_hours' => (int)($_POST['duration_hours'] ?? 0),
            'is_active'      => !empty($_POST['is_active']) ? 1 : 0,
        ];
        if ($data['category_id'] <= 0 || $data['name'] === '') {
            Helpers::flash('error', 'Категория и название обязательны');
            Helpers::redirect('admin_services_form', $id ? ['id' => $id] : []);
        }
        if ($id === 0) {
            Service::create($data);
        } else {
            Service::update($id, $data);
        }
        Helpers::flash('success', 'Услуга сохранена');
        Helpers::redirect('admin_services');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        try {
            Service::delete((int)($_POST['id'] ?? 0));
            Helpers::flash('success', 'Услуга удалена');
        } catch (\Throwable $e) {
            Helpers::flash('error', 'Услуга используется в заявках и не может быть удалена');
        }
        Helpers::redirect('admin_services');
    }
}
