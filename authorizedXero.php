<?php
/*
 * The default code at the top of scripts that need xero authorisation
 */

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

require_once(SITE_ROOT . '/models/UserModel.php');

$pdo = getPDO();

// Storage Class uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();
$xeroTenantId = (string)$storage->getSession()['tenant_id'];
$objUser = new UserModel($pdo);

if (array_key_exists('user_name', $_SESSION)) {
    // stop using these
    $userName = $_SESSION['user_name'];
    $userId = $_SESSION['xero_user_id'] ?? '';
    // and use this

    $user = $_SESSION['user'] ?? $user = $objUser->getJWTValues($storage);
} else {
    try {
        $user = $objUser->getJWTValues($storage);
        $_SESSION['user'] = $user;
    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }
}

if ($storage->getHasExpired()) {
    $provider = getProvider();

    try {
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $storage->getRefreshToken()
        ]);
    } catch (Exception $e) {
        // need to log in again
        header('Location: /index.php');
        exit;
    }


    // Save my token, expiration and refresh token
    $storage->setToken(
        $newAccessToken->getToken(),
        $newAccessToken->getExpires(),
        $xeroTenantId,
        $newAccessToken->getRefreshToken(),
        $newAccessToken->getValues()["id_token"]
    );

}
require_once 'config.php';
$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);
$apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);
