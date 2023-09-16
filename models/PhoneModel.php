<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class PhoneModel extends BaseModel
{
    protected string $sql = "INSERT INTO `phones` (`address_id`, `contact_id`, 
                      `phone_type`, `phone_number`, `phone_area_code`)
                values (:contact_id, :phone_type, :phone_number , :phone_area_code)
                ON DUPLICATE KEY UPDATE phone_number = :phone_number, :phone_area_code = :phone_area_code";

    protected string $table = 'phones';
    protected array $saveKeys = ['contact_id', 'phone_type', 'phone_number', 'phone_area_code'];
    protected array $joins = ['contacts' => "`phones`.`ckcontact_id` = :id1 OR `phones`.`contact_id` = :id2"];
    protected array $virtualFields = ['phone' => "CONCAT(`phone_area_code`,' ',`phone_number`)"];

    public function getDefaults()
    {
        $phones = parent::getDefaults();
        $default = $phones[0];
        $default['phone_type'] = 'DEFAULT';
        $phones[] = $default;
        return $phones;
    }

    public function prepAndSave($data)
    {
        parent::prepAndSave($data);
        if (array_key_exists('phone', $data)) {
            // todo
        } else if (array_key_exists('phones', $data)) {
            $phones = $this->getChildren('contacts', $data['contact']['id']);
            foreach ($data['phones'] as $row) {
                $search = array_search($row['phone_number'], $phones);
                if ($search !== false) {
                    debug($row);
                    debug($phones);
                    debug($search);
                    $row = array_merge($phones[$search], $row);
                }
                $save = $this->getSaveValues($row);
                $this->save($save);
            }
        }
    }
}
