DROP VIEW IF EXISTS `vbdmgmt`;

CREATE VIEW `vbdmgmt` AS
SELECT contracts.contract_id,
       contracts.schedule_unit,
       contracts.xerotenant_id,
       contracts.repeating_invoice_id,
       contracts.ckcontact_id,
       SUM(ISRECENTANDUNPAID(contracts.contract_id,
                             contracts.schedule_unit,
                             invoices.invoice_id,
                             invoices.date,
                             invoices.amount_paid)) AS is_unpaid,
       SUM(invoices.total)                          AS total,
       SUM(invoices.amount_due)                     AS amount_due,
       SUM(invoices.amount_paid)                    AS amount_paid,
       COUNT(invoices.invoice_id)                   AS total_weeks,
       SUM(IF(invoices.amount_due > 0, 1, 0))       AS weeks_due
FROM xeroplus.contracts
         LEFT JOIN
     xeroplus.invoices ON contracts.contract_id = invoices.contract_id
GROUP BY contracts.contract_id
