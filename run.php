<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './models/ClicksendModel.php';

$endpoint = filter_input(INPUT_GET, 'endpoint', FILTER_DEFAULT);

$action = array_key_exists('action', $_GET) ? intval($_GET['action']) : 0;

switch ($endpoint) {
    case 'sendSMS':
        
        $sms = new ClicksendModel();
        $sms->sendSMS('+64273711298', 'Sent from the website');
        return true;
}
