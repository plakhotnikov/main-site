<?php $page = $_GET['page'] ?? 'home'; ?>
<ul>
    <li><a href="<?= h(url('home')) ?>">Главная</a></li>
    <li><a href="<?= h(url('services')) ?>">Услуги</a></li>
    <li><a href="<?= h(url('client_dashboard')) ?>" class="<?= $page === 'client_dashboard' ? 'is-active' : '' ?>">Кабинет</a></li>
    <li><a href="<?= h(url('client_requests')) ?>" class="<?= str_starts_with($page, 'client_request') ? 'is-active' : '' ?>">Мои заявки</a></li>
    <li><a href="<?= h(url('client_request_create')) ?>" class="<?= $page === 'client_request_create' ? 'is-active' : '' ?>">Новая заявка</a></li>
</ul>
