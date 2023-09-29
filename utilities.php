<?php

use League\OAuth2\Client\Provider\GenericProvider;

const SITE_ROOT = __DIR__;
function getProvider(): GenericProvider
{
    return new GenericProvider([
        'clientId' => '279E976572B44F07B1E83D05F4BEC647',
        'clientSecret' => 'C_lTdY0d5SwBROYkhPuErQTxCr2o6g0z81aPB2Kl3y00kILd',
        'redirectUri' => "https://{$_SERVER['HTTP_HOST']}/callback.php",
        'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
        'urlAccessToken' => 'https://identity.xero.com/connect/token',
        'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
    ]);
}

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
