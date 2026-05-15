<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Client;
use App\Models\Consultant;
use App\Models\Industry;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\User;

final class UsersController
{
    public function index(): void
    {
        Auth::requireRole('admin');
        Template::render('admin/users/index', [
            'title' => 'Пользователи',
            'users' => User::withRole(),
        ]);
    }

    public function form(): void
    {
        Auth::requireRole('admin');
        $id = (int)($_GET['id'] ?? 0);
        $user = $id > 0 ? User::find($id) : null;
        $client = $user !== null ? Client::findByUser($id) : null;
        $consultant = $user !== null ? Consultant::findByUser($id) : null;
        Template::render('admin/users/form', [
            'title'           => $user ? 'Редактирование пользователя' : 'Новый пользователь',
            'user'            => $user,
            'client'          => $client,
            'consultant'      => $consultant,
            'roles'           => Role::all([], 'id'),
            'industries'      => Industry::all([], 'name'),
            'specializations' => Specialization::all([], 'name'),
        ]);
    }

    public function save(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);

        $id = (int)($_POST['id'] ?? 0);
        $login    = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $roleId   = (int)($_POST['role_id'] ?? 0);
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $phone    = trim((string)($_POST['phone'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        if ($login === '' || $fullName === '' || $roleId <= 0) {
            Helpers::flash('error', 'Заполните логин, ФИО, роль');
            Helpers::redirect('admin_users_form', $id ? ['id' => $id] : []);
        }
        if ($id === 0 && $password === '') {
            Helpers::flash('error', 'Для нового пользователя нужно задать пароль');
            Helpers::redirect('admin_users_form');
        }

        $existing = User::findByLogin($login);
        if ($existing !== null && (int)$existing['id'] !== $id) {
            Helpers::flash('error', 'Логин уже занят');
            Helpers::redirect('admin_users_form', $id ? ['id' => $id] : []);
        }

        $role = Role::find($roleId);
        if ($role === null) {
            Helpers::flash('error', 'Роль не найдена');
            Helpers::redirect('admin_users_form', $id ? ['id' => $id] : []);
        }

        $data = [
            'login'     => $login,
            'role_id'   => $roleId,
            'full_name' => $fullName,
            'phone'     => $phone !== '' ? $phone : null,
            'email'     => $email !== '' ? $email : null,
            'is_active' => $isActive,
        ];
        if ($password !== '') {
            $data['password'] = $password;
        }

        Database::pdo()->beginTransaction();
        try {
            if ($id === 0) {
                $id = User::create($data);
            } else {
                User::update($id, $data);
            }

            $this->syncProfile($id, $role['code']);
            Database::pdo()->commit();
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            Helpers::flash('error', 'Не удалось сохранить: ' . $e->getMessage());
            Helpers::redirect('admin_users_form', $id ? ['id' => $id] : []);
        }

        Helpers::flash('success', 'Пользователь сохранён');
        Helpers::redirect('admin_users');
    }

    private function syncProfile(int $userId, string $roleCode): void
    {
        // Удалим старые профили, если роль изменилась
        if ($roleCode !== 'client') {
            Database::execute('DELETE FROM clients WHERE user_id = ?', [$userId]);
        }
        if ($roleCode !== 'consultant') {
            Database::execute('DELETE FROM consultants WHERE user_id = ?', [$userId]);
        }

        if ($roleCode === 'client') {
            $existing = Client::findByUser($userId);
            $payload = [
                'company'     => trim((string)($_POST['company'] ?? '')) ?: 'Не указано',
                'inn'         => trim((string)($_POST['inn'] ?? '')) ?: null,
                'industry_id' => (int)($_POST['industry_id'] ?? 0) ?: null,
                'address'     => trim((string)($_POST['address'] ?? '')) ?: null,
            ];
            if ($existing === null) {
                $payload['user_id'] = $userId;
                Client::create($payload);
            } else {
                Client::update((int)$existing['id'], $payload);
            }
        }
        if ($roleCode === 'consultant') {
            $existing = Consultant::findByUser($userId);
            $payload = [
                'position'          => trim((string)($_POST['position'] ?? '')) ?: null,
                'experience_years'  => (int)($_POST['experience_years'] ?? 0),
                'specialization_id' => (int)($_POST['specialization_id'] ?? 0) ?: null,
            ];
            if ($existing === null) {
                $payload['user_id'] = $userId;
                Consultant::create($payload);
            } else {
                Consultant::update((int)$existing['id'], $payload);
            }
        }
    }

    public function delete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $id = (int)($_POST['id'] ?? 0);
        if ($id === Auth::id()) {
            Helpers::flash('error', 'Нельзя удалить самого себя');
            Helpers::redirect('admin_users');
        }
        User::delete($id);
        Helpers::flash('success', 'Пользователь удалён (профили — каскадом)');
        Helpers::redirect('admin_users');
    }

    public function bulkDelete(): void
    {
        Auth::requireRole('admin');
        Csrf::check($_POST['_csrf'] ?? null);
        $ids = array_map('intval', (array)($_POST['ids'] ?? []));
        $ids = array_filter($ids, fn ($v) => $v > 0 && $v !== Auth::id());
        $count = User::bulkDelete($ids);
        Helpers::flash('success', 'Удалено пользователей: ' . $count);
        Helpers::redirect('admin_users');
    }
}
