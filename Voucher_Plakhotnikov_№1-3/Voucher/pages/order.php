<?php
session_start();
require_once __DIR__ . '/data.php';

$logged_in = isset($_COOKIE['auth_user']);
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
                                <?php if (!$logged_in): ?>
                                    <div style="margin-left:6px; margin-top:20px;">
                                        Для оформления заказа необходимо <a href="../index.php">авторизоваться</a>.
                                    </div>
                                <?php else: ?>
                                <form method="post" action="bill.php">
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="492" valign="top" height="106">
                                            <div style="margin-left:1px; margin-top:2px; margin-right:10px "><br>
                                                <div style="margin-left:5px "><img src="../images/1_p1.gif" align="left"></div>
                                                <div style="margin-left:95px "><font class="title">Оформление заказа</font><br>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <table cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td valign="top" height="232" width="248">
                                                        <div style="margin-left:6px; margin-top:2px; "><img src="../images/hl.gif"></div>
                                                        <!-- 1-1: Тип путевки -->
                                                        <div style="margin-left:6px; margin-top:7px;">
                                                            <div class="form-label">Тип путевки:</div>
                                                            <select name="tour_type" required>
                                                                <?php foreach ($tour_types as $key => $type): ?>
                                                                    <option value="<?= $key ?>" <?= (isset($_SESSION['tour_type']) && $_SESSION['tour_type'] === $key) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($type['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <!-- 1-3: Вид питания -->
                                                        <div style="margin-left:6px; margin-top:10px;">
                                                            <div class="form-label">Вид питания:</div>
                                                            <?php foreach ($meal_types as $key => $meal): ?>
                                                                <div style="margin-bottom:3px;">
                                                                    <label><input type="radio" name="meal_type" value="<?= $key ?>" <?= (isset($_SESSION['meal_type']) && $_SESSION['meal_type'] === $key) ? 'checked' : ($key === 'breakfast' && !isset($_SESSION['meal_type']) ? 'checked' : '') ?> required>
                                                                    <?= htmlspecialchars($meal['name']) ?> (<?= $meal['price'] ?> руб/день)</label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>

                                                    <td valign="top" height="215" width="1" background="../images/tal.gif" style="background-repeat:repeat-y"></td>
                                                    <td valign="top" height="215" width="243">
                                                        <div style="margin-left:22px; margin-top:2px; "><img src="../images/hl.gif"></div>
                                                        <!-- 1-2: Контактные данные -->
                                                        <div style="margin-left:22px; margin-top:7px;">
                                                            <div class="form-label">Контактные данные:</div>
                                                            <table cellpadding="2" cellspacing="0" border="0" style="font-size:10px;">
                                                                <tr>
                                                                    <td style="font-size:10px;font-family:Tahoma;">Имя:</td>
                                                                    <td><input type="text" name="client_name" value="<?= htmlspecialchars($_SESSION['client_name'] ?? '') ?>" required style="width:120px;font-size:10px;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="font-size:10px;font-family:Tahoma;">Телефон:</td>
                                                                    <td><input type="tel" name="client_phone" value="<?= htmlspecialchars($_SESSION['client_phone'] ?? '') ?>" required style="width:120px;font-size:10px;"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="font-size:10px;font-family:Tahoma;">Почта:</td>
                                                                    <td><input type="email" name="client_email" value="<?= htmlspecialchars($_SESSION['client_email'] ?? '') ?>" required style="width:120px;font-size:10px;"></td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="btn-row" style="margin-left:22px; margin-top:15px;">
                                                            <input type="submit" class="btn" value="Далее">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                </form>
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
