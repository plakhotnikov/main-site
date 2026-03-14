<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\SimpleType\TblWidth;

if (!isset($_POST['submit'])) {
    header('Location: index.php');
    exit;
}

// --- Collect form data ---
$surname = trim($_POST['surname'] ?? '');
$city = trim($_POST['city'] ?? '');
$delivery_date = trim($_POST['delivery_date'] ?? '');
$address = trim($_POST['address'] ?? '');
$color_name = trim($_POST['color'] ?? '');
$selected_items = $_POST['items'] ?? [];
$quantities = $_POST['qty'] ?? [];
$selected_price_file = basename(trim($_POST['price_file'] ?? ''));

if ($surname === '' || $city === '' || $delivery_date === '' || $address === '' || $color_name === '' || empty($selected_items)) {
    header('Location: index.php');
    exit;
}

// --- Color multipliers ---
$colors = [
    'Орех' => ['multiplier' => 1.1, 'image' => 'орех.png'],
    'Дуб мореный' => ['multiplier' => 1.2, 'image' => 'дуб.png'],
    'Палисандр' => ['multiplier' => 1.3, 'image' => 'палисандр.png'],
    'Эбеновое дерево' => ['multiplier' => 1.4, 'image' => 'эбен.png'],
    'Клен' => ['multiplier' => 1.5, 'image' => 'клен.png'],
    'Лиственница' => ['multiplier' => 1.6, 'image' => 'лиственница.png'],
];

if (!isset($colors[$color_name])) {
    header('Location: index.php');
    exit;
}

$color_multiplier = $colors[$color_name]['multiplier'];
$color_image_file = __DIR__ . '/src/' . $colors[$color_name]['image'];

// --- Read prices from CSV ---
$available_csv_files = [];
foreach (glob(__DIR__ . '/src/*.csv') ?: [] as $csv_file_path) {
    $available_csv_files[] = basename($csv_file_path);
}

if (
    $selected_price_file === ''
    || !preg_match('/\.csv$/i', $selected_price_file)
    || !in_array($selected_price_file, $available_csv_files, true)
) {
    if (in_array('price.csv', $available_csv_files, true)) {
        $selected_price_file = 'price.csv';
    } else {
        $selected_price_file = $available_csv_files[0] ?? '';
    }
}

if ($selected_price_file === '') {
    header('Location: index.php');
    exit;
}

$prices = [];
$csv_path = __DIR__ . '/src/' . $selected_price_file;
$csv_content = file_get_contents($csv_path);
if ($csv_content === false) {
    header('Location: index.php');
    exit;
}
$csv_content = preg_replace('/^\xEF\xBB\xBF/', '', $csv_content); // Remove BOM
$lines = preg_split('/\r\n|\r|\n/', $csv_content);

foreach ($lines as $i => $line) {
    if ($i < 2 || trim($line) === '') continue;
    $parts = explode(';', $line);
    if (count($parts) >= 2) {
        $name = trim($parts[0]);
        $price = (int) trim($parts[1]);
        if ($name !== '' && $price > 0) {
            $prices[$name] = $price;
        }
    }
}

// --- Build order items ---
$order_items = [];
$total = 0;
foreach ($selected_items as $item_name) {
    if (!isset($prices[$item_name])) continue;
    $qty = max(1, (int) ($quantities[$item_name] ?? 1));
    $base_price = $prices[$item_name];
    $sum = $base_price * $qty;
    $order_items[] = [
        'name' => $item_name,
        'price' => $base_price,
        'qty' => $qty,
        'sum' => $sum,
    ];
    $total += $sum;
}

if (empty($order_items)) {
    header('Location: index.php');
    exit;
}

// Format delivery date
$date_obj = DateTime::createFromFormat('Y-m-d', $delivery_date);
$formatted_date = $date_obj ? $date_obj->format('d.m.Y') : htmlspecialchars($delivery_date);

$invoice_number = rand(1000, 9999);
$generated_dir = __DIR__ . '/generated';
if (!is_dir($generated_dir)) {
    mkdir($generated_dir, 0777, true);
}

// =============================================
// GENERATE EXCEL INVOICE
// =============================================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Накладная');

// Column widths
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(14);
$sheet->getColumnDimension('F')->setWidth(10);

// --- Barcode image (top-right, row 1, above header) ---
$barcode_path = __DIR__ . '/src/штрих.JPG';
if (file_exists($barcode_path)) {
    $barcode = new Drawing();
    $barcode->setPath($barcode_path);
    $barcode->setCoordinates('E1');
    $barcode->setWidth(140);
    $barcode->setOffsetX(10);
    $barcode->setWorksheet($sheet);
    $sheet->getRowDimension(1)->setRowHeight(40);
}

