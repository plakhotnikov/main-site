<?php /** @var array $requests */ ?>
<div class="page-header">
    <div>
        <h1>Назначенные мне заявки</h1>
        <div class="subtitle">Всего: <?= count($requests) ?></div>
    </div>
</div>

<?php if (empty($requests)): ?>
    <div class="card empty"><p>На вас пока ничего не назначено.</p></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr><th>#</th><th>Тема</th><th>Клиент</th><th>Услуга</th><th>Приоритет</th><th>Статус</th><th>Дедлайн</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td><?= h($r['title']) ?></td>
                    <td><?= h($r['client_company']) ?></td>
                    <td><?= h($r['service_name']) ?></td>
                    <td><span class="priority priority-<?= h($r['priority']) ?>"><?= h(priority_label($r['priority'])) ?></span></td>
                    <td><span class="<?= h(status_class($r['status_code'])) ?>"><?= h($r['status_name']) ?></span></td>
                    <td><?= h(format_date($r['deadline'])) ?></td>
                    <td><a class="btn btn--ghost btn--small" href="<?= h(url('consultant_request_view', ['id' => $r['id']])) ?>">Открыть</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
