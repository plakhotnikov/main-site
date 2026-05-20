<?php /** @var array $reports */ ?>
<div class="page-header">
    <div><h1>Отчёты по заявкам</h1><div class="subtitle">Всего: <?= count($reports) ?></div></div>
</div>

<?php if (empty($reports)): ?>
    <div class="card empty"><p>Отчётов пока нет.</p></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>#</th><th>Заявка</th><th>Клиент</th><th>Консультант</th><th>Создан</th><th>Файл</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($reports as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td><?= h($r['request_title']) ?></td>
                    <td><?= h($r['client_company']) ?></td>
                    <td><?= h($r['consultant_name'] ?? '—') ?></td>
                    <td><?= h(format_datetime($r['created_at'])) ?></td>
                    <td><code><?= h($r['file_path'] ?? '—') ?></code></td>
                    <td>
                        <?php if (!empty($r['file_path'])): ?>
                            <a class="btn btn--ghost btn--small" href="<?= h(url('admin_report_download', ['id' => $r['id']])) ?>">⬇ DOCX</a>
                        <?php endif; ?>
                        <a class="btn btn--ghost btn--small" href="<?= h(url('admin_request_view', ['id' => $r['request_id']])) ?>">К заявке</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
