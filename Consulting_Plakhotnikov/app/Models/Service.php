<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Service extends BaseModel
{
    protected static string $table = 'services';
    protected static array $fillable = [
        'category_id', 'name', 'description', 'price',
        'duration_hours', 'is_active',
    ];

    public static function active(): array
    {
        return Database::query(
            'SELECT s.*, sc.name AS category_name
               FROM services s
               JOIN service_categories sc ON sc.id = s.category_id
              WHERE s.is_active = 1
              ORDER BY sc.name, s.name'
        );
    }

    public static function withCategory(): array
    {
        return Database::query(
            'SELECT s.*, sc.name AS category_name
               FROM services s
               JOIN service_categories sc ON sc.id = s.category_id
              ORDER BY sc.name, s.name'
        );
    }
}
