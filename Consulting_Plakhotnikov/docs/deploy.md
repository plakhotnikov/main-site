# Развёртывание «Консалтинговая компания»

## Локальный запуск через Docker

В корне основного репозитория уже подготовлены `Dockerfile` и `docker-compose.yml` с PHP 8.2 + Apache + GD + Zip + msmtp и MySQL 8.0.

```bash
docker compose up -d
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/01_schema.sql
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/02_views.sql
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/03_routines.sql
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/04_seed.sql
```

Открыть: <http://localhost/Consulting_Plakhotnikov/public/>.

## Развёртывание на платном хостинге

1. **Требования к хостингу:** PHP 8.2+, MySQL 8.0+, расширения `pdo_mysql`, `gd`, `zip`. Для отчётов нужен право на запись в `storage/reports/`.

2. **Создать БД** на хостинге (например `consulting_main`), кодировка `utf8mb4`, коллация `utf8mb4_unicode_ci`. Импортировать SQL-файлы строго по порядку:

   ```
   01_schema.sql  →  02_views.sql  →  03_routines.sql  →  04_seed.sql
   ```

   Импорт делать через phpMyAdmin или через SSH:

   ```bash
   mysql -u <USER> -p --default-character-set=utf8mb4 consulting_main < 01_schema.sql
   mysql -u <USER> -p --default-character-set=utf8mb4 consulting_main < 02_views.sql
   mysql -u <USER> -p --default-character-set=utf8mb4 consulting_main < 03_routines.sql
   mysql -u <USER> -p --default-character-set=utf8mb4 consulting_main < 04_seed.sql
   ```

   Флаг `--default-character-set=utf8mb4` критичен: без него хранимые процедуры запоминают latin1 и падают на сравнении с utf8mb4-таблицами.

3. **Загрузить файлы**: содержимое папки `Consulting_Plakhotnikov/` положить в `~/public_html/consulting/` (или поддомен). DocumentRoot нужно настроить на подпапку `public/` — это ключевое требование, иначе раскроется содержимое `app/`, `db/`, `config/`. Если хостинг позволяет переопределить DocumentRoot — указать `.../consulting/public`. Если нет — положить содержимое `public/` в корень и поправить относительные пути.

4. **Установить зависимости**:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

   Composer-зависимости (`phpoffice/phpword`, `phpmailer/phpmailer`, `phpoffice/phpspreadsheet`) лежат в общем `vendor/` основного репозитория — пути в `public/index.php` уже учитывают это (`../vendor/autoload.php`).

5. **Настроить `config/config.php`** (или прокинуть переменные окружения `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`):

   ```php
   'db' => [
       'host'     => 'localhost',
       'port'     => '3306',
       'name'     => 'consulting_main',
       'user'     => '...',
       'password' => '...',
       'charset'  => 'utf8mb4',
   ],
   'app' => [
       'base_url' => '/consulting/public',
       ...
   ],
   ```

6. **Дать права на storage** для генерации отчётов:

   ```bash
   chmod -R 775 storage/reports/
   ```

7. **Проверить вход** под `admin / 123` и сгенерировать тестовый отчёт.

## Структура проекта

```
Consulting_Plakhotnikov/
├── public/                  ← DocumentRoot
│   ├── index.php            ← front controller
│   ├── .htaccess
│   └── assets/{css,js,img}/
├── app/
│   ├── Core/                ← Database, Auth, Router, Template, Csrf, BaseModel, Helpers
│   ├── Models/              ← 14 моделей, наследуют BaseModel
│   ├── Controllers/         ← Home, Auth, Client, Consultant, Admin/*, Error
│   └── Views/               ← layouts/, partials/, home/, auth/, client/, consultant/, admin/, errors/
├── db/                      ← 4 SQL-файла + ER-диаграмма
├── docs/                    ← credentials.txt, deploy.md, architecture.md
├── storage/reports/         ← сгенерированные DOCX
└── config/config.php
```
