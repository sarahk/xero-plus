CREATE DEFINER=`root`@`localhost` FUNCTION `getUnpaidWeek`(date_value DATE, amount_paid DECIMAL, week INTEGER) RETURNS int(11)
BEGIN
 DECLARE start_date DATE;
    DECLARE end_date DATE;
    DECLARE add_on INTEGER;

    -- Calculate the additional days offset based on the week parameter
    SET add_on = 7 * (week - 1);

    -- Calculate the start date (Saturday of the week ending on Friday)
    SET start_date = DATE_SUB(CURDATE(), INTERVAL (WEEKDAY(CURDATE()) + 9 + add_on) DAY);

    -- Calculate the end date (Friday of the week ending on Friday)
    SET end_date = DATE_SUB(CURDATE(), INTERVAL (WEEKDAY(CURDATE()) + 3 + add_on) DAY);

    -- Return 1 if the date falls in the range and amount_paid is 0, otherwise return 0
    RETURN IF(date_value BETWEEN start_date AND end_date AND amount_paid = 0, 1, 0);
RETURN 1;
END