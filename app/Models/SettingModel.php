<?php

namespace App\Models;


class SettingModel extends BaseModel
{
    protected string $table = 'settings';
    protected string $insert = "INSERT INTO `settings` (`xerotenant_id`,`category`,`key`,`value`) 
            VALUES (:xeroTenantId, :category, :key, :value)
            ON DUPLICATE KEY UPDATE `value` = :value";

    public function getKeyValue(string $xerotenant_id, string $category, string $key): mixed
    {
        $sql = "SELECT `value` FROM `settings` 
               WHERE `category` = :category 
                 AND `key` = :key 
                 AND `xerotenant_id` = :xerotenant_id 
               LIMIT 1";
        $this->getStatement($sql);
        $binders = ['xerotenant_id' => $xerotenant_id, 'category' => $category, 'key' => $key];

        $value = $this->runQuery($sql, $binders);

        $this->debug($value);
        //$value = $this->pdo->query($sql)->fetchColumn();
        return $value;
    }

    //todo this is cut and paste and needs to be reviewed
    public function prepAndSave(array $data): string
    {
        $checked = $this->checkNullableValues($data);
        $save = $this->getSaveValues($checked);

        $this->debug($this->insert);
        return $this->runQuery($this->insert, $save, 'insert');
    }
}
