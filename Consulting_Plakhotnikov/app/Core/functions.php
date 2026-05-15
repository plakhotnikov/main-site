<?php
declare(strict_types=1);

use App\Core\Helpers;

function h(string|int|float|null $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $page, array $params = []): string
{
    return Helpers::url($page, $params);
}

function asset(string $path): string
{
    return Helpers::baseUrl('assets/' . ltrim($path, '/'));
}

function flash(string $key, ?string $message = null): ?string
{
    return Helpers::flash($key, $message);
}

function format_money(float|string|null $value): string
{
    return number_format((float)$value, 2, ',', ' ') . ' ₽';
}

function format_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    $ts = strtotime($value);
    if ($ts === false) {
        return $value;
    }
    return date('d.m.Y', $ts);
}

function format_datetime(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }
    $ts = strtotime($value);
    if ($ts === false) {
        return $value;
    }
    return date('d.m.Y H:i', $ts);
}

function status_class(string $code): string
{
    return match ($code) {
        'new'         => 'badge badge--blue',
        'assigned'    => 'badge badge--purple',
        'in_progress' => 'badge badge--orange',
        'review'      => 'badge badge--yellow',
        'completed'   => 'badge badge--green',
        'cancelled'   => 'badge badge--gray',
        default       => 'badge',
    };
}

function priority_label(string $code): string
{
    return match ($code) {
        'low'    => 'Низкий',
        'normal' => 'Обычный',
        'high'   => 'Высокий',
        default  => $code,
    };
}
