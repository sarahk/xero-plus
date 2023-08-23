<?php
ini_set('display_errors', 'On');
require __DIR__ . '/vendor/autoload.php';
require_once('storage.php');
require_once('utilities.php');
require_once('functions.php');


$dbh = getDbh();


require_once('views/header.php');
require_once('views/menu.php');


if (isset($_GET['action'])) {
    $action = intval(($_GET['action']));
    switch ($action) {
        case 2: 
            $vehicles = getVehicleList($dbh);
            include 'views/vehicle_log_add.php';
            break;
        case 1:
        default:
            include 'views/vehicle_index.php';
            break;
    }
}


require_once('views/footer.php');
?>