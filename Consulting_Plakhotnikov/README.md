# Курсовой проект «Консалтинговая компания»

**Тема:** Web-приложение для финансово-юридической консалтинговой компании «Никс Менеджмент».
**Студент:** Плахотников Владимир Александрович.
**Стек:** PHP 8.2 (strict types, namespaces, PSR-4-like autoloader) · MySQL 8.0 (InnoDB, utf8mb4_unicode_ci) · Apache 2.4 · GD · PHPWord · Docker Compose · Mailpit.

---

## 1. Назначение

Учебная информационная система для консалтинговой компании. Сайт обслуживает четыре роли:

| Роль | Что видит / что делает |
|---|---|
| **Гость** | Общедоступные страницы: главная, услуги, о компании, контакты, страница входа/регистрации. |
| **Клиент** | Личный кабинет: подаёт заявки, видит свои заявки, статусы, итоговую стоимость, скачивает DOCX-отчёт. |
| **Консультант** | Личный кабинет: видит назначенные ему заявки, меняет статусы, фиксирует консультации, формирует DOCX-отчёт. |
| **Директор (admin)** | Отдельная админ-панель: CRUD по всем сущностям, массовое удаление, назначение консультантов, GD-аналитика. |

Бизнес-процесс заявки: `new → assigned → in_progress → review → completed` (или `cancelled` из любого этапа).

---

## 2. Технологический стек и используемые библиотеки

| Слой | Технология |
|---|---|
| Язык | PHP 8.2 (`declare(strict_types=1)`, namespaces `App\…`) |
| СУБД | MySQL 8.0 (InnoDB, `utf8mb4_unicode_ci`) |
| Веб-сервер | Apache 2.4 (mod_rewrite через `.htaccess`) |
| Шаблоны | Собственный класс `App\Core\Template` (PHP-шаблоны + layout + partials) |
| Доступ к БД | PDO + prepared statements (`App\Core\Database`) |
| Объектная модель | `App\Models\BaseModel` + 14 потомков |
| Графика | GD (имеется в Docker-образе), три кастомных PNG-диаграммы |
| Документы | PHPOffice/PHPWord — генерация DOCX-отчётов |
| Контейнеризация | Docker Compose (web + db + mailpit) |
| Сторонние пакеты | `phpoffice/phpword`, `phpmailer/phpmailer`, `phpoffice/phpspreadsheet` (общий `vendor/` репозитория) |

---

## 3. Структура проекта

