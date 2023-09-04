<?php

require_once (SITE_ROOT.'/models/BaseModel.php');

class ContactModel extends BaseModel
{
    protected $insert = "INSERT into `contacts` (`id`,`contact_id`, `contact_status`, `name`, `first_name`, `last_name`, `email_address`,
                `is_supplier`, `is_customer`, `updated_date_utc`, `xerotenant_id`) 
                values (:id, :contact_id, :contact_status, :name, :first_name, :last_name, :email, 0,1, 
                        :updated_date_utc, :xerotenant_id)
                ON DUPLICATE KEY UPDATE
                (name = :name, first_name = :first_name, last_name = :last_name, email_address = :email, 
                    updated_date_utc = :updated_date_utc, xerotenant_id = :xerotenant_id)";
    protected $address;
    protected $phone;


    function __construct()
    {
        parent::__construct();

        $this->address = new AddressModel();
        $this->phone = new PhoneModel();
    }

    public function prep($data)
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

    public function search()
    {

        $searchFields = ['first_name', 'last_name', 'email', 'phone'];
        $conditions = $values = [];

        foreach ($searchFields as $var) {
            if (!empty($_POST[$var])) {
                $conditions[] = " {$var} = :{$var} ";
                $values[$var] = $_POST[$var];
            }
        }

        $sql = "SELECT * FROM `contacts`
         LEFT JOIN `phones` on contacts.id = phones.ckcontact_id WHERE " . implode(' OR ', $conditions);

        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        $list = $statement->fetchAll(PDO::FETCH_ASSOC);
        $list['draw'] = $_POST['draw'];
        debug($list);
        return $list;
    }
}