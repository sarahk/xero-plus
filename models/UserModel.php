<?php
require_once(SITE_ROOT . '/models/BaseModel.php');

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

class UserModel extends BaseModel
{
    protected string $table = 'users';

    /**
     * @throws Exception
     */
    public function getId($key, $value): int
    {
        $sql = "SELECT `id` FROM `users` where {$key} = :{$key}";

        $statement = $this->pdo->prepare($sql);
        $statement->execute([$key => $value]);
        $list = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (count($list) == 0) {
            throw new Exception("User not found: {$this->table} -> {$key} -> {$value}");
        }
        return $list[0]['id'];
    }

    // the xero code has deprecated code, throws errors but does actually work
    // ob_end_clean is to hide those errors
    
}
