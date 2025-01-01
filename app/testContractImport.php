<?php

namespace App;

use App\Models\ContractModel;
use Throwable;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

Utilities::checkAccessToken();


$xero = new XeroClass();
$auckland = 'ae75d056-4af7-484d-b709-94439130faa4';
$waikato = 'e95df930-c903-4c58-aee9-bbc21b78bde7';
$bop = 'eafd3b39-46c7-41e4-ba4e-6ea6685e39f7';


$xero = new XeroClass();
$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));

//$data = $xero->getXeroInvoices($auckland, date('Y-m-d H:i:s', strtotime('yesterday')));
//print_r($data);


//$xero->getInvoiceRefresh('auckland', $yesterday);

$json = new JsonClass();
//$data = json_decode($json->getContractList(Utilities::getPDO()), true);

//ExtraFunctions::debug($data['result']);
//ExtraFunctions::debugTable($data['result']);

//$xero->getSingleRepeatingInvoiceStub($auckland)

$sql = "select xerotenant_id,repeating_invoice_id, ckcontact_id,schedule_unit 
            from contracts 
            where schedule_unit is null 
            limit 50";
$contract = new ContractModel(Utilities::getPDO());
$result = $contract->testQuery($sql);

if (count($result) === 0) {
    echo '<h1>All done</h1>';
    exit;
}
for ($i = 0; $i < count($result); $i++) {
//    $repeating_invoice = $xero->getRepeatingInvoice($result[$i]['xerotenant_id'], $result[$i]['repeating_invoice_id']);
//    ExtraFunctions::debug($repeating_invoice);
//    $schedule = $xero->getScheduleFromXeroObject($repeating_invoice['schedule']);
//    ExtraFunctions::debug($schedule);
//
//    $result[$i]['schedule'] = $schedule;
//
    try {
        $stub = $xero->getSingleRepeatingInvoiceStub($result[$i]['xerotenant_id'], $result[$i]['repeating_invoice_id'], $result[$i]['ckcontact_id']);
        $result[$i]['raw_schedule_unit'] = $stub['raw_schedule_unit'];
        $result[$i]['schedule_unit'] = $stub['schedule_unit'];
        $result[$i]['saved'] = $xero->saveSingleRepeatingInvoiceStub($result[$i]['xerotenant_id'], $result[$i], $result[$i]['ckcontact_id']);
    } catch (Throwable $e) {
        //echo 'Message: ' . $e->getMessage();
        $result[$i]['raw_schedule_unit'] = '&nbsp;';
        $result[$i]['saved'] = $e->getCode();
    }
}

ExtraFunctions::debugTable($result);
