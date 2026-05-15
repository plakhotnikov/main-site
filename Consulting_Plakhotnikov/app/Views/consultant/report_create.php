<?php
/** @var array $request */
/** @var array|null $report */
use App\Core\Csrf;
?>
<div class="page-header">
    <div>
        <h1>Отчёт по заявке #<?= (int)$request['id'] ?></h1>
        <div class="subtitle"><?= h($request['title']) ?></div>
    </div>
    <a class="btn btn--ghost" href="<?= h(url('consultant_request_view', ['id' => $request['id']])) ?>">← К заявке</a>
</div>

<div class="card">
    <p class="text-muted">При сохранении будет сгенерирован DOCX-файл и отправлен в storage/reports/. Заявка автоматически перейдёт в статус «На согласовании».</p>
    <form class="form" method="post" action="<?= h(url('consultant_save_report')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
        <div class="form-field">
            <label for="content">Текст отчёта *</label>
            <textarea name="content" id="content" rows="14" required><?= h($report['content'] ?? '') ?></textarea>
            <small>Можно использовать переносы строк — они сохранятся в DOCX.</small>
        </div>
        <div class="form-actions">
            <a class="btn btn--ghost" href="<?= h(url('consultant_request_view', ['id' => $request['id']])) ?>">Отмена</a>
            <button class="btn" type="submit">Сохранить + сгенерировать DOCX</button>
        </div>
    </form>
</div>
