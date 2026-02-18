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
                <li class="header__nav-item"><a href="index.html">Главная</a></li>
                <li class="header__nav-item"><a href="hello.php">Hello World</a></li>
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
