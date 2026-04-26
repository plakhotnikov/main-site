-- =====================================================================
-- Курсовой проект: Web-приложение «Консалтинговая компания»
-- Студент: Плахотников Владимир
-- Файл: 01_schema.sql — структура таблиц, FK, ON DELETE
-- СУБД: MySQL 8.0, движок InnoDB, кодировка utf8mb4_unicode_ci
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Удаление в обратном порядке зависимостей (для повторного применения)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS request_services;
DROP TABLE IF EXISTS consultations;
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS consultants;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS request_statuses;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS specializations;
DROP TABLE IF EXISTS industries;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- I. СПРАВОЧНЫЕ ТАБЛИЦЫ (5 шт. — требование ТЗ)
-- =====================================================================

-- 1. Роли пользователей
CREATE TABLE roles (
    id      TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code    VARCHAR(20)  NOT NULL UNIQUE,            -- guest / client / consultant / admin
    name    VARCHAR(50)  NOT NULL                    -- 'Гость', 'Клиент', 'Консультант', 'Директор'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Отрасли деятельности клиентов
CREATE TABLE industries (
    id      INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Специализации консультантов
CREATE TABLE specializations (
    id      INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Категории услуг (Финансовый / Юридический консалтинг)
CREATE TABLE service_categories (
    id      INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code    VARCHAR(20)  NOT NULL UNIQUE,            -- financial / legal
    name    VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Статусы заявок
CREATE TABLE request_statuses (
    id          TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code        VARCHAR(30) NOT NULL UNIQUE,         -- new / assigned / in_progress / review / completed / cancelled
    name        VARCHAR(50) NOT NULL,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- II. ПОЛЬЗОВАТЕЛИ
-- =====================================================================

-- 6. Общая таблица пользователей (логин/пароль/роль/контакты)
-- Пароли хранятся в открытом виде согласно требованию ТЗ (учебный проект).
CREATE TABLE users (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    login       VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(100) NOT NULL,
    role_id     TINYINT UNSIGNED NOT NULL,
    full_name   VARCHAR(150) NOT NULL,
    phone       VARCHAR(30),
    email       VARCHAR(100),
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Профиль клиента (1:1 к users)
CREATE TABLE clients (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL UNIQUE,
    company     VARCHAR(150) NOT NULL,
    inn         VARCHAR(12),
    industry_id INT UNSIGNED,
    address     VARCHAR(255),
    CONSTRAINT fk_clients_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_clients_industry
        FOREIGN KEY (industry_id) REFERENCES industries(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Профиль консультанта (1:1 к users)
CREATE TABLE consultants (
    id                INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id           INT UNSIGNED NOT NULL UNIQUE,
    position          VARCHAR(100),
    experience_years  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    specialization_id INT UNSIGNED,
    CONSTRAINT fk_consultants_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_consultants_spec
        FOREIGN KEY (specialization_id) REFERENCES specializations(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- III. БИЗНЕС-СУЩНОСТИ
-- =====================================================================

-- 9. Каталог услуг
CREATE TABLE services (
    id              INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id     INT UNSIGNED   NOT NULL,
    name            VARCHAR(150)   NOT NULL,
    description     TEXT,
    price           DECIMAL(10,2)  NOT NULL DEFAULT 0,
    duration_hours  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active       TINYINT(1)     NOT NULL DEFAULT 1,
    CONSTRAINT fk_services_category
        FOREIGN KEY (category_id) REFERENCES service_categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Заявки клиентов (центральная сущность)
CREATE TABLE requests (
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    client_id     INT UNSIGNED NOT NULL,
    service_id    INT UNSIGNED NOT NULL,
    consultant_id INT UNSIGNED,
    status_id     TINYINT UNSIGNED NOT NULL,
    title         VARCHAR(200) NOT NULL,
    description   TEXT         NOT NULL,
    priority      ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
    deadline      DATE,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Удалили клиента → заявки и всё дочернее каскадно удаляются (демо ТЗ)
    CONSTRAINT fk_req_client     FOREIGN KEY (client_id)     REFERENCES clients(id)          ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT fk_req_service    FOREIGN KEY (service_id)    REFERENCES services(id)         ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_req_consultant FOREIGN KEY (consultant_id) REFERENCES consultants(id)      ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_req_status     FOREIGN KEY (status_id)     REFERENCES request_statuses(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Консультации (события по заявке: встречи/созвоны)
CREATE TABLE consultations (
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    request_id    INT UNSIGNED NOT NULL,
    held_at       DATETIME      NOT NULL,
    duration_min  SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    notes         TEXT,
    CONSTRAINT fk_cons_request
        FOREIGN KEY (request_id) REFERENCES requests(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. M:N — дополнительные услуги в заявке (для чекбоксов на форме)
CREATE TABLE request_services (
    request_id INT UNSIGNED NOT NULL,
    service_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (request_id, service_id),
    CONSTRAINT fk_rs_request FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_rs_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Итоговые отчёты по заявкам (1 заявка ↔ 1 отчёт)
CREATE TABLE reports (
    id            INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    request_id    INT UNSIGNED NOT NULL UNIQUE,
    consultant_id INT UNSIGNED,
    content       MEDIUMTEXT  NOT NULL,
    file_path     VARCHAR(255),
    created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rep_request    FOREIGN KEY (request_id)    REFERENCES requests(id)    ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT fk_rep_consultant FOREIGN KEY (consultant_id) REFERENCES consultants(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Платежи по заявкам
CREATE TABLE payments (
    id          INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    request_id  INT UNSIGNED   NOT NULL,
    amount      DECIMAL(10,2)  NOT NULL,
    paid_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    method      ENUM('cash','card','transfer') NOT NULL DEFAULT 'transfer',
    CONSTRAINT fk_pay_request
        FOREIGN KEY (request_id) REFERENCES requests(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Индексы для ускорения частых выборок
-- =====================================================================
CREATE INDEX ix_requests_client     ON requests(client_id);
CREATE INDEX ix_requests_consultant ON requests(consultant_id);
CREATE INDEX ix_requests_status     ON requests(status_id);
CREATE INDEX ix_requests_created    ON requests(created_at);
CREATE INDEX ix_consultations_req   ON consultations(request_id);
CREATE INDEX ix_payments_req        ON payments(request_id);
CREATE INDEX ix_services_category   ON services(category_id);
