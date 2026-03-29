<?php
session_start();
require_once __DIR__ . '/data.php';

$logged_in = isset($_COOKIE['auth_user']);

// Сохраняем данные из bill.php в сессию
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['country'])) {
    $_SESSION['country'] = (int)$_POST['country'];
    $_SESSION['services'] = $_POST['services'] ?? [];
    $_SESSION['days'] = (int)$_POST['days'];
}

// Проверяем наличие всех данных
$has_data = isset(
    $_SESSION['tour_type'],
    $_SESSION['meal_type'],
    $_SESSION['client_name'],
    $_SESSION['country'],
    $_SESSION['days']
);

// Расчет стоимости
$tour_type_key = $_SESSION['tour_type'] ?? '';
$meal_type_key = $_SESSION['meal_type'] ?? '';
$country_idx = $_SESSION['country'] ?? 0;
$selected_services = $_SESSION['services'] ?? [];
$days = $_SESSION['days'] ?? 1;

$base_price = 0;
$country_surcharge = 0;
$meal_cost = 0;
$services_total = 0;
$services_list = [];
$country_name = '';
$tour_name = '';
$meal_name = '';
$tour_image = '';

if ($has_data) {
    $tour_name = $tour_types[$tour_type_key]['name'] ?? '';
    $base_price = $tour_types[$tour_type_key]['price'] ?? 0;
    $tour_image = $tour_types[$tour_type_key]['image'] ?? '';

    $country_name = $countries[$tour_type_key][$country_idx]['name'] ?? '';
    $country_surcharge = $countries[$tour_type_key][$country_idx]['surcharge'] ?? 0;

    $meal_name = $meal_types[$meal_type_key]['name'] ?? '';
    $meal_price_per_day = $meal_types[$meal_type_key]['price'] ?? 0;
    $meal_cost = $meal_price_per_day * $days;

    foreach ($selected_services as $si) {
        $si = (int)$si;
        if (isset($services[$tour_type_key][$si])) {
            $srv = $services[$tour_type_key][$si];
            $services_list[] = $srv;
            $services_total += $srv['price'];
        }
    }

    $total = $base_price + $country_surcharge + $meal_cost + $services_total;
    $_SESSION['total'] = $total;
}

