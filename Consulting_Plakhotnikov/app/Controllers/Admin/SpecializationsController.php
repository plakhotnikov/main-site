<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Specialization;

final class SpecializationsController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/simple_list', [
            'title'  => 'Специализации',
            'items'  => Specialization::all([], 'name'),
            'page_create' => 'admin_specializations_form',
            'page_edit'   => 'admin_specializations_form',
            'page_delete' => 'admin_specializations_delete',
            'columns'     => [['name', 'Название']],
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        Template::render('admin/simple_form', [
            'title'   => $id ? 'Специализация' : 'Новая специализация',
            'item'    => $id ? Specialization::find($id) : null,
            'fields'  => [['name' => 'name', 'label' => 'Название', 'required' => true]],
            'page_save' => 'admin_specializations_save',
            'page_back' => 'admin_specializations',
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
            Helpers::redirect('admin_specializations_form', $id ? ['id' => $id] : []);
        }
        $id === 0 ? Specialization::create($data) : Specialization::update($id, $data);
        Helpers::flash('success', 'Специализация сохранена');
        Helpers::redirect('admin_specializations');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        Specialization::delete((int)($_POST['id'] ?? 0));
        Helpers::flash('success', 'Специализация удалена');
        Helpers::redirect('admin_specializations');
    }
}
