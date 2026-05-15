<?php use App\Core\Csrf; ?>
<div class="auth-wrap">
    <div class="card">
        <h1>Вход в кабинет</h1>
        <form class="form" method="post" action="<?= h(url('login_do')) ?>" autocomplete="off">
            <?= Csrf::field() ?>
            <div class="form-field">
                <label for="login">Логин</label>
                <input type="text" name="login" id="login" required>
            </div>
            <div class="form-field">
                <label for="password">Пароль</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-field">
                <label style="display:flex;gap:8px;align-items:center">
                    <input type="checkbox" name="remember" value="1">
                    <span>Запомнить меня (cookie на 30 дней)</span>
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Войти</button>
            </div>
        </form>
        <p class="alt-link">Ещё нет аккаунта? <a href="<?= h(url('register')) ?>">Зарегистрироваться</a></p>
        <hr class="divider">
        <p class="text-muted" style="font-size:13px">Демо-логины: <code>admin</code>, <code>consultant1</code>, <code>consultant2</code>, <code>client1</code>. Пароль у всех: <code>123</code>.</p>
    </div>
</div>