// --- Header (starts at row 2) ---
$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', "Накладная № $invoice_number");
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A3:F3');
$sheet->setCellValue('A3', "Адрес получения заказа: г. $city, $address");
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A4:F4');
$sheet->setCellValue('A4', "Дата получения заказа: $formatted_date");
$sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- Table header ---
$header_row = 6;
$headers = ['№', 'Наименование товара', 'Цвет', 'Цена', 'Количество', 'Сумма'];
$columns = ['A', 'B', 'C', 'D', 'E', 'F'];

$border_style = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

foreach ($headers as $idx => $h) {
    $cell = $columns[$idx] . $header_row;
    $sheet->setCellValue($cell, $h);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}
$sheet->getStyle("A{$header_row}:F{$header_row}")->applyFromArray($border_style);

// --- Data rows ---
$row = $header_row + 1;
foreach ($order_items as $idx => $item) {
    $sheet->setCellValue("A{$row}", $idx + 1);
    $sheet->setCellValue("B{$row}", $item['name']);
    $sheet->setCellValue("D{$row}", $item['price']);
    $sheet->setCellValue("E{$row}", $item['qty']);
    $sheet->setCellValue("F{$row}", $item['sum']);

    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($border_style);
    $row++;
}

// --- Color row ---
$color_total = $total * $color_multiplier;

$sheet->setCellValue("B{$row}", "Цвет: " . mb_strtolower($color_name));
$sheet->getRowDimension($row)->setRowHeight(45);

if (file_exists($color_image_file)) {
    $drawing = new Drawing();
    $drawing->setPath($color_image_file);
    $drawing->setCoordinates("C{$row}");
    $drawing->setWidth(35);
    $drawing->setHeight(35);
    $drawing->setOffsetX(18);
    $drawing->setOffsetY(5);
    $drawing->setWorksheet($sheet);
}

$sheet->setCellValue("D{$row}", $color_multiplier);
$sheet->setCellValue("F{$row}", $color_total);
$sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("C{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle("A{$row}:F{$row}")->applyFromArray($border_style);
$row++;

// --- Total row: "Итого:" centered, borders only top and right on merged area, F cell full border ---
$sheet->mergeCells("A{$row}:E{$row}");
$sheet->setCellValue("A{$row}", 'Итого:');
$sheet->getStyle("A{$row}")->getFont()->setBold(true);
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
    'borders' => [
        'top' => ['borderStyle' => Border::BORDER_THIN],
        'right' => ['borderStyle' => Border::BORDER_THIN],
    ],
]);

$sheet->setCellValue("F{$row}", $color_total);
$sheet->getStyle("F{$row}")->getFont()->setBold(true);
$sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle("F{$row}")->applyFromArray($border_style);
$row += 2;

// --- Summary text ---
$total_formatted = number_format($color_total, 2, ',', ' ');
$sheet->mergeCells("A{$row}:F{$row}");
$sheet->setCellValue("A{$row}", "Всего наименований " . count($order_items) . ", на сумму {$total_formatted} руб.");
$row += 2;

// --- Warranty text in ONE merged cell ---
$warranty_path = __DIR__ . '/src/Гарантийное обслуживание.txt';
if (file_exists($warranty_path)) {
    $warranty_text = file_get_contents($warranty_path);
    $warranty_text = preg_replace('/^\xEF\xBB\xBF/', '', $warranty_text);
    $warranty_text = str_replace("\r\n", "\n", $warranty_text);
    $warranty_text = str_replace("\r", "\n", $warranty_text);
    $warranty_text = trim($warranty_text);

    $sheet->mergeCells("A{$row}:F{$row}");
    $sheet->setCellValue("A{$row}", $warranty_text);
    $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true);
    $sheet->getStyle("A{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

    // Bold the first line "Информация о гарантийном обслуживании:"
    $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
    $warranty_lines = explode("\n", $warranty_text);
    foreach ($warranty_lines as $li => $wline) {
        if ($li > 0) {
            $richText->createText("\n");
        }
        $wline = trim($wline);
        if (mb_strpos($wline, 'Информация о гарантийном') !== false) {
            $boldPart = $richText->createTextRun($wline);
            $boldPart->getFont()->setBold(true);
        } else {
            $richText->createText($wline);
        }
    }
    $sheet->setCellValue("A{$row}", $richText);

    // Set row height to fit all text
    $line_count = count($warranty_lines);
    $sheet->getRowDimension($row)->setRowHeight($line_count * 30);
}

// Save Excel
$xlsx_filename = "Документ_на_выдачу_№{$invoice_number}.xlsx";
$xlsx_path = $generated_dir . '/' . $xlsx_filename;
$writer = new Xlsx($spreadsheet);
$writer->save($xlsx_path);

