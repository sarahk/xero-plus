<?php


namespace Tests\Unit;

use App\classes\ExtraFunctions;
use App\Models\InvoiceModel;
use App\classes\Utilities;

return;
// Adjust the namespace as needed
describe('Are the views getting the right data?', function () {

    beforeEach(function () {
        // Will run before each test in this file

        $this->logger = ExtraFunctions::getPestLogger('OldDebtsTest');
        $this->invoice = new InvoiceModel(Utilities::getPDO());

    });

    afterEach(function () {
    });

    function getRepeatingInvoiceIds()
    {
        $invoice = new InvoiceModel(Utilities::getPDO());
        $sql = 'SELECT repeating_invoice_id FROM vold_debts ORDER BY newest DESC, amount_due DESC LIMIT 10';
        $list = $invoice->pestRunQuery($sql, [], 'query');
        return array_map(fn($row) => $row['repeating_invoice_id'], $list);
    }


    it('Weeks Due Test', function ($repeating_invoice_id) {

        $sql1 = "select count(*) from vinvoices where repeating_invoice_id = :rpi and amount_due > 0";
        $result1 = $this->invoice->pestRunQuery($sql1, ['rpi' => $repeating_invoice_id], 'column');
        $sql2 = "select weeks_due from `vold_debts` where repeating_invoice_id = :rpi";
        $result2 = $this->invoice->pestRunQuery($sql2, ['rpi' => $repeating_invoice_id], 'column');


        $this->logger->log('info', 'Test results: ', [
            'rpi' => $repeating_invoice_id,
            '$sql1' => str_replace(':rpi', "'$repeating_invoice_id'", $sql1),
            '$sql2' => str_replace(':rpi', "'$repeating_invoice_id'", $sql2),
            'result1' => $result1,
            'result2' => $result2
        ]);

        expect($result1)->toBeInt()
            ->and($result2)->toBeInt();
        expect($result1)->toBe($result2);

    })->with(getRepeatingInvoiceIds());

    it('Total Weeks Test', function ($repeating_invoice_id) {

        $sql1 = "select count(*) from vinvoices where repeating_invoice_id = :rpi";
        $result1 = $this->invoice->pestRunQuery($sql1, ['rpi' => $repeating_invoice_id], 'column');
        $sql2 = "select total_weeks from `vold_debts` where repeating_invoice_id = :rpi";
        $result2 = $this->invoice->pestRunQuery($sql2, ['rpi' => $repeating_invoice_id], 'column');


        $this->logger->log('info', 'Test results: ', [
            'rpi' => $repeating_invoice_id,
            '$sql1' => str_replace(':rpi', "'$repeating_invoice_id'", $sql1),
            '$sql2' => str_replace(':rpi', "'$repeating_invoice_id'", $sql2),
            'result1' => $result1,
            'result2' => $result2
        ]);

        expect($result1)->toBeInt()
            ->and($result2)->toBeInt();
        expect($result1)->toBe($result2);

    })->with(getRepeatingInvoiceIds());
});
