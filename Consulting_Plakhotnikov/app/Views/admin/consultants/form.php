<?php
/** @var array|null $consultant */
/** @var array|null $user */
/** @var array $specializations */
use App\Core\Csrf;
?>
<div class="page-header">
    <div>
        <h1>Консультант: <?= h($user['full_name'] ?? '') ?></h1>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('admin_consultants')) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url('admin_consultants_save')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($consultant['id'] ?? 0) ?>">
        <div class="form-row">
            <div class="form-field">
                <label for="position">Должность</label>
                <input type="text" name="position" id="position" value="<?= h($consultant['position'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label for="experience_years">Опыт (лет)</label>
                <input type="number" name="experience_years" id="experience_years" min="0" value="<?= (int)($consultant['experience_years'] ?? 0) ?>">
            </div>
            <div class="form-field">
                <label for="specialization_id">Специализация</label>
                <select name="specialization_id" id="specialization_id">
                    <option value="">—</option>
                    <?php foreach ($specializations as $sp): ?>
                        <option value="<?= (int)$sp['id'] ?>" <?= (int)$sp['id'] === (int)($consultant['specialization_id'] ?? 0) ? 'selected' : '' ?>><?= h($sp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url('admin_consultants')) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить</button>
        </div>
    </form>
</div>
