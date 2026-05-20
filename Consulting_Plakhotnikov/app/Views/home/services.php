<?php
/** @var array $categories */
/** @var array $services */
?>
<div class="page-header">
    <div>
        <h1>Услуги компании</h1>
        <div class="subtitle">Финансовый и юридический консалтинг — фиксированные цены</div>
    </div>
</div>

<?php foreach ($categories as $cat): ?>
    <?php
        $catServices = array_filter($services, fn ($s) => (int)$s['category_id'] === (int)$cat['id']);
        if (!$catServices) continue;
    ?>
    <h2 style="color:var(--color-primary);font-family:var(--font-display);margin:30px 0 16px">
        <?= h($cat['name']) ?>
    </h2>
    <div class="services-grid">
        <?php foreach ($catServices as $s): ?>
            <article class="service-card">
                <div class="service-card__category"><?= h($s['category_name']) ?></div>
                <h3 class="service-card__title"><?= h($s['name']) ?></h3>
                <p class="service-card__desc"><?= h($s['description']) ?></p>
                <div class="service-card__footer">
                    <span class="service-card__price"><?= h(format_money($s['price'])) ?></span>
                    <span class="service-card__hours">≈ <?= (int)$s['duration_hours'] ?> ч</span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
