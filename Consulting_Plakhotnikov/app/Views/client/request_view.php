<?php
/** @var array $request */
/** @var array $extras */
/** @var array $consultations */
/** @var array|null $report */
/** @var array $payments */
/** @var float $total_cost */
?>
<div class="page-header">
    <div>
        <h1>Заявка #<?= (int)$request['id'] ?></h1>
        <div class="subtitle"><?= h($request['title']) ?></div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('client_requests')) ?>">← К моим заявкам</a>
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
                        <thead>
                        <tr><th>Дата</th><th>Длит.</th><th>Заметка</th></tr>
                        </thead>
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

        <?php if ($report !== null): ?>
            <div class="card">
                <h3>Итоговый отчёт</h3>
                <p class="text-muted">Создан <?= h(format_datetime($report['created_at'])) ?></p>
                <p style="white-space:pre-wrap"><?= h($report['content']) ?></p>
                <?php if (!empty($report['file_path'])): ?>
                    <a class="btn" href="<?= h(url('client_report_download', ['id' => $request['id']])) ?>">⬇ Скачать DOCX</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <aside>
        <div class="card">
            <h3>Параметры</h3>
            <div class="detail-row">
                <span class="detail-row__label">Статус</span>
                <span class="detail-row__value"><span class="<?= h(status_class($request['status_code'])) ?>"><?= h($request['status_name']) ?></span></span>
            </div>
            <div class="detail-row" style="margin-top:10px">
                <span class="detail-row__label">Услуга</span>
                <span class="detail-row__value"><?= h($request['service_name']) ?> (<?= h($request['category_name']) ?>)</span>
            </div>
            <div class="detail-row" style="margin-top:10px">
                <span class="detail-row__label">Консультант</span>
                <span class="detail-row__value"><?= h($request['consultant_name'] ?? '— ещё не назначен') ?></span>
            </div>
            <div class="detail-row" style="margin-top:10px">
                <span class="detail-row__label">Приоритет</span>
                <span class="detail-row__value"><?= h(priority_label($request['priority'])) ?></span>
            </div>
            <div class="detail-row" style="margin-top:10px">
                <span class="detail-row__label">Дедлайн</span>
                <span class="detail-row__value"><?= h(format_date($request['deadline'])) ?></span>
            </div>
            <div class="detail-row" style="margin-top:10px">
                <span class="detail-row__label">Создано</span>
                <span class="detail-row__value"><?= h(format_datetime($request['created_at'])) ?></span>
            </div>
            <hr class="divider">
            <div class="detail-row">
                <span class="detail-row__label">Итоговая стоимость</span>
                <span class="detail-row__value" style="font-size:20px;font-weight:700;color:var(--color-primary)"><?= h(format_money($total_cost)) ?></span>
            </div>
        </div>

        <?php if ($payments): ?>
            <div class="card">
                <h3>Платежи</h3>
                <ul style="margin:0;padding-left:18px">
                    <?php foreach ($payments as $p): ?>
                        <li><?= h(format_money($p['amount'])) ?> · <?= h(format_datetime($p['paid_at'])) ?> · <?= h($p['method']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </aside>
</div>
