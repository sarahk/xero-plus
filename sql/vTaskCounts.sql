CREATE ALGORITHM = UNDEFINED DEFINER = `root`@`localhost` SQL SECURITY DEFINER VIEW `vtaskcounts` AS
SELECT `t`.`xerotenant_id` AS `xerotenant_id`,
       `t`.`cabin_id`      AS `cabin_id`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'active')
                        AND (TASK_WINDOW_BUCKET(`t`.`due_date`, `t`.`scheduled_date`) = 'overdue'))
                    THEN
                    1
                ELSE 0
           END))           AS `overdue`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'active')
                        AND (TASK_WINDOW_BUCKET(`t`.`due_date`, `t`.`scheduled_date`) = 'due'))
                    THEN
                    1
                ELSE 0
           END))           AS `due`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'hold')
                        AND (TASK_WINDOW_BUCKET(`t`.`due_date`, `t`.`scheduled_date`) = 'due'))
                    THEN
                    1
                ELSE 0
           END))           AS `hold`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'done')
                        AND (TASK_WINDOW_BUCKET(`t`.`due_date`, `t`.`scheduled_date`) = 'due'))
                    THEN
                    1
                ELSE 0
           END))           AS `done`,
       SUM((CASE
                WHEN
                    ((`t`.`status` = 'active')
                        AND (TASK_WINDOW_BUCKET(`t`.`due_date`, `t`.`scheduled_date`) = 'future'))
                    THEN
                    1
                ELSE 0
           END))           AS `future`
FROM `tasks` `t`
GROUP BY `t`.`xerotenant_id`, `t`.`cabin_id`