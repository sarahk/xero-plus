<?php

namespace Tests\Unit;

use App\Models\Enums\CabinStatus;

// Adjust the namespace as needed
describe('Test of CabinStatus Enum', function () {
    beforeEach(function () {
        // Will run before each test in this file

    });

    afterEach(function () {
        // Cleanup after each test

    });


    it('has the correct Cabin Status defined', function () {
        $cs = CabinStatus::cases();
        //$cs = \App\Models\Enums\CabinStatus::cases();
        expect($cs)->toHaveCount(6); // Check the number of roles
        expect($cs)->toContain(CabinStatus::New);
        expect($cs)->toContain(CabinStatus::Active);
        expect($cs)->toContain(CabinStatus::Sold);
    });

    it('returns the correct value for each status', function () {
        expect(CabinStatus::New->value)->toBeString()->toBe('new');
        expect(CabinStatus::Active->value)->toBeString()->toBe('active');
        expect(CabinStatus::Sold->value)->toBeString()->toBe('sold');
    });

    it('returns the correct label for each status', function () {
        expect(CabinStatus::getCabinStatusLabel('new'))->toBeString()->toBe('New');
        expect(CabinStatus::getCabinStatusLabel('active'))->toBeString()->toBe('Active');
        expect(CabinStatus::getCabinStatusLabel('sold'))->toBeString()->toBe('Sold');
    });

    it('can get the CabinStatus select options', function () {
        $options = CabinStatus::getCabinStatusOptions('new');
        error_log($options);
        expect($options)->toBeString();
    });
});
