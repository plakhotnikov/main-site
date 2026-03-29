<?php
session_start();
require_once __DIR__ . '/data.php';

$logged_in = isset($_COOKIE['auth_user']);

// Сохраняем данные из order.php в сессию
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tour_type'])) {
    $_SESSION['tour_type'] = $_POST['tour_type'];
    $_SESSION['meal_type'] = $_POST['meal_type'];
    $_SESSION['client_name'] = trim($_POST['client_name']);
    $_SESSION['client_phone'] = trim($_POST['client_phone']);
    $_SESSION['client_email'] = trim($_POST['client_email']);
}

$tour_type_key = $_SESSION['tour_type'] ?? '';
$has_order_data = isset($_SESSION['tour_type'], $_SESSION['meal_type'], $_SESSION['client_name']);
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
               <div style="margin-left:88px; margin-top:57px "><img src="../images/w1.gif"></div>
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
                        <?php if (!$logged_in || !$has_order_data): ?>
                            <div style="margin-left:6px; margin-top:20px;">
                                <?php if (!$logged_in): ?>
                                    Для оформления заказа необходимо <a href="../index.php">авторизоваться</a>.
                                <?php else: ?>
                                    Сначала заполните <a href="order.php">форму заказа</a>.
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                        <form method="post" action="basket.php">
                        <table cellpadding="0" cellspacing="0" border="0">
                           <tr>
                              <td width="492" valign="top" height="106">
                                 <div style="margin-left:1px; margin-top:2px; margin-right:10px "><br>
                                    <div style="margin-left:5px "><img src="../images/1_p1.gif" align="left"></div>
                                    <div style="margin-left:95px "><font class="title"><?= htmlspecialchars($tour_types[$tour_type_key]['name'] ?? 'Заказ') ?></font>
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
                                          <!-- 2-1: Страна -->
                                          <div style="margin-left:6px; margin-top:7px;">
                                              <div class="form-label">Страна пребывания:</div>
                                              <?php if (isset($countries[$tour_type_key])): ?>
                                                  <?php foreach ($countries[$tour_type_key] as $i => $country): ?>
                                                      <div style="margin-bottom:3px;">
                                                          <label><input type="radio" name="country" value="<?= $i ?>" <?= ($i === 0) ? 'checked' : '' ?> required>
                                                          <?= htmlspecialchars($country['name']) ?> (+<?= $country['surcharge'] ?> руб.)</label>
                                                      </div>
                                                  <?php endforeach; ?>
                                              <?php endif; ?>
                                          </div>
                                          <!-- 2-2: Дополнительные услуги -->
                                          <div style="margin-left:6px; margin-top:10px;">
                                              <div class="form-label"><?= htmlspecialchars($tour_types[$tour_type_key]['services_label'] ?? 'Доп. услуги') ?>:</div>
                                              <?php if (isset($services[$tour_type_key])): ?>
                                                  <?php foreach ($services[$tour_type_key] as $i => $service): ?>
                                                      <div style="margin-bottom:3px;">
                                                          <label><input type="checkbox" name="services[]" value="<?= $i ?>">
                                                          <?= htmlspecialchars($service['name']) ?> (+<?= $service['price'] ?> руб.)</label>
                                                      </div>
                                                  <?php endforeach; ?>
                                              <?php endif; ?>
                                          </div>

                                       <td valign="top" height="215" width="1" background="../images/tal.gif" style="background-repeat:repeat-y"></td>
                                       <td valign="top" height="215" width="243">
                                          <div style="margin-left:22px; margin-top:2px; "><img src="../images/hl.gif"></div>
                                          <!-- 2-3: Количество дней -->
                                          <div style="margin-left:22px; margin-top:7px;">
                                              <div class="form-label">Количество дней:</div>
                                              <input type="number" name="days" min="1" max="30" value="<?= htmlspecialchars($_SESSION['days'] ?? '1') ?>" required style="width:60px;">
                                          </div>
                                          <!-- Кнопки -->
                                          <div class="btn-row" style="margin-left:22px; margin-top:30px;">
                                              <a href="order.php" class="btn" style="padding:2px 10px; text-decoration:none; display:inline-block;">Вернуться назад</a>
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
