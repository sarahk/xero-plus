<?php

namespace models;

class ContactModel extends BaseModel
{
    protected $sql = "insert into `contacts` (`contact_id`, `contact_status`, `name`, `first_name`, `last_name`, `email_address`,
                `is_supplier`, `is_customer`, `updated_date_utc`, `xerotenant_id`) 
                values (:contact_id, :contact_status, :name, :first_name, :last_name, :email, 0,1, 
                        :updated_date_utc, :xerotenant_id)
                ON DUPLICATE KEY UPDATE
                (name = :name, first_name = :first_name, last_name = :last_name, email_address = :email, 
                    updated_date_utc = :updated_date_utc, xerotenant_id = :xerotenant_id)";
    protected $address;
    protected $phone;

    function __construct() {
          parent::__construct();

              $this->address = new AddressModel();
              $this->phone = new PhoneModel();
    }
    public function prep($data){
        $values = [];

        $vals = [
            'contact_id' => $data['contact_id']??null,
            'contact_status' => $data['contact_status']??'CKMONLY',
            'name' => $data['first_name'] . ' '. $data['last_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'updated_date_utc' => $data['updated_date_utc']??null,
            'xerotenant_id' => $data['xerotenant_id'],
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'],
            'city' => $data['city'],
            'postal_code' => $data['postal_code'],
            'phone_number' => $data['phone_number'],
            'phone_area_code' => $data['phone_area_code'],
        ];

        exit;

        $this->save($values);
        $this->address->save($values);
        $this->phone->save($values);
    }
}