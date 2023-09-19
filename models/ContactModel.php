<?php

require_once(SITE_ROOT . '/models/BaseModel.php');
require_once(SITE_ROOT . '/models/AddressModel.php');
require_once(SITE_ROOT . '/models/ContractModel.php');
require_once(SITE_ROOT . '/models/NoteModel.php');
require_once(SITE_ROOT . '/models/PhoneModel.php');

class ContactModel extends BaseModel
{

    protected array $nullable = ['id', 'contact_id', 'updated_date_utc'];
    protected array $saveKeys = [
        'id', 'contact_id', 'contact_status',
        'name', 'first_name', 'last_name', 'email_address', 'best_way_to_contact',
        'how_did_you_hear',
        'updated_date_utc', 'xerotenant_id'];
    protected array $updateKeys = [
        'contact_status',
        'name', 'first_name', 'last_name', 'email_address', 'best_way_to_contact',
        'how_did_you_hear',
        'updated_date_utc'];
    protected AddressModel $addresses;
    protected PhoneModel $phones;
    protected ContractModel $contracts;
    protected NoteModel $notes;

    protected string $table = 'contacts';

    protected array $hasMany = ['phones', 'addresses', 'contracts', 'notes'];


    function __construct()
    {
        parent::__construct();

        $this->buildInsertSQL();

        $this->addresses = new AddressModel();
        $this->contracts = new ContractModel();
        $this->notes = new NoteModel();
        $this->phones = new PhoneModel();
    }


    public function prepAndSave($data): int
    {
        if (array_key_exists('firstname', $data['contact']) && array_key_exists('last_name', $data['contact'])) {
            $data['contact']['name'] = $data['contact']['first_name'] . ' ' . $data['contact']['last_name'];
        }
        if (array_key_exists('id', $data['contact'])) {
            $id = intval($data['contact']['id']);
            $oldVals = $this->get($id);
            $contact = array_merge($oldVals['contacts'][0], $data['contact']);
        } else $contact = $data['contact'];

        // these can't be empty strings, either a value or null
        $contact = $this->checkNullableValues($contact);

        // we can't pass extra variables
        $save = $this->getSaveValues($contact);
        debug($contact);
        debug($save);
        $newId = $this->save($save);
        if ($newId > 0) $data['contact']['id'] = $newId;

        $this->addresses->prepAndSave($data);

        $data['note']['foreign_id'] = $data['contact']['id'];
        $data['note']['parent'] = 'contacts';
        $this->notes->prepAndSave($data);

        $phone = [];
        if (!empty($data)) {
            $data['phone']['ckcontact_id'] = $data['contact']['id'];
            $data['phone']['contact_id'] = $data['contact']['contact_id'];
            $this->phones->prepAndSave($data);
        }
        return $data['contact']['id'];
    }

    /*
     * need to provide the option to limit it to a particular tenancy
     * add weighting in the future?
     */
    public function search()
    {
        $searchFields = ['first_name', 'last_name', 'email_address', 'phone'];
        $conditions = $values = [];


        foreach ($searchFields as $var) {
            if (!empty($_GET[$var])) {
                $conditions[] = " `{$var}` LIKE :$var ";
                $values[':' . $var] = $_GET[$var];
            }
        }

        $sql = "SELECT `contacts`.`id`, `contacts`.`contact_id`, `contacts`.`status`
            `contacts`.`name`, `first_name`, `last_name`, 
            `email_address`, `phone_area_code`, `phone_number`, 
            `xerotenant_id`, `contacts`.`updated_date_utc`,
            `tenancies`.`colour`
            FROM `contacts`
            LEFT JOIN `phones` on `contacts`.`id` = `phones`.`ckcontact_id` 
            LEFT JOIN `tenancies` on `contacts`.`xerotenant_id` = `tenancies`.`tenant_id`
            WHERE `is_customer` = 1 
              AND (" . implode(' AND ', $conditions) .
            ') LIMIT 10';

        $this->getStatement($sql);
        try {
            $this->statement->execute($values);
            $list = $this->statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }
        return [
            'count' => count($list),
            'draw' => $_GET['draw'],
            'data' => $list
        ];
    }


}
