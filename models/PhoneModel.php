<?php

namespace models;

use models\BaseModel;

class PhoneModel extends BaseModel
{
protected $sql = "INSERT INTO `phones` (contact_id, phone_type, phone_number, phone_area_code)
                values (:contact_id, 'MOBILE', :phone_number , :phone_area_code)
                ON DUPLICATE KEY UPDATE phone_number = :phone_number, :phone_area_code = :phone_area_code";


}