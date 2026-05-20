<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\RequestStatus;

final class StatusesController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/statuses/index', [
            'title' => 'Статусы заявок',
            'items' => RequestStatus::ordered(),
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        Template::render('admin/statuses/form', [
            'title' => $id ? 'Статус' : 'Новый статус',
            'item'  => $id ? RequestStatus::find($id) : null,
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'code'       => trim((string)($_POST['code'] ?? '')),
            'name'       => trim((string)($_POST['name'] ?? '')),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        if ($data['code'] === '' || $data['name'] === '') {
            Helpers::flash('error', 'Код и название обязательны');
            Helpers::redirect('admin_statuses_form', $id ? ['id' => $id] : []);
        }
        $id === 0 ? RequestStatus::create($data) : RequestStatus::update($id, $data);
        Helpers::flash('success', 'Статус сохранён');
        Helpers::redirect('admin_statuses');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        try {
            RequestStatus::delete((int)($_POST['id'] ?? 0));
            Helpers::flash('success', 'Статус удалён');
        } catch (\Throwable $e) {
            Helpers::flash('error', 'Статус используется заявками (RESTRICT)');
        }
        Helpers::redirect('admin_statuses');
    }
}
