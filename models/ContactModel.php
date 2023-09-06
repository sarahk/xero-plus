<?php

require_once(SITE_ROOT . '/models/BaseModel.php');
require_once(SITE_ROOT . '/models/AddressModel.php');
require_once(SITE_ROOT . '/models/PhoneModel.php');

class ContactModel extends BaseModel
{
    protected $insert = "INSERT into `contacts` (`id`,`contact_id`, `contact_status`, `name`, `first_name`, `last_name`, `email_address`,
                `is_supplier`, `is_customer`, `updated_date_utc`, `xerotenant_id`) 
                values (:id, :contact_id, :contact_status, :name, :first_name, :last_name, :email, 0,1, 
                        :updated_date_utc, :xerotenant_id)
                ON DUPLICATE KEY UPDATE
                (name = :name, first_name = :first_name, last_name = :last_name, email_address = :email, 
                    updated_date_utc = :updated_date_utc, xerotenant_id = :xerotenant_id)";
    protected $addresses;
    protected $phones;
    protected $table = 'contacts';
    protected $hasMany = ['phones','addresses','contracts'];


    function __construct()
    {
        parent::__construct();

        $this->addresses = new AddressModel();
        $this->phones = new PhoneModel();
    }


    public function prepAndSave($data)
    {
        $values = [];

        $vals = [
            'id' => $data['id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'contact_status' => $data['contact_status'] ?? '',
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'updated_date_utc' => $data['updated_date_utc'] ?? null,
            'xerotenant_id' => $data['xerotenant_id'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'phone_number' => $data['phone_number'],
            'phone_area_code' => $data['phone_area_code'],
        ];

        exit;

        $this->save($values);
        $this->address->save($values);
        $this->phone->save($values);
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