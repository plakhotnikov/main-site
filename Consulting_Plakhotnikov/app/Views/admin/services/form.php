<?php /** @var array|null $service */ /** @var array $categories */ use App\Core\Csrf; ?>
<div class="page-header">
    <div><h1><?= $service ? 'Редактирование услуги' : 'Новая услуга' ?></h1></div>
    <a class="btn btn--ghost" href="<?= h(url('admin_services')) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url('admin_services_save')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($service['id'] ?? 0) ?>">
        <div class="form-row">
            <div class="form-field">
                <label for="category_id">Категория *</label>
                <select name="category_id" id="category_id" required>
                    <option value="">—</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)($service['category_id'] ?? 0) ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="name">Название *</label>
                <input type="text" name="name" id="name" required value="<?= h($service['name'] ?? '') ?>">
            </div>
        </div>
        <div class="form-field">
            <label for="description">Описание</label>
            <textarea name="description" id="description" rows="4"><?= h($service['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-field">
                <label for="price">Цена, ₽ *</label>
                <input type="text" name="price" id="price" required value="<?= h(number_format((float)($service['price'] ?? 0), 2, '.', '')) ?>">
            </div>
            <div class="form-field">
                <label for="duration_hours">Часы</label>
                <input type="number" name="duration_hours" id="duration_hours" min="0" value="<?= (int)($service['duration_hours'] ?? 0) ?>">
            </div>
            <div class="form-field">
                <label style="display:flex;gap:8px;align-items:center;margin-top:24px">
                    <input type="checkbox" name="is_active" value="1" <?= !$service || (int)($service['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
                    <span>Активна</span>
                </label>
            </div>
        </div>
        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url('admin_services')) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить</button>
        </div>
    </form>
</div>
