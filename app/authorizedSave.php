<?php
/*
 * Saves records and sends back to Authorized Resource
 * Prevents resubmits
 */

namespace App;

use App\Classes\Utilities;
use App\Models\CabinModel;
use App\Models\ContactJoinModel;
use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\NoteModel;
use App\Models\TasksModel;


ini_set('display_errors', 'On');
error_reporting(E_ALL);
// if this script returns anything, it'll be json
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json; charset=utf-8');


require '../vendor/autoload.php';

Utilities::checkAccessToken();

$pdo = Utilities::getPDO();

// P O S T    *****
// this is a workaround to a task being saved from the sidebar
// todo find a better way to do this
$formType = $_POST['formType'] ?? 'unknown';
$action = $formType === 'task' ? 14 : (int)$_POST['action'] ?? 0;
$data = $_POST['data'] ?? [];
$output = ['action' => $action, 'formType' => $formType, 'message' => 'no API calls for action'];

switch ($action) {
    case 0:
        // dashboard

        $task = new TasksModel($pdo);
        $output['result'] = $task->prepAndSave($data);
        $output['message'] = 'Task saved';
        break;

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
        switch ($_POST['formType']) {
            case 'cabin':
                $cabin = new CabinModel($pdo);
                $id = $cabin->prepAndSave($data);
                $output['id'] = $id;
                $output['message'] = 'Cabin saved';
                break;
            case 'notes':
                $note = new NoteModel($pdo);
                $output['result'] = $note->prepAndSave($data);
                $output['message'] = 'Note saved';
                break;
            case 'task':
                $task = new TasksModel($pdo);
                var_dump($_POST);
                $quick_close = (array_key_exists('quick_close', $_POST) && $_POST['quick_close'] === 'true');
                var_dump($quick_close);
                if ($quick_close) {
                    $output['result'] = $task->prepAndUpdate($data);
                } else {
                    $output['result'] = $task->prepAndSave($data);
                }
                $output['message'] = 'Task saved';
        }
}
echo json_encode($output);