<?php
/** @var array $categories */
/** @var array $services */
?>
<section class="hero">
    <h1 class="hero__title">Финансовый и юридический консалтинг для бизнеса</h1>
    <p class="hero__desc">Мы помогаем компаниям наводить порядок в финансах и снимать юридические риски. Полный аудит, налоговое планирование, корпоративное право и сопровождение сделок — под ключ.</p>
    <div class="hero__cta">
        <a class="btn" href="<?= h(url('services')) ?>">Наши услуги</a>
        <a class="btn btn--ghost" href="<?= h(url('register')) ?>">Стать клиентом</a>
    </div>
</section>

<div class="card-grid">
    <div class="stat-card stat-card--accent">
        <div class="stat-card__label">Категорий услуг</div>
        <div class="stat-card__value"><?= count($categories) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Активных направлений</div>
        <div class="stat-card__value"><?= count($services) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Опыт команды</div>
        <div class="stat-card__value">12+ лет</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Конфиденциальность</div>
        <div class="stat-card__value">NDA</div>
    </div>
</div>

<div class="card">
    <h2>Почему именно мы?</h2>
    <div class="card-grid" style="margin-bottom:0">
        <div>
            <h3>Прозрачность</h3>
            <p class="text-muted">Каждая заявка проходит чёткий процесс: новая → назначена → в работе → согласование → завершена. Вы всегда видите статус.</p>
        </div>
        <div>
            <h3>Командная работа</h3>
            <p class="text-muted">К вашей задаче подключаются профильный консультант и поддержка партнёра. Финансы и право — в одном месте.</p>
        </div>
        <div>
            <h3>Документальный итог</h3>
            <p class="text-muted">По завершении заявки клиент получает оформленный отчёт в формате DOCX, готовый для согласования с собственниками.</p>
        </div>
    </div>
</div>

<div class="page-header">
    <div>
        <h1>Услуги, которые востребованы</h1>
        <div class="subtitle">Подборка по двум основным направлениям</div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('services')) ?>">Все услуги →</a>
</div>

<div class="services-grid">
    <?php foreach (array_slice($services, 0, 6) as $s): ?>
        <article class="service-card">
            <div class="service-card__category"><?= h($s['category_name']) ?></div>
            <h3 class="service-card__title"><?= h($s['name']) ?></h3>
            <p class="service-card__desc"><?= h($s['description'] ?: 'Подробности — по запросу') ?></p>
            <div class="service-card__footer">
                <span class="service-card__price"><?= h(format_money($s['price'])) ?></span>
                <span class="service-card__hours">≈ <?= (int)$s['duration_hours'] ?> ч</span>
            </div>
        </article>
    <?php endforeach; ?>
</div>
