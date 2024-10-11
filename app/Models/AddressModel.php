<?php

namespace App\Models;

use App\Models\BaseModel;


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

    public function save($save): int
    {
        $this->debug(['save', $save]);
        exit;
        parent::save($save);
    }

    //todo proper pest testing
    public function saveXeroStub(array $save): int
    {
        //$this->debug(['saveXeroStub', $save]);
        if (!empty($save['address_line1'])) {
            $sql = "INSERT INTO `addresses` ( 
                         `address_line1`, `address_line2`,
                         `city`, `postal_code`, 
                         `ckcontact_id`, `contact_id`, `address_type`)
                VALUES ( 
                        :address_line1, :address_line2,
                        :city, :postal_code, 
                        :ckcontact_id, :contact_id, :address_type);";

            return $this->runQuery($sql, $save, 'insert');
        }
        return 0;
    }

    public function prepAndSave($data): int
    {
        // don't call the parent
        //parent::prepAndSave($data);
        // $this->debug($data);


        if (array_key_exists('address', $data)) {
            //normal
            $this->debug('normal address save');
            if (!empty($data['address']['address_line1'])) {
                return $this->save($data['address']);
            }
        } else if (array_key_exists('contract', $data) && array_key_exists('contract', $data)) {
            $this->debug('contract address save');
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
                $this->debug(['AddressModel prepAndSave', $save]);
                return $this->save($save);
            }

        } else if (array_key_exists('addresses', $data)) {
            // probably from a xero import
            $this->debug('xero import address save');
            $addresses = $this->getChildren('contacts', $data['contact']['id']);

            foreach ($data['addresses'] as $row) {

                if (!empty($row['address_line1'])) {
                    $row['ckcontact_id'] = $data['contact']['id'];

                    $search = array_search($row['address_line1'], $addresses);
                    if ($search !== false) {
                        $this->debug($row);
                        $this->debug($addresses);
                        $this->debug($search);
                        $row = array_merge($addresses[$search], $row);
                    }
                    $row = $this->checkNullableValues($row);
                    $save = $this->getSaveValues($row);
                    $this->save($save);
                }
            }
        }
        //$this->debug('not doing any prep and save');
        return 0;
    }
}
