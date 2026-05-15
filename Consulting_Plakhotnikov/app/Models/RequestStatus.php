<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RequestStatus extends BaseModel
{
    protected static string $table = 'request_statuses';
    protected static array $fillable = ['code', 'name', 'sort_order'];

    public static function findByCode(string $code): ?array
    {
        return Database::one('SELECT * FROM request_statuses WHERE code = ? LIMIT 1', [$code]);
    }

    public static function ordered(): array
    {
        return Database::query('SELECT * FROM request_statuses ORDER BY sort_order, id');
    }
}
