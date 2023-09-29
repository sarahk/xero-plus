<?php
/*
 * Saves records and sends back to Authorised Resource
 * Prevents resubmits
 */

ini_set('display_errors', 'On');
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
require_once('storage.php');
require_once('utilities.php');
require_once('functions.php');

require_once('models/ContactModel.php');
require_once('models/ContractModel.php');
require_once('models/NoteModel.php');
require_once('models/PhoneModel.php');


require_once('authorizedXero.php');

$message = "no API calls";

$pdo = getPDO();

$action = (array_key_exists('action', $_GET) ? $_GET['action'] : 0);

switch ($action) {

    case 10:
        // enquiries
        if (array_key_exists('data', $_GET)) {
            $data = $_GET['data'];

            // address needs to be duplicated

            $contact = new ContactModel($pdo);
            $id = $contact->prepAndSave($data);
            debug($_GET);

            debug($id);
            exit;
            $contact = new ContactModel($pdo);
            $id = $contact->prepAndSave($_GET['data']);
        }
        break;
    default:
        // do nothing
}

debug($_GET);
exit;
header("Location: /authorizedResource.php?action={$action}&id={$id}");
