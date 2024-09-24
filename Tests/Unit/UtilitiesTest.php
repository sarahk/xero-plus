<?php

namespace Tests\Unit;

use League\OAuth2\Client\Provider\GenericProvider;

describe('Test of functions in the Utilities script', function () {

    beforeEach(function () {
        // Will run before each test in this file
        require_once "utilities.php";
        $this->originalServer = $_SERVER;
        $_SERVER['HTTP_HOST'] = 'ckm:8825';
    });

    afterEach(function () {
        // Cleanup after each test
        $_SERVER = $this->originalServer;
    });


    it('get Provider', function () {
        //$result = getcwd();
        //error_log($result);
        $result = getProvider();
        expect($result)->toBeObject()->toBeInstanceOf(GenericProvider::class);
    });

});
