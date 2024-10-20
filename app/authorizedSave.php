<?php
/*
 * Saves records and sends back to Authorised Resource
 * Prevents resubmits
 */

namespace App;

use App\Models\ContactModel;
use App\Utilities;
use App\StorageClass;

ini_set('display_errors', 'On');
error_reporting(E_ALL);
require '../vendor/autoload.php';

require_once('ExtraFunctions.php');


//require_once('authorizedXero.php');

$message = "no API calls";

$pdo = Utilities::getPDO();

$action = $_GET['action'] ?? 0;

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

        }
        break;
    default:
        // do nothing
        echo $message;
}

debug($_GET);
exit;
header("Location: /authorizedResource.php?action={$action}&id={$id}");
