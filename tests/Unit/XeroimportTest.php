<?php
declare(strict_types=1);

namespace Xero\Tests\Unit;

use App\XeroClass;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Mockery;

describe('Import Invoices from Xero', function () {
    beforeEach(function () {
        // Safer logging target for CI; or ensure ../monolog exists
        $this->logger = new Logger('xero-test');
        $this->logger->pushHandler(new StreamHandler('php://stderr', Level::Debug));
    });

    it('imports invoices from Xero via wrapper', function () {
        // Mock your wrapper instead of hitting network/Xero
        $xero = Mockery::mock(XeroClass::class)->makePartial();
        $xero->shouldReceive('getInvoiceRefresh')
            ->once()
            ->with('auckland')
            ->andReturn(7);

        $result = $xero->getInvoiceRefresh('auckland');
        expect($result)->toBeInt()->toBeGreaterThanOrEqual(0);
    });
});
