<?php
$city_list = [
  'Ярославль' => 'img/Ярославль.jpg',
  'Архангельск' => 'img/Архангельск.jpg',
  'Калуга' => 'img/Калуга.jpg',
  'Рязань' => 'img/Рязань.jpg',
];

$payment_labels = [
  'cash' => 'наличные',
  'card' => 'карта',
  'transfer' => 'перевод',
];

$name = trim($_POST['name'] ?? '');
$city = $_POST['city'] ?? '';
$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';
$payment = $_POST['payment'] ?? '';
$roundtrip = isset($_POST['roundtrip']);
$business = isset($_POST['business']);
$submitted = isset($_POST['submit']);
$return_style = $roundtrip ? '' : 'style="display:none;"';
$return_disabled = $roundtrip ? '' : 'disabled';

$errors = [];
if ($submitted) {
  if ($name === '') {
    $errors[] = 'Введите имя.';
  }
  if ($city === '' || !array_key_exists($city, $city_list)) {
    $errors[] = 'Выберите город.';
  }
  if ($date_from === '') {
    $errors[] = 'Выберите дату вылета.';
  }
  if ($roundtrip && $date_to === '') {
    $errors[] = 'Выберите дату возврата.';
  }
  if ($roundtrip && $date_from !== '' && $date_to !== '') {
    $from_dt = DateTime::createFromFormat('Y-m-d', $date_from);
    $to_dt = DateTime::createFromFormat('Y-m-d', $date_to);
    if ($from_dt && $to_dt) {
      $min_return = (clone $from_dt)->modify('+1 day');
      if ($to_dt < $min_return) {
        $errors[] = 'Дата возврата должна быть минимум на 1 день позже даты вылета.';
      }
    }
  }
  if ($payment === '' || !array_key_exists($payment, $payment_labels)) {
    $errors[] = 'Выберите тип оплаты.';
  }
}

function format_date($value) {
  if ($value === '') {
    return '';
  }
  $dt = DateTime::createFromFormat('Y-m-d', $value);
  if ($dt) {
    return $dt->format('d.m.y');
  }
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$direction = $roundtrip ? 'туда и обратно' : 'в одну сторону';
$service = $business ? 'бизнес-класс' : 'эконом';
$payment_text = $payment_labels[$payment] ?? '';
$city_image = $city_list[$city] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Задание 1 — Билеты</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/php-task1.css" />
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
                  <a href="index.html#form">Связаться с нами</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </header>

      <section class="ticket-page">
        <div class="ticket-wrap">
          <form method="post" action="php-task1.php" class="ticket-grid">
            <div>
              <div class="ticket-field">
                <label for="date_from">Туда</label>
                <input id="date_from" name="date_from" type="date" value="<?php echo htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8'); ?>" />
              </div>
              <div class="ticket-field" id="return-field" <?php echo $return_style; ?>>
                <label for="date_to">Обратно</label>
                <input id="date_to" name="date_to" type="date" <?php echo $return_disabled; ?> value="<?php echo htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8'); ?>" />
              </div>

              <div class="ticket-field">
                <label for="name">Имя</label>
                <input id="name" name="name" type="text" placeholder="Введите имя" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" />
              </div>

              <div class="ticket-options">
                <label>
                  <input type="radio" name="payment" value="cash" <?php echo $payment === 'cash' ? 'checked' : ''; ?> />
                  Наличные
                </label>
                <label>
                  <input type="radio" name="payment" value="card" <?php echo $payment === 'card' ? 'checked' : ''; ?> />
                  Карта
                </label>
                <label>
                  <input type="radio" name="payment" value="transfer" <?php echo $payment === 'transfer' ? 'checked' : ''; ?> />
                  Перевод
                </label>
              </div>

              <div class="ticket-options">
                <label>
                  <input type="checkbox" id="roundtrip" name="roundtrip" <?php echo $roundtrip ? 'checked' : ''; ?> />
                  Туда и обратно
                </label>
                <label>
                  <input type="checkbox" name="business" <?php echo $business ? 'checked' : ''; ?> />
                  Бизнес-класс
                </label>
              </div>

              <input type="hidden" name="city" id="city" value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" />

              <div class="ticket-submit">
                <button type="submit" name="submit" value="1">Показать</button>
                <span class="ticket-hint">Нажмите кнопку, чтобы вывести данные ниже.</span>
              </div>

              <?php if ($submitted && count($errors) > 0) { ?>
                <div class="ticket-errors">
                  <?php echo nl2br(implode("\n", $errors)); ?>
                </div>
              <?php } ?>
            </div>

            <div>
              <div class="ticket-field">
                <label>Город</label>
              </div>
              <div class="ticket-city-list">
                <?php foreach ($city_list as $city_name => $city_file) { ?>
                  <button
                    type="button"
                    class="ticket-city-button <?php echo $city === $city_name ? 'is-active' : ''; ?>"
                    data-city="<?php echo htmlspecialchars($city_name, ENT_QUOTES, 'UTF-8'); ?>"
                  >
                    <?php echo htmlspecialchars($city_name, ENT_QUOTES, 'UTF-8'); ?>
                  </button>
                <?php } ?>
              </div>
            </div>
          </form>

          <?php if ($submitted && count($errors) === 0) { ?>
            <div class="ticket-summary">
              <div>
                <p>
                  <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>, Вы отправляетесь в город
                  <?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>
                </p>
                <p>
                  Билет: туда <?php echo format_date($date_from); ?>
                  <?php if ($roundtrip) { ?>
                    , обратно <?php echo format_date($date_to); ?>
                  <?php } ?>
                </p>
                <p>Направление: <?php echo $direction; ?></p>
                <p>Уровень обслуживания: <?php echo $service; ?></p>
                <p>Оплата: <?php echo htmlspecialchars($payment_text, ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
              <?php if ($city_image !== '') { ?>
                <img src="<?php echo htmlspecialchars($city_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" />
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </section>
    </section>

    <script src="js/php-task1.js"></script>
  </body>
</html>
