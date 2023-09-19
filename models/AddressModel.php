<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class AddressModel extends BaseModel
{
    protected string $insert = "INSERT INTO `addresses` (`address_id`, `ckcontact_id`, `contact_id`, `address_type`, 
                         `address_line1`, `address_line2`, `city`, `postal_code`)
                 VALUES (:address_id, :ckcontact_id, :contact_id, :address_type, 
                         :address_line1, :address_line2, :city, :postal_code)
                 ON DUPLICATE KEY 
                 UPDATE 
                     `address_line1` = :address_line1, `address_line2` = :address_line2, `city` = :city, `postal_code` = :postal_code";
    protected array $nullable = ['address_id', 'contact_id'];
    protected array $saveKeys = ['address_id', 'ckcontact_id', 'contact_id', 'address_type',
        'address_line1', 'address_line2', 'city', 'postal_code'];

    protected string $table = 'addresses';
    protected array $joins = ['contacts' => "`addresses`.`ckcontact_id` = :id1"];
    protected array $virtualFields = ['address' => "CONCAT(address_line1,', ', address_line2,', ', city, ' ', postal_code)"];

    public function prepAndSave($data): int
    {
        parent::prepAndSave($data);

        if (array_key_exists('address', $data)) {
            if (!empty($data['address']['address_line1'])) {
                return $this->save($data['address']);
            }
        } else if (array_key_exists('contract', $data) && array_key_exists('contract', $data)) {
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
                $data['address'] = $this->checkNullableValues($data['address']);
                $save = $this->getSaveValues($data['address']);
                debug($save);
                return $this->save($save);
            }

        } else if (array_key_exists('addresses', $data)) {
            // probably from a xero import
            $addresses = $this->getChildren('contacts', $data['contact']['id']);

            foreach ($data['addresses'] as $row) {

                if (!empty($row['address_line1'])) {
                    $row['ckcontact_id'] = $data['contact']['id'];

                    $search = array_search($row['address_line1'], $addresses);
                    if ($search !== false) {
                        debug($row);
                        debug($addresses);
                        debug($search);
                        $row = array_merge($addresses[$search], $row);
                    }
                    $row = $this->checkNullableValues($row);
                    $save = $this->getSaveValues($row);
                    $this->save($save);
                }
            }
        }
        return 0;
    }
}
