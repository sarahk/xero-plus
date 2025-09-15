<?php
declare(strict_types=1);

namespace Tests\Unit;

use Mockery;
use App\XeroClass;

it('imports invoices from Xero via wrapper (unit, mocked)', function () {
    // Arrange: mock your wrapper instead of hitting the network
    $xero = Mockery::mock(XeroClass::class);
    $xero->shouldReceive('getInvoiceRefresh')
        ->once()
        ->with('auckland')
        ->andReturn(7);

    // Act
    $count = $xero->getInvoiceRefresh('auckland');

    // Assert
    expect($count)->toBeInt()->toBe(7);
});
