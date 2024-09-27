<?php

namespace Tests\Unit;

use App\Models\InvoiceModel;
use App\Utilities;


// Adjust the namespace as needed
describe('Test of Invoice Bad Debts', function () {
    beforeEach(function () {
        // Will run before each test in this file

    });

    afterEach(function () {
        // Cleanup after each test

    });

    // we call: https://ckm:8825/run.php?endpoint=image&imageType=baddebt&contract_id=285
    // and it converts to https://image-charts.com/chart?iid=285&chco=ff0000&chs=300x125&cht=lc&chxt=x,y&chxl=0:||2||4||6||8||10||12||14||161:||640||760||880&chm=B,FCECF4,0,0,0&chma=0,0,20,0&chof=webp&chd=a:640,720,800,800,800,880,880,880,880,880,880,880,880,880,880,880&
    it('gets a single contract_id', function () {
        $pdo = Utilities::getPDO();
        $invoice = new InvoiceModel($pdo);
        $contract_id = $invoice->getOneBadDebtor();
        $int = intval($contract_id);
        $chartUrl = $invoice->getChartUrl($contract_id);
        
        expect($contract_id)->toBeString()
            ->and($int)->toBeInt()->toBeGreaterThan(0)
            ->and($chartUrl)->toBeUrl();
    });


});
