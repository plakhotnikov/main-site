<?php
/** @var string $content */
/** @var string $title */
use App\Core\Auth;
use App\Core\Template;

$pageTitle = $title ?? 'Никс Менеджмент';
$role = Auth::role();
$user = Auth::user();
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> — Никс Менеджмент</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(asset('css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="<?= h(url('home')) ?>">
            <span class="brand__icon"></span>
            <span class="brand__name">
                <strong>Никс</strong>
                <span>Менеджмент · Консалтинг</span>
            </span>
        </a>
        <nav class="main-nav">
            <?php Template::partial('menu_' . ($role ?? 'guest')); ?>
        </nav>
        <div class="user-menu">
            <?php if ($user !== null): ?>
                <span class="user-menu__name"><?= h($user['full_name']) ?></span>
                <span class="user-menu__role"><?= h($user['role_name']) ?></span>
                <a class="btn btn--ghost btn--small" href="<?= h(url('logout')) ?>" style="color:#fff;border-color:rgba(255,255,255,.4)">Выйти</a>
            <?php else: ?>
                <a class="btn btn--ghost btn--small" href="<?= h(url('login')) ?>" style="color:#fff;border-color:rgba(255,255,255,.4)">Войти</a>
                <a class="btn btn--accent btn--small" href="<?= h(url('register')) ?>">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="main">
    <div class="container">
        <?php Template::partial('flash'); ?>
        <?= $content ?>
    </div>
</main>

<footer class="site-footer">
    <div class="container site-footer__inner">
        <div>
            <strong>Никс Менеджмент</strong> · Финансовый и юридический консалтинг<br>
            <small>Курсовой проект. Плахотников Владимир, 2026.</small>
        </div>
        <div>
            <a href="<?= h(url('contacts')) ?>">Контакты</a> ·
            <a href="<?= h(url('about')) ?>">О компании</a>
        </div>
    </div>
</footer>
</body>
</html>
