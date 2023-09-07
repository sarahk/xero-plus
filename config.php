<?php 


$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => '279E976572B44F07B1E83D05F4BEC647',
    'clientSecret'            => 'C_lTdY0d5SwBROYkhPuErQTxCr2o6g0z81aPB2Kl3y00kILd',
    'redirectUri'             => 'https://cabinkingmanagement:8890/callback.php',
    'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
    'urlAccessToken'          => 'https://identity.xero.com/connect/token',
    'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
  ]);

const GOOGLE_MAPS_API = 'AIzaSyB32Z6abVU4CzDmYdxfGX1kW4H6slcLjUw';

  /*
  run this when I need composer to update the /vendor/ files
  PATH=/Applications/MAMP/Library/bin:/Applications/MAMP/bin/php/php8.2.0/bin:$PATH
  */