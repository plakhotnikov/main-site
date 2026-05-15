<?php use App\Core\Csrf; /** @var array $industries */ ?>
<div class="auth-wrap" style="max-width:680px">
    <div class="card">
        <h1>Регистрация клиента</h1>
        <p class="text-muted">После регистрации вы попадёте в личный кабинет и сможете создать первую заявку.</p>
        <form class="form" method="post" action="<?= h(url('register_do')) ?>" autocomplete="off">
            <?= Csrf::field() ?>
            <div class="form-row">
                <div class="form-field">
                    <label for="login">Логин *</label>
                    <input type="text" name="login" id="login" minlength="3" required>
                </div>
                <div class="form-field">
                    <label for="full_name">ФИО *</label>
                    <input type="text" name="full_name" id="full_name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="password">Пароль *</label>
                    <input type="password" name="password" id="password" minlength="3" required>
                </div>
                <div class="form-field">
                    <label for="password2">Повторите пароль *</label>
                    <input type="password" name="password2" id="password2" minlength="3" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email">
                </div>
                <div class="form-field">
                    <label for="phone">Телефон</label>
                    <input type="tel" name="phone" id="phone" placeholder="+7 ...">
                </div>
            </div>
            <hr class="divider">
            <div class="form-row">
                <div class="form-field">
                    <label for="company">Компания *</label>
                    <input type="text" name="company" id="company" required>
                </div>
                <div class="form-field">
                    <label for="inn">ИНН</label>
                    <input type="text" name="inn" id="inn" maxlength="12">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="industry_id">Отрасль</label>
                    <select name="industry_id" id="industry_id">
                        <option value="">— не выбрана —</option>
                        <?php foreach ($industries as $i): ?>
                            <option value="<?= (int)$i['id'] ?>"><?= h($i['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="address">Юр. адрес</label>
                    <input type="text" name="address" id="address">
                </div>
            </div>
            <div class="form-actions">
                <a class="btn btn--ghost" href="<?= h(url('login')) ?>">У меня уже есть аккаунт</a>
                <button type="submit" class="btn">Зарегистрироваться</button>
            </div>
        </form>
    </div>
</div>
