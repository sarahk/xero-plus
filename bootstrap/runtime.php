<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load env for *runtime* (use .env if/when you add it)
$root = dirname(__DIR__);

if (!defined('CKM_TESTING_MODE')) {
// only enable test mode on Sarah's laptop
    $hostMatch = ($_SERVER['HTTP_HOST'] === "ckm.local:8890");
    $fileMatch = (substr($_SERVER["REQUEST_URI"], 0, 9) === '/json.php');

    define('CKM_TESTING_MODE', $hostMatch && $fileMatch);
    define('CKM_BYPASS_EXPIRY', $hostMatch && $fileMatch);
}

date_default_timezone_set('Pacific/Auckland'); // or whatever you prefer

// Optional: global guards/sanitisers common to web & CLI
error_reporting(E_ALL);
ini_set('display_errors', '0'); // typically off in prod; control via env


