<?php

namespace App;

use App\Models\UserModel;
use App\Classes\StorageClass;
use App\Classes\Utilities;
use GuzzleHttp\Exception\RequestException;
use \XeroAPI\XeroPHP\Configuration;

//use App\StorageClass;
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require '../vendor/autoload.php';

// Storage Class uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();
$provider = Utilities::getProvider();


//var_dump($_GET);
/* example return
 * array(4) {
  ["code"]=>
  string(43) "RhVnYC0JrKYm72Z9jBeAIWWugv_sZk5DAdeEhKVpF2c"
  ["scope"]=>
  string(187) "openid email profile assets projects accounting.settings accounting.transactions accounting.contacts accounting.journals.read accounting.reports.read accounting.attachments offline_access"
  ["state"]=>
  string(32) "f70c62ca29fe15fc7ad7c88aaa9936db"
  ["session_state"]=>
  string(66) "rJxHfcGj3J6I7O6OgS1YFEQ8rfUlLXjP9sXm0-Na3Lk.h4jvQPDbOp0Oyh4FxM8Paw"
}
 */

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

    Utilities::refreshMaterialTables();
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

        // My code, October 2024
// is there something in my xero.php code that does this too?
        $userClient = new \GuzzleHttp\Client();

        try {
            // Make a GET request to Xero's Connections endpoint
            $response = $userClient->request('GET', 'https://api.xero.com/connections', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Parse the response
            $connections = json_decode($response->getBody(), true);
            var_dump($connections);
            // Loop through the organizations connected and get the user_id for each
            $user_ids = [];
            foreach ($connections as $connection) {
                $user_ids[] = [
                    'id' => $connection['id'],
                    'tenantId' => $connection['tenantId']
                ];
            }
            $users = new UserModel(Utilities::getPDO());
            $user_id = $users->getUserId($user_ids);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $users->field('first_name', 'id', $user_id);

        } catch (RequestException $e) {
            echo 'Error: ' . $e->getMessage();
            exit;
        }


        // end of  my code
        header('Location: ' . $storage->getUrl());
        exit();

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        echo "Callback failed";
        exit();
    }
}
