<?php

namespace App;

use App\Models\ActivityModel;
use App\Models\InvoiceModel;
use App\Models\NoteModel;
use App\Models\TemplateModel;


error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../vendor/autoload.php';

//TODO add security

$endpoint = $_GET['endpoint'] ?? $_POST['endpoint'] ?? '';
$action = $_GET['action'] ?? $_POST['action'] ?? 0;

$form = $_GET['form'] ?? $_POST['form'] ?? '';


$pdo = Utilities::getPDO();

switch ($endpoint) {
    //case 'sendSMS':

    //$sms = new ClicksendModel();
    //$sms->sendSMS('+64273711298', 'Sent from the website');
    //return true;
    case 'Activity':
    case 'activity':
        $activity = new ActivityModel($pdo);
        switch ($action) {

            case 'processSMSQueue':
                $activity->processQueue();
                break;

            case 'SaveManySMS':
                $payload = [
                    'sms_body' => $_POST['smsBody'],
                    'repeating_invoice_ids' => $_POST['repeatingInvoiceIds'],
                ];

                $activity->prepAndSaveMany($payload);
        }
        break;

    case 'image':
        $imageType = array_key_exists('imageType', $_GET) ? $_GET['imageType'] : '';
        switch ($imageType) {
            case 'baddebt':
                $invoice = new InvoiceModel($pdo);
                $chartURL = $invoice->getChartURL($_GET['contract_id'] ?? 0);
                header('Location: ' . $chartURL);
                exit;
        }
        break;

    case 'save':
    case 'Save':

        switch ($form) {
            

            case 'template':
            case 'Template':
                $data = $_POST['payload'];
                var_dump($data);
                $template = new TemplateModel($pdo);
                $template->prepAndSave($data);
                exit;
        }
        break;
    case 'MaterialTables':
        Utilities::refreshMaterialTables();
        break;
}
