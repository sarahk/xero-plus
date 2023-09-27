<?php
const SITE_ROOT = __DIR__;
function getProvider()
{
    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId' => '279E976572B44F07B1E83D05F4BEC647',
        'clientSecret' => 'C_lTdY0d5SwBROYkhPuErQTxCr2o6g0z81aPB2Kl3y00kILd',
        'redirectUri' => 'https://cabinkingmanagement:8890/callback.php',
        'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
        'urlAccessToken' => 'https://identity.xero.com/connect/token',
        'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
    ]);
    return $provider;
}

function getDbh()
{
    try {
        $user = 'xeroplus';
        $pass = 'cabins4all';
        $dbh = new PDO('mysql:host=localhost;dbname=xeroplus', $user, $pass);
        return $dbh;
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
}

?>
