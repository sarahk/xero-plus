<?php
declare(strict_types=1);

namespace App\classes;

use App\Models\TenancyModel;
use App\Models\UserModel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use League\OAuth2\Client\Provider\GenericProvider;
use XeroAPI\XeroPHP\JWTClaims;

//use XeroAPI\XeroPHP\Configuration;
use PDO;
use PDOException;
use Exception;
use TypeError;
use Throwable;
use RuntimeException;

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
            $jwt = new JWTClaims();
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

        if ($storage->hasExpired()) {
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
                $newAccessToken->getValues()['id_token']
            );
            self::setTenanciesForUser($provider, $storage);
        }
        self::setJWTValues($storage);


        return $storage;
    }

    public function getTenanciesForUser(): array
    {
        if (array_key_exists('tenancies', $_SESSION)) {
            return $_SESSION['tenancies'];
        } else {
            return [];
        }
    }

    public static function setTenanciesForUser($provider, $storage): void
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
            $dsn = 'mysql:host=localhost;dbname=xeroplus;charset=utf8mb4';

            //$pdo = new PDO(, $user, $pass);
            //$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            //$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci",
                PDO::ATTR_EMULATE_PREPARES => false, // optional, if you want native prepares
            ]);
            return $pdo;
//"mysql:host=$host;dbname=$db;charset=utf8mb4"
        } catch (PDOException $e) {
            print 'Error!: ' . $e->getMessage() . '<br/>';
            die();
        }
    }

    public static function checkAccessToken(): void
    {
        $tokens = StorageClass::getAccessTokenAndExpiry();

        $currentTime = time();

// If the access token is expired or about to expire, refresh it
        if ($currentTime > $tokens['expires'] - 60) {  // Token expires in less than 1 minute
            $newAccessToken = self::refreshAccessToken('html');

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
        //self::getUserCredentials();
    }


    // Don't call this directly
    protected static function refreshAccessToken(string $what): bool
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

            return true;

        } catch (IdentityProviderException|RequestException|TypeError|Throwable|Exception $e) {
            // Catch any other exception or error
            // should be sending to monolog too
            if ($what === 'html') {
                header('Location: /');
                exit;
            }
            return false;
        }
    }


    public static function refreshAccessTokenJs()
    {
        $tokens = StorageClass::getAccessTokenAndExpiry();
        $currentTime = time();

// If the access token is expired or about to expire, refresh it
        if ($currentTime > $tokens['expires']) {
            // it's expired, we can't refresh and we'll get errors if we try
            return false;
        }
        if ($currentTime > $tokens['expires'] - 60) {
            try {

                return self::refreshAccessToken('JS');

            } catch (RequestException|TypeError|Throwable|Exception $e) {
                // Catch any other exception or error
                // should be sending to monolog too
                return false;
            }
        }
        return true;
    }

    protected static function getUserCredentials()
    {
        // Assuming you already have an OAuth access token
        $storage = new StorageClass();
        $accessToken = $storage->getAccessToken();

        $client = new Client();

// Execute the request
        $response = $client->request('POST', 'https://api.xero.com/connections',
            [
                "Authorization: Bearer $accessToken",
                'Content-Type: application/json'
            ]);

// Check for errors

        // Decode the JSON response
        $data = json_decode($response->getBody(), true);

        // Check if the data is available
        if (isset($data[0])) {
            $user = $data[0];

            // Get the user information (email, user_id, etc.)
            $userID = $user['userId']; // This is the Xero user ID
            $email = $user['emailAddress']; // This is the user's email
            $fullName = $user['name']; // The full name of the user
            setcookie($user);
            // Output or use the user information
            echo "User ID: $userID<br>";
            echo "Full Name: $fullName<br>";
            echo "Email: $email<br>";

        }

// Close the cURL session
        curl_close($ch);

    }


    // structured for datatables
    public static function getParams(): array
    {
        $tenancies = new TenancyModel(self::getPDO());

        return [
            'data' => [],
            'draw' => $_GET['draw'] ?? 1,
            'start' => $_GET['start'] ?? 0,
            'length' => ($_GET['length'] ?? 10) == -1 ? 10 : ($_GET['length'] ?? 10),
            'search' => $_GET['search']['value'] ?? '',
            'order' => $_GET['order'] ?? [0 => ['column' => '0', 'dir' => 'ASC']],
            'invoice_status' => $_GET['invoice_status'] ?? '',
            'dates' => $_GET['search']['dates'] ?? '',
            'contact_status' => $_GET['search']['contact_status'] ?? '',
            'button' => $_GET['button'] ?? '',//datatables
            'key' => $_GET['search']['key'] ?? '',
            'tenancies' => $tenancies->listActiveTenantId(),
            'contract_id' => $_GET['contract_id'] ?? 0,
            'foreign_id' => $_GET['foreign_id'] ?? 0,
            'parent' => $_GET['parent'] ?? '',
        ];
    }

    public static function getParamsPlus(array $plusList = []): array
    {
        $params = self::getParams();
        foreach ($plusList as $item) {
            $params[$item] = $_GET[$item] ?? '';
        }
        return $params;
    }

    /* todo
    this doesn't work because the user doesn't have create table permissions. Needs to be a sql script
    */
    public static function rebuildMaterialTables()
    {
        $tables = [
            'combo' => ['(`contract_id`,`date`)', '(`xerotenant_id`)'],
            'old_debts' => ['(`xerotenant_id`,`contact_id`)'],
            'bdmgmt' => ['(`xerotenant_id`)', '(`ckcontact_id`)']
        ];
        echo "start \n";
        $pdo = self::getPDO();
        foreach ($tables as $table => $indexes) {
            echo "$table \n";
            try {
                // Begin a transaction for atomicity

// drop can force a commit
                echo "DROP TABLE IF EXISTS `m{$table}`;";
                $pdo->exec("DROP TABLE IF EXISTS `m{$table}`;");

                $pdo->beginTransaction();

                $pdo->exec("CREATE TABLE `m{$table}` AS SELECT * FROM `v{$table}`;");

                foreach ($indexes as $k => $index) {
                    $pdo->exec("CREATE INDEX `idx_m{$table}_{$k}` ON `m{$table}` {$index};");
                }
                // Commit the transaction
                $pdo->commit();
                echo "Table `m{$table}` rebuilt successfully.\n";
            } catch (Exception $e) {
                // Roll back the transaction on error

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw new RuntimeException("Failed to build m{$table} table: " . $e->getMessage());
            }
        }
        unset($pdo);
        echo "end \n";
    }

    public static function refreshMaterialTables()
    {
        $tables = ['combo', 'old_debts', 'bdmgmt'];

        $pdo = self::getPDO();
        foreach ($tables as $table) {
            self:: refreshMaterialTable($pdo, $table);
        }
        unset($pdo);
        echo 'done';

    }

    protected static function refreshMaterialTable(PDO $pdo, string $table): void
    {
//        drop table if exists mcombo;
//        CREATE TABLE mcombo AS
//        SELECT * FROM vcombo;
//        CREATE INDEX idx_m_contract_date ON mcombo (contract_id, date);
//        CREATE INDEX idx_m_xerotenant_id ON mcombo (xerotenant_id);

        try {
            // Begin a transaction for atomicity
            $pdo->beginTransaction();

            // Clear the existing table
            $pdo->exec("DELETE FROM m{$table}");

            // Insert new data from vcombo
            $pdo->exec("INSERT INTO m{$table} SELECT * FROM v{$table}");

            // Commit the transaction
            $pdo->commit();
        } catch (Exception $e) {
            // Roll back the transaction on error
            $pdo->rollBack();
            throw new RuntimeException("Failed to refresh m{$table} table: " . $e->getMessage());
        }
    }
}
