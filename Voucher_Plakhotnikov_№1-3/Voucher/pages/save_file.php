<?php
session_start();
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

// Проверка данных
$has_data = isset(
    $_SESSION['tour_type'], $_SESSION['meal_type'], $_SESSION['client_name'],
    $_SESSION['country'], $_SESSION['days'], $_SESSION['total']
);

if (!$has_data) {
    header('Location: basket.php?save_error=1');
    exit;
}

// Собираем данные
$tour_type_key = $_SESSION['tour_type'];
$meal_type_key = $_SESSION['meal_type'];
$country_idx = $_SESSION['country'];
$selected_services = $_SESSION['services'] ?? [];
$days = $_SESSION['days'];
$total = $_SESSION['total'];

$tour_name = $tour_types[$tour_type_key]['name'];
$base_price = $tour_types[$tour_type_key]['price'];
$country_name = $countries[$tour_type_key][$country_idx]['name'];
$country_surcharge = $countries[$tour_type_key][$country_idx]['surcharge'];
$meal_name = $meal_types[$meal_type_key]['name'];
$meal_price = $meal_types[$meal_type_key]['price'];
$meal_cost = $meal_price * $days;

$services_list = [];
$services_total = 0;
foreach ($selected_services as $si) {
    $si = (int)$si;
    if (isset($services[$tour_type_key][$si])) {
        $services_list[] = $services[$tour_type_key][$si];
        $services_total += $services[$tour_type_key][$si]['price'];
    }
}

$client_name = $_SESSION['client_name'];
$client_phone = $_SESSION['client_phone'];
$client_email = $_SESSION['client_email'];
$voucher_number = rand(1000, 9999);
$price_with_country = $base_price + $country_surcharge;

// --- Генерация DOCX ---
$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

$section = $phpWord->addSection([
    'marginTop' => 600,
    'marginBottom' => 600,
    'marginLeft' => 1200,
    'marginRight' => 800,
]);

// Шапка: одна таблица, пропорция 3:1
// borderBottomSize на параграфе дает линию на всю ширину ячейки
$lineParaStyle = ['borderBottomSize' => 6, 'borderBottomColor' => '000000', 'spaceAfter' => 0];

$headerTable = $section->addTable();

// Строка 1: слева пусто, справа "Утверждено..."
$headerTable->addRow();
$headerTable->addCell(7500);
$rightCell = $headerTable->addCell(2500);
$rightCell->addText('Утверждено Главным', ['size' => 10], ['alignment' => Jc::START]);
$rightCell->addText('Министерством туризма', ['size' => 10], ['alignment' => Jc::START]);
$rightCell->addText('от 01.02.2022', ['size' => 10], ['alignment' => Jc::START]);

// Строка 2: слева пусто, справа "Код формы по ОКУН"
$headerTable->addRow();
$headerTable->addCell(7500);
$headerTable->addCell(2500)->addText('Код формы по ОКУН', ['size' => 10], ['alignment' => Jc::START]);

// Строка 3: "Туроператор Какой-то" (линия на всю ширину) на одном уровне с "061000"
$headerTable->addRow();
$leftCell = $headerTable->addCell(7500);
$leftCell->addText('Туроператор Какой-то', ['size' => 10], $lineParaStyle);
$leftCell->addText('название', ['size' => 7, 'color' => '999999', 'italic' => true], ['alignment' => Jc::CENTER]);
$headerTable->addCell(2500)->addText('061000', ['size' => 10], ['alignment' => Jc::START]);

// Строка 4: "г. Ухта, ИНН" (линия на всю ширину)
$headerTable->addRow();
$leftCell2 = $headerTable->addCell(7500);
$leftCell2->addText('г. Ухта, ИНН 123456987', ['size' => 10], $lineParaStyle);
$leftCell2->addText('город, ИНН', ['size' => 7, 'color' => '999999', 'italic' => true], ['alignment' => Jc::CENTER]);
$headerTable->addCell(2500);

$section->addTextBreak(1);

