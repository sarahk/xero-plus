<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class PhoneModel extends BaseModel
{
    protected $sql = "INSERT INTO `phones` (contact_id, phone_type, phone_number, phone_area_code)
                values (:contact_id, 'MOBILE', :phone_number , :phone_area_code)
                ON DUPLICATE KEY UPDATE phone_number = :phone_number, :phone_area_code = :phone_area_code";

    protected $table = 'phones';
    protected $joins = ['contacts' => "`phones`.`ckcontact_id` = :id1 OR `phones`.`contact_id` = :id2"];
    protected $virtualFields = ['phone' => "CONCAT(`phone_area_code`,' ',`phone_number`)"];

    public function getDefaults()
    {
        $phones = parent::getDefaults();
        $default = $phones[0];
        $default['phone_type'] = 'DEFAULT';
        $phones[] = $default;
        return $phones;
    }
}