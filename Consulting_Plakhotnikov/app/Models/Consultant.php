<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Consultant extends BaseModel
{
    protected static string $table = 'consultants';
    protected static array $fillable = ['user_id', 'position', 'experience_years', 'specialization_id'];

    public static function withUser(): array
    {
        return Database::query(
            'SELECT co.*, u.full_name, u.email, u.phone, u.login, sp.name AS specialization_name
               FROM consultants co
               JOIN users u ON u.id = co.user_id
               LEFT JOIN specializations sp ON sp.id = co.specialization_id
              ORDER BY u.full_name'
        );
    }

    public static function findByUser(int $userId): ?array
    {
        return Database::one('SELECT * FROM consultants WHERE user_id = ? LIMIT 1', [$userId]);
    }

    public static function workload(): array
    {
        return Database::query('SELECT * FROM v_consultant_workload ORDER BY consultant_name');
    }
}