// Заголовок
$section->addText(
    'Туристическая путевка № ' . $voucher_number,
    ['bold' => true, 'size' => 14],
    ['alignment' => Jc::CENTER]
);

$section->addTextBreak(1);

// Заказчик
$section->addText(
    'Заказчик туристического продукта :   ' . $client_name,
    ['size' => 11]
);

$section->addTextBreak(0);

// Таблица контактов
$borderStyle = [
    'borderTopSize' => 6, 'borderTopColor' => '000000',
    'borderBottomSize' => 6, 'borderBottomColor' => '000000',
    'borderLeftSize' => 6, 'borderLeftColor' => '000000',
    'borderRightSize' => 6, 'borderRightColor' => '000000',
];
$contactTable = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '000000',
    'cellMargin' => 50,
]);
$contactTable->addRow();
$contactTable->addCell(2000, $borderStyle)->addText('Телефон:', ['size' => 10]);
$contactTable->addCell(2000, $borderStyle)->addText($client_phone, ['size' => 10]);
$contactTable->addCell(3000, $borderStyle)->addText('Электронная почта:', ['size' => 10]);
$contactTable->addCell(2500, $borderStyle)->addText($client_email, ['size' => 10]);

$section->addTextBreak(1);

// Тип путевки (label жирный, значение обычное)
$typeRun = $section->addTextRun();
$typeRun->addText('Тип путевки: ', ['size' => 11, 'bold' => true]);
$typeRun->addText($tour_name, ['size' => 11]);
$section->addTextBreak(0);
$section->addText('Страна пребывания: ' . $country_name, ['size' => 11]);
$section->addTextBreak(0);
$section->addText('Цена путевки базовая: ' . $base_price . ' руб.', ['size' => 11]);
$section->addTextBreak(0);
$section->addText('Цена путевки с учетом страны: ' . $price_with_country . ' руб.', ['size' => 11]);
$section->addTextBreak(0);
$section->addText('Питание: ' . $meal_name . ', ' . $meal_price . ' руб/день', ['size' => 11]);
$section->addTextBreak(0);

// Дополнительные услуги (нумерованный список)
$section->addText('Дополнительные услуги:', ['size' => 11]);
if (!empty($services_list)) {
    foreach ($services_list as $i => $srv) {
        $num = $i + 1;
        $section->addText("    {$num}. " . $srv['name'] . ', ' . $srv['price'] . ' руб.', ['size' => 11]);
    }
} else {
    $section->addText('    нет', ['size' => 11]);
}

$section->addTextBreak(0);
$section->addText('Стоимость дополнительных услуг: ' . $services_total . ' руб.', ['size' => 11]);
$section->addText('Количество дней: ' . $days, ['size' => 11]);

$section->addTextBreak(1);

// Полная стоимость
$section->addText(
    'Полная стоимость тура: ' . $total . ' руб.',
    ['bold' => true, 'size' => 13]
);

$section->addTextBreak(2);

// Дата и оператор — оба блока выровнены влево
$footerTable = $section->addTable();
$footerTable->addRow();
$footerTable->addCell(5000)->addText('Дата оформления:', ['size' => 10]);
$footerTable->addCell(5000)->addText('Оператор:', ['size' => 10]);
$footerTable->addRow();
$footerTable->addCell(5000)->addText(date('d.m.Y'), ['size' => 10]);
$footerTable->addCell(5000)->addText('Плахотников В. А.', ['size' => 10]);

// Сохранение
$generated_dir = __DIR__ . '/../generated';
if (!is_dir($generated_dir)) {
    mkdir($generated_dir, 0777, true);
}

$filename = 'Plakhotnikov_' . date('d-m-Y') . '.docx';
$filepath = $generated_dir . '/' . $filename;

try {
    $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($filepath);
    $_SESSION['last_file'] = $filepath;
    $_SESSION['last_filename'] = $filename;
    header('Location: basket.php?saved=1&file=' . urlencode($filename));
} catch (Exception $e) {
    header('Location: basket.php?save_error=1');
}
exit;
