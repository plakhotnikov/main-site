<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class RequestService extends BaseModel
{
    protected static string $table = 'request_services';
    protected static array $fillable = ['request_id', 'service_id'];

    public static function attach(int $requestId, int $serviceId): void
    {
        Database::execute(
            'INSERT IGNORE INTO request_services (request_id, service_id) VALUES (?, ?)',
            [$requestId, $serviceId]
        );
    }

    public static function detachAll(int $requestId): void
    {
        Database::execute('DELETE FROM request_services WHERE request_id = ?', [$requestId]);
    }

    public static function servicesFor(int $requestId): array
    {
        return Database::query(
            'SELECT s.* FROM request_services rs
               JOIN services s ON s.id = rs.service_id
              WHERE rs.request_id = ?
              ORDER BY s.name',
            [$requestId]
        );
    }
}