```
Consulting_Plakhotnikov/
├── public/                          ← DocumentRoot веб-сервера
│   ├── index.php                    ← front controller (autoload + сессия + cookie + Router)
│   ├── .htaccess
│   └── assets/css/style.css         ← фирменный CSS (≈585 строк, тема «Никс Менеджмент»)
│
├── app/
│   ├── Core/                        ← инфраструктура
│   │   ├── Database.php             ← PDO singleton (prepared)
│   │   ├── Auth.php                 ← сессия + cookie auth_remember (30 дней)
│   │   ├── Csrf.php                 ← CSRF-токен на каждую форму
│   │   ├── Router.php               ← карта ?page=… → controller@action
│   │   ├── Template.php             ← render(view, data, layout) + partial()
│   │   ├── Helpers.php              ← url/redirect/flash/baseUrl
│   │   └── functions.php            ← глобальные h(), url(), asset(), format_money(), …
│   │
│   ├── Models/                      ← 14 моделей по числу таблиц
│   │   ├── BaseModel.php            ← find/all/create/update/delete/bulkDelete (все prepared)
│   │   ├── User.php  Role.php
│   │   ├── Client.php  Consultant.php
│   │   ├── Industry.php  Specialization.php
│   │   ├── ServiceCategory.php  Service.php
│   │   ├── Request.php  RequestStatus.php  RequestService.php
│   │   ├── Consultation.php  Report.php  Payment.php
│   │
│   ├── Controllers/                 ← 16 контроллеров
│   │   ├── HomeController.php       ← / services / about / contacts
│   │   ├── AuthController.php       ← login / logout / register
│   │   ├── ClientController.php     ← кабинет клиента
│   │   ├── ConsultantController.php ← кабинет консультанта + генерация DOCX
│   │   ├── ErrorController.php      ← 404
│   │   └── Admin/                   ← 11 админ-контроллеров
│   │       ├── DashboardController.php
│   │       ├── ChartsController.php          ← GD: 3 PNG-диаграммы
│   │       ├── UsersController.php           ← CRUD + bulk-delete
│   │       ├── ClientsController.php
│   │       ├── ConsultantsController.php
│   │       ├── ServicesController.php
│   │       ├── CategoriesController.php
│   │       ├── IndustriesController.php
│   │       ├── SpecializationsController.php
│   │       ├── StatusesController.php
│   │       ├── RequestsController.php        ← + assign + bulk-delete (sp_bulk_delete_requests)
│   │       └── ReportsController.php         ← список DOCX-отчётов
│   │
│   └── Views/                       ← шаблоны
│       ├── layouts/main.php         ← общий layout (header / main / footer)
│       ├── partials/                ← menu_guest / menu_client / menu_consultant / menu_admin / flash
│       ├── home/                    ← index, services, about, contacts
│       ├── auth/                    ← login, register
│       ├── client/                  ← dashboard, requests, request_create, request_view
│       ├── consultant/              ← dashboard, requests, request_view, report_create
│       ├── admin/                   ← dashboard, simple_list, simple_form + папки по сущностям
│       └── errors/404.php
│
├── db/
│   ├── 01_schema.sql                ← 14 таблиц + 5 справочников + индексы + FK + ON DELETE
│   ├── 02_views.sql                 ← 4 VIEW (v_client_requests, v_consultant_workload, v_revenue_by_category, v_admin_requests_full)
│   ├── 03_routines.sql              ← 3 PROCEDURE + 2 FUNCTION
│   └── 04_seed.sql                  ← демо-данные (роли, услуги, пользователи, заявка)
│
├── docs/
│   ├── architecture.md              ← Mermaid: ER, граф каскадов, FSM, sequence-диаграммы (≈770 строк)
│   ├── deploy.md                    ← инструкция развёртывания (Docker + платный хостинг)
│   ├── credentials.txt              ← демо-логины + чек-лист проверки
│   └── completed_requirements.md    ← карта требований ТЗ → где реализовано
│
├── storage/reports/                 ← сгенерированные DOCX-файлы консультантов
├── config/config.php                ← БД, base_url, remember_days, путь к reports
└── README.md                        ← этот файл
```

---

## 4. База данных

### 4.1 Состав

| Тип объекта | Имена | Назначение |
|---|---|---|
| **Справочники (5)** | `roles`, `industries`, `specializations`, `service_categories`, `request_statuses` | Нормализация и ENUM-замена. |
| **Пользовательские таблицы** | `users`, `clients` (1:1), `consultants` (1:1) | Логин + два профиля. Пароли — в открытом виде согласно ТЗ. |
| **Бизнес-таблицы** | `services`, `requests`, `consultations`, `request_services` (M:N), `reports` (1:1 к заявке), `payments` | Каталог услуг + заявка с цепочкой дочерних записей. |
| **Представления (4)** | `v_client_requests`, `v_consultant_workload`, `v_revenue_by_category`, `v_admin_requests_full` | Все списочные выборки делаются через них. |
| **Хранимые процедуры (3)** | `sp_assign_consultant`, `sp_change_status`, `sp_bulk_delete_requests` | Назначение, смена статуса, массовое удаление. |
| **Функции (2)** | `fn_request_total_cost`, `fn_consultant_revenue` | Итоговая стоимость и доход консультанта (для DOCX и диаграмм). |

### 4.2 Каскадное удаление (без UI-кнопки)

Реализовано на уровне FK `ON DELETE CASCADE / SET NULL / RESTRICT`:

