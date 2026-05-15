<?php
/**
 * Курсовой проект «Консалтинговая компания»
 * Студент: Плахотников Владимир
 * Конфигурация приложения.
 */

return [
    'app' => [
        'name'      => 'Никс Менеджмент — Консалтинговая компания',
        'base_url'  => '/Consulting_Plakhotnikov/public',
        'remember_days' => 30,
        'reports_dir'   => __DIR__ . '/../storage/reports',
    ],
    'db' => [
        'host'     => getenv('DB_HOST') ?: 'db',
        'port'     => getenv('DB_PORT') ?: '3306',
        'name'     => getenv('DB_NAME') ?: 'main_site',
        'user'     => getenv('DB_USER') ?: 'app',
        'password' => getenv('DB_PASS') ?: 'app',
        'charset'  => 'utf8mb4',
    ],
];
