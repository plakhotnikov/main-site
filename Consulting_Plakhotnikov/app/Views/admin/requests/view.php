<?php
/** @var array $request */
/** @var array $extras */
/** @var array $consultations */
/** @var array $consultants */
/** @var array $statuses */
/** @var array|null $report */
/** @var array $payments */
/** @var float $total_cost */
use App\Core\Csrf;
?>
<div class="page-header">
    <div>
        <h1>Заявка #<?= (int)$request['id'] ?></h1>
        <div class="subtitle"><?= h($request['title']) ?></div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('admin_requests')) ?>">← К списку</a>
</div>

<div class="two-col">
    <div>
        <div class="card">
            <h2>Описание</h2>
            <p style="white-space:pre-wrap"><?= h($request['description']) ?></p>
        </div>

        <?php if ($extras): ?>
            <div class="card">
                <h3>Доп. услуги</h3>
                <ul style="margin:0;padding-left:18px">
                    <?php foreach ($extras as $s): ?>
                        <li><?= h($s['name']) ?> — <?= h(format_money($s['price'])) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Консультации</h3>
            <?php if ($consultations): ?>
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Дата</th><th>Длит.</th><th>Заметка</th></tr></thead>
                        <tbody>
                        <?php foreach ($consultations as $c): ?>
                            <tr>
                                <td><?= h(format_datetime($c['held_at'])) ?></td>
                                <td><?= (int)$c['duration_min'] ?> мин</td>
                                <td style="white-space:pre-wrap"><?= h($c['notes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty"><p>Записей пока нет</p></div>
            <?php endif; ?>
        </div>

        <?php if ($report): ?>
            <div class="card">
                <h3>Отчёт</h3>
                <p class="text-muted"><?= h(format_datetime($report['created_at'])) ?></p>
                <p style="white-space:pre-wrap"><?= h($report['content']) ?></p>
                <?php if (!empty($report['file_path'])): ?>
                    <a class="btn" href="<?= h(url('admin_report_download', ['id' => $report['id']])) ?>">⬇ DOCX</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <aside>
        <div class="card">
            <h3>Параметры</h3>
            <div class="detail-row"><span class="detail-row__label">Статус</span><span class="detail-row__value"><span class="<?= h(status_class($request['status_code'])) ?>"><?= h($request['status_name']) ?></span></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Клиент</span><span class="detail-row__value"><?= h($request['client_company']) ?></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Услуга</span><span class="detail-row__value"><?= h($request['service_name']) ?></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Консультант</span><span class="detail-row__value"><?= h($request['consultant_name'] ?? '—') ?></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Стоимость</span><span class="detail-row__value" style="font-weight:700;color:var(--color-primary)"><?= h(format_money($total_cost)) ?></span></div>
        </div>

        <div class="card">
            <h3>Назначить консультанта</h3>
            <form class="form" method="post" action="<?= h(url('admin_request_assign')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                <div class="form-field">
                    <label for="consultant_id">Консультант</label>
                    <select name="consultant_id" id="consultant_id" required>
                        <option value="">— выберите —</option>
                        <?php foreach ($consultants as $c): ?>
                            <option value="<?= (int)$c['consultant_id'] ?>" <?= (int)$c['consultant_id'] === (int)$request['consultant_id'] ? 'selected' : '' ?>>
                                <?= h($c['consultant_name']) ?>
                                <?php if ($c['specialization']): ?> · <?= h($c['specialization']) ?><?php endif; ?>
                                · загрузка <?= (int)$c['active_requests'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button class="btn" type="submit">Назначить (sp_assign_consultant)</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Опасная зона</h3>
            <form class="inline-form" method="post" action="<?= h(url('admin_request_delete')) ?>" onsubmit="return confirm('Удалить заявку? Консультации, отчёт и платежи удалятся каскадом.');">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int)$request['id'] ?>">
                <button class="btn btn--danger" type="submit">🗑 Удалить заявку</button>
            </form>
        </div>
    </aside>
</div>
