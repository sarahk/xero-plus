<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class InvoiceModel extends BaseModel
{
    protected string $table = 'invoices';
    protected array $saveKeys = [
        'invoice_id', 'contact_id', 'contract_id', 'status',
        'repeating_invoice_id',
        'invoice_number', 'reference', 'total',
        'amount_due', 'amount_paid',
        'date', 'due_date', 'updated_date_utc', 'xerotenant_id'
    ];
    protected array $updateKeys = [
        'repeating_invoice_id', 'status', 'total', 'amount_due', 'amount_paid',
        'updated_date_utc', 'xerotenant_id'
    ];

    protected ContactModel $contacts;
    protected ContractModel $contracts;

    function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
        $this->contacts = new ContactModel($pdo);
        $this->contracts = new ContractModel($pdo);
    }

    // I N V O I C E
    // check we have a contract id and a ckcontact id
    // we WILL have a repeating invoice id
    // invoices are always imported from xero
    public function prepAndSave($data): int
    {

        if (!array_key_exists('contract_id', $data['invoice']) || !$data['invoice']['contract_id']) {
            //$contract_id = $this->contracts->getBestMatch($data['contact_id'], $data['updated_date_utc']);
            $contract = $this->contracts->get('repeating_invoice_id', $data['invoice']['repeating_invoice_id']);
            $data['invoice']['contract_id'] = $contract['contracts']['contract_id'];


            if (!array_key_exists('contact_id', $data['invoice'])) {
                $data['contract']['contact_id'] = $contract['contracts']['contact_id'];
            }
        }

        $checked = $this->checkNullableValues($data['invoice']);
        $save = $this->getSaveValues($checked);

        return $this->save($save);

        // todo return invoice id
        return 0;
    }


    public function list($params): array
    {

        $where = $statuses = null;
        /*
        <th>#</th>
        <th>Contact</th>
        <th>Ref</th>
        <th>Total</th>
        <th>Due</th>
        <th>Date</th>
*/

        $searchValues = [];

        $tenancies = '(';
        foreach ($params['tenancies'] as $k => $val) {
            if ($k > 0) {
                $tenancies .= ' OR ';
            }
            $tenancies .= "`invoices`.`xerotenant_id` = '{$val}'";
        }
        $tenancies .= ') ';

        if (is_array($params['order'])) {
            $direction = strtoupper($params['order'][0]['dir'] ?? 'DESC');

            switch ($params['order'][0]['column']) {
                case 0:
                    $order = "invoices.invoice_number {$direction}";
                    break;
                case 1:
                    $order = "contacts.last_name {$direction}, contacts.first_name ASC";
                    break;
                case 2:
                    $order = "invoices.reference {$direction}";
                    break;
                case 3:
                    $order = "invoices.total {$direction}";
                    break;
                case 4: // amount due
                    $order = "invoices.amount_due {$direction}";
                    break;
                case 5:
                default:
                    $order = "invoices.due_date {$direction}";
                    break;
            }
        } else {
            $order = "invoices.due_date DESC";
        }

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "`contacts`.`name` LIKE :search ",
                "`contacts`.`last_name` LIKE :search ",
                "`contacts`.`first_name` LIKE :search ",
                "`invoices`.`invoice_number` LIKE :search "
            ];
            $searchValues['search'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }

        if (!empty($params['button'])) {
            $searchValues['status'] = strtoupper($params['button']);
            $conditions[] = "`invoices`.`status` = :status";
        } else {
            //$conditions[] = "`invoices`.`status` = 'AUTHORISED'";
        }

        if (isset($_GET['repeating_invoice_id'])) {
            $conditions[] = "`invoices`.`repeating_invoice_id` = :repeating_invoice_id";
            $searchValues['repeating_invoice_id'] = $_GET['repeating_invoice_id'];
        }


        $fields = [
            'invoices.invoice_id',
            'invoices.status',
            'invoices.invoice_number',
            'invoices.reference',
            'invoices.total',
            'invoices.amount_paid',
            'invoices.amount_due',
            'invoices.due_date',
            'invoices.contact_id',
            'contacts.name'
        ];


        $sql = "SELECT " . implode(', ', $fields) . " FROM `invoices` 
            LEFT JOIN `contacts` ON (`invoices`.`contact_id` = `contacts`.`contact_id`) 
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY {$order} 
            LIMIT {$params['start']}, {$params['length']}";

        $this->getStatement($sql);
        try {
            $this->statement->execute($searchValues);

            //$invoices = $this->statement->fetchAll();
            $invoices = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
            $this->statement->debugDumpParams();
        }

        $output = $params;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(*) FROM invoices 
                WHERE $tenancies";

        $recordsFiltered = "SELECT count(*) as `filtered` FROM `invoices` 
                LEFT JOIN `contacts` ON (`invoices`.`contact_id` = `contacts`.`contact_id`) 
                WHERE  " . implode(' AND ', $conditions);


        $output['recordsTotal'] = $this->pdo->query($recordsTotal)->fetchColumn();

        try {
            $this->getStatement($recordsFiltered);
            $this->statement->execute($searchValues);
            $output['recordsFiltered'] = $this->statement->fetchAll(PDO::FETCH_ASSOC)[0]['filtered'];
        } catch (PDOException $e) {
            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$recordsFiltered\n";
            $this->statement->debugDumpParams();
        }


        //$output['refreshInvoice'] = $refreshInvoice;
        // $output['refreshContact'] = $refreshContact;


        if (count($invoices) > 0) {
            foreach ($invoices as $k => $row) {
                if (empty($row['name'])) {
                    $contactName = "<a href='#' data-toggle='modal' data-target='#contactSingle' data-contactid='{$row['contact_id']}'>{$row['contact_id']}</a>";
                } else {
                    $contactName = "<a href='#' data-toggle='modal' data-target='#contactSingle' data-contactid='{$row['contact_id']}'>{$row['name']}</a>";
                }
                $output['data'][] = [
                    'number' => "<a href='/authorizedResource.php?action=12&invoice_id={$row['invoice_id']}'>{$row['invoice_number']}</a>",
                    'reference' => $row['reference'],
                    'contact' => $contactName,
                    'status' => "<a href='https://go.xero.com/AccountsReceivable/View.aspx?InvoiceID={$row['invoice_id']}' target='_blank'>{$row['status']}</a>",
                    'total' => $row['total'],
                    'amount_paid' => $row['amount_paid'],
                    'amount_due' => $row['amount_due'],
                    'due_date' => date('d F Y', strtotime($row['due_date']))
                ];
            }
            $output['row'] = $row;
        }
        return $output;
    }
}
