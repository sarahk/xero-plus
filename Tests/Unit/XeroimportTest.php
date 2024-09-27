<?php

namespace Xero\Tests\Unit;

use App\Models\InvoiceModel;
use App\Utilities;
use \App\XeroClass;
use XeroAPI\XeroPHP\Models\Accounting;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\ApiException;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


describe('Import Invoices from Xero', function () {

    // https://ckm:8825/xero.php?endpoint=Invoices&action=refresh&tenancy=auckland

    it('can authenticate with Xero', function () {
        // Configure your Xero client
        $tenancy_id = 'ae75d056-4af7-484d-b709-94439130faa4'; //Auckland
        $logger = new Logger('my_logger');
// Now add some handlers
        $logger->pushHandler(new StreamHandler(__DIR__ . '../monolog/XeroimportTest.log', Level::Debug));

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken(getXeroAccessToken());

        //$apiInstance = new AccountingApi(null, $config);
        $logger->debug('config', [$config]);

        $result = file_get_contents('https://ckm:8825/json.php?endpoint=Contacts&action=refreshSingle&tenancy=auckland&contact_id=7fee9f9a-98ff-40cb-8568-90f95de7d94b')
        $logger->debug('result', [$result]);
        try {
            // Example of retrieving contacts after authentication
            //$contacts = $apiInstance->getContacts($tenancy_id);
            //expect($contacts)->not->toBeNull();  // Pest assertion
        } catch (ApiException $e) {
            dump($e->getMessage()); // Debugging in Pest
            $this->fail('Authentication with Xero failed');
        }
    });

// Function to get or refresh Xero access token
    function getXeroAccessToken()
    {
        // Ideally, get the access token from a secure source, such as a storage or database
        return 'YOUR_ACCESS_TOKEN';
    }


    //$xero->getInvoiceRefresh($tenancy);
    it('import invoices from Xero', function () {
        //$tenancy_id = 'ae75d056-4af7-484d-b709-94439130faa4'; //Auckland
        $xero = new XeroClass();
        $result = $xero->getInvoiceRefresh('auckland');
        expect($result)->toBeInt();
    });
});
