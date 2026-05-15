<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Client extends BaseModel
{
    protected static string $table = 'clients';
    protected static array $fillable = ['user_id', 'company', 'inn', 'industry_id', 'address'];

    public static function withUser(): array
    {
        return Database::query(
            'SELECT c.*, u.full_name, u.email, u.phone, u.login, ind.name AS industry_name
               FROM clients c
               JOIN users u ON u.id = c.user_id
               LEFT JOIN industries ind ON ind.id = c.industry_id
              ORDER BY u.full_name'
        );
    }

    public static function findByUser(int $userId): ?array
    {
        return Database::one('SELECT * FROM clients WHERE user_id = ? LIMIT 1', [$userId]);
    }
}
