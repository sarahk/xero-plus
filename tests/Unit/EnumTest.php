<?php

namespace Tests\Unit;

use App\Models\Enums\CabinStatus;
use App\Models\Enums\CabinStyle;
use App\Models\Enums\TaskStatus;
use App\Models\Enums\TaskType;

// C A B I N   S T A T U S
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
        expect($cs)->toHaveCount(6)
            ->toContain(CabinStatus::New)
            ->toContain(CabinStatus::Active)
            ->toContain(CabinStatus::Sold);
    });

    it('returns the correct value for each status', function () {
        expect(CabinStatus::New->value)->toBeString()->toBe('new')
            ->and(CabinStatus::Active->value)->toBeString()->toBe('active')
            ->and(CabinStatus::Sold->value)->toBeString()->toBe('sold');
    });

    it('returns the correct label for each status', function () {
        expect(CabinStatus::getLabel('new'))->toBeString()->toBe('New')
            ->and(CabinStatus::getLabel('active'))->toBeString()->toBe('Active')
            ->and(CabinStatus::getLabel('sold'))->toBeString()->toBe('Sold');
    });

    it('returns all the names for Cabin Status', function () {
        expect(CabinStatus::getAllNames())->toBeArray();
        //error_log(print_r(CabinStatus::getAllNames()));
    });

    it('returns all the values for Cabin Status', function () {

        expect(CabinStatus::getAllValues())->toBeArray();
        //error_log(print_r(CabinStatus::getAllValues()));
    });

    it('can get the CabinStatus select options', function () {
        $options = CabinStatus::getSelectOptions('new');
        //error_log($options);
        expect($options)->toBeString();
    });
});

// C A B I N   S T Y L E S
describe('Test of CabinStyles Enum', function () {

    it('returns all the names for Cabin Style', function () {
        expect(CabinStyle::getAllNames())->toBeArray();
        //error_log(print_r(CabinStyle::getAllNames()));
    });

    it('returns all the values for Cabin Style', function () {

        expect(CabinStyle::getAllValues())->toBeArray();
        //error_log(print_r(CabinStyle::getAllValues()));
    });

    it('can get the CabinStyle select options', function () {
        $options = CabinStyle::getSelectOptions('new');
        //error_log($options);
        expect($options)->toBeString();
    });
});

// T A S K   S T A T U S
describe('Test of TaskStatus Enum', function () {

    it('returns all the names for Task Status', function () {
        expect(TaskStatus::getAllNames())->toBeArray();
        //error_log(print_r(TaskStatus::getAllNames()));
    });

    it('returns all the values for Task Status', function () {

        expect(TaskStatus::getAllValues())->toBeArray();
        //error_log(print_r(TaskStatus::getAllValues()));
    });

    it('can get the Task Status select options', function () {
        $options = TaskStatus::getSelectOptions('new');
        //error_log($options);
        expect($options)->toBeString();
    });
});

// T A S K   T Y P E
describe('Test of TaskType Enum', function () {

    it('returns all the names for Task Types', function () {
        expect(TaskType::getAllNames())->toBeArray();
        //error_log(print_r(TaskType::getAllNames()));
    });

    it('returns all the values for Task Types', function () {

        expect(TaskType::getAllValues())->toBeArray();
        //error_log(print_r(TaskType::getAllValues()));
    });
    it('returns all the icons for Task Types', function () {

        expect(TaskType::getAllIcons())->toBeArray();
        //error_log(print_r(TaskType::getAllIcons()));
    });


    it('can get the Task Type select options', function () {
        $options = TaskType::getSelectOptions('new');
        //error_log($options);
        expect($options)->toBeString();
    });
});
