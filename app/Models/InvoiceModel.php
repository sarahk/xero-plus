<?php

namespace App\Models;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


use PDO;
use PDOException;

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
    protected array $orderByColumns = [
        0 => "invoices.invoice_number DIR",
        1 => "contacts.last_name DIR, contacts.first_name ASC",
        2 => "invoices.reference DIR",
        3 => "invoices.total DIR",
        4 => "invoices.amount_due DIR",
        5 => "invoices.date DIR",

    ];
    protected int $orderByDefault = 5;

    function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();

    }

    // I N V O I C E
    // we WILL have a repeating invoice id
    // invoices are always imported from xero
    public function prepAndSave(array $data): int
    {
        $checked = $this->checkNullableValues($data);
        $save = $this->getSaveValues($checked);

        return $this->save($save);

    }


    /**
     * @param array $params <string, mixed>
     * @return array<mixed>
     */
    public function list(array $params): array
    {
        /*
        <th>#</th>
        <th>Contact</th>
        <th>Ref</th>
        <th>Total</th>
        <th>Due</th>
        <th>Date</th>
*/

        $searchValues = [];

        $tenancies = $this->getTenanciesWhere($params);
        $order = $this->getOrderBy($params);

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
            ORDER BY $order 
            LIMIT {$params['start']}, {$params['length']}";


        $invoices = $this->runQuery($sql, $searchValues);

        $output = $params;
        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $searchValues;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(*) FROM `invoices` 
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
            foreach ($invoices as $row) {
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
// for debugging
                $output['row'] = $row;
            }

        }
        return $output;
    }

    /**
     * @param array $params <mixed>
     * @return string
     */
    protected function getOrderByBadDebts(array $params): string
    {
        $columns = [
            'contacts.name DIR',
            'due DIR', 'weeks_due DIR', 'total_weeks DIR'
        ];
        if (is_array($params['order'])) {
            $direction = strtoupper($params['order'][0]['dir'] ?? 'DESC');
            $column = $params['order'][0]['column'];
            foreach ($columns as $k => $v) {
                if ($k == $column) {
                    return str_replace('DIR', $direction, $v);
                }
            }
        }
        return str_replace('DIR', 'DESC', $columns[2]);
    }

    /**
     * @param array $params <mixed>
     * @return array<mixed>
     */
    public function getBadDebtTotal(array $params): array
    {
        $tenancies = $this->getTenanciesWhere($params);
        $sql = "SELECT sum(amount_due) AS `total`
                    FROM `invoices`
                    WHERE $tenancies
                    AND datediff(now(), `invoices`.`date`) < 365";
        $result = $this->runQuery($sql, []);
        return $result[0];
    }

    /**
     * @param array $params <mixed>
     * @return array<mixed>
     */
    public function listBadDebts(array $params): array
    {

        $tenancyList = $this->getTenancyList();

        $searchValues = [];
        $tenancies = $this->getTenanciesWhere($params);
        $order = $this->getOrderByBadDebts($params);

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "`contacts`.`name` LIKE :search ",
                "`contacts`.`last_name` LIKE :search ",
                "`contacts`.`first_name` LIKE :search "
            ];
            $searchValues['search'] = '%' . $params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }

        //TODO
        // WHAT BUTTONS DO WE NEED
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

        if (isset($_GET['repeating_invoice_id'])) {
            $conditions[] = "`invoices`.`repeating_invoice_id` = :repeating_invoice_id";
            $searchValues['repeating_invoice_id'] = $_GET['repeating_invoice_id'];
        }

        // this clause defines what a bad debt actually is
        $conditions[] = "invoices.`amount_due` > 0 AND invoices.due_date < now()";

        $sql = "SELECT `contacts`.`id`,
            `contacts`.`xerotenant_id`,
            `contacts`.`name`, 
            `contacts`.`contact_id`,
            `contacts`.`xerotenant_id`,
            `contacts`.`id` as `DT_RowId`,
            SUM(`invoices`.`amount_due`) as due, 
            COUNT(`invoices`.`invoice_id`) as weeks_due,
            (SELECT COUNT(i2.invoice_id) FROM invoices as i2 
                                         WHERE i2.contact_id = contacts.contact_id) AS total_weeks
            FROM `contacts` 
            LEFT JOIN `invoices` ON invoices.contact_id = contacts.`contact_id`
            WHERE " . implode(' AND ', $conditions) . "
            GROUP BY `contacts`.`id`
            ORDER BY $order 
            LIMIT {$params['start']}, {$params['length']}";


        //  (SELECT CONCAT(phones.phone_area_code, ' ', phones.phone_number) as `phone` from `phones` WHERE phones.ckcontact_id = contacts.id ORDER BY `phone_type` DESC LIMIT 1) AS phone,

        $badDebts = $this->runQuery($sql, $searchValues);

        $output = $params;
        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $searchValues;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(invoices.contract_id) FROM `invoices` 
                WHERE $tenancies AND {$conditions[count($conditions)-1]}
                GROUP BY `invoices`.`contract_id`";

        $recordsFiltered = "SELECT count(invoices.contract_id) as `filtered` FROM `invoices` 
                LEFT JOIN `contacts` ON (`invoices`.`contact_id` = `contacts`.`contact_id`) 
                WHERE  " . implode(' AND ', $conditions);


        $output['recordsTotal'] = $this->pdo->query($recordsTotal)->fetchColumn();
        $output['recordsFiltered'] = $this->getRecordsFiltered($conditions, $searchValues, $recordsFiltered);


        if (count($badDebts) > 0) {
            foreach ($badDebts as $row) {


                $output['data'][] = [
                    'DT_RowId' => $row['DT_RowId'],
                    'contact' => $this->getFormattedContactCell($row),
                    'name' => $row['name'],
                    'due' => $row['due'],
                    'weeks_due' => $row['weeks_due'],
                    'total_weeks' => $row['total_weeks'],
                    'colour' => $tenancyList[$row['xerotenant_id']]['colour'],
                    'chart' => "<img src='/run.php?endpoint=image&imageType=baddebt&contact_id={$row['contact_id']}' 
                                    alt=\"Bad Debt history for {$row['name']}\" 
                                    width='300' height='125'/>"
                ];
                // for debugging
                $output['row'] = $row;
            }

        }
        return $output;
    }

    public function getChartURL(string $contact_id): string
    {
        $data = $this->getChartData($contact_id);

        $parts = [
            'iid=' . $contact_id,
            'chco=ff0000',
            'chs=300x125',
            'cht=lc',
            'chxt=x,y',  // label the axis
            'chxl=' . $data['xaxis'] . $data['yaxis'],
            'chm=B,FCECF4,0,0,0', // fill
            'chma=0,0,20,0', //margins
            'chof=webp',
            'chd=a:' . $data['data'],
        ];

        //lc - chart has scale
        //ls - no scale

        return 'https://image-charts.com/chart?' . implode('&', $parts) . '&';
    }

    protected function getFormattedContactCell($row): string
    {
        if (empty($row['name'])) $row['name'] = $row['contact_id'];

        $contacts = new ContactModel($this->pdo);

        $email = $contacts->get('contact_id', $row['contact_id']);

        $output = "<a href='#' data-bs-toggle='modal' data-bs-target='#contactSingle' data-tenancyid='{$row['xerotenant_id']}' data-contactid='{$row['contact_id']}'>{$row['name']}</a>
                        <br/><i class='fa-solid fa-at'></i> <a href='mailto:{$email['contacts']['email_address']}'>{$email['contacts']['email_address']}</a>";

        if (isset($email['phones']) && count($email['phones'])) {
            foreach ($email['phones'] as $phone) {
                if (!empty($phone['phone_number'])) {
                    $output .= "<br/><a href='tel:{$phone['phone_area_code']}{$phone['phone_number']}'>({$phone['phone_area_code']}) {$phone['phone_number']}</a>";
                }
            }
        }

        return $output;
    }

    //https://ckm:8825/run.php?endpoint=image&imageType=baddebt&contract_id=191
    protected function getChartData($contact_id): array
    {
        $xaxis = [
            'full' => '0:|1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16',
            'slim' => '0:||2||4||6||8||10||12||14||16',
            'slim-r' => '0:|16||14||12||10||8||6||4||2'
        ];

        //$contract = new ContractModel($this->pdo);
        //$contact_id = $contract->field('contact_id', 'contract_id', $contract_id);

        $sql = "SELECT 
                    weeks.week_number,
                        (
                        SELECT SUM(invoices.total) 
                        FROM invoices 
                        WHERE invoices.contact_id = :contact_id1
                          AND FLOOR(DATEDIFF(CURDATE(), invoices.date) / 7) <= weeks.week_number
                    ) AS `owing`,
                    (
                        SELECT SUM(payments.amount) 
                        FROM payments 
                        WHERE payments.contact_id = :contact_id2
                          AND FLOOR(DATEDIFF(CURDATE(), payments.date) / 7) <= weeks.week_number
                    ) AS `paid`
                FROM 
                    weeks";

        $output = array_fill(0, 16, 0);

        $result = $this->runQuery($sql, ['contact_id1' => $contact_id, 'contact_id2' => $contact_id]);
        if (count($result)) {
            foreach ($result as $row) {
                $output[$row['week_number']] = $row['owing'] - $row['paid'];
            }
        }


        $max = ceil(max($output) / 10) * 10;
        $min = floor(min($output) / 10) * 10;
        $mid = round($min + (($max - $min) / 2));
        return [
            'data' => implode(',', array_reverse($output)),
            'xaxis' => $xaxis['slim-r'],
            'yaxis' => "1:||$min||$mid||$max"
        ];

        return $output;

//        $this->getStatement($sql);
//        try {
//            $this->statement->execute(['contract1' => $contract_id, 'contract2' => $contract_id]);
//            $result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
//            $data = array_column($result, 'totaldue');
//            $data = array_reverse($data);
//
//            $max = ceil(max($data) / 10) * 10;
//            //$number = ceil($input / 10) * 10;
//            $min = floor(min($data) / 10) * 10;
//            $mid = round($min + (($max - $min) / 2));
//            return [
//                'data' => implode(',', $data),
//                'xaxis' => $xaxis['slim'],
//                'yaxis' => "1:||$min||$mid||$max"
//            ];
//
//        } catch (PDOException $e) {
//            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
//            $this->statement->debugDumpParams();
//        }
        //return [];
    }

    function getPDF($invoice_id)
    {
        //GET https://api.xero.com/api.xro/2.0/Invoices/acb4b8d6-e8bc-41c9-9a57-dccae7ad51de
    }

    /* Get One Bad Debtor
       used for testing

    */
    function getOneBadDebtor(): string
    {
// todo check this works after the change from using contract_id to contact_id for bad debts
        $log = new Logger('InvoiceModel.getOneBadDebtor');
        $log->pushHandler(new StreamHandler('monolog.log', Level::Info));


        $sql = "SELECT i.contact_id, i.invoice_id, i.date,
                    (SELECT sum(i2.amount_due) FROM invoices AS i2 WHERE i.contact_id = i2.contact_id) AS `total`
                FROM 
                    invoices i
                INNER JOIN (
                    SELECT 
                        contact_id, 
                        MAX(invoices.date) AS latest_invoice_date
                    FROM 
                        invoices
                    GROUP BY 
                        contact_id
                ) as latest_invoices
                ON i.contract_id = latest_invoices.contract_id
                AND i.date = latest_invoices.latest_invoice_date
                WHERE YEAR(i.date) = YEAR(NOW())
                ORDER BY `total` DESC
                LIMIT 3, 1;";

        //$log->info('SQL',$sql);

        $this->getStatement($sql);
        try {
            $this->statement->execute();
            $result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            $log->info('Query result', $result);
            $log->info('Query count', [count($result)]);
            $log->info('Will return', [$result[0]['contract_id']]);

            return $result[0]['contract_id'];

        } catch (PDOException $e) {
            echo "[list] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
            $log->error("[list] Error Message for $this->table: " . $e->getMessage());
            $this->statement->debugDumpParams();
        }
        return 0;
    }

}