// Сообщения
$message = '';
$message_class = '';
if (isset($_GET['saved'])) {
    $dl_file = htmlspecialchars($_GET['file'] ?? '');
    $message = 'Файл успешно записан! <a href="download.php?file=' . urlencode($_GET['file'] ?? '') . '">Скачать</a>';
    $message_class = 'message-ok';
}
if (isset($_GET['mail_ok'])) {
    $message = 'Письмо успешно отправлено!';
    $message_class = 'message-ok';
}
if (isset($_GET['mail_error'])) {
    $message = 'Ошибка отправки письма.';
    $message_class = 'message-error';
}
if (isset($_GET['save_error'])) {
    $message = 'Ошибка записи файла.';
    $message_class = 'message-error';
}
?>
<html>
    <head>
        <title>Работа</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="../css/style.css" rel="stylesheet" type="text/css">
    </head>

    <body topmargin="0" bottommargin="0" rightmargin="0"  leftmargin="0"   background="../images/back_main.gif">
        <table cellpadding="0" cellspacing="0" border="0"  align="center" width="583" height="614">
            <tr>
                <td valign="top" width="583" height="208" background="../images/row1.gif">
                    <div style="margin-left:88px; margin-top:57px "><img src="../images/w1.gif">    </div>
                    <div class="auth-status" style="margin-left:200px; margin-top:-15px;">
                        <?php if ($logged_in): ?>
                            Вы зашли как <?= htmlspecialchars($_COOKIE['auth_user']) ?>
                        <?php else: ?>
                            Вы не авторизованы
                        <?php endif; ?>
                    </div>
                    <div style="margin-left:50px; margin-top:55px ">
                        <a href="../index.php">Главная<img src="../images/m1.gif" border="0" ></a>
                        <img src="../images/spacer.gif" width="20" height="10">
                        <a href="order.php">Заказ<img src="../images/m2.gif" border="0" ></a>
                        <img src="../images/spacer.gif" width="5" height="10">
                        <a href="basket.php">Корзина<img src="../images/m3.gif" border="0" ></a>
                        <img src="../images/spacer.gif" width="5" height="10">
                        <a href="index-3.php">О компании<img src="../images/m4.gif" border="0" ></a>
                        <img src="../images/spacer.gif" width="5" height="10">
                        <a href="index-4.php">Контакты<img src="../images/m5.gif" border="0" ></a>
                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top" width="583" height="338"  bgcolor="#FFFFFF">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td valign="top" height="338" width="42"></td>
                            <td valign="top" height="338" width="492">
                                <?php if (!$logged_in || !$has_data): ?>
                                    <div style="margin-left:6px; margin-top:20px;">
                                        <?php if (!$logged_in): ?>
                                            Для просмотра корзины необходимо <a href="../index.php">авторизоваться</a>.
                                        <?php else: ?>
                                            Корзина пуста. <a href="order.php">Оформите заказ</a>.
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="492" valign="top" height="106">
                                            <div style="margin-left:1px; margin-top:2px; margin-right:10px "><br>
                                                <div style="margin-left:5px "><img src="../images/1_p1.gif" align="left"></div>
                                                <div style="margin-left:95px "><font class="title"><?= htmlspecialchars($tour_name) ?></font><br>
                                                    <?php if ($message): ?>
                                                        <span class="<?= $message_class ?>"><?= $message ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <table cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed; width:492px;">
                                                <tr>
                                                    <!-- 3-1: Информация о заказе -->
                                                    <td valign="top" height="232" width="248">
                                                        <div style="margin-left:6px; margin-top:2px; "><img src="../images/hl.gif"></div>
                                                        <div style="margin-left:6px; margin-top:5px;">
                                                            <img src="<?= htmlspecialchars($tour_image) ?>" width="220" style="height:auto;"><br>
                                                            <div class="info-block">
                                                                <b>Тип:</b> <?= htmlspecialchars($tour_name) ?><br>
                                                                <b>Страна:</b> <?= htmlspecialchars($country_name) ?><br>
                                                                <b>Питание:</b> <?= htmlspecialchars($meal_name) ?><br>
                                                                <b>Дней:</b> <?= $days ?><br>
                                                                <?php if (!empty($services_list)): ?>
                                                                    <b>Доп. услуги:</b><br>
                                                                    <?php foreach ($services_list as $srv): ?>
                                                                        &bull; <?= htmlspecialchars($srv['name']) ?><br>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                                <br>
                                                                <b>Заказчик:</b> <?= htmlspecialchars($_SESSION['client_name']) ?><br>
                                                                <b>Тел:</b> <?= htmlspecialchars($_SESSION['client_phone']) ?><br>
                                                                <b>Email:</b> <?= htmlspecialchars($_SESSION['client_email']) ?>
                                                            </div>
                                                        </div>

                                                    <td valign="top" height="215" width="1" background="../images/tal.gif" style="background-repeat:repeat-y"></td>
                                                    <!-- 3-2: Ценовая сводка -->
                                                    <td valign="top" height="215" width="243">
                                                        <div style="margin-left:22px; margin-top:2px; "><img src="../images/hl.gif"></div>
                                                        <div style="margin-left:22px; margin-top:5px;">
                                                            <div class="form-label">Стоимость:</div>
                                                            <div class="info-block">
                                                                Базовая цена: <?= $base_price ?> руб.<br>
                                                                Страна (<?= htmlspecialchars($country_name) ?>): +<?= $country_surcharge ?> руб.<br>
                                                                Питание (<?= htmlspecialchars($meal_name) ?>, <?= $days ?> дн.): <?= $meal_cost ?> руб.<br>
                                                                <?php if (!empty($services_list)): ?>
                                                                    <?php foreach ($services_list as $srv): ?>
                                                                        <?= htmlspecialchars($srv['name']) ?>: +<?= $srv['price'] ?> руб.<br>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                                <div class="total-sum">Итого: <?= $total ?> руб.</div>
                                                            </div>
                                                        </div>
                                                        <div class="btn-row" style="margin-left:22px; margin-top:10px;">
                                                            <form method="post" action="send_email.php" style="display:inline;">
                                                                <input type="submit" class="btn" value="Отправить на почту">
                                                            </form>
                                                            <form method="post" action="save_file.php" style="display:inline;">
                                                                <input type="submit" class="btn" value="Записать в файл">
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <?php endif; ?>
                            </td>
                            <td valign="top" height="338" width="49"></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top" width="583" height="68" background="../images/row3.gif">
                    <div style="margin-left:51px; margin-top:31px ">
                        <a href="#"><img src="../images/p1.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="26" height="9">
                        <a href="#"><img src="../images/p2.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="30" height="9">
                        <a href="#"><img src="../images/p3.gif" border="0"></a>
                        <img src="../images/spacer.gif" width="149" height="9">
                        <a href="index-5.php"><img src="../images/copyright.gif" border="0"></a>
                    </div>
                </td>
            </tr>

        </table>
    </body>
</html>
