<?php
declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

final class ReportDocx
{
    public static function generate(string $filePath, array $request, string $content, float $totalCost, array $consultations): void
    {
        $word = new PhpWord();
        $word->setDefaultFontName('DejaVu Sans');
        $word->setDefaultFontSize(11);

        $section = $word->addSection([
            'marginLeft' => Converter::cmToTwip(2.5),
            'marginRight' => Converter::cmToTwip(1.5),
            'marginTop' => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
        ]);

        $word->addTitleStyle(1, ['size' => 16, 'bold' => true, 'color' => '142A1D']);
        $word->addTitleStyle(2, ['size' => 13, 'bold' => true, 'color' => '142A1D']);

        $section->addText('Никс Менеджмент — Консалтинговая компания', ['bold' => true, 'size' => 14, 'color' => '142A1D']);
        $section->addText('Итоговый отчёт по заявке №' . (int)$request['id'], ['bold' => true, 'size' => 13]);
        $section->addTextBreak(1);

        $section->addText('Дата формирования: ' . date('d.m.Y H:i'));
        $section->addText('Клиент: ' . ($request['client_name'] ?? '') . ' (' . ($request['client_company'] ?? '') . ')');
        $section->addText('Услуга: ' . ($request['service_name'] ?? '') . ' — категория «' . ($request['category_name'] ?? '') . '»');
        $section->addText('Консультант: ' . ($request['consultant_name'] ?? '—'));
        $section->addText('Статус заявки: ' . ($request['status_name'] ?? ''));
        $section->addText('Приоритет: ' . ($request['priority'] ?? ''));
        if (!empty($request['deadline'])) {
            $section->addText('Дедлайн: ' . date('d.m.Y', strtotime((string)$request['deadline'])));
        }
        $section->addTextBreak(1);

        $section->addTitle('1. Описание задачи', 2);
        $section->addText((string)$request['description']);
        $section->addTextBreak(1);

        $section->addTitle('2. Содержание отчёта', 2);
        foreach (preg_split("/\r?\n/", $content) as $line) {
            $section->addText($line);
        }
        $section->addTextBreak(1);

        if ($consultations) {
            $section->addTitle('3. Хронология консультаций', 2);
            foreach ($consultations as $c) {
                $section->addText('• ' . date('d.m.Y H:i', strtotime((string)$c['held_at']))
                    . ' (' . (int)$c['duration_min'] . ' мин): ' . (string)$c['notes']);
            }
            $section->addTextBreak(1);
        }

        $section->addTitle('Итоговая стоимость', 2);
        $section->addText('Сумма к оплате: ' . number_format($totalCost, 2, ',', ' ') . ' ₽',
            ['bold' => true, 'size' => 12]);

        $section->addTextBreak(2);
        $section->addText('________________________________');
        $section->addText('Подпись консультанта');

        $writer = IOFactory::createWriter($word, 'Word2007');
        $writer->save($filePath);
    }
}
