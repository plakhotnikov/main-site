<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Consultant;
use App\Models\Specialization;
use App\Models\User;

final class ConsultantsController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/consultants/index', [
            'title'       => 'Консультанты',
            'consultants' => Consultant::withUser(),
            'workload'    => Consultant::workload(),
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $consultant = $id > 0 ? Consultant::find($id) : null;
        $user = $consultant !== null ? User::find((int)$consultant['user_id']) : null;
        Template::render('admin/consultants/form', [
            'title'           => $consultant ? 'Консультант' : 'Новый консультант',
            'consultant'      => $consultant,
            'user'            => $user,
            'specializations' => Specialization::all([], 'name'),
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $payload = [
            'position'          => trim((string)($_POST['position'] ?? '')) ?: null,
            'experience_years'  => (int)($_POST['experience_years'] ?? 0),
            'specialization_id' => (int)($_POST['specialization_id'] ?? 0) ?: null,
        ];
        if ($id > 0) {
            Consultant::update($id, $payload);
            Helpers::flash('success', 'Консультант сохранён');
        } else {
            Helpers::flash('error', 'Создавайте консультанта через раздел «Пользователи»');
        }
        Helpers::redirect('admin_consultants');
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        $row = Consultant::find($id);
        if ($row !== null) {
            User::delete((int)$row['user_id']);
        }
        Helpers::flash('success', 'Консультант удалён');
        Helpers::redirect('admin_consultants');
    }
}
