<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Payment extends BaseModel
{
    protected static string $table = 'payments';
    protected static array $fillable = ['request_id', 'amount', 'paid_at', 'method'];

    public static function forRequest(int $requestId): array
    {
        return Database::query(
            'SELECT * FROM payments WHERE request_id = ? ORDER BY paid_at DESC',
            [$requestId]
        );
    }

    public static function revenueByCategory(): array
    {
        return Database::query('SELECT * FROM v_revenue_by_category ORDER BY category');
    }
}
