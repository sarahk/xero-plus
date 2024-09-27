<?php

namespace App;

use App\Models\InvoiceModel;
use App\Models\TemplateModel;


error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../vendor/autoload.php';

//TODO add security

$endpoint = array_key_exists('endpoint', $_GET) ? $_GET['endpoint'] : '';
$action = array_key_exists('action', $_GET) ? intval($_GET['action']) : 0;

$form = array_key_exists('form', $_GET) ?? '';

$pdo = Utilities::getPDO();

switch ($endpoint) {
    //case 'sendSMS':

    //$sms = new ClicksendModel();
    //$sms->sendSMS('+64273711298', 'Sent from the website');
    //return true;

    case 'image':
        $imageType = array_key_exists('imageType', $_GET) ? $_GET['imageType'] : '';
        switch ($imageType) {
            case 'baddebt':
                $invoice = new InvoiceModel($pdo);
                $chartURL = $invoice->getChartURL($_GET['contract_id']);
                header('Location: ' . $chartURL);
                exit;
        }
        break;

    case 'save':


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
