<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User extends BaseModel
{
    protected static string $table = 'users';
    protected static array $fillable = [
        'login', 'password', 'role_id', 'full_name',
        'phone', 'email', 'is_active', 'remember_token',
    ];

    public static function findByLogin(string $login): ?array
    {
        return Database::one('SELECT * FROM users WHERE login = ? LIMIT 1', [$login]);
    }

    public static function withRole(): array
    {
        return Database::query(
            'SELECT u.*, r.code AS role_code, r.name AS role_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
              ORDER BY u.id'
        );
    }
}
