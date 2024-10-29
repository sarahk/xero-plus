<?php

namespace App\Models;

use App\Models\BaseModel;


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
}
