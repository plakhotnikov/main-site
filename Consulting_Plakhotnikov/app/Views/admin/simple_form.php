<?php
/** @var array|null $item */
/** @var array $fields */
/** @var string $page_save */
/** @var string $page_back */
use App\Core\Csrf;
?>
<div class="page-header">
    <div><h1><?= h($title) ?></h1></div>
    <a class="btn btn--ghost" href="<?= h(url($page_back)) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url($page_save)) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
        <?php foreach ($fields as $f): ?>
            <div class="form-field">
                <label for="<?= h($f['name']) ?>"><?= h($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></label>
                <input type="text" name="<?= h($f['name']) ?>" id="<?= h($f['name']) ?>"
                       value="<?= h($item[$f['name']] ?? '') ?>" <?= !empty($f['required']) ? 'required' : '' ?>>
            </div>
        <?php endforeach; ?>
        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url($page_back)) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить</button>
        </div>
    </form>
</div>
