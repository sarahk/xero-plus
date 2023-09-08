<?php

require_once(SITE_ROOT . '/models/BaseModel.php');
require_once(SITE_ROOT . '/models/AddressModel.php');
require_once(SITE_ROOT . '/models/ContractModel.php');
require_once(SITE_ROOT . '/models/NoteModel.php');
require_once(SITE_ROOT . '/models/PhoneModel.php');

class ContactModel extends BaseModel
{
    protected $insert = "INSERT into `contacts` (`id`,`contact_id`, `contact_status`, `name`, `first_name`, `last_name`, `email_address`,
                        `best_way_to_contact`,`how_did_you_hear`,
                `is_supplier`, `is_customer`, `updated_date_utc`, `xerotenant_id`) 
                VALUES (:id, :contact_id, :contact_status, :name, :first_name, :last_name, :email_address, :best_way_to_contact, :how_did_you_hear, 0, 1, 
                        :updated_date_utc, :xerotenant_id)
                ON DUPLICATE KEY UPDATE
                `name` = :name, `first_name` = :first_name, `last_name` = :last_name, `email_address` = :email_address, 
                    `best_way_to_contact` = :best_way_to_contact, `how_did_you_hear` = :how_did_you_hear,
                    `updated_date_utc` = :updated_date_utc";
    protected $addresses;
    protected $phones;
    protected $contracts;
    protected $notes;

    protected $table = 'contacts';
    protected $hasMany = ['phones', 'addresses', 'contracts', 'notes'];


    function __construct()
    {
        parent::__construct();

        $this->addresses = new AddressModel();
        $this->contracts = new ContractModel();
        $this->notes = new NoteModel();
        $this->phones = new PhoneModel();
    }


    public function prepAndSave($data)
    {
        $id = intval($data['contact']['id']);
        $oldVals = $this->get($id);
        $contact = array_merge($oldVals['contacts'], $data['contact']);
        $contact['name'] = $contact['first_name'] . ' ' . $contact['last_name'];

        debug($contact);
        $newId = $this->save($contact);
        if ($newId > 0) $data['contact']['id'] = $newId;

        debug($data['contact']['id']);

        $this->addresses->prepAndSave($data);

        return ($id);
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

        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        $list = $statement->fetchAll(PDO::FETCH_ASSOC);

        return [
            'count' => count($list),
            'draw' => $_GET['draw'],
            'data' => $list
        ];
    }
}