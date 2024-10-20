<?php

namespace App;

use App\StorageClass;
use App\Models\UserModel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use League\OAuth2\Client\Provider\GenericProvider;
use XeroAPI\XeroPHP\Configuration;
use PDO;
use PDOException;
use Exception;

if (!isset($_SESSION)) {
    session_start();
}


// todo this whole class could be static

class Utilities
{
    public static $google_maps_api = 'AIzaSyB32Z6abVU4CzDmYdxfGX1kW4H6slcLjUw';

    public static function getProvider(): GenericProvider
    {
        return new GenericProvider(options: self::getXeroCredentials());
    }

    protected static function getXeroCredentials(): array
    {
        return [
            'clientId' => '279E976572B44F07B1E83D05F4BEC647',
            'clientSecret' => 'C_lTdY0d5SwBROYkhPuErQTxCr2o6g0z81aPB2Kl3y00kILd',
            'redirectUri' => "https://{$_SERVER['HTTP_HOST']}/callback.php",
            'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken' => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
        ];
    }

    /**
     * @throws Exception
     */
    public static function setJWTValues($storage): void
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
            $users = new UserModel(Utilities::getPDO());
            $_SESSION['user_id'] = $users->getId('user_id', $_SESSION['xero_user_id']);
        }

    }


    public static function getStorage(): StorageClass
    {
        $storage = new StorageClass();
        $xeroTenantId = (string)$storage->getSession()['tenant_id'];

        if ($storage->getHasExpired()) {
            $provider = self::getProvider();
            try {
                $newAccessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $storage->getRefreshToken()
                ]);
            } catch (Exception $e) {
                // need to log in again
                // todo monolog the event
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

    public static function setTenanciesforUser($provider, $storage): void
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
    require_once 'CKMProvider.php';
    $config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);
    $apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
        new GuzzleHttp\Client(),
        $config
    );*/


    public static function getPDO(): PDO
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

    public static function checkAccessToken(): void
    {
        $tokens = StorageClass::getAccessTokenAndExpiry();

        $currentTime = time();

// If the access token is expired or about to expire, refresh it
        if ($currentTime > $tokens['expires'] - 60) {  // Token expires in less than 1 minute
            $newAccessToken = self::refreshAccessToken();

            if ($newAccessToken) {
                //todo do we need to do anything?
                //echo "Access token refreshed successfully!";
                // Proceed with API requests using $newAccessToken
            } else {
                //todo add monolog
                //echo "Failed to refresh access token!";
                // Handle token refresh failure (e.g., re-authenticate the user)
            }
        }
    }

    protected static function refreshAccessToken()
    {
        $client = new Client(); //guzzle
        try {
            //$provider = self::getProvider();
            $credentials = self::getXeroCredentials();

            $response = $client->request('POST', 'https://identity.xero.com/connect/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => StorageClass::getRefreshTokenStatic(),
                    'client_id' => $credentials['clientId'],
                    'client_secret' => $credentials['clientSecret'],
                    'redirect_uri' => $credentials['redirectUri'],
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);

            // Extract new tokens
            $newAccessToken = $responseBody['access_token'];
            $newRefreshToken = $responseBody['refresh_token'];

            // Store the new tokens securely (e.g., in the session or database)
            StorageClass::storeNewTokens($newAccessToken, $newRefreshToken);

            return $newAccessToken; // Return the new access token
        } catch (RequestException $e) {
            echo 'Error refreshing access token: ' . $e->getMessage();
            return null;
        }
    }
}
