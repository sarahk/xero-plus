<?php

namespace App;

use \XeroAPI\XeroPHP\Configuration;

//use App\StorageClass;
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require '../vendor/autoload.php';

//use function App\Utilities\getProvider;

// Storage Class uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();
$provider = Utilities::getProvider();

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
    echo "Something went wrong, no authorization code found";
    exit("Something went wrong, no authorization code found");

    // Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    echo "Invalid State";
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {

    try {

        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        $config = \XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$accessToken->getToken());

        $identityInstance = new \XeroAPI\XeroPHP\Api\IdentityApi(
            new \GuzzleHttp\Client(),
            $config
        );

        $result = $identityInstance->getConnections();

        // Save my tokens, expiration tenant_id
        $storage->setToken(
            $accessToken->getToken(),
            $accessToken->getExpires(),
            $result[0]->getTenantId(),
            $accessToken->getRefreshToken(),
            $accessToken->getValues()["id_token"]
        );

        header('Location: ' . './authorizedResource.php');
        exit();

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        echo "Callback failed";
        exit();
    }
}
