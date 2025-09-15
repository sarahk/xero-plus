<?php
declare(strict_types=1);

namespace Tests\Integration;


// ./vendor/bin/pest tests/Integration/ComboEndpointTest.php
it('returns DataTables-friendly JSON for Combo List', function () {

    // Send DataTables-like params (adjust if your endpoint expects search[value])
    $url = 'https://ckm.local:8890/json.php?endpoint=Combo&action=List&contract_id=78'
        . '&draw=1&start=0&length=10&order[0][column]=0&order[0][dir]=ASC&search=';

    // cURL with relaxed TLS for local/self-signed certs
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => false, // dev only
        CURLOPT_SSL_VERIFYHOST => false, // dev only
    ]);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($body === false || ($info['http_code'] ?? 0) === 0) {
        $this->markTestSkipped('Local endpoint not reachable: ' . ($err ?: 'unknown error'));
    }

    // Helpful assert message if non-200
    expect($info['http_code'] ?? 0)->toBe(200, 'HTTP ' . $info['http_code'] . ' Body: ' . substr((string)$body, 0, 300));

    // Content-Type should be JSON (helps catch HTML error pages)
    $ctype = strtolower((string)($info['content_type'] ?? ''));
    expect($ctype)->toContain('application/json');

    // Decode and show snippet on failure
    $json = json_decode((string)$body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $snippet = substr((string)$body, 0, 500);
        $this->fail('JSON decode error: ' . json_last_error_msg() . "\nFirst 500 bytes:\n" . $snippet);
    }

    expect($json)->toBeArray()->toHaveKeys(['data', 'draw', 'start', 'length', 'search', 'order']);
    expect($json['data'])->toBeArray();

    expect($json['draw'])->toBe('1');
    expect($json['start'])->toBe('0');
    expect($json['length'])->toBe('10');

    // Your API returns "search": "" (string). If you later switch to DataTables' object, tweak this.
    expect($json['search'])->toBe('');

    expect($json['order'])->toBeArray();
    expect($json['order'][0] ?? null)->toBeArray();
    expect((string)($json['order'][0]['column'] ?? ''))->toBe('0');
    expect(strtoupper((string)($json['order'][0]['dir'] ?? '')))->toBe('ASC');
})->group('integration');
