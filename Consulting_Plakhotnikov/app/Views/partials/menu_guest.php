<?php $page = $_GET['page'] ?? 'home'; ?>
<ul>
    <li><a href="<?= h(url('home')) ?>" class="<?= $page === 'home' ? 'is-active' : '' ?>">Главная</a></li>
    <li><a href="<?= h(url('services')) ?>" class="<?= $page === 'services' ? 'is-active' : '' ?>">Услуги</a></li>
    <li><a href="<?= h(url('about')) ?>" class="<?= $page === 'about' ? 'is-active' : '' ?>">О компании</a></li>
    <li><a href="<?= h(url('contacts')) ?>" class="<?= $page === 'contacts' ? 'is-active' : '' ?>">Контакты</a></li>
</ul>
