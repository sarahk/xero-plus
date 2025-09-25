CREATE ALGORITHM = UNDEFINED DEFINER = `root`@`localhost` SQL SECURITY DEFINER VIEW `vtaskcounts` AS
SELECT `t`.`xerotenant_id` AS `xerotenant_id`,
       `t`.`cabin_id`      AS `cabin_id`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'active')
                        AND (CAST(GREATEST(IFNULL(`t`.`due_date`, '1000-01-01'),
                                           IFNULL(`t`.`scheduled_date`, '1000-01-01'))
                                 AS DATE) < CURDATE()))
                    THEN
                    1
                ELSE 0
           END))           AS `overdue`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'active')
                        AND (((`t`.`due_date` IS NOT NULL)
                            AND (`t`.`due_date` BETWEEN CURDATE() AND `d`.`friday_next_week`))
                            OR ((`t`.`scheduled_date` IS NOT NULL)
                                AND (`t`.`scheduled_date` BETWEEN CURDATE() AND `d`.`friday_next_week`))))
                    THEN
                    1
                ELSE 0
           END))           AS `due`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'hold')
                        AND (((`t`.`due_date` IS NOT NULL)
                            AND (`t`.`due_date` BETWEEN CURDATE() AND `d`.`friday_next_week`))
                            OR ((`t`.`scheduled_date` IS NOT NULL)
                                AND (`t`.`scheduled_date` BETWEEN CURDATE() AND `d`.`friday_next_week`))))
                    THEN
                    1
                ELSE 0
           END))           AS `hold`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'done')
                        AND (((`t`.`due_date` IS NOT NULL)
                            AND (`t`.`due_date` BETWEEN CURDATE() AND `d`.`friday_next_week`))
                            OR ((`t`.`scheduled_date` IS NOT NULL)
                                AND (`t`.`scheduled_date` BETWEEN CURDATE() AND `d`.`friday_next_week`))))
                    THEN
                    1
                ELSE 0
           END))           AS `done`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'active')
                        AND (IFNULL(`t`.`due_date`, '1000-01-01') > `d`.`friday_next_week`)
                        AND (IFNULL(`t`.`scheduled_date`, '1000-01-01') > `d`.`friday_next_week`))
                    THEN
                    1
                ELSE 0
           END))           AS `future`
FROM (`tasks` `t`
    JOIN (SELECT (CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY)                     AS `monday_this_week`,
                 ((CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY) + INTERVAL 11 DAY) AS `friday_next_week`) `d`)
GROUP BY `t`.`xerotenant_id`, `t`.`cabin_id`