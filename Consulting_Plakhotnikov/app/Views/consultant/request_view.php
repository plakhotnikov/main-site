<?php
/** @var array $request */
/** @var array $extras */
/** @var array $consultations */
/** @var array $statuses */
/** @var array|null $report */
/** @var array $payments */
/** @var float $total_cost */
use App\Core\Csrf;
$allowedTransitions = ['in_progress', 'review', 'completed', 'cancelled'];
?>
<div class="page-header">
    <div>
        <h1>Заявка #<?= (int)$request['id'] ?></h1>
        <div class="subtitle"><?= h($request['title']) ?></div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('consultant_requests')) ?>">← К списку</a>
</div>

<div class="two-col">
    <div>
        <div class="card">
            <h2>Описание задачи</h2>
            <p style="white-space:pre-wrap"><?= h($request['description']) ?></p>
        </div>

        <?php if ($extras): ?>
            <div class="card">
                <h3>Дополнительные услуги</h3>
                <ul style="margin:0;padding-left:18px">
                    <?php foreach ($extras as $s): ?>
                        <li><?= h($s['name']) ?> — <?= h(format_money($s['price'])) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Журнал консультаций</h3>
            <?php if (empty($consultations)): ?>
                <div class="empty"><p>Записей пока нет.</p></div>
            <?php else: ?>
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
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Добавить запись о консультации</h3>
            <form class="form" method="post" action="<?= h(url('consultant_add_consultation')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                <div class="form-row">
                    <div class="form-field">
                        <label for="held_at">Дата и время</label>
                        <input type="datetime-local" name="held_at" id="held_at" required>
                    </div>
                    <div class="form-field">
                        <label for="duration_min">Длительность, мин</label>
                        <input type="number" name="duration_min" id="duration_min" min="0" value="60">
                    </div>
                </div>
                <div class="form-field">
                    <label for="notes">Заметка</label>
                    <textarea name="notes" id="notes" rows="3" required></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn" type="submit">Сохранить</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Итоговый отчёт</h3>
            <?php if ($report !== null): ?>
                <p class="text-muted">Создан <?= h(format_datetime($report['created_at'])) ?>. Файл: <?= h($report['file_path'] ?: '—') ?></p>
                <p style="white-space:pre-wrap"><?= h($report['content']) ?></p>
            <?php endif; ?>
            <a class="btn" href="<?= h(url('consultant_create_report', ['id' => $request['id']])) ?>">
                <?= $report === null ? 'Создать отчёт' : 'Обновить отчёт' ?>
            </a>
            <?php if ($report !== null && !empty($report['file_path'])): ?>
                <a class="btn btn--ghost" href="<?= h(url('consultant_report_download', ['id' => $request['id']])) ?>">⬇ Скачать DOCX</a>
            <?php endif; ?>
        </div>
    </div>

    <aside>
        <div class="card">
            <h3>Клиент</h3>
            <p><strong><?= h($request['client_company']) ?></strong><br>
               Контакт: <?= h($request['client_name']) ?></p>
        </div>

        <div class="card">
            <h3>Параметры</h3>
            <div class="detail-row"><span class="detail-row__label">Статус</span><span class="detail-row__value"><span class="<?= h(status_class($request['status_code'])) ?>"><?= h($request['status_name']) ?></span></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Услуга</span><span class="detail-row__value"><?= h($request['service_name']) ?></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Приоритет</span><span class="detail-row__value"><?= h(priority_label($request['priority'])) ?></span></div>
            <div class="detail-row" style="margin-top:10px"><span class="detail-row__label">Дедлайн</span><span class="detail-row__value"><?= h(format_date($request['deadline'])) ?></span></div>
            <hr class="divider">
            <div class="detail-row">
                <span class="detail-row__label">Стоимость</span>
                <span class="detail-row__value" style="font-size:20px;font-weight:700;color:var(--color-primary)"><?= h(format_money($total_cost)) ?></span>
            </div>
        </div>

        <div class="card">
            <h3>Сменить статус</h3>
            <form class="form" method="post" action="<?= h(url('consultant_change_status')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
                <div class="form-field">
                    <label for="status_code">Новый статус</label>
                    <select name="status_code" id="status_code" required>
                        <?php foreach ($statuses as $s): if (!in_array($s['code'], $allowedTransitions, true)) continue; ?>
                            <option value="<?= h($s['code']) ?>"><?= h($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="comment">Комментарий (попадёт в журнал)</label>
                    <textarea name="comment" id="comment" rows="2"></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn" type="submit">Применить</button>
                </div>
            </form>
        </div>
    </aside>
</div>
