<?php
declare(strict_types=1);

namespace App\Models;

final class Role extends BaseModel
{
    protected static string $table = 'roles';
    protected static array $fillable = ['code', 'name'];
}
