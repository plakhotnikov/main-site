<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Report extends BaseModel
{
    protected static string $table = 'reports';
    protected static array $fillable = ['request_id', 'consultant_id', 'content', 'file_path'];

    public static function findByRequest(int $requestId): ?array
    {
        return Database::one('SELECT * FROM reports WHERE request_id = ? LIMIT 1', [$requestId]);
    }

    public static function withRequest(): array
    {
        return Database::query(
            'SELECT rp.*, r.title AS request_title, c.company AS client_company,
                    u.full_name AS consultant_name
               FROM reports rp
               JOIN requests r ON r.id = rp.request_id
               JOIN clients  c ON c.id = r.client_id
               LEFT JOIN consultants co ON co.id = rp.consultant_id
               LEFT JOIN users u ON u.id = co.user_id
              ORDER BY rp.created_at DESC'
        );
    }
}
