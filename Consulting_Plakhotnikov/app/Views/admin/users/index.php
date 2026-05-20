<?php /** @var array $users */ use App\Core\Csrf; ?>
<div class="page-header">
    <div>
        <h1>Пользователи</h1>
        <div class="subtitle">Всего: <?= count($users) ?></div>
    </div>
    <a class="btn" href="<?= h(url('admin_users_form')) ?>">+ Добавить</a>
</div>

<form method="post" action="<?= h(url('admin_users_bulk_delete')) ?>" onsubmit="return confirm('Удалить выбранных пользователей? Их клиентские/консультантские профили и заявки удалятся каскадом.');">
    <?= Csrf::field() ?>
    <div class="card flex-row" style="justify-content:space-between">
        <div class="text-muted">Отметьте пользователей и нажмите «Удалить выбранное» — массовое удаление через prepared SQL.</div>
        <button class="btn btn--danger btn--small" type="submit">🗑 Удалить выбранное</button>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr>
                <th><input type="checkbox" onchange="document.querySelectorAll('input[name=\'ids[]\']').forEach(c=>c.checked=this.checked)"></th>
                <th>#</th>
                <th>Логин</th>
                <th>ФИО</th>
                <th>Роль</th>
                <th>Email</th>
                <th>Активен</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= (int)$u['id'] ?>"></td>
                    <td>#<?= (int)$u['id'] ?></td>
                    <td><code><?= h($u['login']) ?></code></td>
                    <td><?= h($u['full_name']) ?></td>
                    <td><span class="badge"><?= h($u['role_name']) ?></span></td>
                    <td><?= h($u['email'] ?? '—') ?></td>
                    <td><?= (int)$u['is_active'] === 1 ? '✓' : '—' ?></td>
                    <td class="actions">
                        <a class="btn btn--ghost btn--small" href="<?= h(url('admin_users_form', ['id' => $u['id']])) ?>">✎</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</form>
