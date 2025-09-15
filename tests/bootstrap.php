<?php
declare(strict_types=1);

// 1) Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// 2) Load .env.testing from the project root (…/CKM/.env.testing)
$root = dirname(__DIR__);


// 3) Mark test mode (your code can check these)
define('CKM_TESTING_MODE', true);
define('CKM_BYPASS_EXPIRY', true);

// 4) Sensible test defaults
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Pacific/Auckland'); // or your preferred TZ
