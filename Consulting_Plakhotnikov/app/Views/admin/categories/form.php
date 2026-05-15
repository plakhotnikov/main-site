<?php /** @var array|null $item */ use App\Core\Csrf; ?>
<div class="page-header">
    <div><h1><?= $item ? 'Категория' : 'Новая категория' ?></h1></div>
    <a class="btn btn--ghost" href="<?= h(url('admin_categories')) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url('admin_categories_save')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
        <div class="form-row">
            <div class="form-field">
                <label for="code">Код *</label>
                <input type="text" name="code" id="code" required value="<?= h($item['code'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label for="name">Название *</label>
                <input type="text" name="name" id="name" required value="<?= h($item['name'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url('admin_categories')) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить</button>
        </div>
    </form>
</div>
