<div class="card" style="text-align:center;padding:60px 30px">
    <h1 style="font-size:48px;color:var(--color-primary);margin:0 0 14px">404</h1>
    <p class="text-muted">Страница «<?= h($page ?? '') ?>» не найдена.</p>
    <p><a class="btn" href="<?= h(url('home')) ?>">На главную</a></p>
</div>
