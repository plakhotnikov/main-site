<?php
/** @var array $items */
/** @var string $page_create */
/** @var string $page_edit */
/** @var string $page_delete */
/** @var array  $columns  list of [field, label] */
use App\Core\Csrf;
?>
<div class="page-header">
    <div><h1><?= h($title) ?></h1><div class="subtitle">Всего: <?= count($items) ?></div></div>
    <a class="btn" href="<?= h(url($page_create)) ?>">+ Добавить</a>
</div>

<?php if (empty($items)): ?>
    <div class="card empty"><p>Записей пока нет.</p></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead>
            <tr>
                <th>#</th>
                <?php foreach ($columns as $col): ?>
                    <th><?= h($col[1]) ?></th>
                <?php endforeach; ?>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row): ?>
                <tr>
                    <td>#<?= (int)$row['id'] ?></td>
                    <?php foreach ($columns as $col): ?>
                        <td><?= h($row[$col[0]] ?? '') ?></td>
                    <?php endforeach; ?>
                    <td class="actions">
                        <a class="btn btn--ghost btn--small" href="<?= h(url($page_edit, ['id' => $row['id']])) ?>">✎</a>
                        <form class="inline-form" method="post" action="<?= h(url($page_delete)) ?>" onsubmit="return confirm('Удалить запись?');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button class="btn btn--danger btn--small" type="submit">✗</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
