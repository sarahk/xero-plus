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

    protected function getFromXero($data): void
    {

        parent::getFromXero($data); // should be nothing there but let's check
        if (empty($data['repeating_invoice_id'])) {
            return;
        }

        $xero = new XeroClass();
        $repeating_invoice_id = $data['repeating_invoice_id'];
        $xeroTenantId = $this->getTenantId($repeating_invoice_id);

        $data = $xero->getSingleRepeatingInvoiceData($xeroTenantId, $repeating_invoice_id);
        $this->prepAndSave($data);
    }

    protected function getTenantId($repeating_invoice_id): string
    {
        $sql = "SELECT `xerotenant_id` FROM `invoices` WHERE `repeating_invoice_id` = :keyVal LIMIT 1";
        $data = $this->runQuery($sql, ['keyVal' => $repeating_invoice_id]);
        return $data[0]['xerotenant_id'];
    }


}
