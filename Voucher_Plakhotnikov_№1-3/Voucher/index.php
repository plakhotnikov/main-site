<?php
$login_error = '';
$logged_in = isset($_COOKIE['auth_user']);

// Обработка выхода
if (isset($_GET['logout'])) {
    setcookie('auth_user', '', time() - 3600, '/');
    header('Location: index.php');
    exit;
}

// Обработка входа
if (isset($_POST['login_submit'])) {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($login === 'admin' && $password === '123') {
        setcookie('auth_user', 'admin', time() + 86400, '/');
        header('Location: index.php');
        exit;
    } else {
        $login_error = 'Неверный логин или пароль';
    }
}
?>
<html>
    <head>
        <title>Работа</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>

    <body topmargin="0" bottommargin="0" rightmargin="0"  leftmargin="0"   background="images/back_main.gif">
        <table cellpadding="0" cellspacing="0" border="0"  align="center" width="583" height="614">
            <tr>
                <td valign="top" width="583" height="208" background="images/row1.gif">
                    <div style="margin-left:88px; margin-top:57px "><img src="images/w1.gif"></div>
                    <div class="auth-status" style="margin-left:200px; margin-top:-15px;">
                        <?php if ($logged_in): ?>
                            Вы зашли как <?= htmlspecialchars($_COOKIE['auth_user']) ?>
                        <?php else: ?>
                            Вы не авторизованы
                        <?php endif; ?>
                    </div>
                    <div style="margin-left:50px; margin-top:55px ">
                        <a href="index.php">Главная<img src="images/m1.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="10" height="10">
                        <a href="pages/order.php">Заказ<img src="images/m2.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="5" height="10">
                        <a href="pages/basket.php">Корзина<img src="images/m3.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="5" height="10">
                        <a href="pages/index-3.php">О компании<img src="images/m4.gif" border="0" ></a>
                        <img src="images/spacer.gif" width="5" height="10">
                        <a href="pages/index-4.php">Контакты<img src="images/m5.gif" border="0" ></a>

                    </div>
                </td>
            </tr>
            <tr>
                <td valign="top" width="583" height="338"  bgcolor="#FFFFFF">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td valign="top" height="338" width="42"></td>
                            <td valign="top" height="338" width="492">
                                <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="492" valign="top" height="106">

                                            <div style="margin-left:1px; margin-top:2px; margin-right:10px "><br>
                                                <div style="margin-left:5px "><img src="./images/1_p1.gif" align="left"></div>
                                                <div style="margin-left:95px "><font class="title">Туристическая путевка</font><br>

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="492" valign="top" height="232">
                                            <table cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td valign="top" height="232" width="248">
                                                        <div style="margin-left:6px; margin-top:2px; "><img src="./images/hl.gif"></div>
                                                        <?php if (!$logged_in): ?>
                                                        <div style="margin-left:6px; margin-top:7px;">
                                                            <div class="auth-form">
                                                                <font class="title">Авторизация</font><br><br>
                                                                <form method="post" action="index.php">
                                                                    <label>логин</label>
                                                                    <input type="text" name="login" value="">
                                                                    <label>пароль</label>
                                                                    <input type="password" name="password" value="">
                                                                    <br>
                                                                    <input type="submit" name="login_submit" value="Войти">
                                                                    <?php if ($login_error): ?>
                                                                        <div class="auth-error"><?= $login_error ?></div>
                                                                    <?php endif; ?>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <div style="margin-left:6px; margin-top:7px;">
                                                            <font class="title">Добро пожаловать, <?= htmlspecialchars($_COOKIE['auth_user']) ?>!</font><br><br>
                                                            Перейдите в раздел <a href="pages/order.php">Заказ</a> для оформления путевки.
                                                            <br><br>
                                                            <div class="auth-link"><a href="index.php?logout=1">Выйти</a></div>
                                                        </div>
                                                        <?php endif; ?>

                                                    <td valign="top" height="215" width="1" background="./images/tal.gif" style="background-repeat:repeat-y"></td>
                                                    <td valign="top" height="215" width="243">
                                                        <div style="margin-left:22px; margin-top:2px; "><img src="./images/hl.gif"></div>
                                                        <div style="margin-left:22px; margin-top:7px; "><img src="./images/1_w2.gif"></div>
                                                        <div style="margin-left:22px; margin-top:13px; ">

                                                            <br><br><br><br>

                                                        </div>
                                                        <div style="margin-left:22px; margin-top:16px; "><img src="./images/hl.gif"></div>
                                                        <div style="margin-left:22px; margin-top:7px; "><img src="./images/1_w4.gif"></div>
                                                        <div style="margin-left:22px; margin-top:9px; ">

                                                        </div>
                                                        </div>




                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td valign="top" height="338" width="49"></td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td valign="top" width="583" height="68" background="images/row3.gif">
                    <div style="margin-left:51px; margin-top:31px ">
                        <a href="#"><img src="images/p1.gif" border="0"></a>
                        <img src="images/spacer.gif" width="26" height="9">
                        <a href="#"><img src="images/p2.gif" border="0"></a>
                        <img src="images/spacer.gif" width="30" height="9">
                        <a href="#"><img src="images/p3.gif" border="0"></a>
                        <img src="images/spacer.gif" width="149" height="9">
                        <a href="index-5.html"><img src="images/copyright.gif" border="0"></a>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>
