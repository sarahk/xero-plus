<?php
// tests/Unit/EnvTest.php
/*
 *
./vendor/bin/pest tests/Unit/EnvTest.php
 */
it('sees test env flags', function () {
    expect(getenv('APP_ENV'))->toBe('testing');
    expect((bool)filter_var(getenv('BYPASS_EXPIRY'), FILTER_VALIDATE_BOOLEAN))->toBeTrue();
});
