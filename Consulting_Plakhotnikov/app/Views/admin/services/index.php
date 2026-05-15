<?php /** @var array $services */ use App\Core\Csrf; ?>
<div class="page-header">
    <div>
        <h1>Услуги</h1>
        <div class="subtitle">Всего: <?= count($services) ?></div>
    </div>
    <a class="btn" href="<?= h(url('admin_services_form')) ?>">+ Добавить</a>
</div>

<div class="table-wrap">
    <table class="data">
        <thead><tr><th>#</th><th>Категория</th><th>Название</th><th>Цена</th><th>Часы</th><th>Активна</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td>#<?= (int)$s['id'] ?></td>
                <td><?= h($s['category_name']) ?></td>
                <td><?= h($s['name']) ?></td>
                <td><?= h(format_money($s['price'])) ?></td>
                <td><?= (int)$s['duration_hours'] ?></td>
                <td><?= (int)$s['is_active'] === 1 ? '✓' : '—' ?></td>
                <td class="actions">
                    <a class="btn btn--ghost btn--small" href="<?= h(url('admin_services_form', ['id' => $s['id']])) ?>">✎</a>
                    <form class="inline-form" method="post" action="<?= h(url('admin_services_delete')) ?>" onsubmit="return confirm('Удалить услугу?');">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <button class="btn btn--danger btn--small" type="submit">✗</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
