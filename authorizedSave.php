<?php
/*
 * Saves records and sends to Authorised Resource
 * Prevents resubmits
 */
require_once ('models/ContactModel.php');

$message = "no API calls";
$dbh = getDbh();
$action = filter_input(INPUT_GET, 'action');
$id = intval($_GET['id']);

switch ($action) {

    case 10:
        // enquiries
        if (array_key_exists('data', $_POST)) {
            $contact = new ContactModel();
            $id = $contact->prepAndSave($_POST['data']);
        }
        break;
    default:
        // do nothing
}
header("Location: authorizedResource?action={$action}&id={$id}");