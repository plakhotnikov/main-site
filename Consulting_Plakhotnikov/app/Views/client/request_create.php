<?php
/** @var array $services */
/** @var array $industries */
use App\Core\Csrf;
?>
<div class="page-header">
    <div>
        <h1>Новая заявка</h1>
        <div class="subtitle">Чем подробнее опишете задачу — тем быстрее назначим консультанта</div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('client_requests')) ?>">← К списку</a>
</div>

<div class="card">
    <form class="form" method="post" action="<?= h(url('client_request_store')) ?>">
        <?= Csrf::field() ?>

        <div class="form-row">
            <div class="form-field">
                <label for="title">Тема заявки *</label>
                <input type="text" name="title" id="title" required maxlength="200" placeholder="Например: «Аудит отчётности за 2025»">
            </div>
            <div class="form-field">
                <label for="service_id">Основная услуга *</label>
                <select name="service_id" id="service_id" required>
                    <option value="">— выберите услугу —</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= (int)$s['id'] ?>"><?= h($s['category_name']) ?> · <?= h($s['name']) ?> · <?= h(format_money($s['price'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-field">
            <label for="description">Описание задачи *</label>
            <textarea name="description" id="description" required rows="5" placeholder="Что нужно сделать, какие документы есть, что важно учесть"></textarea>
        </div>

        <div class="form-row">
            <div class="form-field">
                <label>Приоритет *</label>
                <div class="radio-group">
                    <label><input type="radio" name="priority" value="low"> Низкий</label>
                    <label><input type="radio" name="priority" value="normal" checked> Обычный</label>
                    <label><input type="radio" name="priority" value="high"> Высокий</label>
                </div>
            </div>
            <div class="form-field">
                <label for="deadline">Желаемый дедлайн</label>
                <input type="date" name="deadline" id="deadline">
                <small>Опционально</small>
            </div>
        </div>

        <div class="form-field">
            <label>Дополнительные услуги</label>
            <div class="checkbox-group">
                <?php foreach ($services as $s): ?>
                    <label>
                        <input type="checkbox" name="extra_services[]" value="<?= (int)$s['id'] ?>">
                        <span><?= h($s['name']) ?> <span class="text-muted">— <?= h(format_money($s['price'])) ?></span></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <small>Можно выбрать несколько (M:N → request_services)</small>
        </div>

        <hr class="divider">
        <h3>Оплата *</h3>
        <div class="form-field">
            <label>Способ оплаты</label>
            <div class="radio-group">
                <label><input type="radio" name="payment_method" value="card" checked> Карта</label>
                <label><input type="radio" name="payment_method" value="transfer"> Банковский перевод</label>
                <label><input type="radio" name="payment_method" value="cash"> Наличные</label>
            </div>
            <small>Списывается полная стоимость заявки (основная услуга + выбранные доп. услуги)</small>
        </div>

        <hr class="divider">
        <h3>Контактные данные для этой заявки</h3>
        <div class="form-row">
            <div class="form-field">
                <label for="contact_phone">Телефон</label>
                <input type="text" name="contact_phone" id="contact_phone" placeholder="+7 ...">
            </div>
            <div class="form-field">
                <label for="contact_email">Email</label>
                <input type="email" name="contact_email" id="contact_email">
            </div>
            <div class="form-field">
                <label for="contact_inn">ИНН (для договора)</label>
                <input type="text" name="contact_inn" id="contact_inn" maxlength="12">
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url('client_requests')) ?>">Отмена</a>
            <button type="submit" class="btn">Создать заявку</button>
        </div>
    </form>
</div>
