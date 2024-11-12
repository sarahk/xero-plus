<?php

namespace App\Models;

use App\Models\Enums\CabinStyle;
use App\Models\Enums\EnquiryRating;
use App\Models\Enums\EnquiryStatus;
use App\XeroClass;
use PDO;


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
        'status', 'cabin_type', 'hiab', 'painted', 'winz', 'how_did_you_hear',
        'delivery_date', 'scheduled_delivery_date', 'delivery_time', 'sms_reminder_invoice',
        'pickup_date', 'scheduled_pickup_date', 'enquiry_rating', 'cabin_use',
        'address_line1', 'address_line2', 'city', 'postal_code',
        'lat', 'long', 'place_id',
        'updated', 'stub'
    ];
    protected array $updateKeys = [
        'cabin_id', 'reference', 'total', 'schedule_unit', 'status',
        'cabin_type', 'hiab', 'painted', 'winz', 'how_did_you_hear',
        'delivery_date', 'scheduled_delivery_date', 'delivery_time', 'sms_reminder_invoice',
        'pickup_date', 'scheduled_pickup_date', 'enquiry_rating', 'cabin_use',
        'address_line1', 'address_line2', 'city', 'postal_code',
        'lat', 'long', 'place_id',
        'updated', 'stub'
    ];
    protected array $nullable = ['contract_id', 'repeating_invoice_id', 'cabin_id', 'contact_id'];
    protected ContractModel $contract;
    protected bool $hasStub = true;
    protected array $orderByColumns = [
        0 => 'contracts.contract_id DIR',
        1 => 'contracts.status DIR',
        2 => 'contacts.last_name DIR',
        6 => 'amount_due DIR',
    ];

    function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }

    /**
     * @param array $data <mixed>
     * @return int
     */
    public function saveXeroStub(array $data): int
    {
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `contracts` ( 
                    `repeating_invoice_id`,
                    `xerotenant_id`,
                    `contact_id` ,
                    `ckcontact_id`,
                    `reference`,
                    `schedule_unit`,
                    `total`,
                    `tax_type`,
                    `stub`, 
                     `date`,
                     `updated`)
                    VALUES 
                    (:repeating_invoice_id, :xerotenant_id, :contact_id, :ckcontact_id,
                     :reference, :schedule_unit, :total, :tax_type, :stub, '$date', '$date');";

        return $this->runQuery($sql, $data, 'insert');
    }

    //  C O N T R A C T

    /**
     * @param array $data <mixed>
     * @return int
     */
    public function prepAndSave(array $data): string
    {
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
            //***********
            return $this->save($save);
            //***********
        }

        // normal save
        if (array_key_exists('contract_id', $data['contract']) && $data['contract']['contract_id']) {
            $oldVals = $this->get('contract_id', $data['contract']['contract_id']);
        } else if (array_key_exists('repeating_invoice_id', $data)) {
            // this should never be needed
            $oldVals = $this->get('repeating_invoice_id', $data['contract']['repeating_invoice_id']);
        } else {
            $defaults = $this->getDefaults();
            $oldVals['contracts'] = $defaults[0];
        }


// uses a special version of array_merge

        $contract = $this->array_merge($oldVals['contracts'], $data['contract']);

        $contract['updated'] = date('Y-m-d H:i:s');

        $checked = $this->checkNullableValues($contract);
        $save = $this->getSaveValues($checked);

        return $this->runQuery($this->insert, $save, 'insert');
    }


    protected function getTenantId(string $repeating_invoice_id): string
    {
        $sql = "SELECT `xerotenant_id` FROM `invoices` WHERE `repeating_invoice_id` = :keyVal LIMIT 1";
        $data = $this->runQuery($sql, ['keyVal' => $repeating_invoice_id]);
        return $data[0]['xerotenant_id'];
    }

    /**
     * @param array $params <string, mixed>
     * @return array<mixed>
     */
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

        // subsets are used on the home screen
        if (!empty($params['subset'])) {
            switch ($params['subset']) {
                case 'New':
                    $conditions[] = 'contracts.status = "New"';
                    break;
                case 'Waiting':
                    $conditions[] = 'contracts.status = "Yes"';
                    $conditions[] = '(contracts.delivery_date = "" OR contracts.delivery_date IS NULL)';
            }
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
                    `contracts`.`reference`, contracts.date,`delivery_date`, `pickup_date` , `scheduled_delivery_date`,
                    contracts.`address_line1`, contracts.`address_line2`, contracts.city, contracts.`postal_code`,
                    `contracts`.`cabin_type`, contracts.enquiry_rating,
	                `contacts`.`id` as `ckcontact_id`, `contacts`.`name`, `contacts`.`email_address`,
                    `contracts`.`xerotenant_id`,
                    (SELECT SUM(`amount_due`) 
                        FROM `invoices` 
                        WHERE `invoices`.`repeating_invoice_id` = `contracts`.`repeating_invoice_id`) AS `amount_due`,
                    (SELECT concat(`phone_area_code`,' ', `phone_number`) AS `phone_number`
                        FROM `phones`
                        WHERE `phones`.`ckcontact_id` = contacts.`id`
                        ORDER BY `phone_type` DESC LIMIT 1) AS `phone_number`
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
                    $idCell[] = "<img src='/images/Xero_software_logo.svg' height='15' width='15' style='margin-left: .5em' alt='Record is in Xero'>";
                } else {
                    $idCell[] = "<img src='/images/Xero_disabled.svg' height='15' width='15' style='margin-left: .5em' alt='Record is NOT in Xero'>";
                }

                $output['data'][] = [
                    'contract_id' => implode($idCell),
                    'status' => $row['status'],
                    'name' => "<a href='/authorizedResource.php?action=10&id={$row['ckcontact_id']}'>{$row['name']}</a>",
                    'details' => $this->getDetailsCell($row),
                    'reference' => $row['reference'],
                    'address' => $this->formatAddress($row),
                    'phone_number' => $row['phone_number'] ?? '',
                    'amount_due' => $row['amount_due'],
                    'date' => $this->getPrettyDate($row['date'] ?? ''),
                    'scheduled_delivery_date' => $this->getPrettyDate($row['scheduled_delivery_date'] ?? ''),
                    'colour' => $tenancyList[$row['xerotenant_id']]['colour'],
                    'rating' => EnquiryRating::getImage($row['enquiry_rating'] ?? 0)
                ];
