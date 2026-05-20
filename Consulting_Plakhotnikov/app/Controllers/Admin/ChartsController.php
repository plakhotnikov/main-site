<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Payment;
use App\Models\Request as RequestModel;

final class ChartsController
{
    private const FONT      = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
    private const FONT_BOLD = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    public function render(): void
    {
        Auth::requireRole('admin');
        $type = (string)($_GET['type'] ?? 'monthly');
        match ($type) {
            'categories'  => $this->categoriesPie(),
            'consultants' => $this->consultantsBars(),
            default       => $this->monthlyBars(),
        };
    }

    private function monthlyBars(): void
    {
        $year = (int)date('Y');
        $rows = RequestModel::monthlyStats($year);
        $data = array_fill(1, 12, 0);
        foreach ($rows as $r) {
            $data[(int)$r['m']] = (int)$r['c'];
        }

        $w = 880; $h = 420;
        $img = imagecreatetruecolor($w, $h);
        $colors = $this->palette($img);
        imagefilledrectangle($img, 0, 0, $w, $h, $colors['bg']);

        $this->title($img, $colors, "Заявки по месяцам ($year)", $w);

        $padL = 60; $padR = 30; $padT = 60; $padB = 60;
        $maxVal = max(1, max($data));
        // Округляем верхнюю границу до удобного шага
        $step = (int)max(1, ceil($maxVal / 5));
        $top = $step * 5;

        $chartW = $w - $padL - $padR;
        $chartH = $h - $padT - $padB;

        // Сетка по горизонтали
        for ($i = 0; $i <= 5; $i++) {
            $y = $padT + (int)($chartH * $i / 5);
            imageline($img, $padL, $y, $w - $padR, $y, $colors['grid']);
            $label = (string)($top - $step * $i);
            imagettftext($img, 9, 0, $padL - 30, $y + 4, $colors['axis'], self::FONT, $label);
        }

        $months = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
        $barW = (int)($chartW / 12 * 0.7);
        $gap = (int)($chartW / 12);
        for ($m = 1; $m <= 12; $m++) {
            $val = $data[$m];
            $x = $padL + ($m - 1) * $gap + (int)(($gap - $barW) / 2);
            $barH = (int)($chartH * ($val / $top));
            $y = $padT + $chartH - $barH;
            // Градиент: рисуем горизонтальные линии разной яркости
            for ($i = 0; $i < $barH; $i++) {
                $shade = imagecolorallocate(
                    $img,
                    20 + (int)(40 * $i / max(1, $barH)),
                    42 + (int)(60 * $i / max(1, $barH)),
                    29 + (int)(50 * $i / max(1, $barH))
                );
                imageline($img, $x, $y + $i, $x + $barW, $y + $i, $shade);
            }
            imagerectangle($img, $x, $y, $x + $barW, $padT + $chartH, $colors['border']);
            imagettftext($img, 9, 0, $x + 2, $h - $padB + 18, $colors['axis'], self::FONT, $months[$m - 1]);
            if ($val > 0) {
                imagettftext($img, 9, 0, $x + 4, $y - 4, $colors['fg'], self::FONT_BOLD, (string)$val);
            }
        }

        // Оси
        imageline($img, $padL, $padT, $padL, $padT + $chartH, $colors['axis']);
        imageline($img, $padL, $padT + $chartH, $w - $padR, $padT + $chartH, $colors['axis']);

        $this->stream($img);
    }

    private function categoriesPie(): void
    {
        $rows = Payment::revenueByCategory();
        $w = 880; $h = 420;
        $img = imagecreatetruecolor($w, $h);
        $colors = $this->palette($img);
        imagefilledrectangle($img, 0, 0, $w, $h, $colors['bg']);
        $this->title($img, $colors, 'Доход по категориям услуг', $w);

        $total = 0.0;
        foreach ($rows as $r) {
            $total += (float)$r['total_revenue'];
        }

        $cx = 240; $cy = 240; $r = 140;
        $palette = [
            imagecolorallocate($img, 20, 42, 29),
            imagecolorallocate($img, 130, 160, 70),
            imagecolorallocate($img, 198, 162, 80),
            imagecolorallocate($img, 70, 110, 140),
            imagecolorallocate($img, 165, 70, 60),
            imagecolorallocate($img, 100, 100, 100),
        ];

        if ($total <= 0 || empty($rows)) {
            imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $colors['grid']);
            imagettftext($img, 14, 0, $cx - 80, $cy + 5, $colors['axis'], self::FONT, 'Нет платежей');
        } else {
            $start = 0.0;
            foreach ($rows as $i => $row) {
                $share = (float)$row['total_revenue'] / $total;
                $end = $start + 360 * $share;
                $color = $palette[$i % count($palette)];
                if ($end - $start > 0.001) {
                    imagefilledarc($img, $cx, $cy, $r * 2, $r * 2, (int)$start, (int)$end, $color, IMG_ARC_PIE);
                }
                $start = $end;
            }
            // Обводка
            imageellipse($img, $cx, $cy, $r * 2, $r * 2, $colors['border']);
        }

