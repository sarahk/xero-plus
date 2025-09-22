<?php
declare(strict_types=1);

use App\Models\CabinModel;
use App\Utilities;
use Tests\Integration\TestLogger;

// ./vendor/bin/pest tests/Feature/CabinSaveTest.php -v --stop-on-failure
//
// /Applications/MAMP/bin/php/php8.4.1/bin/php ./vendor/bin/pest tests/Feature/CabinSaveTest.php -v
// anything using PDO will fail if we aren't using MAMP's version of PHP because that connects to the MySQL database
test('Utilities::getPDO returns PDO', function () {
    $logger = new TestLogger();
    $pdo = Utilities::getPDO();                 // with use
    // $pdo = \App\Utilities::getPDO();         // fully qualified alternative
    expect($pdo)->toBeInstanceOf(PDO::class);
});


test('updates a cabin and returns its id', function () {
    expect(true)->toBeTrue();
    session_start();
    $logger = new TestLogger();
    $logger->log('Info', 'CabinSaveTest');


    $pdo = Utilities::getPDO();
    expect($pdo->getAttribute(PDO::ATTR_DRIVER_NAME))->toBe('mysql');
    $model = new CabinModel($pdo);


    $cabin_number = '103';
    $data = [
        'action' => '14',
        'cabin_id' => '2',
        'cabinnumber' => $cabin_number,
        'cabinstyle' => 'std-right',
        'cabinstylecurrent' => '',
        'cabinstatus' => 'active',
        'cabinstatuscurrent' => '',
        'xerotenant_id' => 'ae75d056-4af7-484d-b709-94439130faa4',
        'owner' => '',
    ];

    expect($data)->toBeArray();

    $id = intval($model->prepAndSave($data));

    expect($id)->toBeInt()->toBeGreaterThan(0);

    $stmt = $pdo->prepare('SELECT cabinnumber, `style`, `status` FROM cabins WHERE cabin_id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    expect($row)->not->toBeFalse();
    expect($row['cabinnumber'])->toBe($cabin_number);;

    // cleanup if you’re not wrapping in a transaction
    //$pdo->prepare('DELETE FROM cabins WHERE id = :id')->execute(['id' => $id]);
});
