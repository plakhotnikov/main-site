<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Helpers;
use App\Core\Template;
use App\Models\Client;
use App\Models\Industry;
use App\Models\User;

final class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirectByRole();
        }
        Template::render('auth/login', [
            'title' => 'Вход',
        ]);
    }

    public function login(): void
    {
        Csrf::check($_POST['_csrf'] ?? null);
        $login    = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);

        if ($login === '' || $password === '') {
            Helpers::flash('error', 'Заполните логин и пароль');
            Helpers::redirect('login');
        }

        if (!Auth::login($login, $password, $remember)) {
            Helpers::flash('error', 'Неверный логин или пароль');
            Helpers::redirect('login');
        }

        Helpers::flash('success', 'Добро пожаловать, ' . Auth::user()['full_name']);
        $this->redirectByRole();
    }

    public function logout(): void
    {
        Auth::logout();
        Helpers::flash('success', 'Вы вышли из системы');
        Helpers::redirect('home');
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->redirectByRole();
        }
        Template::render('auth/register', [
            'title'      => 'Регистрация клиента',
            'industries' => Industry::all([], 'name'),
        ]);
    }

    public function register(): void
    {
        Csrf::check($_POST['_csrf'] ?? null);

        $login     = trim((string)($_POST['login'] ?? ''));
        $password  = (string)($_POST['password'] ?? '');
        $password2 = (string)($_POST['password2'] ?? '');
        $fullName  = trim((string)($_POST['full_name'] ?? ''));
        $email     = trim((string)($_POST['email'] ?? ''));
        $phone     = trim((string)($_POST['phone'] ?? ''));
        $company   = trim((string)($_POST['company'] ?? ''));
        $inn       = trim((string)($_POST['inn'] ?? ''));
        $industryId = (int)($_POST['industry_id'] ?? 0) ?: null;
        $address   = trim((string)($_POST['address'] ?? ''));

        $errors = [];
        if ($login === '' || strlen($login) < 3) {
            $errors[] = 'Логин — не короче 3 символов';
        }
        if ($password === '' || strlen($password) < 3) {
            $errors[] = 'Пароль — не короче 3 символов';
        }
        if ($password !== $password2) {
            $errors[] = 'Пароли не совпадают';
        }
        if ($fullName === '') {
            $errors[] = 'Укажите ФИО';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email';
        }
        if ($company === '') {
            $errors[] = 'Укажите название компании';
        }
        if (User::findByLogin($login) !== null) {
            $errors[] = 'Этот логин уже занят';
        }

        if ($errors) {
            Helpers::flash('error', implode('. ', $errors));
            Helpers::redirect('register');
        }

        $clientRoleId = (int)Database::one("SELECT id FROM roles WHERE code = 'client' LIMIT 1")['id'];

        Database::pdo()->beginTransaction();
        try {
            $userId = User::create([
                'login'     => $login,
                'password'  => $password,
                'role_id'   => $clientRoleId,
                'full_name' => $fullName,
                'email'     => $email !== '' ? $email : null,
                'phone'     => $phone !== '' ? $phone : null,
                'is_active' => 1,
            ]);
            Client::create([
                'user_id'     => $userId,
                'company'     => $company,
                'inn'         => $inn !== '' ? $inn : null,
                'industry_id' => $industryId,
                'address'     => $address !== '' ? $address : null,
            ]);
            Database::pdo()->commit();
        } catch (\Throwable $e) {
            Database::pdo()->rollBack();
            Helpers::flash('error', 'Ошибка регистрации: ' . $e->getMessage());
            Helpers::redirect('register');
        }

        Auth::login($login, $password, true);
        Helpers::flash('success', 'Регистрация прошла успешно. Добро пожаловать!');
        Helpers::redirect('client_dashboard');
    }

    private function redirectByRole(): never
    {
        $role = Auth::role();
        $page = match ($role) {
            'admin'      => 'admin_dashboard',
            'consultant' => 'consultant_dashboard',
            'client'     => 'client_dashboard',
            default      => 'home',
        };
        Helpers::redirect($page);
    }
}
