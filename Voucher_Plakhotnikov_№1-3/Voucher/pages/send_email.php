<?php
session_start();
require_once __DIR__ . '/data.php';

// Проверка данных
$has_data = isset(
    $_SESSION['tour_type'], $_SESSION['meal_type'], $_SESSION['client_name'],
    $_SESSION['country'], $_SESSION['days'], $_SESSION['total']
);

if (!$has_data) {
    header('Location: basket.php?mail_error=1');
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
$country_name = $countries[$tour_type_key][$country_idx]['name'];
$meal_name = $meal_types[$meal_type_key]['name'];

$services_list = [];
foreach ($selected_services as $si) {
    $si = (int)$si;
    if (isset($services[$tour_type_key][$si])) {
        $services_list[] = $services[$tour_type_key][$si];
    }
}

$client_name = $_SESSION['client_name'];
$client_email = $_SESSION['client_email'];

// Картинка тура — встраиваем как inline CID
$image_rel_path = $tour_types[$tour_type_key]['image'];
$image_abs_path = realpath(__DIR__ . '/' . $image_rel_path);
$image_cid = 'tour_image_' . $tour_type_key;
$image_mime = '';
$image_data = '';
$has_image = false;
if ($image_abs_path && file_exists($image_abs_path)) {
    $ext = strtolower(pathinfo($image_abs_path, PATHINFO_EXTENSION));
    $mime_map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
    $image_mime = $mime_map[$ext] ?? 'image/jpeg';
    $image_data = file_get_contents($image_abs_path);
    $has_image = true;
}

// Если DOCX ещё не создан — генерируем
$attachment_path = $_SESSION['last_file'] ?? '';
$attachment_name = $_SESSION['last_filename'] ?? '';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

if (!$attachment_path || !file_exists($attachment_path)) {

    $base_price = $tour_types[$tour_type_key]['price'];
    $country_surcharge = $countries[$tour_type_key][$country_idx]['surcharge'];
    $meal_price = $meal_types[$meal_type_key]['price'];
    $meal_cost = $meal_price * $days;
    $services_total = 0;
    foreach ($services_list as $srv) {
        $services_total += $srv['price'];
    }
    $price_with_country = $base_price + $country_surcharge;
    $voucher_number = rand(1000, 9999);

    $phpWord = new PhpWord();
    $phpWord->setDefaultFontName('Times New Roman');
    $phpWord->setDefaultFontSize(12);
    $section = $phpWord->addSection(['marginTop' => 600, 'marginBottom' => 600, 'marginLeft' => 1200, 'marginRight' => 800]);

    $lineParaStyle = ['borderBottomSize' => 6, 'borderBottomColor' => '000000', 'spaceAfter' => 0];
    $headerTable = $section->addTable();
    $headerTable->addRow();
    $headerTable->addCell(7500);
    $rightCell = $headerTable->addCell(2500);
    $rightCell->addText('Утверждено Главным', ['size' => 10], ['alignment' => Jc::START]);
    $rightCell->addText('Министерством туризма', ['size' => 10], ['alignment' => Jc::START]);
    $rightCell->addText('от 01.02.2022', ['size' => 10], ['alignment' => Jc::START]);
    $headerTable->addRow();
    $headerTable->addCell(7500);
    $headerTable->addCell(2500)->addText('Код формы по ОКУН', ['size' => 10], ['alignment' => Jc::START]);
    $headerTable->addRow();
    $leftCell = $headerTable->addCell(7500);
    $leftCell->addText('Туроператор Какой-то', ['size' => 10], $lineParaStyle);
    $leftCell->addText('название', ['size' => 7, 'color' => '999999', 'italic' => true], ['alignment' => Jc::CENTER]);
    $headerTable->addCell(2500)->addText('061000', ['size' => 10], ['alignment' => Jc::START]);
    $headerTable->addRow();
    $leftCell2 = $headerTable->addCell(7500);
    $leftCell2->addText('г. Ухта, ИНН 123456987', ['size' => 10], $lineParaStyle);
    $leftCell2->addText('город, ИНН', ['size' => 7, 'color' => '999999', 'italic' => true], ['alignment' => Jc::CENTER]);
    $headerTable->addCell(2500);

    $section->addTextBreak(1);
    $section->addText('Туристическая путевка № ' . $voucher_number, ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
    $section->addTextBreak(1);
    $section->addText('Заказчик туристического продукта :   ' . $client_name, ['size' => 11]);
    $section->addTextBreak(0);

    $borderStyle = ['borderTopSize' => 6, 'borderTopColor' => '000000', 'borderBottomSize' => 6, 'borderBottomColor' => '000000', 'borderLeftSize' => 6, 'borderLeftColor' => '000000', 'borderRightSize' => 6, 'borderRightColor' => '000000'];
    $contactTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 50]);
    $contactTable->addRow();
    $contactTable->addCell(2000, $borderStyle)->addText('Телефон:', ['size' => 10]);
    $contactTable->addCell(2000, $borderStyle)->addText($_SESSION['client_phone'], ['size' => 10]);
    $contactTable->addCell(3000, $borderStyle)->addText('Электронная почта:', ['size' => 10]);
    $contactTable->addCell(2500, $borderStyle)->addText($client_email, ['size' => 10]);
    $section->addTextBreak(1);

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
    $section->addText('Полная стоимость тура: ' . $total . ' руб.', ['bold' => true, 'size' => 13]);
    $section->addTextBreak(2);
    $footerTable = $section->addTable();
    $footerTable->addRow();
    $footerTable->addCell(5000)->addText('Дата оформления:', ['size' => 10]);
    $footerTable->addCell(5000)->addText('Оператор:', ['size' => 10]);
    $footerTable->addRow();
    $footerTable->addCell(5000)->addText(date('d.m.Y'), ['size' => 10]);
    $footerTable->addCell(5000)->addText('Плахотников В. А.', ['size' => 10]);

    $generated_dir = __DIR__ . '/../generated';
    if (!is_dir($generated_dir)) {
        mkdir($generated_dir, 0777, true);
    }
    $attachment_name = 'Plakhotnikov_' . date('d-m-Y') . '.docx';
    $attachment_path = $generated_dir . '/' . $attachment_name;
    $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($attachment_path);
    $_SESSION['last_file'] = $attachment_path;
    $_SESSION['last_filename'] = $attachment_name;
}

