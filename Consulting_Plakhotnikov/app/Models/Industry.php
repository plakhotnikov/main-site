<?php
declare(strict_types=1);

namespace App\Models;

final class Industry extends BaseModel
{
    protected static string $table = 'industries';
    protected static array $fillable = ['name'];
}
