<?php
declare(strict_types=1);

namespace App\Models;

final class Specialization extends BaseModel
{
    protected static string $table = 'specializations';
    protected static array $fillable = ['name'];
}
