<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Industry;

final class IndustriesController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/simple_list', [
            'title'  => 'Отрасли',
            'items'  => Industry::all([], 'name'),
            'page_create' => 'admin_industries_form',
            'page_edit'   => 'admin_industries_form',
            'page_delete' => 'admin_industries_delete',
            'columns'     => [['name', 'Название']],
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        Template::render('admin/simple_form', [
            'title'   => $id ? 'Отрасль' : 'Новая отрасль',
            'item'    => $id ? Industry::find($id) : null,
            'fields'  => [['name' => 'name', 'label' => 'Название', 'required' => true]],
            'page_save' => 'admin_industries_save',
            'page_back' => 'admin_industries',
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $data = ['name' => trim((string)($_POST['name'] ?? ''))];
        if ($data['name'] === '') {
            Helpers::flash('error', 'Название обязательно');
            Helpers::redirect('admin_industries_form', $id ? ['id' => $id] : []);
        }
        $id === 0 ? Industry::create($data) : Industry::update($id, $data);
        Helpers::flash('success', 'Отрасль сохранена');
        Helpers::redirect('admin_industries');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        Industry::delete((int)($_POST['id'] ?? 0));
        Helpers::flash('success', 'Отрасль удалена (industry_id у клиентов сброшен в NULL)');
        Helpers::redirect('admin_industries');
    }
}
