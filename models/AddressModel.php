<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class AddressModel extends BaseModel
{
    protected $insert = "INSERT INTO `addresses` (`address_id`, `ckcontact_id`, `contact_id`, `address_type`, 
                         `address_line1`, `address_line2`, `city`, `postal_code`)
                 VALUES (:address_id, :ckcontact_id, :contact_id, :address_type, 
                         :address_line1, :address_line2, :city, :postal_code)
                 ON DUPLICATE KEY 
                 UPDATE 
                     `address_line1` = :address_line1, `address_line2` = :address_line2, `city` = :city, `postal_code` = :postal_code";


    protected $table = 'addresses';
    protected $joins = ['contacts' => "`addresses`.`ckcontact_id` = :id1"];
    protected $virtualFields = ['address' => "CONCAT(address_line1,', ', address_line2,', ', city, ' ', postal_code)"];

    public function prepAndSave($data)
    {
        parent::prepAndSave($data);

        if (array_key_exists('address', $data)) {
            if (!empty($data['address']['address_line1'])) {
                return $this->save($data['address']);
            }
        } else {
            if (array_key_exists('contact', $data) && array_key_exists('contract', $data)) {
                if (!empty($data['contract']['address_line1'])) {
                    $data['address'] = [
                        'ckcontact_id' => intval($data['contact']['id']),
                        'contact_id' => $data['contact']['contact_id'],
                        'address_type' => 'STREET',
                        'address_line1' => $data['contract']['address_line1'],
                        'address_line2' => $data['contract']['address_line2'],
                        'city' => $data['contract']['city'],
                        'postal_code' => $data['contract']['postal_code'],
                    ];
                    return $this->save($data['address']);
                }
            }
        }
        return null;
    }
}