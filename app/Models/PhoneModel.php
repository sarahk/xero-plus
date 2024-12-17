<?php

namespace App\Models;

use PDO;

class PhoneModel extends BaseModel
{
    protected string $sql = "INSERT INTO `phones` (`ckcontact_id`, `contact_id`, 
                      `phone_type`, `phone_number`, `phone_area_code`)
                values (:ckcontact_id, :contact_id, :phone_type, :phone_number , :phone_area_code)
                ON DUPLICATE KEY UPDATE `phone_number` = :phone_number, 
                                        `phone_area_code` = :phone_area_code";

    protected string $table = 'phones';
    protected array $saveKeys = ['ckcontact_id', 'contact_id', 'phone_type', 'phone_number', 'phone_area_code'];
    protected array $updateKeys = ['phone_number', 'phone_area_code'];
    protected array $joins = ['contacts' => "`phones`.`ckcontact_id` = :id1 OR `phones`.`contact_id` = :id2"];
    protected array $virtualFields = ['phone' => "CONCAT(`phone_area_code`,' ',`phone_number`)"];

    function __construct(PDO $pdo)
    {
        parent::__construct($pdo);

        $this->buildInsertSQL();
    }

    /**
     * @return array<mixed>
     */
    public function getDefaults(): array
    {
        $phones = parent::getDefaults();
        $default = [0 => $phones[0], 1 => $phones[0]];
        $default[1]['phone_type'] = 'DEFAULT';
        $this->defaults = $default;

        return $phones;
    }

    public function prepAndSave($data): string
    {
        parent::prepAndSave($data);

        if (array_key_exists('phone', $data)) {
            // todo
            $phones = $this->getChildren('contacts', $data['contact']['id']);

            foreach ($phones as $row) {
                $search = in_array($data['phone']['phone_type'], $row);
                if ($search !== false) {
                    $row = array_merge($row, $data['phone']);
                }
                $save = $this->getSaveValues($row);
                if ($save['phone_number'])
                    return $this->save($save);
            }
        }
        return 0;
    }

    public function saveOne($data): int
    {
        $this->debug($data);
        $sql = 'INSERT INTO phones (ckcontact_id, contact_id, phone_type, phone_number, phone_area_code)
                    VALUES (:ckcontact_id, :contact_id, :phone_type, :phone_number, :phone_area_code)
                    ON DUPLICATE KEY UPDATE
                        ckcontact_id = :ckcontact_id,
                        phone_number = :phone_number,
                        phone_area_code = :phone_area_code;';
        return $this->runQuery($sql, $data, 'insert');
    }
}
