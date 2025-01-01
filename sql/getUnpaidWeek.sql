DELIMITER $$

CREATE
    DEFINER = `root`@`localhost` FUNCTION `getUnpaidWeek`(
    date_value DATE,
    amount_paid DECIMAL(10, 2),
    week_number INT
)
    RETURNS INT
    DETERMINISTIC
BEGIN
    DECLARE start_date DATE;
    DECLARE end_date DATE;
    DECLARE add_on INT;
    DECLARE today DATE;

    -- Store the current date for consistency
    SET today = CURDATE();

    -- Calculate the additional days offset based on the week_number parameter
    SET add_on = 7 * (week_number - 1);

    -- Calculate the start date (Saturday of the week ending on Friday)
    SET start_date = DATE_SUB(today, INTERVAL (WEEKDAY(today) + 9 + add_on) DAY);

    -- Calculate the end date (Friday of the week ending on Friday)
    SET end_date = DATE_SUB(today, INTERVAL (WEEKDAY(today) + 3 + add_on) DAY);

    -- Return 1 if the date falls in the range and amount_paid is 0, otherwise return 0
    RETURN IF((date_value BETWEEN start_date AND end_date) AND amount_paid = 0, 1, 0);
END$$

DELIMITER ;
