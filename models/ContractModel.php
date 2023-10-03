<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class ContractModel extends BaseModel
{
    protected string $table = 'contracts';
    protected array $joins = ['contacts' => "`contracts`.`ckcontact_id` = :id1 OR `contracts`.`contact_id` = :id2"];
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

    function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->buildInsertSQL();
    }

    //  C O N T R A C T
    public function prepAndSave($data): int
    {
        $contract = $data['contract'];

        //return parent::prepAndSave($data);
        if (array_keys_exist(['contract_id', 'repeating_invoice_id'], $contract)) {
            if (array_key_exists('contract_id', $contract) && $contract['contract_id']) {
                $oldVals = $this->get('contract_id', $contract['contract_id']);
            } else if (array_key_exists('repeating_invoice_id', $contract)) {
                $oldVals = $this->get('repeating_invoice_id', $data['contract']['repeating_invoice_id']);
            }
        } else $oldVals = $this->contracts->getDefaults();

        $contract = array_merge($oldVals['contracts'], $contract);

        $contract['updated'] = date('Y-m-d H:i:s');

        $checked = $this->checkNullableValues($contract);
        $save = $this->getSaveValues($checked);

        return $this->save($save);
    }
}