// for debugging
                $output['row'] = $row;
            }

        }

        return $output;
    }

    /**
     * @param array $row <string, mixed>
     * @return string
     */
    protected function getDetailsCell(array $row): string
    {
        // verbose cabin style? CabinStyle::getLabel($row['cabin_type'])
        ///Bob Welsh, 88 Central Rd, Henderson. Ph 022-345 67789. Large

        $output = "{$row['name']}, {$row['address_line1']}, {$row['address_line2']}. {$row['cabin_type']}";
        //todo add a url
        $url = "authorizedResource.php?action=10&id={$row['ckcontact_id']}&contract_id={$row['contract_id']}";

        return "<a href='$url'>$output</a>";
    }

    public function getChildren($parent, mixed $parentId, $defaults = true): array
    {
        $contacts = new ContactModel($this->pdo);
        $phones = new PhoneModel($this->pdo);

        if (!$parentId) {
            $output = $contacts->getDefaults();
            $output[0]['Phones'] = $phones->getDefaults();
            return $output;
        }


        $output = $contacts->getAllJoins('contract', $parentId);
        if (count($output) > 0) {
            for ($i = 0; $i < count($output); $i++) {
                if (is_null($output[$i]['id'])) $output[$i]['Phones'] = $phones->getDefaults();
                else
                    $output['contacts'][$i]['phones'] = $phones->getChildren('contacts', $output['contacts'][$i]['id']);
            }
        } else $output[0]['Phones'] = $phones->getDefaults();
        return $output;
    }

    /**
     * @param int $contract_id
     * @return array<mixed>
     */
    public function getContactsAndPhone(int $contract_id): array
    {
        $sql = "SELECT contacts.*, `contactjoins`.`sort_order`
                    FROM `contacts` 
                    LEFT JOIN `contactjoins` ON `contactjoins`.`ckcontact_id` = contacts.`id`
                    WHERE `contactjoins`.`foreign_id` = :contract_id
                    AND `contactjoins`.join_type = 'contract'
                    ORDER BY `contactjoins`.sort_order";
        $result = $this->runQuery($sql, ['contract_id' => $contract_id]);

        $phones = new PhoneModel($this->pdo);
        $defaultPhone = $phones->getDefaults();


        if (count($result) == 0) {
            $this->checkForImportErrors($contract_id);
            $contact = new ContactModel($this->pdo);
            $result = [0 => $contact->getDefaults()];
            $result[0]['Phone'] = $defaultPhone;
        } else {

            for ($i = 0; $i < count($result); $i++) {
                $contactPhones = $phones->get('ckcontact_id', $result[$i]['id']);
                $result[$i]['Phones'] = $contactPhones['phones'];
            }
        }
        return $result;
    }

    protected function checkForImportErrors($contract_id): void
    {
        $ckcontact_id = $this->field('ckcontact_id', 'contract_id', $contract_id);
        if (!empty($ckcontact_id)) {
            $sql = "INSERT INTO `contactjoins` (`ckcontact_id`, `join_type`, `foreign_id`, `updated`)
                    VALUES (:ckcontact_id, :join_type, :foreign_id, NOW())";
            $this->runQuery($sql, ['ckcontact_id' => $ckcontact_id, 'join_type' => 'contract', 'foreign_id' => $contract_id], 'insert');
        }
    }

    public function getPaymentSummary($params)
    {
        $sql = 'SELECT
                SUM(IF(amount_due = 0, 1, 0)) AS fully_paid,
                SUM(IF(amount_due > 0 AND amount_due < total, 1, 0)) AS part_paid,
                SUM(IF(amount_due = total, 1, 0)) AS unpaid,
                SUM(amount_due) AS amount_due
            FROM invoices WHERE contract_id = :contract_id';
        return $this->runQuery($sql, ['contract_id' => $params['contract_id']]);
    }


    public function getOtherContracts($params)
    {

        $sql = 'SELECT contracts.*
                FROM contracts 
                LEFT JOIN contactjoins ON(contactjoins.foreign_id = contracts.contract_id
                    AND contactjoins.join_type = "contract")
                WHERE contactjoins.ckcontact_id = :ckcontact_id
                    AND contracts.contract_id != :contract_id';

        $result = $this->runQuery($sql, ['ckcontact_id' => $params['ckcontact_id'], 'contract_id' => $params['contract_id']]);
        return $result;
    }
}