- `users` → `clients`, `consultants` — **CASCADE**
- `clients` → `requests` — **CASCADE**
- `requests` → `consultations`, `request_services`, `reports`, `payments` — **CASCADE**
- `industries` → `clients`, `specializations` → `consultants`, `consultants` → `requests` — **SET NULL**
- `roles`, `service_categories`, `services`, `request_statuses` — **RESTRICT**

Цепочка: удалили клиента → удалились его заявки → каскадом удалились консультации, доп. услуги, отчёт и платежи. Никаких кнопок «удалить также…» в UI нет — пользователь жмёт обычное «Удалить», БД делает остальное.

### 4.3 Все запросы — через объекты + prepared

`App\Core\Database` оборачивает PDO в три метода: `query()`, `one()`, `execute()`. Все они формируют `prepare()` + `execute([…])`. Прямой конкатенации параметров в SQL нет нигде. Списочные выборки идут через `SELECT * FROM v_…`, мутирующие операции — через `CALL sp_…` или `INSERT/UPDATE/DELETE` с `?`-маркерами.

---

## 5. Архитектура приложения

### 5.1 Front controller + Router

`public/index.php` — единая точка входа. Запускает сессию, подключает Composer-autoload и собственный PSR-4-like autoloader для `App\*`, восстанавливает авторизацию из cookie и передаёт управление в `Router::dispatch($_GET['page'])`. Карта роутов — массив `App\Core\Router::ROUTES` (≈60 эндпоинтов).

### 5.2 Шаблоны

`App\Core\Template::render('view.name', $data, 'main')`:

1. Рендерит view-файл (`app/Views/view/name.php`) в строку через `ob_start()`.
2. Подставляет его в layout (`app/Views/layouts/main.php`) под именем `$content`.
3. Layout сам подключает шапку, меню (`partials/menu_<role>.php`), flash-сообщения, footer.

Никакого «голого текста на белом фоне» — везде используется общий layout с шапкой, навигацией по ролям, контентным блоком и подвалом.

### 5.3 Авторизация

`App\Core\Auth::login()` — `SELECT … WHERE login=? AND password=?` (prepared, открытый пароль по ТЗ). При успехе пишется `$_SESSION['user_id']`. Если поставлена галка «Запомнить меня», генерируется `bin2hex(random_bytes(32))`, пишется в `users.remember_token` и в cookie `auth_remember` (HttpOnly, 30 дней).

`Auth::resumeFromCookie()` вызывается при каждом запросе, и если сессии нет, но в cookie есть валидный токен — пользователь подтягивается из БД и сессия восстанавливается.

`Auth::requireRole('admin')` и т. п. — guard в начале каждого контроллера.

### 5.4 CSRF

`App\Core\Csrf::field()` встраивает hidden-поле `_csrf` во все POST-формы. На стороне сервера — `Csrf::check($_POST['_csrf'])` через `hash_equals`. Без валидного токена POST падает с 500-кой и понятным сообщением.

---

## 6. Виды форм и ввода данных

Все шесть типов ввода требований ТЗ присутствуют. Эталонная форма — `Views/client/request_create.php`:

| Тип | Где |
|---|---|
| Ручной ввод (`<input type="text">`) | Тема заявки, телефон, ИНН |
| Текстовое поле (`<textarea>`) | Описание задачи |
| Выпадающий список (`<select>`) | Основная услуга, отрасль, роль, специализация, консультант |
| Радиокнопки | Приоритет (low/normal/high), способ оплаты в платежах |
| Чекбоксы (групповые) | Дополнительные услуги (M:N → `request_services`), отметка `is_active`, чекбоксы в массовом удалении |
| Специальные `<input>` | `type="date"` (дедлайн), `type="email"`, `type="tel"`, `type="number"`, `type="datetime-local"` (консультация) |

---

## 7. Графическая библиотека (GD)

`App\Controllers\Admin\ChartsController` рендерит три PNG-картинки и стримит их через `header('Content-Type: image/png'); imagepng($img);`. Шрифт — DejaVu Sans, установленный в Docker-образ.

