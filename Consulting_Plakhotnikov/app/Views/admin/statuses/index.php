<?php /** @var array $items */ use App\Core\Csrf; ?>
<div class="page-header">
    <div><h1>Статусы заявок</h1></div>
    <a class="btn" href="<?= h(url('admin_statuses_form')) ?>">+ Добавить</a>
</div>

<div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Код</th><th>Название</th><th>Сорт.</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr>
                <td>#<?= (int)$i['id'] ?></td>
                <td><code><?= h($i['code']) ?></code></td>
                <td><?= h($i['name']) ?></td>
                <td><?= (int)$i['sort_order'] ?></td>
                <td class="actions">
                    <a class="btn btn--ghost btn--small" href="<?= h(url('admin_statuses_form', ['id' => $i['id']])) ?>">✎</a>
                    <form class="inline-form" method="post" action="<?= h(url('admin_statuses_delete')) ?>" onsubmit="return confirm('Удалить статус?');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                        <button class="btn btn--danger btn--small" type="submit">✗</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
