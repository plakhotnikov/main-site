-- =====================================================================
-- Курсовой проект: Web-приложение «Консалтинговая компания»
-- Файл: 03_routines.sql — хранимые процедуры и функции
-- =====================================================================

-- Явная кодировка соединения. Без этого процедуры запоминают
-- character_set_client клиента (часто latin1), что приводит к ошибкам
-- "Illegal mix of collations" при работе с utf8mb4-таблицами.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS sp_assign_consultant;
DROP PROCEDURE IF EXISTS sp_change_status;
DROP PROCEDURE IF EXISTS sp_bulk_delete_requests;
DROP FUNCTION  IF EXISTS fn_request_total_cost;
DROP FUNCTION  IF EXISTS fn_consultant_revenue;

DELIMITER $$

-- ---------------------------------------------------------------------
-- 1. Назначение консультанта на заявку с переводом в статус "assigned".
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_assign_consultant(
    IN p_request_id    INT UNSIGNED,
    IN p_consultant_id INT UNSIGNED
)
BEGIN
    DECLARE v_status_id TINYINT UNSIGNED;
    SELECT id INTO v_status_id FROM request_statuses
     WHERE code = 'assigned' COLLATE utf8mb4_unicode_ci LIMIT 1;

    UPDATE requests
       SET consultant_id = p_consultant_id,
           status_id     = v_status_id
     WHERE id = p_request_id;
END $$

-- ---------------------------------------------------------------------
-- 2. Смена статуса заявки. Если передан комментарий — пишется
--    запись в журнал консультаций (audit-trail).
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_change_status(
    IN p_request_id  INT UNSIGNED,
    IN p_status_code VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN p_comment     TEXT        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
)
BEGIN
    DECLARE v_status_id TINYINT UNSIGNED;
    SELECT id INTO v_status_id FROM request_statuses
     WHERE code = p_status_code COLLATE utf8mb4_unicode_ci LIMIT 1;

    UPDATE requests SET status_id = v_status_id WHERE id = p_request_id;

    IF p_comment IS NOT NULL AND CHAR_LENGTH(p_comment) > 0 THEN
        INSERT INTO consultations(request_id, held_at, duration_min, notes)
        VALUES (p_request_id, NOW(), 0,
                CONCAT('[Смена статуса → ', p_status_code, '] ', p_comment));
    END IF;
END $$

-- ---------------------------------------------------------------------
-- 3. Массовое удаление заявок. p_ids — строка вида '1,5,9,12'.
--    Каскадно удалит consultations / request_services / reports / payments.
--    Используется для кнопки "Удалить выбранное" с чекбоксами.
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_bulk_delete_requests(
    IN p_ids TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
)
BEGIN
    SET @sql = CONCAT('DELETE FROM requests WHERE id IN (', p_ids, ')');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END $$

-- ---------------------------------------------------------------------
-- 4. Итоговая стоимость заявки = цена основной услуги + Σ доп. услуг.
-- ---------------------------------------------------------------------
CREATE FUNCTION fn_request_total_cost(p_request_id INT UNSIGNED)
RETURNS DECIMAL(10,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_main  DECIMAL(10,2) DEFAULT 0;
    DECLARE v_extra DECIMAL(10,2) DEFAULT 0;

    SELECT s.price INTO v_main
      FROM requests r
      JOIN services s ON s.id = r.service_id
     WHERE r.id = p_request_id;

    SELECT COALESCE(SUM(s.price), 0) INTO v_extra
      FROM request_services rs
      JOIN services s ON s.id = rs.service_id
     WHERE rs.request_id = p_request_id;

    RETURN COALESCE(v_main, 0) + v_extra;
END $$

-- ---------------------------------------------------------------------
-- 5. Доход консультанта за конкретный месяц (для гистограммы).
-- ---------------------------------------------------------------------
CREATE FUNCTION fn_consultant_revenue(
    p_consultant_id INT UNSIGNED,
    p_year          INT,
    p_month         INT
)
RETURNS DECIMAL(10,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(10,2);

    SELECT COALESCE(SUM(p.amount), 0) INTO v_total
      FROM payments p
      JOIN requests r ON r.id = p.request_id
     WHERE r.consultant_id = p_consultant_id
       AND YEAR(p.paid_at)  = p_year
       AND MONTH(p.paid_at) = p_month;

    RETURN v_total;
END $$

DELIMITER ;
