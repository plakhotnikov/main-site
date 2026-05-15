<?php
/** @var array $counts */
/** @var array $workload */
/** @var array $revenue_cat */
/** @var array $recent */
?>
<div class="page-header">
    <div>
        <h1>Админ-панель</h1>
        <div class="subtitle">Сводная картина по компании</div>
    </div>
</div>

<div class="card-grid">
    <div class="stat-card stat-card--accent">
        <div class="stat-card__label">Всего заявок</div>
        <div class="stat-card__value"><?= $counts['requests'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">В работе</div>
        <div class="stat-card__value"><?= $counts['in_progress'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Завершены</div>
        <div class="stat-card__value"><?= $counts['completed'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Доход (всего)</div>
        <div class="stat-card__value"><?= h(format_money($counts['revenue'])) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Клиентов</div>
        <div class="stat-card__value"><?= $counts['clients'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Консультантов</div>
        <div class="stat-card__value"><?= $counts['consultants'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Пользователей</div>
        <div class="stat-card__value"><?= $counts['users'] ?></div>
    </div>
</div>

<h2 style="color:var(--color-primary);font-family:var(--font-display);margin:30px 0 16px">Аналитика (GD)</h2>
<div class="charts-grid">
    <div class="chart-card">
        <h3>Заявки по месяцам (текущий год)</h3>
        <img src="<?= h(url('admin_chart', ['type' => 'monthly'])) ?>" alt="Гистограмма заявок по месяцам">
    </div>
    <div class="chart-card">
        <h3>Доход по категориям услуг</h3>
        <img src="<?= h(url('admin_chart', ['type' => 'categories'])) ?>" alt="Круговая диаграмма по категориям">
    </div>
    <div class="chart-card">
        <h3>Доход консультантов (текущий месяц)</h3>
        <img src="<?= h(url('admin_chart', ['type' => 'consultants'])) ?>" alt="Доход консультантов">
    </div>
</div>

<div class="two-col">
    <div class="card">
        <h2>Последние заявки</h2>
        <?php if (empty($recent)): ?>
            <div class="empty"><p>Пока нет заявок</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>#</th><th>Тема</th><th>Клиент</th><th>Статус</th><th>Создано</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td>#<?= (int)$r['id'] ?></td>
                            <td><?= h($r['title']) ?></td>
                            <td><?= h($r['client_company']) ?></td>
                            <td><span class="<?= h(status_class($r['status_code'])) ?>"><?= h($r['status']) ?></span></td>
                            <td><?= h(format_date($r['created_at'])) ?></td>
                            <td><a class="btn btn--ghost btn--small" href="<?= h(url('admin_request_view', ['id' => $r['id']])) ?>">Открыть</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Загрузка консультантов</h2>
        <?php if (empty($workload)): ?>
            <div class="empty"><p>Нет консультантов</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Имя</th><th>Спец.</th><th>Активные</th><th>Завершены</th></tr></thead>
                    <tbody>
                    <?php foreach ($workload as $w): ?>
                        <tr>
                            <td><?= h($w['consultant_name']) ?></td>
                            <td><?= h($w['specialization'] ?? '—') ?></td>
                            <td><?= (int)$w['active_requests'] ?></td>
                            <td><?= (int)$w['completed_requests'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
