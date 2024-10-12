<?php

namespace App\Models;


// S E A R C H   O N L Y
class ComboModel extends BaseModel
{
    protected string $table = 'vcombo';
    protected bool $view = true;
    protected int $orderByDefault = 3;
    protected string $orderByDefaultDirection = 'DESC';

    protected array $orderByColumns = [
        0 => "vcombo.invoice_number DIR",
        2 => "vcombo.reference DIR",
        3 => "vcombo.amount DIR",
        4 => "vcombo.amount_due DIR",
        6 => "vcombo.date DIR",
    ];

    public function list($params): array
    {
        /*
        <th>#</th>
        <th>Contact</th>
        <th>Ref</th>
        <th>Total</th>
        <th>Due</th>
        <th>Date</th>
*/

        $tenancy = new TenancyModel($this->pdo);
        $raw = $tenancy->list();
        $tenancyList = array_column($raw, null, 'tenant_id');

        $searchValues = [];

        $tenancies = $this->getTenanciesWhere($params);

        $contact_id = $_GET['contact_id'] ?? '';
        if (!empty($contact_id)) {
            // added to tenancies because we need it to run on the total and filter count queries
            $tenancies .= " AND `contact_id` = '$contact_id'";
        }


        $order = $this->getOrderBy($params);

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "combo.reference LIKE :search1",
                "`combo`.`invoice_number` LIKE :search2"
            ];
            //$searchValues['search'] = '%' . $params['search'] . '%';
            $searchValues['search1'] = '%' . $params['search'] . '%';
            $searchValues['search2'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }


        // todo what buttons
        if (!empty($params['button']) && $params['button'] !== 'read') {
            if ($params['button'] == 'overdue') {
                $searchValues['overduedate'] = date('Y-m-d', strtotime('-7 days'));
                $conditions[] = "`invoices`.`due_date` <= :overduedate AND `invoices`.`amount_due` > 0";

            } else {
                $searchValues['status'] = strtoupper($params['button']);
                $conditions[] = "`invoices`.`status` = :status";
            }
        } else {
            //todo
            //$conditions[] = "`invoices`.`status` = 'AUTHORISED'";  // VOIDED, PAID
        }


        $fields = [
            '`row_type`',
            '`xero_id`',
            '`status`',
            '`invoice_number`',
            '`reference`',
            '`amount`',
            '`amount_due`',
            '`date`',
            '`xerotenant_id`'
        ];


        $sql = "SELECT " . implode(', ', $fields) . " FROM `vcombo` 
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY $order 
            LIMIT {$params['start']}, {$params['length']}";


        $result = $this->runQuery($sql, $searchValues);

        $output = $params;
        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $searchValues;
        // adds in tenancies because it doesn't use $conditions
        
        $output['recordsTotal'] = $this->getRecordsTotal($tenancies);
        $output['recordsFiltered'] = $this->getRecordsFiltered($conditions, $searchValues);

        if (count($result) > 0) {
            foreach ($result as $row) {
// todo change the links if it's payment
                $output['data'][] = [
                    'row_type' => "{$tenancyList[$row['xerotenant_id']]['shortname']} <b>{$row['row_type']}</b>",
                    'number' => "<a href='/authorizedResource.php?action=12&invoice_id={$row['xero_id']}'>{$row['invoice_number']}</a>",
                    'reference' => $row['reference'],
                    'status' => "<a href='https://go.xero.com/AccountsReceivable/View.aspx?InvoiceID={$row['xero_id']}' target='_blank'>{$row['status']}</a>",
                    'amount' => $row['amount'],
                    'amount_due' => $row['amount_due'],
                    'date' => date('d F Y', strtotime($row['date'])),
                    'colour' => $tenancyList[$row['xerotenant_id']]['colour']
                ];
// for debugging
                $output['row'] = $row;
            }

        }

        return $output;
    }
}


/*
 *
 *
 * CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vcombo`
AS SELECT
   'I' AS `row_type`,
   `invoices`.`invoice_id` AS `xero_id`,
   `invoices`.`status` AS `status`,
   `invoices`.`invoice_number` AS `invoice_number`,
   `invoices`.`reference` AS `reference`,
   `invoices`.`total` AS `amount`,
   `invoices`.`amount_due` AS `amount_due`,
   `invoices`.`date` AS `date`,
   `invoices`.`contact_id` AS `contact_id`
FROM `invoices`
union select 'P' AS `row_type`,`payments`.`payment_id` AS `xero_id`,`payments`.`status` AS `status`,'' AS `invoice_number`,`payments`.`reference` AS `reference`,`payments`.`amount` AS `amount`,0 AS `amount_due`,`payments`.`date` AS `date`,`payments`.`contact_id` AS `contact_id` from `payments`;
 */
