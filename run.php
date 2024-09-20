<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('utilities.php');
require_once('functions.php');

//require_once './models/ClicksendModel.php';
require_once './models/TemplateModel.php';

//TODO add security

$endpoint = filter_input(INPUT_GET, 'endpoint', FILTER_DEFAULT);

$action = array_key_exists('action', $_GET) ? intval($_GET['action']) : 0;
$form = array_key_exists('form', $_GET) ?? '';

switch ($endpoint) {
    //case 'sendSMS':

    //$sms = new ClicksendModel();
    //$sms->sendSMS('+64273711298', 'Sent from the website');
    //return true;

    case 'save':
        $pdo = getPDO();

        switch ($form) {
            case 'template':
            case 'Template':
                $template = new TemplateModel($pdo);
                $data = $_POST['payload'];
                var_dump($data);
                $data['dateupdate'] = date('Y-m-d H:i:s');
                $template->prepAndSave($data);
                exit;
        }
}
