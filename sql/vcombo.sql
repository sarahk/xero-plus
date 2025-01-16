DROP VIEW IF EXISTS `vcombo`;

CREATE ALGORITHM = UNDEFINED DEFINER = `root`@`localhost` SQL SECURITY DEFINER VIEW `vcombo` AS
SELECT 'I'                         AS `row_type`,
       `invoices`.`invoice_id`     AS `invoice_id`,
       null                        AS `payment_id`,
       `invoices`.`status`         AS `status`,
       `invoices`.`invoice_number` AS `invoice_number`,
       `invoices`.`contract_id`    AS `contract_id`,
       `invoices`.`reference`      AS `reference`,
       `invoices`.`total`          AS `amount`,
       `invoices`.`amount_due`     AS `amount_due`,
       `invoices`.`date`           AS `date`,
       invoices.due_date,
       `invoices`.`contact_id`     AS `contact_id`,
       `invoices`.`xerotenant_id`  AS `xerotenant_id`
FROM `invoices`
UNION
SELECT 'P'                        AS `row_type`,
       `payments`.`invoice_id`    AS `invoice_id`,
       `payments`.`payment_id`    AS `payment_id`,
       `payments`.`status`        AS `status`,
       i2.invoice_number          AS `invoice_number`,
       `payments`.`contract_id`   AS `contract_id`,
       `payments`.`reference`     AS `reference`,
       `payments`.`amount`        AS `amount`,
       0                          AS `amount_due`,
       `payments`.`date`          AS `date`,
       null                       as due_date,
       `payments`.`contact_id`    AS `contact_id`,
       `payments`.`xerotenant_id` AS `xerotenant_id`
FROM `payments`
         left join invoices as i2 on payments.invoice_id = i2.invoice_id;
