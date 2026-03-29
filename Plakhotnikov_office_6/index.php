<?php
$colors = [
    'Орех' => 1.1,
    'Дуб мореный' => 1.2,
    'Палисандр' => 1.3,
    'Эбеновое дерево' => 1.4,
    'Клен' => 1.5,
    'Лиственница' => 1.6,
];

$furniture_items = ['Банкетка', 'Кровать', 'Комод', 'Шкаф', 'Стул', 'Стол'];

$cities = ['Москва', 'Санкт-Петербург', 'Пермь', 'Саратов', 'Самара', 'Казань', 'Новосибирск'];

$csv_files = [];
foreach (glob(__DIR__ . '/src/*.csv') ?: [] as $csv_path) {
    $csv_files[] = basename($csv_path);
}
sort($csv_files, SORT_NATURAL | SORT_FLAG_CASE);

$result = null;
if (isset($_GET['success'])) {
    $result = [
        'invoice_number' => $_GET['invoice'] ?? '',
        'total' => $_GET['total'] ?? '',
        'items_count' => $_GET['items_count'] ?? '',
        'xlsx_file' => $_GET['xlsx'] ?? '',
        'pdf_file' => $_GET['pdf'] ?? '',
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Заказ мебели</title>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<section class="first-bg" style="background-image: url(../img/Pattern.jpg)">
    <header class="header">
        <div class="container">
            <div class="header__content">
                <a class="header__logo" href="../index.html">
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
                                <li><a href="../index.html">Главная</a></li>
                                <li><a href="../task2.html">Задание 2</a></li>
                                <li><a href="../task3.html">Задание 3</a></li>
                                <li><a href="../task5.html">Задание 5</a></li>
                                <li><a href="../task6.html">Задание 6</a></li>
                                <li><a href="../hello.php">Hello World</a></li>
                                <li><a href="../php-task1.php">Задание 1 (PHP)</a></li>
                                <li><a href="index.php">Заказ мебели</a></li>
                                <li><a href="../Voucher_Plakhotnikov_№1-3/Voucher/index.php">Voucher</a></li>
                            </ul>
                        </li>
                        <li class="header__nav-item">
                            <a href="../index.html#form">Связаться с нами</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
</section>

<section class="order-page">
    <div class="container">
        <h1 class="order-title">Заказ мебели</h1>

        <form method="post" action="process.php" class="order-form">
            <div class="form-group">
                <label for="surname">Фамилия</label>
                <input type="text" id="surname" name="surname" required />
            </div>

            <div class="form-group">
                <label for="city">Город доставки</label>
                <select id="city" name="city" required>
                    <option value="">—</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="delivery_date">Дата доставки</label>
                <input type="date" id="delivery_date" name="delivery_date" />
            </div>

            <div class="form-group">
                <label for="address">Адрес</label>
                <input type="text" id="address" name="address" required />
            </div>

            <div class="form-columns">
                <div class="form-column">
                    <h3>Выберите цвет мебели</h3>
                    <?php foreach ($colors as $name => $multiplier): ?>
                        <label class="radio-label">
                            <input type="radio" name="color" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required />
                            <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> <?= $multiplier ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-column">
                    <h3>Выберите предметы мебели</h3>
                    <?php foreach ($furniture_items as $item): ?>
                        <div class="furniture-row">
                            <label class="checkbox-label">
                                <input type="checkbox" name="items[]" value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>" />
                                <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-column">
                    <h3>Количество</h3>
                    <?php foreach ($furniture_items as $item): ?>
                        <div class="furniture-row">
                            <input type="number" name="qty[<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>]" min="1" value="1" class="qty-input" data-for="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>" />
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="price_file">Файл цен (CSV)</label>
                <select id="price_file" name="price_file" required <?= empty($csv_files) ? 'disabled' : '' ?>>
                    <?php if (!empty($csv_files)): ?>
                        <?php foreach ($csv_files as $csv_file): ?>
                            <option value="<?= htmlspecialchars($csv_file, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($csv_file, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">CSV-файлы в папке src не найдены</option>
                    <?php endif; ?>
                </select>
                <?php if (empty($csv_files)): ?>
                    <input type="hidden" name="price_file" value="" />
                <?php endif; ?>
            </div>

            <div class="form-submit">
                <button type="submit" name="submit" value="1" class="btn-submit">Оформить заказ</button>
            </div>
        </form>

        <?php if ($result): ?>
            <div class="order-result">
                <h2>Заказ оформлен!</h2>
                <p>Накладная № <?= htmlspecialchars($result['invoice_number'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>Всего наименований: <?= htmlspecialchars($result['items_count'], ENT_QUOTES, 'UTF-8') ?>, на сумму <?= htmlspecialchars($result['total'], ENT_QUOTES, 'UTF-8') ?> руб.</p>
                <div class="download-links">
                    <?php if ($result['xlsx_file']): ?>
                        <a href="download.php?file=<?= urlencode($result['xlsx_file']) ?>" class="btn-download">Скачать накладную (Excel)</a>
                    <?php endif; ?>
                    <?php if ($result['pdf_file']): ?>
                        <a href="download.php?file=<?= urlencode($result['pdf_file']) ?>" class="btn-download">Скачать запись о документах (PDF)</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.querySelectorAll('input[type="checkbox"][name="items[]"]').forEach(cb => {
    const item = cb.value;
    const qtyInput = document.querySelector('.qty-input[data-for="' + item + '"]');
    if (qtyInput) {
        qtyInput.disabled = !cb.checked;
        cb.addEventListener('change', () => { qtyInput.disabled = !cb.checked; });
    }
});

// Safari pre-fills date visually but doesn't set .value until interaction.
// Force-read the rendered value before submit so validation passes.
document.querySelector('.order-form').addEventListener('submit', function(e) {
    const dateInput = document.getElementById('delivery_date');
    if (!dateInput.value) {
        e.preventDefault();
        dateInput.focus();
        alert('Пожалуйста, укажите дату доставки.');
    }
});
</script>
</body>
</html>
