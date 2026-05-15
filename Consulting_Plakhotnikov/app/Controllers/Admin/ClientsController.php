<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Client;
use App\Models\Industry;
use App\Models\User;

final class ClientsController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/clients/index', [
            'title'   => 'Клиенты',
            'clients' => Client::withUser(),
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $client = $id > 0 ? Client::find($id) : null;
        $user = $client !== null ? User::find((int)$client['user_id']) : null;
        Template::render('admin/clients/form', [
            'title'      => $client ? 'Клиент' : 'Новый клиент',
            'client'     => $client,
            'user'       => $user,
            'industries' => Industry::all([], 'name'),
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $payload = [
            'company'     => trim((string)($_POST['company'] ?? '')) ?: 'Не указано',
            'inn'         => trim((string)($_POST['inn'] ?? '')) ?: null,
            'industry_id' => (int)($_POST['industry_id'] ?? 0) ?: null,
            'address'     => trim((string)($_POST['address'] ?? '')) ?: null,
        ];
        if ($id > 0) {
            Client::update($id, $payload);
        } else {
            Helpers::flash('error', 'Создание клиента — через раздел «Пользователи»');
            Helpers::redirect('admin_clients');
        }
        Helpers::flash('success', 'Клиент сохранён');
        Helpers::redirect('admin_clients');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        // Удаляем USER, чтобы каскадом ушли заявки и далее. Это и есть демо ТЗ.
        $client = Client::find($id);
        if ($client !== null) {
            User::delete((int)$client['user_id']);
        }
        Helpers::flash('success', 'Клиент удалён вместе с заявками (CASCADE)');
        Helpers::redirect('admin_clients');
    }
}
