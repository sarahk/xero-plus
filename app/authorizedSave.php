<?php
/*
 * Saves records and sends back to Authorised Resource
 * Prevents resubmits
 */

namespace App;

use App\Models\ContractModel;

ini_set('display_errors', 'On');
error_reporting(E_ALL);
// if this script returns anything, it'll be json
header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");


require '../vendor/autoload.php';

Utilities::checkAccessToken();

$message = "no API calls";

$pdo = Utilities::getPDO();

// P O S T    *****
$action = $_POST['action'] ?? 0;

switch ($action) {

    case 10:
        // enquiries
        if (array_key_exists('data', $_POST)) {
            $data = $_POST['data']['data'];

            // address needs to be duplicated
            $contract = new ContractModel($pdo);
            $contract_id = $contract->prepAndSave($data);
            //ExtraFunctions::debug($_POST);
            //ExtraFunctions::debug($id);
            //output for the form
            echo json_encode([
                'contract_id' => $contract_id
            ]);
            exit;

        }
        break;
    default:
        // do nothing
        echo $message;
}

debug($_GET);
exit;
header("Location: /authorizedResource.php?action={$action}&id={$id}");
