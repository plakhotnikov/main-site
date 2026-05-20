<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hello World</title>
    <link rel="stylesheet" href="css/style.css" />
  </head>
  <body>
    <section class="first-bg" style="background-image: url(img/Pattern.jpg)">
      <header class="header">
        <div class="container">
          <div class="header__content">
            <a class="header__logo" href="index.html">
              <div class="header__logo-icon"></div>
              <div class="header__logo-text">
                <span class="header__brand-name">Никс</span>
                <span class="header__brand-subtitle">Менеджмент</span>
              </div>
            </a>
            <nav class="header__nav">
                                                        <ul class="header__nav-list">
                <li class="header__nav-item header__nav-item--dropdown">
                  <button class="header__nav-trigger" type="button">
                    Навигация
                    <span class="header__nav-caret">▼</span>
                  </button>
                  <ul class="header__nav-sublist">
                    <li><a href="index.html">Главная</a></li>
                    <li><a href="task2.html">Задание 2</a></li>
                    <li><a href="task3.html">Задание 3</a></li>
                    <li><a href="task5.html">Задание 5</a></li>
                    <li><a href="task6.html">Задание 6</a></li>
                    <li><a href="hello.php">Hello World</a></li>
                    <li><a href="php-task1.php">Задание 1 (PHP)</a></li>
                    <li><a href="Plakhotnikov_office_6/index.php">Заказ мебели</a></li>
                    <li><a href="Voucher_Plakhotnikov_№1-3/Voucher/index.php">Voucher</a></li>
                    <li><a href="Consulting_Plakhotnikov/public/index.php">Личный кабинет (6 лаба, 2 сем)</a></li>
                  </ul>
                </li>
                <li class="header__nav-item">
                  <a href="#form">Связаться с нами</a>
                </li>
              </ul>


            </nav>
          </div>
        </div>
      </header>
      <section class="hero">
        <div class="container">
          <div class="hero__content">
            <div class="hero__info">
              <h1 class="hero__title title"> <?php echo "Hello World";?></h1>
              <p class="hero__desc">Страница для задания по серверу.</p>
              <button class="hero__button buttom-primary">
                <a href="index.html">Вернуться на главную</a>
              </button>
            </div>
          </div>
        </div>
      </section>
    </section>
  </body>
</html>
