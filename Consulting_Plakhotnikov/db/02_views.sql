-- =====================================================================
-- Курсовой проект: Web-приложение «Консалтинговая компания»
-- Файл: 02_views.sql — представления
-- =====================================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP VIEW IF EXISTS v_client_requests;
DROP VIEW IF EXISTS v_consultant_workload;
DROP VIEW IF EXISTS v_revenue_by_category;
DROP VIEW IF EXISTS v_admin_requests_full;

-- ---------------------------------------------------------------------
-- 1. Заявки с раскрытыми справочниками
-- Используется в кабинете клиента и в админке для отображения списков.
-- ---------------------------------------------------------------------
CREATE VIEW v_client_requests AS
SELECT
    r.id,
    r.title,
    r.description,
    r.priority,
    r.deadline,
    r.created_at,
    r.updated_at,
    c.id            AS client_id,
    u_cl.full_name  AS client_name,
    c.company       AS client_company,
    s.id            AS service_id,
    s.name          AS service_name,
    s.price         AS service_price,
    sc.id           AS category_id,
    sc.name         AS category_name,
    co.id           AS consultant_id,
    u_co.full_name  AS consultant_name,
    rs.id           AS status_id,
    rs.code         AS status_code,
    rs.name         AS status_name
FROM requests r
JOIN clients c              ON c.id  = r.client_id
JOIN users   u_cl           ON u_cl.id = c.user_id
JOIN services s             ON s.id  = r.service_id
JOIN service_categories sc  ON sc.id = s.category_id
JOIN request_statuses rs    ON rs.id = r.status_id
LEFT JOIN consultants co    ON co.id = r.consultant_id
LEFT JOIN users u_co        ON u_co.id = co.user_id;

-- ---------------------------------------------------------------------
-- 2. Загрузка консультантов: всего / активные / завершённые заявки
-- Источник для дашборда админа.
-- ---------------------------------------------------------------------
CREATE VIEW v_consultant_workload AS
SELECT
    co.id                                         AS consultant_id,
    u.full_name                                   AS consultant_name,
    sp.name                                       AS specialization,
    COUNT(r.id)                                   AS total_requests,
    SUM(CASE WHEN rs.code IN ('assigned','in_progress','review') THEN 1 ELSE 0 END) AS active_requests,
    SUM(CASE WHEN rs.code = 'completed' THEN 1 ELSE 0 END)                          AS completed_requests
FROM consultants co
JOIN users u                  ON u.id  = co.user_id
LEFT JOIN specializations sp  ON sp.id = co.specialization_id
LEFT JOIN requests r          ON r.consultant_id = co.id
LEFT JOIN request_statuses rs ON rs.id = r.status_id
GROUP BY co.id, u.full_name, sp.name;

-- ---------------------------------------------------------------------
-- 3. Доход по категориям услуг (для круговой диаграммы в админке)
-- ---------------------------------------------------------------------
CREATE VIEW v_revenue_by_category AS
SELECT
    sc.id                       AS category_id,
    sc.name                     AS category,
    COUNT(DISTINCT r.id)        AS requests_count,
    COALESCE(SUM(p.amount), 0)  AS total_revenue
FROM service_categories sc
LEFT JOIN services s   ON s.category_id = sc.id
LEFT JOIN requests r   ON r.service_id  = s.id
LEFT JOIN payments p   ON p.request_id  = r.id
GROUP BY sc.id, sc.name;

-- ---------------------------------------------------------------------
-- 4. Полный срез заявок для админ-списка (с подсчётом консультаций
--    и наличием отчёта). Используется на странице /admin/requests.
-- ---------------------------------------------------------------------
CREATE VIEW v_admin_requests_full AS
SELECT
    r.id,
    r.title,
    r.priority,
    r.deadline,
    r.created_at,
    r.updated_at,
    c.id            AS client_id,
    c.company       AS client_company,
    ind.name        AS client_industry,
    s.name          AS service,
    sc.name         AS category,
    s.price         AS service_price,
    co.id           AS consultant_id,
    u_co.full_name  AS consultant_name,
    rs.code         AS status_code,
    rs.name         AS status,
    (SELECT COUNT(*) FROM consultations cs WHERE cs.request_id = r.id) AS consultations_count,
    (SELECT COUNT(*) FROM reports rp       WHERE rp.request_id = r.id) AS has_report
FROM requests r
JOIN clients c              ON c.id  = r.client_id
LEFT JOIN industries ind    ON ind.id = c.industry_id
JOIN services s             ON s.id  = r.service_id
JOIN service_categories sc  ON sc.id = s.category_id
JOIN request_statuses rs    ON rs.id = r.status_id
LEFT JOIN consultants co    ON co.id = r.consultant_id
LEFT JOIN users u_co        ON u_co.id = co.user_id;
