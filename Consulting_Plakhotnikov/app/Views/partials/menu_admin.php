<?php $page = $_GET['page'] ?? 'home'; ?>
<ul>
    <li><a href="<?= h(url('admin_dashboard')) ?>" class="<?= $page === 'admin_dashboard' ? 'is-active' : '' ?>">Дашборд</a></li>
    <li><a href="<?= h(url('admin_requests')) ?>" class="<?= str_starts_with($page, 'admin_request') ? 'is-active' : '' ?>">Заявки</a></li>
    <li><a href="<?= h(url('admin_users')) ?>" class="<?= str_starts_with($page, 'admin_user') ? 'is-active' : '' ?>">Пользователи</a></li>
    <li><a href="<?= h(url('admin_clients')) ?>" class="<?= str_starts_with($page, 'admin_client') ? 'is-active' : '' ?>">Клиенты</a></li>
    <li><a href="<?= h(url('admin_consultants')) ?>" class="<?= str_starts_with($page, 'admin_consultant') ? 'is-active' : '' ?>">Консультанты</a></li>
    <li><a href="<?= h(url('admin_services')) ?>" class="<?= str_starts_with($page, 'admin_service') ? 'is-active' : '' ?>">Услуги</a></li>
    <li><a href="<?= h(url('admin_categories')) ?>" class="<?= str_starts_with($page, 'admin_categ') ? 'is-active' : '' ?>">Категории</a></li>
    <li><a href="<?= h(url('admin_industries')) ?>" class="<?= str_starts_with($page, 'admin_indust') ? 'is-active' : '' ?>">Отрасли</a></li>
    <li><a href="<?= h(url('admin_specializations')) ?>" class="<?= str_starts_with($page, 'admin_special') ? 'is-active' : '' ?>">Специализации</a></li>
    <li><a href="<?= h(url('admin_statuses')) ?>" class="<?= str_starts_with($page, 'admin_status') ? 'is-active' : '' ?>">Статусы</a></li>
    <li><a href="<?= h(url('admin_reports')) ?>" class="<?= str_starts_with($page, 'admin_report') ? 'is-active' : '' ?>">Отчёты</a></li>
</ul>
