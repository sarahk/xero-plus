<?php
/*
 * The default code at the top of scripts that need xero authorisation
 */

// Use this class to deserialize error caught
use XeroAPI\XeroPHP\AccountingObjectSerializer;
use XeroAPI\XeroPHP\ApiException;

// Storage Class uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();
$xeroTenantId = (string)$storage->getSession()['tenant_id'];
if (array_key_exists('user_name', $_SESSION)) {
    $userName = $_SESSION['user_name'];
} else {
    try {
        $jwt = new XeroAPI\XeroPHP\JWTClaims();
        $jwt->setTokenId((string)$storage->getIdToken());
        // Set access token in order to get authentication event id
        $jwt->setTokenAccess((string)$storage->getAccessToken());
        $jwt->decode();

        $userName = $_SESSION['user_name'] = $jwt->getGivenName();
        $userEmail = $_SESSION['user_email'] = $jwt->getEmail();
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
