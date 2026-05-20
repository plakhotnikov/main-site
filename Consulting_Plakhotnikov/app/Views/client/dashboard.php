<?php
/** @var array $stats */
/** @var array $requests */
/** @var array|null $client */
?>
<div class="page-header">
    <div>
        <h1>Личный кабинет</h1>
        <div class="subtitle">Краткая сводка по вашим заявкам</div>
    </div>
    <a class="btn" href="<?= h(url('client_request_create')) ?>">+ Новая заявка</a>
</div>

<div class="card-grid">
    <div class="stat-card stat-card--accent">
        <div class="stat-card__label">Всего заявок</div>
        <div class="stat-card__value"><?= $stats['total'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Новые</div>
        <div class="stat-card__value"><?= $stats['new'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">В работе</div>
        <div class="stat-card__value"><?= $stats['in_progress'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Завершены</div>
        <div class="stat-card__value"><?= $stats['completed'] ?></div>
    </div>
</div>

<div class="card">
    <h2>Последние заявки</h2>
    <?php if (empty($requests)): ?>
        <div class="empty">
            <p>У вас пока нет заявок.</p>
            <p><a class="btn" href="<?= h(url('client_request_create')) ?>">Создать первую</a></p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Тема</th>
                    <th>Услуга</th>
                    <th>Консультант</th>
                    <th>Статус</th>
                    <th>Создано</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td>#<?= (int)$r['id'] ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td><?= h($r['service_name']) ?></td>
                        <td><?= h($r['consultant_name'] ?? '—') ?></td>
                        <td><span class="<?= h(status_class($r['status_code'])) ?>"><?= h($r['status_name']) ?></span></td>
                        <td><?= h(format_date($r['created_at'])) ?></td>
                        <td><a class="btn btn--ghost btn--small" href="<?= h(url('client_request_view', ['id' => $r['id']])) ?>">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="text-align:right;margin:0"><a href="<?= h(url('client_requests')) ?>">Все мои заявки →</a></p>
    <?php endif; ?>
</div>

<?php if ($client !== null): ?>
<div class="card">
    <h3>Профиль компании</h3>
    <div class="detail-grid">
        <div class="detail-row"><span class="detail-row__label">Компания</span><span class="detail-row__value"><?= h($client['company']) ?></span></div>
        <div class="detail-row"><span class="detail-row__label">ИНН</span><span class="detail-row__value"><?= h($client['inn'] ?? '—') ?></span></div>
        <div class="detail-row"><span class="detail-row__label">Адрес</span><span class="detail-row__value"><?= h($client['address'] ?? '—') ?></span></div>
    </div>
</div>
<?php endif; ?>