// =============================================
// GENERATE/APPEND DOCX DOCUMENT RECORD → convert to PDF
// =============================================
$docx_filename = 'Запись_о_документах.docx';
$docx_path = $generated_dir . '/' . $docx_filename;

$cellBorderStyle = [
    'borderTopSize' => 6,
    'borderTopColor' => '000000',
    'borderBottomSize' => 6,
    'borderBottomColor' => '000000',
    'borderLeftSize' => 6,
    'borderLeftColor' => '000000',
    'borderRightSize' => 6,
    'borderRightColor' => '000000',
];

$boldFont = ['bold' => true, 'size' => 12];
$centerParagraph = ['alignment' => 'center'];
$normalFont = ['size' => 12];

// Collect existing records if file exists
$existing_records = [];
if (file_exists($docx_path)) {
    $oldWord = WordIOFactory::load($docx_path, 'Word2007');
    $sections = $oldWord->getSections();
    if (!empty($sections)) {
        foreach ($sections[0]->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                foreach ($element->getRows() as $ri => $tableRow) {
                    if ($ri === 0) continue; // skip header
                    $cells = $tableRow->getCells();
                    $rowData = [];
                    foreach ($cells as $cell) {
                        $text = '';
                        foreach ($cell->getElements() as $el) {
                            if ($el instanceof \PhpOffice\PhpWord\Element\Text) {
                                $text = $el->getText();
                            } elseif ($el instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                foreach ($el->getElements() as $tre) {
                                    if (method_exists($tre, 'getText')) {
                                        $text .= $tre->getText();
                                    }
                                }
                            }
                        }
                        $rowData[] = $text;
                    }
                    $existing_records[] = $rowData;
                }
                break;
            }
        }
    }
}

// Add current order
$record_number = count($existing_records) + 1;
$existing_records[] = [
    (string) $record_number,
    (string) $invoice_number,
    $city,
    $address,
    number_format($color_total, 0, ',', ' '),
];

// Build fresh DOCX with all records
$phpWord = new PhpWord();
$section = $phpWord->addSection([
    'orientation' => 'landscape',
    'marginTop' => 600,
    'marginBottom' => 600,
    'marginLeft' => 600,
    'marginRight' => 600,
]);

$table = $section->addTable([
    'borderSize' => 6,
    'borderColor' => '000000',
    'cellMargin' => 80,
    'unit' => TblWidth::TWIP,
]);

// Header
$table->addRow();
$table->addCell(1200, $cellBorderStyle)->addText('№', $boldFont, $centerParagraph);
$table->addCell(2800, $cellBorderStyle)->addText('№ накладной', $boldFont, $centerParagraph);
$table->addCell(3000, $cellBorderStyle)->addText('Город', $boldFont, $centerParagraph);
$table->addCell(3500, $cellBorderStyle)->addText('Адрес', $boldFont, $centerParagraph);
$table->addCell(2800, $cellBorderStyle)->addText('Итоговая сумма', $boldFont, $centerParagraph);

// Data rows
foreach ($existing_records as $rec) {
    $table->addRow();
    $table->addCell(1200, $cellBorderStyle)->addText($rec[0] ?? '', $normalFont, $centerParagraph);
    $table->addCell(2800, $cellBorderStyle)->addText($rec[1] ?? '', $normalFont, $centerParagraph);
    $table->addCell(3000, $cellBorderStyle)->addText($rec[2] ?? '', $normalFont, $centerParagraph);
    $table->addCell(3500, $cellBorderStyle)->addText($rec[3] ?? '', $normalFont, $centerParagraph);
    $table->addCell(2800, $cellBorderStyle)->addText($rec[4] ?? '', $normalFont, $centerParagraph);
}

$docxWriter = WordIOFactory::createWriter($phpWord, 'Word2007');
$docxWriter->save($docx_path);

// Also save as ODT
$odt_path = $generated_dir . '/Запись_о_документах.odt';
$convert_odt_cmd = 'HOME=/tmp soffice --headless --convert-to odt --outdir ' . escapeshellarg($generated_dir) . ' ' . escapeshellarg($docx_path) . ' 2>&1';
exec($convert_odt_cmd);

// Convert DOCX to PDF via LibreOffice
$pdf_filename = 'Запись_о_документах.pdf';
$convert_cmd = 'HOME=/tmp soffice --headless --convert-to pdf --outdir ' . escapeshellarg($generated_dir) . ' ' . escapeshellarg($docx_path) . ' 2>&1';
exec($convert_cmd, $output, $return_code);

// --- Redirect back with results ---
$params = http_build_query([
    'success' => 1,
    'invoice' => $invoice_number,
    'total' => number_format($color_total, 2, ',', ' '),
    'items_count' => count($order_items),
    'xlsx' => $xlsx_filename,
    'pdf' => $pdf_filename,
]);

header("Location: index.php?{$params}");
exit;
