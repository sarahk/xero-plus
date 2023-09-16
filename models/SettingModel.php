<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class SettingModel extends BaseModel
{
    protected string $table = 'settings';
    protected string $insert = "INSERT INTO `settings` (`xerotenant_id`,`category`,`key`,`value`) 
            VALUES (:xeroTenantId, :category, :key, :value)
            ON DUPLICATE KEY UPDATE `value` = :value";

    public function getKeyValue($xerotenant_id, $category, $key)
    {
        $sql = "SELECT `value` FROM `settings` 
               WHERE `category` = :category 
                 AND `key` = :key 
                 AND `xerotenant_id` = :xerotenant_id 
               LIMIT 1";
        $this->getStatement($sql);
        $binders = ['xerotenant_id' => $xerotenant_id, 'category' => $category, 'key' => $key];
        debug($binders);
        debug($sql);
        try {
            $value = $this->statement->execute($binders);
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }

        debug($value);
        //$value = $this->pdo->query($sql)->fetchColumn();
        return $value;
    }
}
