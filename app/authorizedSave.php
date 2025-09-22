<?php
/*
 * Saves records and sends back to Authorised Resource
 * Prevents resubmits
 */

namespace App;

use App\Models\CabinModel;
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

$pdo = Utilities::getPDO();

// P O S T    *****
$action = $_POST['action'] ?? 0;
$data = $_POST['data'] ?? [];
$output = ['action' => $action, 'message' => 'no API calls'];

switch ($action) {

    case 10:
        // enquiries
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
        $output = array_merge($output,
            [
                'contract_id' => $contract_id,
                'contact_ids' => $contact_ids,
                'note_id' => $note_id ?? ''
            ]);
        $output['message'] = 'Contract saved';
        break;

    case 14:

        $cabin = new CabinModel($pdo);
        $id = $cabin->prepAndSave($data);
        $output['id'] = $id;
        $output['message'] = 'Cabin saved';
        break;

}
echo json_encode($output);