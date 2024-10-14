<?php

namespace App\Models;

use App\XeroClass;


class ContractModel extends BaseModel
{
    protected string $table = 'contracts';
    protected array $joins = [
        'contacts' => "`contracts`.`ckcontact_id` = :id1 OR `contracts`.`contact_id` = :id2",
        'cabins' => "`contracts`.`cabin_id` = :id1"
    ];
    protected array $virtualFields = ['address' => "CONCAT(address_line1,', ', address_line2,', ', city, ' ', postal_code)"];


    protected string $orderBy = "delivery_date DESC";
    protected array $saveKeys = [
        'contract_id', 'repeating_invoice_id', 'cabin_id',
        'contact_id', 'ckcontact_id', 'reference', 'total', 'schedule_unit',
        'status', 'cabin_type', 'hiab', 'painted', 'winz',
        'delivery_date', 'scheduled_delivery_date', 'delivery_time',
        'pickup_date', 'scheduled_pickup_date',
        'address_line1', 'address_line2', 'city', 'postal_code',
        'lat', 'long', 'place_id', 'updated', 'stub'
    ];
    protected array $updateKeys = [
        'cabin_id', 'reference', 'total', 'schedule_unit', 'status',
        'cabin_type', 'hiab', 'painted', 'winz',
        'delivery_date', 'scheduled_delivery_date', 'delivery_time',
        'pickup_date', 'scheduled_pickup_date',
        'address_line1', 'address_line2', 'city', 'postal_code',
        'lat', 'long', 'place_id', 'updated', 'stub'];
    protected array $nullable = ['contract_id', 'repeating_invoice_id', 'cabin_id', 'contact_id'];
    protected ContractModel $contract;
    protected bool $hasStub = true;
    protected array $orderByColumns = [
        0 => 'contracts.contract_id DIR',
        1 => 'contracts.status DIR',
        2 => 'contacts.last_name DIR',
        6 => 'amount_due DIR',
    ];

    function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }

    public function saveXeroStub(array $data): int
    {
        $sql = "INSERT INTO `contracts` ( 
                    `repeating_invoice_id`,
                    `xerotenant_id`,
                    `contact_id` ,
                    `ckcontact_id`,
                    `reference`,
                    `schedule_unit`,
                    `total`,
                    `tax_type`,
                    `stub`  )
                    VALUES 
                    (:repeating_invoice_id, :xerotenant_id, :contact_id, :ckcontact_id,
                     :reference, :schedule_unit, :total, :tax_type, :stub);";

        return $this->runQuery($sql, $data, 'insert');
    }

    //  C O N T R A C T
    public function prepAndSave($data): int
    {
        $lookingFor = ['contract_id', 'repeating_invoice_id', 'xeroRefresh'];
        if ($this->array_keys_exist($lookingFor, $data)) {

            if (array_key_exists('xeroRefresh', $data) && $data['xeroRefresh']) {
                // we don't want to get the old $oldVals = ['contracts' => []];
                // we're saving fewer columns
                $this->debug('Contract Model prepAndSave xeroRefresh');
                $this->insert = "UPDATE `contracts` SET 
                       `stub` = 0, 
                       `contact_id` = :contact_id,
                       `ckcontact_id` = :ckcontact_id,
                       `reference` = :reference,
                       `schedule_unit` = :schedule_unit
                       WHERE repeating_invoice_id = :repeating_invoice_id";
                $save = [
                    'contact_id' => $data['contact_id'],
                    'ckcontact_id' => $data['ckcontact_id'],
                    'reference' => $data['reference'],
                    'schedule_unit' => $data['schedule_unit'],
                    'repeating_invoice_id' => $data['repeating_invoice_id']
                ];

                return $this->save($save);
            } else if (array_key_exists('contract_id', $data) && $data['contract_id']) {
                $oldVals = $this->get('contract_id', $data['contract_id']);
            } else if (array_key_exists('repeating_invoice_id', $data)) {
                $oldVals = $this->get('repeating_invoice_id', $data['contract']['repeating_invoice_id']);
            } else {
                //$oldVals = ['contracts' => []];
                $defaults = $this->getDefaults();
                $oldVals['contracts'] = $defaults[0];
            }

        } else {
            $defaults = $this->getDefaults();
            $oldVals['contracts'] = $defaults[0];
        }
// uses a special version of array_merge
        $contract = $this->array_merge($oldVals['contracts'], $data['contract']);

        $contract['updated'] = date('Y-m-d H:i:s');

        $checked = $this->checkNullableValues($contract);
        $save = $this->getSaveValues($checked);

        return $this->save($save);
    }


    protected function getTenantId(string $repeating_invoice_id): string
    {
        $sql = "SELECT `xerotenant_id` FROM `invoices` WHERE `repeating_invoice_id` = :keyVal LIMIT 1";
        $data = $this->runQuery($sql, ['keyVal' => $repeating_invoice_id]);
        return $data[0]['xerotenant_id'];
    }

    public function list(array $params): array
    {
        $tenancy = new TenancyModel($this->pdo);
        $raw = $tenancy->list();
        $tenancyList = array_column($raw, null, 'tenant_id');

        $searchValues = [];

        $tenancies = $this->getTenanciesWhere($params);

        $order = $this->getOrderBy($params);

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "`contracts`.`reference` LIKE :search1",
                "`contacts`.`name` LIKE :search2",
                "`contacts`.`first_name` LIKE :search3",
                "`contacts`.`last_name` LIKE :search4",
                "`contacts`.`email_address` LIKE :search5",
                "`contracts`.`status` LIKE :search6",
            ];

            for ($i = 1; $i <= count($search); $i++) {
                $searchValues["search$i"] = '%' . $params['search'] . '%';
            }

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

        $sql = "SELECT `contracts`.`contract_id`, contracts.repeating_invoice_id, contracts.`status`, 
                    `contracts`.`reference`, `delivery_date`, `pickup_date` ,
                    contracts.`address_line1`, contracts.`address_line2`, contracts.city, contracts.`postal_code`,
	                `contacts`.`id` as `ckcontact_id`, `contacts`.`name`, `contacts`.`email_address`,
                    `contracts`.`xerotenant_id`,
                    (SELECT SUM(`amount_due`) 
                        FROM `invoices` 
                        WHERE `invoices`.`repeating_invoice_id` = `contracts`.`repeating_invoice_id`) AS `amount_due`
                FROM `contracts`
                LEFT JOIN `contacts` ON `contracts`.`ckcontact_id` = `contacts`.`id`
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
// todo change the xero links to ones that work
                $idCell = [];
                if (!empty($row['repeating_invoice_id'])) {
                    $idCell[] = "<a href='/authorizedResource.php?action=10&id={$row['ckcontact_id']}'>{$row['contract_id']}</a>";
                    $idCell[] = "<img src='/images/Xero_software_logo.svg' height='15' width='15' style='margin-left: .5em'>";
                } else {
                    $idCell[] = "<img src='/images/Xero_disabled.svg' height='15' width='15' style='margin-left: .5em'>";
                }

                $output['data'][] = [
                    'contract_id' => implode($idCell),
                    'status' => $row['status'],
                    'name' => "<a href='/authorizedResource.php?action=10&id={$row['ckcontact_id']}'>{$row['name']}</a>",
                    'details' => '',//todo
                    'reference' => $row['reference'],
                    'address' => $this->formatAddress($row),
                    'amount_due' => $row['amount_due'],
                    'colour' => $tenancyList[$row['xerotenant_id']]['colour']
                ];
// for debugging
                $output['row'] = $row;
            }

        }

        return $output;
    }


}
