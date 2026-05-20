<?php $page = $_GET['page'] ?? 'home'; ?>
<ul>
    <li><a href="<?= h(url('home')) ?>">Главная</a></li>
    <li><a href="<?= h(url('consultant_dashboard')) ?>" class="<?= $page === 'consultant_dashboard' ? 'is-active' : '' ?>">Кабинет</a></li>
    <li><a href="<?= h(url('consultant_requests')) ?>" class="<?= str_starts_with($page, 'consultant_request') ? 'is-active' : '' ?>">Мои заявки</a></li>
</ul>
