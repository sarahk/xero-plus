<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class ContractModel extends \BaseModel
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
        'lat', 'long', 'place_id', 'updated'
    ];
    protected array $updateKeys = [
        'cabin_id', 'reference', 'total', 'schedule_unit', 'status',
        'cabin_type', 'hiab', 'painted', 'winz',
        'delivery_date', 'scheduled_delivery_date', 'delivery_time',
        'pickup_date', 'scheduled_pickup_date',
        'address_line1', 'address_line2', 'city', 'postal_code',
        'lat', 'long', 'place_id', 'updated'];
    protected array $nullable = ['contract_id', 'repeating_invoice_id', 'cabin_id', 'contact_id'];
    protected ContractModel $contract;

    function __construct()
    {
        parent::__construct();
        $this->buildInsertSQL();
        $this->contract = new ContractModel();
    }

    //  C O N T R A C T
    public function prepAndSave($data): int
    {
        $contract = $data['contracts'];
        debug($contract);
        //return parent::prepAndSave($data);
        if (!array_key_exists('contract_id', $invoice) || $invoice['contract_id']) {
            $contract = $this->get('repeating_invoice_id', $data['contract']['repeating_invoice_id']);
        } else $contract['contracts'] = $this->contracts->getDefaults()['contracts'][0];

        debug($data);
        debug($contract);
        debug($invoice);

        $merged = array_merge(
            $contract['contracts'],
            $invoice,
            ['updated' => date('Y-m-d H:i:s')]
        );

        $checked = $this->checkNullableValues($merged);
        $save = $this->getSaveValues($checked);
        $contract_id = $this->save($save);

        return $contract_id;
    }


}