| Тип | Что показывает | SQL-источник |
|---|---|---|
| `monthly` | Гистограмма заявок по месяцам текущего года, с градиентом и сеткой | `requests GROUP BY MONTH(created_at)` |
| `categories` | Круговая диаграмма дохода по категориям услуг + легенда с % | `v_revenue_by_category` |
| `consultants` | Горизонтальные столбцы дохода консультантов за текущий месяц | `fn_consultant_revenue(id, year, month)` |

На дашборде админа просто `<img src="?page=admin_chart&type=…">` — браузер параллельно подгружает три PNG.

---

## 8. Генерация документов (PHPWord)

`ConsultantController::saveReport()` создаёт `report_<requestId>_<datetime>.docx` в `storage/reports/`. Документ содержит шапку компании, информацию о клиенте/услуге/консультанте, описание задачи, текст отчёта, хронологию консультаций и итоговую стоимость (`fn_request_total_cost`). Путь сохраняется в `reports.file_path`, клиент скачивает файл через `Content-Disposition: attachment`.

---

## 9. Запуск

### 9.1 Локально через Docker

```bash
docker compose up -d
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/01_schema.sql
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/02_views.sql
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/03_routines.sql
docker exec -i main-site-db mysql -uapp -papp main_site < Consulting_Plakhotnikov/db/04_seed.sql
```

Открыть: <http://localhost/Consulting_Plakhotnikov/public/>.

Подробности (хостинг, .htaccess, переменные окружения) — в [`docs/deploy.md`](docs/deploy.md).

### 9.2 Демо-аккаунты

| Роль | Логин | Пароль |
|---|---|---|
| Директор | `admin` | `123` |
| Консультант (фин.) | `consultant1` | `123` |
| Консультант (юрист) | `consultant2` | `123` |
| Клиент | `client1` | `123` |
| Гость | — | без входа |

Дополнительно см. [`docs/credentials.txt`](docs/credentials.txt).

---

## 10. Чек-лист быстрой проверки

1. **Авторизация и cookie.** Войти под `client1 / 123` с галкой «Запомнить меня», закрыть браузер, открыть `localhost/Consulting_Plakhotnikov/public/` — вход выполнится автоматически через cookie `auth_remember`.
2. **Все типы форм.** `/client/request/create` — `text`, `textarea`, `select`, `radio`, `checkbox[]`, `date`, `email`, `tel`.
3. **CRUD + bulk-delete.** `/admin/requests` → отметить чекбоксами несколько заявок → «Удалить выбранное» → вызывается `sp_bulk_delete_requests` и каскад на дочерние таблицы.
4. **Каскадное удаление без UI-кнопки.** `/admin/clients` → удалить клиента → автоматически исчезают его заявки, консультации, request_services, отчёты и платежи (через `ON DELETE CASCADE`).
5. **GD-диаграммы.** `/admin/dashboard` — три PNG (`?page=admin_chart&type=monthly|categories|consultants`).
6. **DOCX-отчёт.** Под `consultant1` → открыть заявку → «Создать отчёт» → файл появляется в `storage/reports/`; под `client1` тот же отчёт можно скачать.
7. **Роли.** `client1` → попытка зайти на `?page=admin_dashboard` → 403. `consultant1` видит только заявки, где `consultant_id = его id`.

---

## 11. Документация

- [`docs/completed_requirements.md`](docs/completed_requirements.md) — построчная карта требований ТЗ → где и в каком объёме они реализованы.
- [`docs/architecture.md`](docs/architecture.md) — Mermaid-диаграммы: ER, граф каскадов, FSM статусов, шесть sequence-диаграмм по ключевым сценариям, карта классов, карта ролей.
- [`docs/deploy.md`](docs/deploy.md) — развёртывание локально (Docker) и на платном хостинге.
- [`docs/credentials.txt`](docs/credentials.txt) — демо-логины и чек-лист проверки.
