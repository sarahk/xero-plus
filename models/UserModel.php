<?php
include_once(SITE_ROOT . '/models/BaseModel.php');

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function getId($key, $value): int
    {
        $sql = "SELECT `id` FROM `users` where `{$key}` = :value";

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
}