// Формируем HTML-тело письма
$services_html = '';
if (!empty($services_list)) {
    $services_html = '<ul>';
    foreach ($services_list as $srv) {
        $services_html .= '<li>' . htmlspecialchars($srv['name']) . '</li>';
    }
    $services_html .= '</ul>';
}

$image_tag = $has_image ? '<img src="cid:' . $image_cid . '" style="width:280px;height:auto;">' : '';

$info_html = 'Вы приобрели путевку:<br>'
    . '&nbsp;&nbsp;&nbsp;&nbsp;<b>' . htmlspecialchars($tour_name) . '</b>, страна ' . htmlspecialchars($country_name) . '.<br>'
    . 'В путевку входят: <b>' . htmlspecialchars($meal_name) . '</b><br>';
if (!empty($services_list)) {
    $info_html .= 'Дополнительные услуги:' . $services_html;
}

$html_body = '
<html>
<body style="font-family: Arial, sans-serif;">
    <p style="text-align:center;"><b>Уважаемый ' . htmlspecialchars($client_name) . '!</b></p>
    <table cellpadding="0" cellspacing="10" border="0">
        <tr>
            <td valign="top" width="300">' . $image_tag . '</td>
            <td valign="top">' . $info_html . '</td>
        </tr>
    </table>
    <p>Полная стоимость тура: <b>' . $total . ' руб.</b></p>
    <br>
    <p><i>Почти настоящая компания.</i></p>
</body>
</html>';

// Отправка через PHPMailer + Gmail SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // SMTP настройки
    $mail->isSMTP();
    require_once __DIR__ . '/mail_config.php';
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    // Адреса
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->addAddress($client_email, $client_name);

    // Тема
    $mail->Subject = 'Для заказа «Туристическая путевка»';

    // HTML тело
    $mail->isHTML(true);
    $mail->Body = $html_body;

    // Inline-картинка
    if ($has_image && $image_abs_path) {
        $mail->addEmbeddedImage($image_abs_path, $image_cid, 'tour.jpg');
    }

    // Вложение DOCX
    if ($attachment_path && file_exists($attachment_path)) {
        $mail->addAttachment($attachment_path, $attachment_name);
    }

    $mail->send();
    $sent = true;
} catch (\Exception $e) {
    $sent = false;
}

if ($sent) {
    header('Location: basket.php?mail_ok=1');
} else {
    header('Location: basket.php?mail_error=1');
}
exit;
