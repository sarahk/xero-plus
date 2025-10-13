<?php

namespace App\Models\Query;

use App\Classes\XeroUrl;
use App\Models\Enums\ComboStatus;
use App\Classes\Utilities;

/**
 * S E A R C H   O N L Y
 */
class ComboQueryModel extends BaseQueryModel
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

    public function __construct()
    {
        parent::__construct();
        $this->addToParams('contact_id');
        $this->addToParams('contract_id', ['contract_id', 'id']);
    }

    private function getTotalDue($contract_id): string
    {
        $sql = "SELECT SUM(amount_due) as total_due 
                    FROM mcombo 
                    WHERE contract_id = :contract_id
                    AND row_type = 'I'";
        return $this->runQuery($sql, ['contract_id' => $contract_id], 'column');
    }

    public function list(): string
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

        $tenancies = $this->getTenanciesWhere($this->params);


        if (!empty($this->params['contact_id'])) {
            // added to tenancies because we need it to run on the total and filter count queries
            $tenancies .= " AND `contact_id` = :contact_id";
            $search_values['contact_id'] = $this->params['contact_id'];
        }


        if (!empty($this->params['contract_id'])) {
            $tenancies .= " AND `contract_id` = :contract_id";
            $search_values['contract_id'] = $this->params['contract_id'];
        }

        $order = $this->getOrderBy();

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "mcombo.reference LIKE :search",
                'contacts.name LIKE :search'
            ];
            $search_values['search'] = '%' . $this->params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }


        $secondFilter = '';
        // todo what buttons
        if (!empty($this->params['dataFilter'])) {
            switch ($this->params['dataFilter']) {
                case 'invoices':
                case 'payments':
                    $conditions[] = "`row_type` = :row_type";
                    $search_values['row_type'] = strtoupper(substr($this->params['dataFilter'], 0, 1));
                    $secondFilter = " AND `row_type` = :row_type";
                    break;
                case 'due':
                    $conditions[] = "amount_due > 0";
                    $secondFilter = " AND `row_type` = 'I'";
                    break;
                case 'overdue':
                    $conditions[] = "`due_date` <= :overduedate AND `amount_due` > 0";
                    $search_values['overduedate'] = date('Y-m-d', strtotime('-7 days'));
                    $secondFilter = " AND `row_type` = 'I'";
                    break;
            }
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
            LIMIT {$this->params['start']}, {$this->params['length']}";


        $result = $this->runQuery($sql, $search_values);

        $output = $this->params;
        $output['mainquery'] = $this->cleanSql($sql);
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $records_total = "SELECT COUNT(*) FROM mcombo WHERE $tenancies $secondFilter";
        $records_filtered = "SELECT COUNT(*) 
                                FROM mcombo 
                                LEFT JOIN contacts on `mcombo`.`contact_id` = `contacts`.`contact_id`
                                WHERE " . implode(' AND ', $conditions);
        $output['recordsTotal'] = $this->runQuery($records_total, $search_values, 'column');
        $output['recordsFiltered'] = $this->runQuery($records_filtered, $search_values, 'column');

        $xeroLinkMaker = new XeroUrl();
        if (count($result) > 0) {
            foreach ($result as $row) {

// todo change the links if it's payment
                $url = "/page.php?action=12&invoice_id={$row['invoice_id']}";
                $colour = $tenancy_list[$row['xerotenant_id']]['colour'];
                $status = strtolower($row['status']);
                if ($row['row_type'] === 'I') {
                    $xeroUrl = $xeroLinkMaker->viewInvoice($row['xerotenant_id'], $row['invoice_id']);
                    //$xeroUrl = "https://api.xero.com/api.xro/2.0/Invoices/" . $row['invoice_id'];
                    $xeroLink = $xeroLinkMaker->getIconLink($xeroUrl);
                } else {
                    $xeroLink = '';
                }

                $output['data'][] = [
                    'DT_RowId' => "row_{$row['invoice_id']}",
                    'DT_RowClass' => 'status-' . ComboStatus::getStatusByDate($row['row_type'], $row['date'], $row['due_date'], $row['amount_due']),
                    'row_type' => "{$tenancy_list[$row['xerotenant_id']]['shortname']} <b>{$row['row_type']}</b>",
                    'invoice_number' => "<a href='$url'>{$row['invoice_number']}</a> $xeroLink",
                    'reference' => $row['reference'],
                    'name_only' => $row['name'],
                    'name' => "<a href='$url'>{$row['name']}</a>",
                    'status' => "<a href='https://go.xero.com/AccountsReceivable/View.aspx?InvoiceID={$row['invoice_id']}' target='_blank'>{$row['status']}</a>",
                    'amount' => $row['amount'],
                    'invoice_amount' => ($row['row_type'] === 'I' ? $row['amount'] : '&nbsp;'),
                    'payment_amount' => ($row['row_type'] === 'P' ? $row['amount'] : '&nbsp;'),
                    'amount_due' => $row['amount_due'],
                    'balance' => $row['balance'],
                    'date' => $this->getPrettyDate($row['date']),
                    'due_date' => $this->getPrettyDate($row['due_date']),
                    'colour' => $colour,
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

        $output['total_due'] = $this->getTotalDue($this->params['contract_id']);

        return json_encode($output);
    }

    protected function getActivityDescription(array $row): string
    {
        $url = '/page.php?action=12&invoice_id=' . $row['invoice_id'];
        $display = ($row['row_type'] === 'I' ? 'Invoice #' . $row['invoice_number'] : 'Payment on Invoice #' . $row['invoice_number']);
        return "<a href='$url' target='_blank'>$display</a>";
    }

    /**
     * overrides the parent method
     * @param array $params
     * @return string
     */
    protected function getOrderBy(): string
    {
        $orderParams = $this->params['order'][0];
        $colName = $orderParams['name'] ?? '';
        $column = (int)$orderParams['column'];
        $direction = ($column >= 0 || !empty($colName)) ? $orderParams['dir'] : $this->orderByDefaultDirection;

        if (!empty($colName)) {
            $orderBy = match ($colName) {
                'date' => '`date`',
                'row_type' => '`row_type`',
                'invoice_number' => '`invoice_number`',
                'reference' => '`reference`',
                'name' => '`contacts`.`name`',
                'amount' => '`amount`',
                'amount_due' => '`amount_due`',
                'status' => '`status`',
                default => '',
            };

            if (!empty($orderBy)) {
                return $orderBy . ' ' . $direction;
            }
        }

        // the name didn't make sense? do we have a column number?
        if (array_key_exists($column, $this->orderByColumns)) {
            //$this->logInfo('OrderBy using the column', [str_replace('DIR', $direction, $this->orderByColumns[$orderParams['column']])]);
            return str_replace('DIR', $direction, $this->orderByColumns[$column]);
        }

        // nothing else indicated? use this
        //$this->logInfo('OrderBy default', [str_replace('DIR', 'DESC', $this->orderByColumns[$this->defaultOrderByColumn])]);
        return str_replace('DIR', 'DESC', $this->orderByColumns[$this->defaultOrderByColumn]);
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