        // Легенда
        $lx = 480; $ly = 110;
        imagettftext($img, 11, 0, $lx, $ly - 18, $colors['fg'], self::FONT_BOLD, 'Легенда');
        foreach ($rows as $i => $row) {
            $color = $palette[$i % count($palette)];
            $share = $total > 0 ? (float)$row['total_revenue'] / $total * 100 : 0;
            imagefilledrectangle($img, $lx, $ly, $lx + 18, $ly + 14, $color);
            imagerectangle($img, $lx, $ly, $lx + 18, $ly + 14, $colors['border']);
            $label = sprintf('%s — %s ₽ (%.1f%%)',
                (string)$row['category'],
                number_format((float)$row['total_revenue'], 0, ',', ' '),
                $share);
            imagettftext($img, 10, 0, $lx + 26, $ly + 12, $colors['axis'], self::FONT, $label);
            $ly += 28;
        }

        $this->stream($img);
    }

    private function consultantsBars(): void
    {
        $year = (int)date('Y');
        $month = (int)date('n');
        $rows = RequestModel::consultantsRevenue($year, $month);

        $w = 880; $h = 420;
        $img = imagecreatetruecolor($w, $h);
        $colors = $this->palette($img);
        imagefilledrectangle($img, 0, 0, $w, $h, $colors['bg']);

        $monthRu = ['','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'];
        $this->title($img, $colors, "Доход консультантов за {$monthRu[$month]} $year", $w);

        if (empty($rows)) {
            imagettftext($img, 14, 0, 60, 200, $colors['axis'], self::FONT, 'Нет данных');
            $this->stream($img);
            return;
        }

        $padL = 220; $padR = 80; $padT = 60; $padB = 40;
        $chartW = $w - $padL - $padR;
        $chartH = $h - $padT - $padB;
        $maxVal = 0.0;
        foreach ($rows as $r) {
            $maxVal = max($maxVal, (float)$r['revenue']);
        }
        if ($maxVal <= 0) {
            $maxVal = 1;
        }

        $barCount = count($rows);
        $rowH = (int)($chartH / max(1, $barCount));
        $barH = (int)($rowH * 0.6);

        foreach ($rows as $i => $row) {
            $y = $padT + $i * $rowH + (int)(($rowH - $barH) / 2);
            $rev = (float)$row['revenue'];
            $barW = (int)($chartW * ($rev / $maxVal));
            // Имя
            imagettftext($img, 10, 0, 20, $y + $barH - 4, $colors['fg'], self::FONT, $this->shorten((string)$row['full_name'], 26));
            // Полоска (градиент по горизонтали)
            for ($x = 0; $x < $barW; $x++) {
                $shade = imagecolorallocate(
                    $img,
                    20 + (int)(80 * $x / max(1, $barW)),
                    42 + (int)(100 * $x / max(1, $barW)),
                    29 + (int)(40 * $x / max(1, $barW))
                );
                imageline($img, $padL + $x, $y, $padL + $x, $y + $barH, $shade);
            }
            imagerectangle($img, $padL, $y, $padL + max(1, $barW), $y + $barH, $colors['border']);
            // Сумма справа
            $label = number_format($rev, 0, ',', ' ') . ' ₽';
            imagettftext($img, 10, 0, $padL + $barW + 8, $y + $barH - 4, $colors['fg'], self::FONT, $label);
        }

        $this->stream($img);
    }

    /** @return array{bg:int,fg:int,grid:int,axis:int,border:int} */
    private function palette($img): array
    {
        return [
            'bg'     => imagecolorallocate($img, 250, 250, 247),
            'fg'     => imagecolorallocate($img, 20, 42, 29),
            'grid'   => imagecolorallocate($img, 220, 220, 220),
            'axis'   => imagecolorallocate($img, 60, 60, 60),
            'border' => imagecolorallocate($img, 20, 42, 29),
        ];
    }

    private function title($img, array $colors, string $text, int $w): void
    {
        imagettftext($img, 13, 0, 30, 30, $colors['fg'], self::FONT_BOLD, $text);
        imageline($img, 30, 38, $w - 30, 38, $colors['grid']);
    }

    private function shorten(string $s, int $max): string
    {
        if (mb_strlen($s) <= $max) return $s;
        return mb_substr($s, 0, $max - 1) . '…';
    }

    private function stream($img): void
    {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: image/png');
        header('Cache-Control: no-store');
        imagepng($img);
        imagedestroy($img);
        exit;
    }
}
