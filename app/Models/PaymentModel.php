<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class PaymentModel extends BaseModel
{
    protected string $table = 'payments';
    protected string $primaryKey = 'payment_id';
    protected array $hasMany = [];

    protected array $nullable = [];
    protected array $saveKeys = ['payment_id', 'invoice_id', 'contract_id', 'date', 'status', 'amount', 'reference',
        'is_reconciled', 'updated', 'is_batch', 'updated_date_utc', 'payment_type', 'xerotenant_id', 'contact_id'];

    protected array $updateKeys = ['updated', 'is_reconciled', 'status'];

    protected string $orderByDefaultDirection = 'DESC';
    protected array $orderByColumns = [0 => 'payments.date DIR'];
    protected int $orderByDefault = 0;


    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }

    public function getContractId($invoice_id): int
    {
        $sql = 'SELECT contract_id FROM invoices where invoice_id = :invoice_id LIMIT 1';
        $result = $this->runQuery($sql, ['invoice_id' => $invoice_id], 'column');

        if ($result) return $result;
        else return 0;
    }

    public function repairContractId(): void
    {
        $sql = "UPDATE payments
                LEFT JOIN invoices ON invoices.invoice_id = payments.invoice_id
                SET payments.contract_id = invoices.contract_id
                WHERE payments.invoice_id > ''
                  AND payments.contract_id IS NULL;";
        $this->runQuery($sql, []);
    }


    // expected to list for a single invoice
    public function list($params): array
    {

        $tenancy_list = $this->getTenancyList();

        $search_values = [];

        $tenancies = $this->getTenanciesWhere($params);

        // expect a value here
        if (!empty($params['invoice_id'])) {
            // added to tenancies because we need it to run on the total and filter count queries
            $tenancies .= ' AND `invoice_id` = :invoice_id';
            $search_values['invoice_id'] = $params['invoice_id'];
        }

        if (!empty($params['contract_id'])) {
            $tenancies .= ' AND `contract_id` = :contract_id';
            $search_values['contract_id'] = $params['contract_id'];
        }

        $order = $this->getOrderBy($params);

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "payments.reference LIKE :search",
                'contacts.name LIKE :search'
            ];
            $search_values['search'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }


        $fields = [
            'payments.date',
            'payments.status',
            'payments.reference',
            'payments.amount',
        ];


        $sql = 'SELECT ' . implode(', ', $fields) . ' 
            FROM `payments` 
            LEFT JOIN contacts on `payments`.`contact_id` = `contacts`.`contact_id`
            WHERE ' . implode(' AND ', $conditions) . "
            ORDER BY $order " . $this->getListLimits($params);


        $result = $this->runQuery($sql, $search_values);

        $output = $params;
        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $records_total = "SELECT COUNT(*) FROM payments WHERE $tenancies";
        $records_filtered = 'SELECT COUNT(*) 
                                FROM payments 
                                LEFT JOIN contacts on payments.contact_id = contacts.contact_id
                                WHERE ' . implode(' AND ', $conditions);
        $output['recordsTotal'] = $this->runQuery($records_total, $search_values, 'column');
        $output['recordsFiltered'] = $this->runQuery($records_filtered, $search_values, 'column');

        if (count($result) > 0) {
            foreach ($result as $row) {

                $output['data'][] = [
                    'reference' => $row['reference'],
                    'status' => $row['status'],
                    'amount' => $row['amount'],
                    'date' => $this->getPrettyDate($row['date']),
                ];
// for debugging
                $output['row'] = $row;
            }
        }
        return $output;
    }


}
