<?php /** @var array $consultants */ /** @var array $workload */ use App\Core\Csrf;
$wlMap = [];
foreach ($workload as $w) { $wlMap[(int)$w['consultant_id']] = $w; }
?>
<div class="page-header">
    <div>
        <h1>Консультанты</h1>
        <div class="subtitle">Всего: <?= count($consultants) ?></div>
    </div>
    <a class="btn" href="<?= h(url('admin_users_form')) ?>">+ Создать (через «Пользователи»)</a>
</div>

<?php if (empty($consultants)): ?>
    <div class="card empty"><p>Консультантов пока нет.</p></div>
<?php else: ?>
    <div class="table-wrap">
        <table class="data">
            <thead><tr><th>#</th><th>Логин</th><th>ФИО</th><th>Должность</th><th>Опыт</th><th>Специализация</th><th>Активные</th><th>Завершено</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($consultants as $c): $wl = $wlMap[(int)$c['id']] ?? null; ?>
                <tr>
                    <td>#<?= (int)$c['id'] ?></td>
                    <td><code><?= h($c['login']) ?></code></td>
                    <td><?= h($c['full_name']) ?></td>
                    <td><?= h($c['position'] ?? '—') ?></td>
                    <td><?= (int)$c['experience_years'] ?> лет</td>
                    <td><?= h($c['specialization_name'] ?? '—') ?></td>
                    <td><?= (int)($wl['active_requests'] ?? 0) ?></td>
                    <td><?= (int)($wl['completed_requests'] ?? 0) ?></td>
                    <td class="actions">
                        <a class="btn btn--ghost btn--small" href="<?= h(url('admin_consultants_form', ['id' => $c['id']])) ?>">✎</a>
                        <form class="inline-form" method="post" action="<?= h(url('admin_consultants_delete')) ?>" onsubmit="return confirm('Удалить консультанта?');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button class="btn btn--danger btn--small" type="submit">✗</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
