<?php
declare(strict_types=1);

namespace App\Models;

final class ServiceCategory extends BaseModel
{
    protected static string $table = 'service_categories';
    protected static array $fillable = ['code', 'name'];
}
