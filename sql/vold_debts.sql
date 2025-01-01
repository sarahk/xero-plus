CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `vold_debts` AS
    SELECT 
        `vinvoices`.`xerotenant_id` AS `xerotenant_id`,
        `vinvoices`.`contract_id` AS `contract_id`,
        `vinvoices`.`repeating_invoice_id` AS `repeating_invoice_id`,
        `vinvoices`.`contact_id` AS `contact_id`,
        `agg`.`total_amount` AS `total_amount`,
        `agg`.`amount_due` AS `amount_due`,
        `agg`.`total_weeks` AS `total_weeks`,
        `agg`.`unpaidweek1` AS `unpaidweek1`,
        `agg`.`unpaidweek2` AS `unpaidweek2`,
        `agg`.`unpaidweek3` AS `unpaidweek3`,
        `agg`.`oldest` AS `oldest`,
        `agg`.`newest` AS `newest`,
        `agg`.`weeks_due` AS `weeks_due`
    FROM
        (`xeroplus`.`vinvoices`
        LEFT JOIN (SELECT 
            `vinvoices`.`repeating_invoice_id` AS `repeating_invoice_id`,
                SUM(`vinvoices`.`total`) AS `total_amount`,
                SUM(`vinvoices`.`amount_due`) AS `amount_due`,
                COUNT(`vinvoices`.`invoice_id`) AS `total_weeks`,
                SUM(GETUNPAIDWEEK(`vinvoices`.`date`, `vinvoices`.`amount_paid`, 1)) AS `unpaidweek1`,
                SUM(GETUNPAIDWEEK(`vinvoices`.`date`, `vinvoices`.`amount_paid`, 2)) AS `unpaidweek2`,
                SUM(GETUNPAIDWEEK(`vinvoices`.`date`, `vinvoices`.`amount_paid`, 3)) AS `unpaidweek3`,
                MIN(`vinvoices`.`date`) AS `oldest`,
                MAX(`vinvoices`.`date`) AS `newest`,
                COUNT((CASE
                    WHEN (`vinvoices`.`amount_due` > 0) THEN 1
                END)) AS `weeks_due`
        FROM
            `xeroplus`.`vinvoices`
        GROUP BY `vinvoices`.`repeating_invoice_id`) `agg` ON ((`agg`.`repeating_invoice_id` = `vinvoices`.`repeating_invoice_id`)))
    GROUP BY `vinvoices`.`xerotenant_id` , `vinvoices`.`repeating_invoice_id` , `vinvoices`.`contact_id`