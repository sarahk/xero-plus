<?php

require_once (SITE_ROOT.'/models/BaseModel.php');

class AddressModel extends BaseModel
{
    protected $sql = "INSERT INTO `addresses` (`contact_id`, `address_type`, `address_line1`,`address_line2`, 
                `address_line3`, `address_line4`,`city`, `postal_code`, `country`)
                 values (:contact_id, 'STREET', :address_line1, :address_line2, :address_line3, :address_line4, :city, :postal_code, 'New Zealand')
                 ON DUPLICATE KEY UPDATE ";

    protected $table = 'addresses';
    protected $joins = ['contacts' => "`addresses`.`ckcontact_id` = :id1 OR `addresses`.`contact_id` = :id2"];
    protected $virtualFields = ['address' =>"CONCAT(address_line1,', ', address_line2,', ', city, ' ', postal_code)"];

}