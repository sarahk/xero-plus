<?php
declare(strict_types=1);

use Tests\Integration\TestLogger;

// A tiny subclass to inject a unique label before parent::__construct() runs

describe('TestLogger', function () {

    test('creates a writable path and writes a line', function () {

        $logger = new TestLogger();

        // 1) Path is a string and directory exists
        expect($logger->log_path)->toBeString()->not->toBe('');
        expect(is_dir($logger->log_path))->toBeTrue();   // requires mkdir() fix above

        $file = $logger->log_path . 'pest-tests.log';
        if (file_exists($file)) unlink($file);

        // 2) Write a line
        $logger->log('info', 'hello world', ['foo' => 123]);

        // 3) File exists and contains expected parts (level, date, message, context)
        expect(file_exists($file))->toBeTrue();

        $contents = file_get_contents($file);
        expect($contents)->toContain('INFO', 'hello world');
        // your formatter: "%level_name% | %datetime% > %message% | %context% %extra%"
        expect($contents)->toMatch('/INFO \\| \\d{4}-\\d{1,2}-\\d{1,2}, \\d{1,2}:\\d{2} (am|pm) > hello world \\|/');

        // 4) Context is present in some form
        expect($contents)->toContain('foo');
    });

    test('appends on subsequent writes', function () {

        $logger = new TestLogger();
        $file = $logger->log_path . 'pest-tests.log';

        // First write
        $logger->log('debug', 'first');
        $size1 = file_exists($file) ? filesize($file) : 0;

        // Second write
        $logger->log('debug', 'second');
        $size2 = filesize($file);

        expect($size2)->toBeGreaterThan($size1);

        $contents = file_get_contents($file);
        expect($contents)->toContain('first', 'second');
    });
});
