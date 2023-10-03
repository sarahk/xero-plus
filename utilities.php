<?php

use League\OAuth2\Client\Provider\GenericProvider;

session_start();
const SITE_ROOT = __DIR__;
include_once 'storage.php';
include_once SITE_ROOT . '/models/UserModel.php';

function getProvider(): GenericProvider
{
    return new GenericProvider(options: [
        'clientId' => '279E976572B44F07B1E83D05F4BEC647',
        'clientSecret' => 'C_lTdY0d5SwBROYkhPuErQTxCr2o6g0z81aPB2Kl3y00kILd',
        'redirectUri' => "https://{$_SERVER['HTTP_HOST']}/callback.php",
        'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
        'urlAccessToken' => 'https://identity.xero.com/connect/token',
        'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
    ]);
}

function setJWTValues($storage): array
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
    $objUser = new UserModel(getPDO());
    $user['id'] = $objUser->getId('user_id', $user['xeroUserId']);
    return $user;
}

function getStorage(): StorageClass
{
    $storage = new StorageClass();
    $xeroTenantId = (string)$storage->getSession()['tenant_id'];

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

        $_SESSION['user'] = setJWTValues($storage);
        setTenanciesforUser($provider, $storage);
    }

    return $storage;
}

function getTenanciesForUser(): array
{
    if (array_key_exists('tenancies', $_SESSION)) {
        return $_SESSION['tenancies'];
    } else {
        return [];
    }
}

function setTenanciesforUser($provider, $storage): void
{
    if (!array_key_exists('tenancies', $_SESSION)) {

        $accessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $storage->getRefreshToken()
        ]);
        $options = [
            'scope' => ['openid email profile offline_access accounting.transactions accounting.settings']
        ];
        $connectionsResponse = $provider->getAuthenticatedRequest(
            'GET',
            'https://api.xero.com/Connections',
            $accessToken->getToken(),
            $options
        );

        $xeroTenantIdArray = $provider->getParsedResponse($connectionsResponse);
        $_SESSION['tenancies'] = $xeroTenantIdArray;
    }
}

/*
require_once 'config.php';
$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);
$apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);*/


function getPDO(): PDO
{
    try {
        $user = 'xeroplus';
        $pass = 'cabins4all';
        return new PDO('mysql:host=localhost;dbname=xeroplus', $user, $pass);

    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}
