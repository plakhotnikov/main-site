<?php /** @var array $stats */ /** @var array $requests */ ?>
<div class="page-header">
    <div>
        <h1>Кабинет консультанта</h1>
        <div class="subtitle">Сводка по назначенным заявкам</div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('consultant_requests')) ?>">Все мои заявки →</a>
</div>

<div class="card-grid">
    <div class="stat-card stat-card--accent">
        <div class="stat-card__label">Всего</div>
        <div class="stat-card__value"><?= $stats['total'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">В работе</div>
        <div class="stat-card__value"><?= $stats['active'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Завершено</div>
        <div class="stat-card__value"><?= $stats['completed'] ?></div>
    </div>
</div>

<div class="card">
    <h2>Свежие заявки</h2>
    <?php if (empty($requests)): ?>
        <div class="empty"><p>На вас пока ничего не назначено.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>#</th><th>Тема</th><th>Клиент</th><th>Статус</th><th>Дедлайн</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id'] ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td><?= h($r['client_company']) ?></td>
                        <td><span class="<?= h(status_class($r['status_code'])) ?>"><?= h($r['status_name']) ?></span></td>
                        <td><?= h(format_date($r['deadline'])) ?></td>
                        <td><a class="btn btn--ghost btn--small" href="<?= h(url('consultant_request_view', ['id' => $r['id']])) ?>">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
