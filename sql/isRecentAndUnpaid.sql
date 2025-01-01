DELIMITER $$

CREATE FUNCTION xeroplus.isRecentAndUnpaid(
    v_contract_id INT,
    v_schedule_unit VARCHAR(25),
    v_invoice_id CHAR(36),
    v_invoice_date DATETIME,
    v_amount_paid FLOAT
)
    RETURNS TINYINT
    DETERMINISTIC
BEGIN
    DECLARE result TINYINT DEFAULT 0;
    DECLARE cutoff_date_too_new DATE;
    DECLARE cutoff_date_too_old DATE;

    -- If amount_paid > 0, return 0 immediately
    IF v_amount_paid > 0 THEN
        RETURN 0;
    END IF;

    SET cutoff_date_too_new = DATE_SUB(CURDATE(), INTERVAL (WEEKDAY(CURDATE()) + 3) DAY);
    SET cutoff_date_too_old = DATE_SUB(CURDATE(), INTERVAL 1 YEAR);


    IF v_invoice_date > cutoff_date_too_new or v_invoice_date < cutoff_date_too_old then
        RETURN 0;
    END IF;

    -- Logic for WEEKLY (limit 3)
    IF v_schedule_unit = 'WEEKLY' THEN
        SELECT EXISTS (SELECT 1
                       FROM (SELECT invoice_id
                             FROM invoices
                             WHERE contract_id = v_contract_id
                               and invoices.date < cutoff_date_too_new
                               and invoices.date > cutoff_date_too_old
                             ORDER BY date DESC
                             LIMIT 3) AS recent_invoices
                       WHERE recent_invoices.invoice_id = v_invoice_id)
        INTO result;

        -- Logic for FORTNIGHTLY (limit 2)
    ELSEIF v_schedule_unit = 'FORTNIGHTLY' THEN
        SELECT EXISTS (SELECT 1
                       FROM (SELECT invoice_id
                             FROM invoices
                             WHERE contract_id = v_contract_id
                               and invoices.date < cutoff_date_too_new
                               and invoices.date > cutoff_date_too_old
                             ORDER BY date DESC
                             LIMIT 2) AS recent_invoices
                       WHERE recent_invoices.invoice_id = v_invoice_id)
        INTO result;

        -- Logic for default (limit 1)
    ELSE
        SELECT EXISTS (SELECT 1
                       FROM (SELECT invoice_id
                             FROM invoices
                             WHERE contract_id = v_contract_id
                               and invoices.date < cutoff_date_too_new
                               and invoices.date > cutoff_date_too_old
                             ORDER BY date DESC
                             LIMIT 1) AS recent_invoices
                       WHERE recent_invoices.invoice_id = v_invoice_id)
        INTO result;
    END IF;

    RETURN result;
END$$

DELIMITER ;
