<?php /** @var array $clients */ use App\Core\Csrf; ?>
<div class="page-header">
    <div>
        <h1>Клиенты</h1>
        <div class="subtitle">Всего: <?= count($clients) ?></div>
    </div>
    <a class="btn" href="<?= h(url('admin_users_form')) ?>">+ Создать (через «Пользователи»)</a>
</div>

<?php if (empty($clients)): ?>
    <div class="card empty"><p>Клиентов пока нет.</p></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>#</th><th>Логин</th><th>ФИО</th><th>Компания</th><th>ИНН</th><th>Отрасль</th><th>Email</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($clients as $c): ?>
                <tr>
                    <td>#<?= (int)$c['id'] ?></td>
                    <td><code><?= h($c['login']) ?></code></td>
                    <td><?= h($c['full_name']) ?></td>
                    <td><?= h($c['company']) ?></td>
                    <td><?= h($c['inn'] ?? '—') ?></td>
                    <td><?= h($c['industry_name'] ?? '—') ?></td>
                    <td><?= h($c['email'] ?? '—') ?></td>
                    <td class="actions">
                        <a class="btn btn--ghost btn--small" href="<?= h(url('admin_clients_form', ['id' => $c['id']])) ?>">✎</a>
                        <form class="inline-form" method="post" action="<?= h(url('admin_clients_delete')) ?>" onsubmit="return confirm('Удалить клиента? Его заявки и связанные данные удалятся каскадом (демо ON DELETE CASCADE).');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button class="btn btn--danger btn--small" type="submit">✗</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
