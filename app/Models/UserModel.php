<?php

namespace App\Models;

use App\Models\BaseModel;

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function getId($key, $value): int
    {
        $sql = "SELECT `id` FROM `users` WHERE `{$key}` = :value";

        $this->getStatement($sql);

        try {
            $this->statement->execute(['value' => $value]);
            $list = $this->statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($list) == 0) {
                $this->statement->debugDumpParams();
                throw new Exception("User not found: $this->table -> $key -> {$value}");
                exit;
            }
        } catch (PDOException $e) {
            echo "[getIdStatement] Error Message for $this->table: " . $e->getMessage() . "\n$sql\n";
            $this->statement->debugDumpParams();
        }

        return $list[0]['id'];
    }

    /**
     * run by callback.php to get the userid
     * @param array $list
     * @return int
     */
    public function getUserId(array $list): int
    {
        $where = $searchValues = [];
        foreach ($list as $k => $user) {
            $where[] = "(xerouser_id = :user_id$k AND xerotenant_id = :tenant_id$k)";
            $searchValues["user_id$k"] = $user['id'];
            $searchValues["tenant_id$k"] = $user['tenantId'];
        }
        $sql = "SELECT `user_id` FROM `userstenancies` WHERE " . implode(" OR ", $where) . " LIMIT 1";
        $result = $this->runQuery($sql, $searchValues);

        if (count($result) == 0) {
            // new user they need to be set up by Sarah
            echo '<h5>Call Sarah with the information below</h5>';
            $this->debug($list);
            exit;
        }
        
        return $result[0]['user_id'];
    }
}
