<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Consultation extends BaseModel
{
    protected static string $table = 'consultations';
    protected static array $fillable = ['request_id', 'held_at', 'duration_min', 'notes'];

    public static function forRequest(int $requestId): array
    {
        return Database::query(
            'SELECT * FROM consultations WHERE request_id = ? ORDER BY held_at DESC, id DESC',
            [$requestId]
        );
    }
}
