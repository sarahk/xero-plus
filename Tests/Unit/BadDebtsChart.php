<?php

namespace Tests\Unit;

use App\ExtraFunctions;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use App\Models\InvoiceModel;
use App\Utilities;
use ReflectionClass;


// Adjust the namespace as needed
describe('Are the views getting the right data?', function () {

    beforeEach(function () {
        //beforeAll(function () {
        $this->logger = ExtraFunctions::getPestLogger('BadDebtsChart');
        $this->invoice = new InvoiceModel(Utilities::getPDO());
    });

    afterEach(function () {
    });

    function getContractIds()
    {
        $invoice = new InvoiceModel(Utilities::getPDO());
        $sql = 'SELECT contract_id 
                    FROM vold_debts 
                    WHERE YEAR(newest)= YEAR(CURDATE())
                    ORDER BY amount_due DESC, newest DESC
                    LIMIT 10';
        $list = $invoice->pestRunQuery($sql, [], 'query');
        return array_map(fn($row) => $row['contract_id'], $list);
    }


    it('Chart Data Test', function ($contract_id) {

        $this->logger->log('info', $contract_id);
        // Use Reflection to make the protected method accessible
        $reflection = new ReflectionClass($this->invoice);
        $method = $reflection->getMethod('getChartData');
        $method->setAccessible(true);
        $result = $method->invoke($this->invoice, $contract_id);

        $range = explode('||', $result['yaxis']);

        $this->logger->log('info', 'Test results: ', [
            'contract_id' => $contract_id,
            'data' => $result,
            'range' => $range
        ]);


        expect($result)->toBeArray()
            ->and($result['data'])->toBeString()
            ->and($result['xaxis'])->toBeString()
            ->and($result['yaxis'])->toBeString();
        expect($range[1])->toBeLessThan($range[2])
            ->and($range[2])->toBeLessThan($range[3]);

    })->with(getContractIds());


});
