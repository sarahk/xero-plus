<?php
require_once(SITE_ROOT . '/models/BaseModel.php');

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

class UserModel extends BaseModel
{
    public function getId($key, $value): int
    {
        $sql = "SELECT `id` FROM `users` where {$key} = :{$key}";
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$key => $value]);
        $list = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $list[0]['id'];
    }

    // the xero code has deprecated code, throws errors but does actually work
    // ob_end_clean is to hide those errors
    public function getJWTValues($storage)
    {
        ob_start();
        $jwt = new XeroAPI\XeroPHP\JWTClaims();
        $jwt->setTokenId((string)$storage->getIdToken());
        // Set access token in order to get authentication event id
        $jwt->setTokenAccess((string)$storage->getAccessToken());
        $jwt->decode();

        $user = [
            'userName' => $_SESSION['user_name'] = $jwt->getGivenName(),
            'xeroUserId' => $_SESSION['xero_user_id'] = $jwt->getXeroUserId(),
            'userEmail' => $_SESSION['user_email'] = $jwt->getEmail()
        ];
        ob_end_clean();
        $user['id'] = $this->getId('user_id', $user['xeroUserId']);
        return $user;
    }
}
