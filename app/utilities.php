<?php

namespace App;

use App\StorageClass;
use App\Models\UserModel;

use \League\OAuth2\Client\Provider\GenericProvider;
use \XeroAPI\XeroPHP\Configuration;
use PDO;

if (!isset($_SESSION)) {
    session_start();
}
const SITE_ROOT = __DIR__;
const SIDE_BAR = true;
const GOOGLE_MAPS_API = 'AIzaSyB32Z6abVU4CzDmYdxfGX1kW4H6slcLjUw';


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

/**
 * @throws Exception
 */
function setJWTValues($storage): void
{
    if (!array_key_exists('user_id', $_SESSION)) {
        ob_start();
        $jwt = new XeroAPI\XeroPHP\JWTClaims();
        $jwt->setTokenId((string)$storage->getIdToken());
        // Set access token in order to get authentication event id
        $jwt->setTokenAccess((string)$storage->getAccessToken());
        $jwt->decode();

        $_SESSION['user_name'] = $jwt->getGivenName();
        $_SESSION['xero_user_id'] = $jwt->getXeroUserId();
        $_SESSION['user_email'] = $jwt->getEmail();

        ob_end_clean();
        $users = new UserModel(getPDO());
        $_SESSION['user_id'] = $users->getId('user_id', $_SESSION['xero_user_id']);
    }

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
        setTenanciesforUser($provider, $storage);
    }
    setJWTValues($storage);


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
        return new PDO('mysql:host=localhost;dbname=xeroplus;charset=utf8mb4', $user, $pass);
//"mysql:host=$host;dbname=$db;charset=utf8mb4"
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}

class lists
{
    static function getOwners(): array
    {
        return $owners = [
            '---' => '',
            'jk' => 'Chloe',
            'ck' => 'Charlotte'
        ];
    }

    static function getCabinStyles(): array
    {
        return [
            ['name' => 'std', 'label' => 'Standard'],
            ['name' => 'std-left', 'label' => 'Standard, Left'],
            ['name' => 'std-right', 'label' => 'Standard, Right'],
            ['name' => 'large', 'label' => 'Large'],
            ['name' => 'xl', 'label' => 'Extra Large']
        ];
    }

    static function getCabinStyle($name): string
    {
        $styles = lists::getCabinStyles();
        foreach ($styles as $row) {
            if ($row['name'] === $name) {
                return $row['label'];
            }
        }
        return 'uknown: ' . $name;
    }

    static function getCabinStatuses()
    {
        return [
            ['name' => 'new', 'label' => 'New'],
            ['name' => 'active', 'label' => 'Active'],
            ['name' => 'repairs', 'label' => 'Needs Repairs'],
            ['name' => 'sold', 'label' => 'Sold'],
            ['name' => 'stolen', 'label' => 'Stolen'],
            ['name' => 'write-off', 'label' => 'Write Off'],
        ];
    }

    static function getCabinStatus($name): string
    {
        $types = lists::getCabinStatuses();
        foreach ($types as $row) {
            if ($row['name'] === $name) {
                return $row['label'];
            }
        }
        return 'uknown: ' . $name;
    }

    static function getTaskTypes(): array
    {
        return [
            'wof' => [
                'name' => 'wof',
                'label' => 'Electrical WOF',
                'repeats' => true,
                'years' => 4,
                'delete' => false,
                'icon' => 'bolt-lightning'
            ],
            'buy' => [
                'name' => 'buy', 'label' => 'Shopping', 'repeats' => false, 'delete' => true,
                'icon' => 'cart-shopping'
            ],
            'repair' => [
                'name' => 'repair', 'label' => 'Repairs', 'repeats' => false, 'delete' => true,
                'icon' => 'hammer'
            ]
        ];
    }

    static function getTaskType($name): string
    {
        $types = lists::getTaskTypes();
        foreach ($types as $row) {
            if ($row['name'] === $name) {
                return $row['label'];
            }
        }
        return 'uknown: ' . $name;
    }

    static function getTaskStatus(): array
    {
        return ['open', 'complete', 'cancelled'];
    }
}
