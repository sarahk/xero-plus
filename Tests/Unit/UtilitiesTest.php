<?php

namespace Tests\Unit;

use App\Utilities;

use League\OAuth2\Client\Provider\GenericProvider;

describe('Test of functions in the Utilities script', function () {

    beforeEach(function () {
        // Will run before each test in this file
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
        $result = Utilities::getProvider();
        expect($result)->toBeObject()->toBeInstanceOf(GenericProvider::class);
    });

});
