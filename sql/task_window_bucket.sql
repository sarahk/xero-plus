DELIMITER //

CREATE FUNCTION task_window_bucket(p_due DATE, p_sched DATE)
    RETURNS VARCHAR(8)
    DETERMINISTIC
BEGIN
    DECLARE v_today DATE DEFAULT CURDATE();
    DECLARE v_monday DATE DEFAULT v_today - INTERVAL WEEKDAY(v_today) DAY;
    DECLARE v_friday_next DATE DEFAULT v_monday + INTERVAL 11 DAY;

    -- If both missing, no bucket
    IF p_due IS NULL AND p_sched IS NULL THEN
        RETURN "due";
    END IF;

    -- OVERDUE: later of the two is before Monday this week
    IF COALESCE(GREATEST(IFNULL(p_due, '1000-01-01'), IFNULL(p_sched, '1000-01-01')), '1000-01-01') < v_monday THEN
        RETURN 'overdue';
    END IF;

    -- DUE: either date falls in the window [Mon this week .. Fri next week]
    IF (p_due IS NOT NULL AND p_due BETWEEN v_monday AND v_friday_next)
        OR
       (p_sched IS NOT NULL AND p_sched BETWEEN v_monday AND v_friday_next)
    THEN
        RETURN 'due';
    END IF;

    -- FUTURE: both dates are after Friday next week (mirror of your current view logic)
    IF IFNULL(p_due, '1000-01-01') > v_friday_next
        AND IFNULL(p_sched, '1000-01-01') > v_friday_next
    THEN
        RETURN 'future';
    END IF;

    RETURN "due"; -- straddlers / edge cases need to be reviewed and updated.
END//

DELIMITER ;
