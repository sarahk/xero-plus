<?php
/*
 * Saves records and sends back to Authorised Resource
 * Prevents resubmits
 */

namespace App;

use App\Models\ContactJoinModel;
use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\NoteModel;

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
            $data = $_POST['data'];

            // address needs to be duplicated
            $contact = new ContactModel($pdo);
            $contact_ids = $contact->prepAndSaveMany($data); // could be more than one record

            $contract = new ContractModel($pdo);
            $contract_id = $contract->prepAndSave($data);

            $contact_joins = new ContactJoinModel($pdo);
            $contact_joins->prepAndSaveMany('contract', $contract_id, $contact_ids);

            if (!empty($data['note']['note'])) {
                $note = new NoteModel($pdo);
                $data['note']['foreign_id'] = $contract_id;

                $note_id = $note->prepAndSave($data);
            }
            //ExtraFunctions::debug($_POST);
            //ExtraFunctions::debug($id);
            //output for the form
            echo json_encode([
                'contract_id' => $contract_id,
                'contact_ids' => $contact_ids,
                'note_id' => $note_id ?? ''
            ]);
            exit;

        }
        break;
    default:
        // do nothing
        echo $message;
}

ExtraFunctions::debug($_GET);
exit;
header("Location: /authorizedResource.php?action={$action}&id={$id}");
