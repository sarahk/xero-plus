<?php

namespace App\Models;


// S E A R C H   O N L Y
class ComboModel extends BaseModel
{
    protected string $table = 'mcombo';
    protected bool $view = true;
    protected int $orderByDefault = 6;
    protected string $orderByDefaultDirection = 'DESC';

    protected array $orderByColumns = [
        0 => "mcombo.invoice_number DIR",
        2 => "mcombo.reference DIR",
        3 => "contacts.last_name DIR, contacts.first_name ASC",
        4 => "mcombo.amount DIR",
        5 => "mcombo.amount_due DIR",
        6 => "mcombo.date DIR",
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
        $tenancy_list = $this->getTenancyList();

        $search_values = [];

        $tenancies = $this->getTenanciesWhere($params);


        if (!empty($params['contact_id'])) {
            // added to tenancies because we need it to run on the total and filter count queries
            $tenancies .= " AND `contact_id` = :contact_id";
            $search_values['contact_id'] = $params['contact_id'];
        }


        if (!empty($params['contract_id'])) {
            $tenancies .= " AND `contract_id` = :contract_id";
            $search_values['contract_id'] = $params['contract_id'];
        }

        $order = $this->getOrderBy($params);

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "mcombo.reference LIKE :search",
                'contacts.name LIKE :search'
            ];
            $search_values['search'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }


        // todo what buttons
        if (!empty($params['button']) && $params['button'] !== 'read') {
            if ($params['button'] == 'overdue') {
                $search_values['overduedate'] = date('Y-m-d', strtotime('-7 days'));
                $conditions[] = "`invoices`.`due_date` <= :overduedate AND `invoices`.`amount_due` > 0";
            } else {
                $search_values['status'] = strtoupper($params['button']);
                $conditions[] = "`invoices`.`status` = :status";
            }
        } else {
            //todo
            //$conditions[] = "`invoices`.`status` = 'AUTHORISED'";  // VOIDED, PAID
        }


        $fields = [
            'row_type',
            'invoice_id',
            'contract_id',
            'status',
            'contacts.name',
            'invoice_number',
            'reference',
            'amount',
            'amount_due',
            'date',
            'due_date',
            'mcombo.xerotenant_id',
            "(SELECT SUM(v2.amount * IF(v2.row_type = 'I', 1, - 1))
                FROM
                    mcombo AS v2
                WHERE
                    v2.contract_id = mcombo.contract_id
                    AND v2.date <= mcombo.date) AS balance"
        ];

        // Jan 2025
        // changed to use the materialized table mcombo

        $sql = "SELECT " . implode(', ', $fields) . " 
            FROM `mcombo` 
            LEFT JOIN contacts on `mcombo`.`contact_id` = `contacts`.`contact_id`
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY $order, row_type DESC
            LIMIT {$params['start']}, {$params['length']}";


        $result = $this->runQuery($sql, $search_values);

        $output = $params;
        $output['mainquery'] = $this->cleanSql($sql);
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $records_total = "SELECT COUNT(*) FROM mcombo WHERE $tenancies";
        $records_filtered = "SELECT COUNT(*) 
                                FROM mcombo 
                                LEFT JOIN contacts on `mcombo`.`contact_id` = `contacts`.`contact_id`
                                WHERE " . implode(' AND ', $conditions);
        $output['recordsTotal'] = $this->runQuery($records_total, $search_values, 'column');
        $output['recordsFiltered'] = $this->runQuery($records_filtered, $search_values, 'column');

        if (count($result) > 0) {
            foreach ($result as $row) {

// todo change the links if it's payment
                $url = "/page.php?action=12&invoice_id={$row['invoice_id']}";
                $output['data'][] = [
                    'row_type' => "{$tenancy_list[$row['xerotenant_id']]['shortname']} <b>{$row['row_type']}</b>",
                    'number' => "<a href='$url'>{$row['invoice_number']}</a>",
                    'reference' => $row['reference'],
                    'name' => "<a href='$url'>{$row['name']}</a>",
                    'status' => "<a href='https://go.xero.com/AccountsReceivable/View.aspx?InvoiceID={$row['invoice_id']}' target='_blank'>{$row['status']}</a>",
                    'amount' => $row['amount'],
                    'invoice_amount' => ($row['row_type'] === 'I' ? $row['amount'] : '&nbsp;'),
                    'payment_amount' => ($row['row_type'] === 'P' ? $row['amount'] : '&nbsp;'),
                    'amount_due' => $row['amount_due'],
                    'balance' => $row['balance'],
                    'date' => $this->getPrettyDate($row['date']),
                    'due_date' => $this->getPrettyDate($row['due_date']),
                    'colour' => $tenancy_list[$row['xerotenant_id']]['colour'],
                    'activity' => $this->getActivityDescription($row),
                ];
// for debugging
                /* todo make these columns available
                 * {data: "date", name: 'date'},
                {data: "activity"},
                {data: "reference", name: 'reference'},
                {data: "due_date"},
                {data: "invoice_amount", name: 'amount'},
                {data: "payment_amount"},
                {data: "balance"},
                 */
                $output['row'] = $row;
            }

        }

        return $output;
    }

    protected function getActivityDescription(array $row): string
    {
        $url = '/page.php?action=12&invoice_id=' . $row['invoice_id'];
        $display = ($row['row_type'] === 'I' ? 'Invoice #' . $row['invoice_number'] : 'Payment on Invoice #' . $row['invoice_number']);
        return "<a href='$url' target='_blank'>$display</a>";
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
