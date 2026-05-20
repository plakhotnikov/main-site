<?php /** @var array $requests */ use App\Core\Csrf; ?>
<div class="page-header">
    <div>
        <h1>Заявки</h1>
        <div class="subtitle">Всего: <?= count($requests) ?></div>
    </div>
</div>

<?php if (empty($requests)): ?>
    <div class="card empty"><p>Заявок пока нет.</p></div>
<?php else: ?>
    <form method="post" action="<?= h(url('admin_requests_bulk_delete')) ?>" onsubmit="return confirm('Удалить выбранные заявки? Связанные данные удалятся каскадом.');">
        <?= Csrf::field() ?>
        <div class="card flex-row" style="justify-content:space-between">
            <div class="text-muted">Отметьте заявки и нажмите «Удалить выбранное» — будет вызвана <code>sp_bulk_delete_requests</code>.</div>
            <button class="btn btn--danger btn--small" type="submit">🗑 Удалить выбранное</button>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                <tr>
                    <th><input type="checkbox" onchange="document.querySelectorAll('input[name=\'ids[]\']').forEach(c=>c.checked=this.checked)"></th>
                    <th>#</th>
                    <th>Тема</th>
                    <th>Клиент</th>
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
                        <td><input type="checkbox" name="ids[]" value="<?= (int)$r['id'] ?>"></td>
                        <td>#<?= (int)$r['id'] ?></td>
                        <td><?= h($r['title']) ?></td>
                        <td><?= h($r['client_company']) ?></td>
                        <td><?= h($r['service']) ?> <small class="text-muted">(<?= h($r['category']) ?>)</small></td>
                        <td><?= h($r['consultant_name'] ?? '—') ?></td>
                        <td><span class="<?= h(status_class($r['status_code'])) ?>"><?= h($r['status']) ?></span></td>
                        <td><?= h(format_date($r['created_at'])) ?></td>
                        <td><a class="btn btn--ghost btn--small" href="<?= h(url('admin_request_view', ['id' => $r['id']])) ?>">Открыть</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
<?php endif; ?>
