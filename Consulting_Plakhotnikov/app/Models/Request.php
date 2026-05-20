<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Request extends BaseModel
{
    protected static string $table = 'requests';
    protected static array $fillable = [
        'client_id', 'service_id', 'consultant_id', 'status_id',
        'title', 'description', 'priority', 'deadline',
    ];

    public static function forClient(int $clientId): array
    {
        return Database::query(
            'SELECT * FROM v_client_requests WHERE client_id = ? ORDER BY created_at DESC',
            [$clientId]
        );
    }

    public static function forConsultant(int $consultantId): array
    {
        return Database::query(
            'SELECT * FROM v_client_requests WHERE consultant_id = ? ORDER BY created_at DESC',
            [$consultantId]
        );
    }

    public static function fullForAdmin(): array
    {
        return Database::query('SELECT * FROM v_admin_requests_full ORDER BY created_at DESC');
    }

    public static function fullById(int $id): ?array
    {
        return Database::one('SELECT * FROM v_admin_requests_full WHERE id = ? LIMIT 1', [$id]);
    }

    public static function clientView(int $id): ?array
    {
        return Database::one('SELECT * FROM v_client_requests WHERE id = ? LIMIT 1', [$id]);
    }

    public static function totalCost(int $id): float
    {
        $row = Database::one('SELECT fn_request_total_cost(?) AS total', [$id]);
        return (float)($row['total'] ?? 0);
    }

    public static function assignConsultant(int $requestId, int $consultantId): void
    {
        Database::execute('CALL sp_assign_consultant(?, ?)', [$requestId, $consultantId]);
    }

    public static function changeStatus(int $requestId, string $statusCode, string $comment = ''): void
    {
        Database::execute('CALL sp_change_status(?, ?, ?)', [$requestId, $statusCode, $comment]);
    }

    public static function bulkDeleteByProc(array $ids): int
    {
        $clean = array_filter(array_map('intval', $ids), static fn ($v) => $v > 0);
        if (empty($clean)) {
            return 0;
        }
        $list = implode(',', $clean);
        Database::execute('CALL sp_bulk_delete_requests(?)', [$list]);
        return count($clean);
    }

    public static function monthlyStats(int $year): array
    {
        return Database::query(
            'SELECT MONTH(created_at) AS m, COUNT(*) AS c
               FROM requests
              WHERE YEAR(created_at) = ?
              GROUP BY MONTH(created_at)
              ORDER BY m',
            [$year]
        );
    }

    public static function consultantsRevenue(int $year, int $month): array
    {
        return Database::query(
            'SELECT co.id, u.full_name,
                    fn_consultant_revenue(co.id, ?, ?) AS revenue
               FROM consultants co
               JOIN users u ON u.id = co.user_id
              ORDER BY revenue DESC, u.full_name',
            [$year, $month]
        );
    }
}
