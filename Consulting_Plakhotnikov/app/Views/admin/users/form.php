<?php
/** @var array|null $user */
/** @var array|null $client */
/** @var array|null $consultant */
/** @var array $roles */
/** @var array $industries */
/** @var array $specializations */
use App\Core\Csrf;
?>
<div class="page-header">
    <div>
        <h1><?= $user ? 'Редактирование пользователя' : 'Новый пользователь' ?></h1>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('admin_users')) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url('admin_users_save')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int)($user['id'] ?? 0) ?>">

        <div class="form-row">
            <div class="form-field">
                <label for="login">Логин *</label>
                <input type="text" name="login" id="login" required value="<?= h($user['login'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label for="full_name">ФИО *</label>
                <input type="text" name="full_name" id="full_name" required value="<?= h($user['full_name'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="password"><?= $user ? 'Новый пароль (оставьте пустым, чтобы не менять)' : 'Пароль *' ?></label>
                <input type="text" name="password" id="password" <?= $user ? '' : 'required' ?> autocomplete="new-password">
            </div>
            <div class="form-field">
                <label for="role_id">Роль *</label>
                <select name="role_id" id="role_id" required onchange="window.__toggleRoleBlocks(this.value)">
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int)$r['id'] ?>" data-code="<?= h($r['code']) ?>" <?= ((int)$r['id'] === (int)($user['role_id'] ?? 0)) ? 'selected' : '' ?>>
                            <?= h($r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= h($user['email'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label for="phone">Телефон</label>
                <input type="text" name="phone" id="phone" value="<?= h($user['phone'] ?? '') ?>">
            </div>
            <div class="form-field">
                <label style="display:flex;gap:8px;align-items:center;margin-top:24px">
                    <input type="checkbox" name="is_active" value="1" <?= !$user || (int)($user['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
                    <span>Активен</span>
                </label>
            </div>
        </div>

        <div id="role-client" style="display:none">
            <hr class="divider">
            <h3>Профиль клиента</h3>
            <div class="form-row">
                <div class="form-field">
                    <label for="company">Компания</label>
                    <input type="text" name="company" id="company" value="<?= h($client['company'] ?? '') ?>">
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
                            <option value="<?= (int)$i['id'] ?>" <?= ((int)$i['id'] === (int)($client['industry_id'] ?? 0)) ? 'selected' : '' ?>><?= h($i['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="address">Адрес</label>
                    <input type="text" name="address" id="address" value="<?= h($client['address'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div id="role-consultant" style="display:none">
            <hr class="divider">
            <h3>Профиль консультанта</h3>
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
                            <option value="<?= (int)$sp['id'] ?>" <?= ((int)$sp['id'] === (int)($consultant['specialization_id'] ?? 0)) ? 'selected' : '' ?>><?= h($sp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <?php if ($user): ?>
                <form method="post" action="<?= h(url('admin_users_delete')) ?>" class="inline-form" onsubmit="return confirm('Удалить пользователя? Все связанные данные удалятся каскадом.');">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                    <button class="btn btn--danger" type="submit">🗑 Удалить</button>
                </form>
            <?php endif; ?>
            <a class="btn btn--ghost" href="<?= h(url('admin_users')) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить</button>
        </div>
    </form>
</div>

<script>
window.__toggleRoleBlocks = function(value){
    var sel = document.getElementById('role_id');
    var opt = sel.options[sel.selectedIndex];
    var code = opt ? opt.getAttribute('data-code') : '';
    document.getElementById('role-client').style.display    = code === 'client' ? '' : 'none';
    document.getElementById('role-consultant').style.display = code === 'consultant' ? '' : 'none';
};
window.__toggleRoleBlocks();
</script>
