<?php
/** @var array|null $client */
/** @var array|null $user */
/** @var array $industries */
use App\Core\Csrf;
?>
<div class="page-header">
    <div>
        <h1>Клиент: <?= h($user['full_name'] ?? '') ?></h1>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('admin_clients')) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url('admin_clients_save')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($client['id'] ?? 0) ?>">
        <div class="form-row">
            <div class="form-field">
                <label for="company">Компания *</label>
                <input type="text" name="company" id="company" required value="<?= h($client['company'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label for="inn">ИНН</label>
                <input type="text" name="inn" id="inn" value="<?= h($client['inn'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-field">
                <label for="industry_id">Отрасль</label>
                <select name="industry_id" id="industry_id">
                    <option value="">—</option>
                    <?php foreach ($industries as $i): ?>
                        <option value="<?= (int)$i['id'] ?>" <?= (int)$i['id'] === (int)($client['industry_id'] ?? 0) ? 'selected' : '' ?>><?= h($i['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="address">Адрес</label>
                <input type="text" name="address" id="address" value="<?= h($client['address'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url('admin_clients')) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить</button>
        </div>
    </form>
</div>
