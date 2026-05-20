# Архитектура Web-приложения «Консалтинговая компания»

**Студент:** Плахотников Владимир
**Стек:** PHP 8.2 + MySQL 8.0 + Apache + GD, Docker
**Корень курсового проекта:** `Consulting_Plakhotnikov/`

Документ описывает архитектуру курсового проекта в виде набора схем и диаграмм

---

## Оглавление

1. [Высокоуровневая архитектура](#1-высокоуровневая-архитектура)
2. [ER-диаграмма базы данных](#2-er-диаграмма-базы-данных)
3. [Граф каскадного удаления](#3-граф-каскадного-удаления-on-delete)
4. [Конечный автомат статусов заявки](#4-конечный-автомат-статусов-заявки)
5. [Карта классов (модели)](#5-карта-классов-модели)
6. [Sequence: Авторизация с cookie «Запомнить меня»](#6-sequence-авторизация-с-cookie-запомнить-меня)
7. [Sequence: Создание заявки клиентом](#7-sequence-создание-заявки-клиентом)
8. [Sequence: Назначение консультанта администратором](#8-sequence-назначение-консультанта-администратором)
9. [Sequence: Работа консультанта и формирование отчёта](#9-sequence-работа-консультанта-и-формирование-отчёта)
10. [Sequence: Массовое удаление с чекбоксами](#10-sequence-массовое-удаление-с-чекбоксами)
11. [Sequence: GD-диаграммы в админке](#11-sequence-gd-диаграммы-в-админке)
12. [Карта ролей и разделов сайта](#12-карта-ролей-и-разделов-сайта)

---

## 1. Высокоуровневая архитектура

```mermaid
graph TB
    subgraph Browser["Браузер пользователя"]
        UI[HTML + CSS + JS]
    end

    subgraph Docker["Docker compose"]
        subgraph Web["Контейнер web (PHP 8.2 + Apache + GD)"]
            FC[public/index.php<br/>Front Controller]
            ROUTER[Router]
            AUTH[Auth<br/>session + cookie]
            TEMPL[Template<br/>layout + partials]

            subgraph Controllers
                HOME[HomeController]
                AUTHC[AuthController]
                CLIENTC[ClientController]
                CONSC[ConsultantController]
                ADMIN[Admin/*<br/>11 контроллеров]
                CHARTS[ChartsController<br/>GD PNG]
            end

            subgraph Models["Models (PDO + prepared)"]
                BM[BaseModel]
                M14[14 моделей<br/>Request, User, ...]
            end

            PHPWORD[PHPWord<br/>генерация DOCX]
        end

        subgraph DB["Контейнер db (MySQL 8.0)"]
            TABLES[(14 таблиц)]
            VIEWS{{4 VIEW}}
            PROCS[/3 PROCEDURE/]
            FUNCS[/2 FUNCTION/]
        end

        STORAGE[(storage/reports/<br/>DOCX-файлы)]
    end

    UI -->|HTTP| FC
    FC --> ROUTER
    ROUTER --> AUTH
    ROUTER --> Controllers
    Controllers --> Models
    Controllers --> TEMPL
    Models -->|PDO prepared| TABLES
    Models -->|SELECT| VIEWS
    Models -->|CALL| PROCS
    Models -->|SELECT fn_*| FUNCS
    CHARTS -->|imagepng| UI
    CONSC --> PHPWORD
    PHPWORD --> STORAGE
    CLIENTC --> STORAGE
```

---

## 2. ER-диаграмма базы данных

```mermaid
erDiagram
    roles ||--o{ users : "role_id"
    users ||--o| clients : "user_id 1:1"
    users ||--o| consultants : "user_id 1:1"
    industries ||--o{ clients : "industry_id"
    specializations ||--o{ consultants : "specialization_id"
    service_categories ||--o{ services : "category_id"
    clients ||--o{ requests : "client_id"
    services ||--o{ requests : "service_id"
    consultants ||--o{ requests : "consultant_id"
    request_statuses ||--o{ requests : "status_id"
    requests ||--o{ consultations : "request_id"
    requests ||--o{ request_services : "request_id"
    services ||--o{ request_services : "service_id"
    requests ||--|| reports : "request_id 1:1"
    consultants ||--o{ reports : "consultant_id"
    requests ||--o{ payments : "request_id"

    roles {
        TINYINT id PK
        VARCHAR code UK "guest|client|consultant|admin"
        VARCHAR name
    }
    users {
        INT id PK
        VARCHAR login UK
        VARCHAR password "plain (по ТЗ)"
        TINYINT role_id FK
        VARCHAR full_name
        VARCHAR phone
        VARCHAR email
        TINYINT is_active
        DATETIME created_at
    }
    clients {
        INT id "PK"
        INT user_id "FK_UK"
        VARCHAR company
        VARCHAR inn
        INT industry_id "FK"
        VARCHAR address
    }
    consultants {
        INT id "PK"
        INT user_id "FK_UK"
        VARCHAR position
        TINYINT experience_years
        INT specialization_id "FK"
    }
    industries {
        INT id PK
        VARCHAR name UK
    }
    specializations {
        INT id PK
        VARCHAR name UK
    }
    service_categories {
        INT id PK
        VARCHAR code UK "financial|legal"
        VARCHAR name
    }
    services {
        INT id PK
        INT category_id FK
        VARCHAR name
        TEXT description
        DECIMAL price
        SMALLINT duration_hours
        TINYINT is_active
    }
    requests {
        INT id "PK"
        INT client_id "FK"
        INT service_id "FK"
        INT consultant_id "FK_NULL"
        TINYINT status_id "FK"
        VARCHAR title
        TEXT description
        ENUM priority "low|normal|high"
        DATE deadline
        DATETIME created_at
        DATETIME updated_at
    }
    request_statuses {
        TINYINT id PK
        VARCHAR code UK
        VARCHAR name
        TINYINT sort_order
    }
    consultations {
        INT id PK
        INT request_id FK
        DATETIME held_at
        SMALLINT duration_min
        TEXT notes
    }
    request_services {
        INT request_id PK_FK
        INT service_id PK_FK
    }
    reports {
        INT id PK
        INT request_id FK_UK
        INT consultant_id FK_NULL
        MEDIUMTEXT content
        VARCHAR file_path
        DATETIME created_at
    }
    payments {
        INT id PK
        INT request_id FK
        DECIMAL amount
        DATETIME paid_at
        ENUM method "cash|card|transfer"
    }
```

**Условные обозначения связей:**

- `||--o{` — один-ко-многим (обязательная сторона: одна запись слева, ноль или много справа)
- `||--o|` — один-к-одному с опциональной правой стороной (профиль клиента/консультанта)
- `||--||` — один-к-одному обязательный (заявка ↔ отчёт, ограничено `UNIQUE`)

---

## 3. Граф каскадного удаления (ON DELETE)

Стрелка показывает «что удаляется при удалении источника».

```mermaid
graph LR
    USERS[users] -->|CASCADE| CLIENTS[clients]
    USERS -->|CASCADE| CONSULTANTS[consultants]
    CLIENTS -->|CASCADE| REQUESTS[requests]
    REQUESTS -->|CASCADE| CONSULTATIONS[consultations]
    REQUESTS -->|CASCADE| RS[request_services]
    REQUESTS -->|CASCADE| REPORTS[reports]
    REQUESTS -->|CASCADE| PAYMENTS[payments]

    INDUSTRIES[industries] -.->|SET NULL| CLIENTS
    SPECS[specializations] -.->|SET NULL| CONSULTANTS
    CONSULTANTS -.->|SET NULL| REQUESTS
    CONSULTANTS -.->|SET NULL| REPORTS

    ROLES[roles] ===>|RESTRICT| USERS
    SC[service_categories] ===>|RESTRICT| SERVICES[services]
    SERVICES ===>|RESTRICT| REQUESTS
    STATUSES[request_statuses] ===>|RESTRICT| REQUESTS

    classDef cascade fill:#ffe0e0,stroke:#c0392b
    classDef setnull fill:#fff7d6,stroke:#d4ac0d
    classDef restrict fill:#d6eaff,stroke:#2874a6
```

**Легенда:**

- 🟥 **CASCADE** (сплошная стрелка): удалили клиента → удалились его заявки → каскадом удалились консультации, доп. услуги, отчёт и платежи
- 🟨 **SET NULL** (пунктирная): уволили консультанта — его заявки остаются, поле `consultant_id` обнуляется
- 🟦 **RESTRICT** (двойная): нельзя удалить запись из справочника, пока есть ссылки на неё

---

## 4. Конечный автомат статусов заявки

```mermaid
stateDiagram-v2
    [*] --> new : клиент создал заявку
    new --> assigned : админ назначил<br/>консультанта<br/>(sp_assign_consultant)
    new --> cancelled : админ/клиент<br/>отменил
    assigned --> in_progress : консультант<br/>начал работу
    assigned --> cancelled
    in_progress --> review : консультант<br/>отправил<br/>на согласование
    in_progress --> cancelled
    review --> completed : клиент принял
    review --> in_progress : клиент вернул<br/>с замечаниями
    completed --> [*]
    cancelled --> [*]

    note right of completed
        формируется отчёт
        в reports + DOCX
    end note
```

Все переходы выполняются через процедуру `sp_change_status(request_id, status_code, comment)`, которая дополнительно пишет запись в `consultations` для аудита.

---

## 5. Карта классов (модели)

```mermaid
classDiagram
    class BaseModel {
        <<abstract>>
        +string table$
        +array fillable$
        +find(int id)
        +all(array where, string order)
        +create(array data)
        +update(int id, array data)
        +delete(int id)
        +bulkDelete(array ids)
        #db() PDO
    }

    class Database {
        -PDO instance$
        +getInstance() PDO$
        +query(string sql, array params) array$
    }

    class Auth {
        +login(string login, string pass, bool remember) bool$
        +logout()$
        +check() bool$
        +user() User$
        +role() string$
        +requireRole(string role)$
    }

    class User {
        +int id
        +string login
        +string password
        +int role_id
        +string full_name
        +role() Role
    }

    class Role {
        +int id
        +string code
        +string name
    }

    class Client {
        +int id
        +int user_id
        +string company
        +string inn
        +int industry_id
        +user() User
        +industry() Industry
        +requests() List~Request~
    }

    class Consultant {
        +int id
        +int user_id
        +string position
        +int experience_years
        +int specialization_id
        +user() User
        +specialization() Specialization
        +activeRequests() List~Request~
    }

    class Industry {
        +int id
        +string name
    }

    class Specialization {
        +int id
        +string name
    }

    class ServiceCategory {
        +int id
        +string code
        +string name
        +services() List~Service~
    }

    class Service {
        +int id
        +int category_id
        +string name
        +decimal price
        +int duration_hours
        +bool is_active
        +category() ServiceCategory
    }

    class Request {
        +int id
        +int client_id
        +int service_id
        +int consultant_id
        +int status_id
        +string title
        +client() Client
        +service() Service
        +consultant() Consultant
        +status() RequestStatus
        +consultations() List~Consultation~
        +extraServices() List~Service~
        +totalCost() float
    }

    class RequestStatus {
        +int id
        +string code
        +string name
        +int sort_order
    }

    class Consultation {
        +int id
        +int request_id
        +datetime held_at
        +int duration_min
        +string notes
        +request() Request
    }

    class Report {
        +int id
        +int request_id
        +int consultant_id
        +string content
        +string file_path
        +request() Request
        +consultant() Consultant
        +generateDocx() string
    }

    class Payment {
        +int id
        +int request_id
        +decimal amount
        +datetime paid_at
        +string method
        +request() Request
    }

    BaseModel <|-- User
    BaseModel <|-- Role
    BaseModel <|-- Client
    BaseModel <|-- Consultant
    BaseModel <|-- Industry
    BaseModel <|-- Specialization
    BaseModel <|-- ServiceCategory
    BaseModel <|-- Service
    BaseModel <|-- Request
    BaseModel <|-- RequestStatus
    BaseModel <|-- Consultation
    BaseModel <|-- Report
    BaseModel <|-- Payment

    BaseModel ..> Database : uses
    Auth ..> User : authenticates
```

---

## 6. Sequence: Авторизация с cookie «Запомнить меня»

```mermaid
sequenceDiagram
    actor U as Пользователь
    participant B as Браузер
    participant FC as index.php<br/>(Front Controller)
    participant A as Auth
    participant DB as MySQL

    U->>B: Открывает /login
    B->>FC: GET ?page=login
    FC->>A: check()
    A-->>FC: false
    FC-->>B: HTML формы login.php
    B-->>U: Форма (login, password, [✓] Запомнить меня)

    U->>B: Submit
    B->>FC: POST ?page=login {login, pass, remember}
    FC->>A: login(login, pass, remember=true)
    A->>DB: SELECT * FROM users WHERE login=? AND password=?<br/>(prepared statement)
    DB-->>A: user row
    A->>A: $_SESSION['user_id'] = user.id

    alt remember == true
        A->>A: token = bin2hex(random_bytes(32))
        A->>DB: UPDATE users SET remember_token = ?
        A->>B: setcookie('auth_remember', token, +30 days)
    end

    A-->>FC: success
    FC-->>B: 302 redirect по роли<br/>(client/consultant/admin)
    B-->>U: Личный кабинет

    Note over U,B: Через неделю пользователь снова открывает сайт

    U->>B: Открывает /
    B->>FC: GET / (cookie auth_remember=token)
    FC->>A: check()
    A->>DB: SELECT user WHERE remember_token = ?
    DB-->>A: user row
    A->>A: $_SESSION['user_id'] = user.id
    A-->>FC: true
    FC-->>B: главная (уже авторизован)
```

---

## 7. Sequence: Создание заявки клиентом

```mermaid
sequenceDiagram
    actor C as Клиент
    participant B as Браузер
    participant FC as index.php
    participant CC as ClientController
    participant RM as Request (model)
    participant RSM as RequestService (model)
    participant DB as MySQL

    C->>B: /client/request/create
    B->>FC: GET ?page=client_request_create
    FC->>CC: createForm()
    CC->>DB: SELECT * FROM services WHERE is_active=1
    CC->>DB: SELECT * FROM industries
    DB-->>CC: справочники
    CC-->>B: форма (text, textarea, select, radio, checkbox, date, email)
    B-->>C: видит форму

    C->>B: Заполнил, Submit
    B->>FC: POST {service_id, title, description,<br/>priority(radio), extra_services[](checkbox),<br/>deadline, phone(text), email}

    FC->>CC: store()
    CC->>CC: CSRF check
    CC->>CC: валидация полей
    CC->>DB: BEGIN TRANSACTION

    CC->>RM: create({client_id, service_id, status_id=NEW, ...})
    RM->>DB: INSERT INTO requests (...) (prepared)
    DB-->>RM: request_id

    loop по каждой extra-услуге из чекбоксов
        CC->>RSM: create({request_id, service_id})
        RSM->>DB: INSERT INTO request_services (prepared)
    end

    CC->>DB: COMMIT
    CC-->>B: 302 → /client/request/{id}
    B-->>C: «Заявка #N создана» + статус «Новая»
```

---

## 8. Sequence: Назначение консультанта администратором

```mermaid
sequenceDiagram
    actor A as Админ
    participant B as Браузер
    participant AC as Admin/RequestsController
    participant DB as MySQL

    A->>B: /admin/requests
    B->>AC: GET ?page=admin_requests
    AC->>DB: SELECT * FROM v_admin_requests_full
    DB-->>AC: список заявок
    AC-->>B: HTML с таблицей<br/>(новые подсвечены)

    A->>B: Клик по заявке #N
    B->>AC: GET ?page=admin_request_view&id=N
    AC->>DB: SELECT * FROM v_admin_requests_full WHERE id=?
    AC->>DB: SELECT * FROM v_consultant_workload<br/>(чтобы видеть нагрузку)
    DB-->>AC: данные
    AC-->>B: страница заявки + select<br/>«Назначить консультанта»<br/>(подкрашены загруженные)

    A->>B: Выбирает consultant1, Submit
    B->>AC: POST ?page=admin_request_assign<br/>{request_id, consultant_id}
    AC->>AC: requireRole('admin') + CSRF
    AC->>DB: CALL sp_assign_consultant(?, ?)
    Note right of DB: внутри процедуры:<br/>UPDATE requests SET<br/>consultant_id=?, status_id=ASSIGNED
    DB-->>AC: ok

    AC-->>B: 302 → /admin/request/N
    B-->>A: статус заявки → «Назначена»
```

---

## 9. Sequence: Работа консультанта и формирование отчёта

```mermaid
sequenceDiagram
    actor CO as Консультант
    participant B as Браузер
    participant CC as ConsultantController
    participant DB as MySQL
    participant PW as PHPWord
    participant FS as storage/reports/

    CO->>B: /consultant/requests
    B->>CC: GET ?page=consultant_requests
    CC->>DB: SELECT * FROM v_client_requests<br/>WHERE consultant_id=? (prepared)
    DB-->>CC: мои заявки
    CC-->>B: список

    CO->>B: открыть #N → "В работу"
    B->>CC: POST ?page=consultant_change_status<br/>{request_id, status='in_progress'}
    CC->>DB: CALL sp_change_status(N, 'in_progress', 'Начал работу')
    Note right of DB: процедура также вставит<br/>запись в consultations<br/>(audit-trail)

    CO->>B: добавить консультацию
    B->>CC: POST ?page=consultant_add_consultation<br/>{request_id, held_at, duration_min, notes}
    CC->>DB: INSERT INTO consultations (prepared)

    Note over CO,FS: Завершение работы

    CO->>B: «Создать отчёт» → форма
    B->>CC: POST ?page=consultant_create_report<br/>{request_id, content}
    CC->>DB: INSERT INTO reports(request_id, consultant_id, content, file_path=NULL)

    CC->>PW: Word\PhpWord + шаблон
    Note right of PW: использует паттерн из<br/>Voucher/pages/save_file.php
    PW->>DB: SELECT fn_request_total_cost(N)
    DB-->>PW: 130 000.00
    PW->>FS: saveAs(report_N_2026-04-26.docx)

    CC->>DB: UPDATE reports SET file_path=? WHERE request_id=?
    CC->>DB: CALL sp_change_status(N, 'review', 'Отчёт готов')

    CC-->>B: 302 → /consultant/request/N
```

Клиент потом скачивает отчёт:

```mermaid
sequenceDiagram
    actor C as Клиент
    participant B as Браузер
    participant CL as ClientController
    participant FS as storage/reports/

    C->>B: /client/reports/N
    B->>CL: GET ?page=client_report_download&id=N
    CL->>CL: requireRole('client') +<br/>проверка что отчёт его
    CL->>FS: read file_path
    FS-->>B: DOCX (Content-Disposition: attachment)
    B-->>C: скачивание
```

---

## 10. Sequence: Массовое удаление с чекбоксами

```mermaid
sequenceDiagram
    actor A as Админ
    participant B as Браузер
    participant AC as Admin/RequestsController
    participant DB as MySQL

    A->>B: /admin/requests
    B->>AC: GET список
    AC->>DB: SELECT * FROM v_admin_requests_full
    AC-->>B: таблица<br/>(каждая строка — checkbox name="ids[]")

    A->>B: отметил 3 заявки → "Удалить выбранное"
    B->>AC: POST ?page=admin_requests_bulk_delete<br/>{ids: [5, 9, 12]}
    AC->>AC: requireRole('admin') + CSRF
    AC->>AC: ids = array_map('intval', $_POST['ids'])

    AC->>DB: CALL sp_bulk_delete_requests('5,9,12')
    Note right of DB: PREPARE stmt FROM<br/>'DELETE FROM requests WHERE id IN (5,9,12)'<br/>EXECUTE — каскадно удалит<br/>consultations, request_services,<br/>reports, payments
    DB-->>AC: ok

    AC-->>B: 302 → /admin/requests<br/>flash: «Удалено 3 заявок»
    B-->>A: обновлённый список
```

---

## 11. Sequence: GD-диаграммы в админке

```mermaid
sequenceDiagram
    actor A as Админ
    participant B as Браузер
    participant FC as index.php
    participant CH as Admin/ChartsController
    participant DB as MySQL

    A->>B: /admin/dashboard
    B->>FC: GET admin_dashboard
    FC-->>B: HTML с тремя img-тегами<br/>type=monthly, categories, consultants
    Note over B,CH: Браузер делает 3 параллельных запроса PNG

    par запрос monthly
        B->>CH: GET admin_chart type=monthly
        CH->>DB: SELECT MONTH(created_at), COUNT(*)<br/>FROM requests GROUP BY 1
        DB-->>CH: 12 строк
        CH->>CH: imagecreatetruecolor 800x400<br/>imagefilledrectangle<br/>imagettftext
        CH->>CH: header Content-Type image/png<br/>imagepng
        CH-->>B: PNG гистограмма по месяцам
    and запрос categories
        B->>CH: GET admin_chart type=categories
        CH->>DB: SELECT * FROM v_revenue_by_category
        CH->>CH: imagefilledarc x N
        CH-->>B: PNG круговая
    and запрос consultants
        B->>CH: GET admin_chart type=consultants
        CH->>DB: SELECT u.full_name,<br/>fn_consultant_revenue<br/>FROM consultants ...
        CH->>CH: горизонтальные столбцы
        CH-->>B: PNG доход
    end

    B-->>A: дашборд с 3 картинками
```

---

## 12. Карта ролей и разделов сайта

```mermaid
flowchart LR
    subgraph Roles["Роли пользователей"]
        direction TB
        GUEST(["👤 Гость"])
        CLIENT(["🧑‍💼 Клиент"])
        CONSULT(["👨‍💻 Консультант"])
        ADMIN(["👔 Директор"])
    end

    subgraph Public["🌐 Публичная часть"]
        HOME["Главная"]
        SERVICES["Услуги"]
        ABOUT["О компании"]
        LOGIN["Логин"]
        REGISTER["Регистрация"]
    end

    subgraph ClientArea["💼 Кабинет клиента"]
        C_DASH["Дашборд"]
        C_NEW["Создать заявку<br/>все типы полей"]
        C_LIST["Мои заявки"]
        C_REPORT["Скачать DOCX"]
    end

    subgraph ConsultArea["📋 Кабинет консультанта"]
        CO_DASH["Дашборд"]
        CO_LIST["Мои заявки"]
        CO_CONS["Добавить консультацию"]
        CO_STATUS["Сменить статус"]
        CO_REPORT["Создать отчёт + DOCX"]
    end

    subgraph AdminArea["⚙️ Админ-панель"]
        A_DASH["Дашборд + GD-диаграммы"]
        A_USERS["CRUD users + bulk-delete"]
        A_CLIENTS["CRUD clients"]
        A_CONSULTS["CRUD consultants"]
        A_SVC["CRUD services"]
        A_REF["CRUD справочники"]
        A_REQ["CRUD requests<br/>+ назначить + bulk-delete"]
    end

    GUEST --> Public
    CLIENT --> Public
    CLIENT --> ClientArea
    CONSULT --> Public
    CONSULT --> ConsultArea
    ADMIN --> Public
    ADMIN --> ClientArea
    ADMIN --> ConsultArea
    ADMIN --> AdminArea
```

---

## Связь со схемой БД

| Sequence-сценарий | Какие таблицы / VIEW / процедуры задействованы |
|---|---|
| §6 Логин с кукой | `users` (SELECT prepared), session, cookie `auth_remember` |
| §7 Создание заявки | `requests` INSERT, `request_services` INSERT (M:N), транзакция |
| §8 Назначение консультанта | `sp_assign_consultant` → `requests` UPDATE; чтение `v_consultant_workload` |
| §9 Работа консультанта | `sp_change_status`, `consultations` INSERT, `reports` INSERT, `fn_request_total_cost`, PHPWord → DOCX |
| §10 Массовое удаление | `sp_bulk_delete_requests` → каскад на consultations/request_services/reports/payments |
| §11 GD-диаграммы | `v_revenue_by_category`, `fn_consultant_revenue`, `requests` GROUP BY |

---

