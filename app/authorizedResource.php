<?php
namespace App;

use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\InvoiceModel;
use App\Models\UserModel;

use DateTime;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '../vendor/autoload.php';
require_once('functions.php');


$pdo = Utilities::getPDO();
$storage = new StorageClass();
$provider = Utilities::getProvider();
const LOGGEDOUT = false;

$message = "no API calls";

$action = array_key_exists('action', $_GET) ? intval($_GET['action']) : 0;
$debug = array_key_exists('debug', $_GET);
$id = (array_key_exists('id', $_GET)) ? intval($_GET['id']) : 0;

//$userId = $_SESSION['user']['user_id'];

$json = new JsonClass();
define("TENANCIES", $json->getOrganisationList());


switch ($action) {
    case 1:
        // Get Organisation details
        $xero = new XeroClass();
        $xeroTenantIdArray = $xero->getTenantIdArray();


        // https://api.xero.com/connections
//Authorization: "Bearer " + access_token
//Content-Type: application/json
        //     exit;
        break;

    case 2:
        // Create Contact
        try {
            $person = new XeroAPI\XeroPHP\Models\Accounting\ContactPerson;
            $person->setFirstName("John")
                ->setLastName("Smith")
                ->setEmailAddress("john.smith@24locks.com")
                ->setIncludeInEmails(true);

            $arr_persons = [];
            array_push($arr_persons, $person);

            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setName('FooBar')
                ->setFirstName("Foo")
                ->setLastName("Bar")
                ->setEmailAddress("ben.bowden@24locks.com")
                ->setContactPersons($arr_persons);

            $arr_contacts = [];
            array_push($arr_contacts, $contact);
            $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
            $contacts->setContacts($arr_contacts);

            $apiResponse = $apiInstance->createContacts($xeroTenantId, $contacts);
            $message = 'New Contact Name: ' . $apiResponse->getContacts()[0]->getName();
        } catch (ApiException $e) {
            $error = AccountingObjectSerializer::deserialize(
                $e->getResponseBody(),
                '\XeroAPI\XeroPHP\Models\Accounting\Error',
                []
            );
            $message = "ApiException - " . $error->getElements()[0]["validation_errors"][0]["message"];
        }
        break;

    case 3: // filter invoices
        $if_modified_since = new DateTime("2019-01-02T19:20:30+01:00"); // \DateTime | Only records created or modified since this timestamp will be returned
        $if_modified_since = null;
        $where = 'Type=="ACCREC"'; // string
        $where = null;
        $order = null; // string
        $ids = null; // string[] | Filter by a comma-separated list of Invoice Ids.
        $invoice_numbers = null; // string[] |  Filter by a comma-separated list of Invoice Numbers.
        $contact_ids = null; // string[] | Filter by a comma-separated list of ContactIDs.
        $statuses = array("DRAFT", "SUBMITTED");
        $statuses = [];
        $page = 1; // int | e.g. page=1 – Up to 100 invoices will be returned in a single API call with line items
        $include_archived = null; // bool | e.g. includeArchived=true - Contacts with a status of ARCHIVED will be included
        $created_by_my_app = null; // bool | When set to true you'll only retrieve Invoices created by your app
        $unitdp = null; // int | e.g. unitdp=4 – You can opt in to use four decimal places for unit amounts

        try {
            $apiResponse = $apiInstance->getInvoices($xeroTenantId, $if_modified_since, $where, $order, $ids, $invoice_numbers, $contact_ids, $statuses, $page, $include_archived, $created_by_my_app, $unitdp);
            if (count($apiResponse->getInvoices()) > 0) {
                $message = 'Total invoices found: ' . count($apiResponse->getInvoices());
                //var_export($apiResponse->getInvoices());
            } else {
                $message = "No invoices found matching filter criteria";
            }
        } catch (Exception $e) {
            echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
        }
        break;

    case 9:
        // don't actually need to do anything?
        break;
    case 4:
        // Create Multiple Contacts
        try {
            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setName('George Jetson')
                ->setFirstName("George")
                ->setLastName("Jetson")
                ->setEmailAddress("george.jetson@aol.com");

            // Add the same contact twice - the first one will succeed, but the
            // second contact will throw a validation error which we'll catch.
            $arr_contacts = [];
            array_push($arr_contacts, $contact);
            array_push($arr_contacts, $contact);
            $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
            $contacts->setContacts($arr_contacts);

            $apiResponse = $apiInstance->createContacts($xeroTenantId, $contacts, false);
            $message = 'First contacts created: ' . $apiResponse->getContacts()[0]->getName();

            if ($apiResponse->getContacts()[1]->getHasValidationErrors()) {
                $message = $message . '<br> Second contact validation error : ' . $apiResponse->getContacts()[1]->getValidationErrors()[0]["message"];
            }

        } catch (ApiException $e) {
            $error = AccountingObjectSerializer::deserialize(
                $e->getResponseBody(),
                '\XeroAPI\XeroPHP\Models\Accounting\Error',
                []
            );
            $message = "ApiException - " . $error->getElements()[0]["validation_errors"][0]["message"];
        }
        break;


    case 10:
        // enquiries
        $contacts = new ContactModel($pdo);
        $data = $contacts->get('id', $id);

        $contracts = new ContractModel($pdo);

        $users = new UserModel($pdo);
        $user = $users->get('user_id', $_SESSION['xero_user_id']);

        $data['contracts'] = $contracts->getChildren('contacts', $id);

        if ($debug) {
            debug($_SESSION);
            debug($data);
            exit;
        }
        break;

    case 11:
        // map of cabin locations
        // do nothing?

        break;

    case 12:
        // look at info related to an invoice
        $invoice = new InvoiceModel($pdo);
        $data = $invoice->get('invoice_id', $_GET['invoice_id'])['invoices'];
        $contact = new ContactModel($pdo);
        $data['contact']['id'] = $contact->field('id', 'contact_id', $data['contact_id']);
        break;

    case 13:
        // list of cabins
        $modals = ['cabin-single.php'];
        break;

    case 14:
        // single cabin record
        $cabin = new CabinModel($pdo);
        $data = $cabin->get('cabin_id', $_GET['cabin_id'], false);

        //$contracts = new ContractModel($pdo);
        $data['contracts'] = $cabin->getCurrentContract($_GET['cabin_id']);
        $contacts = new ContactModel($pdo);
        $contact = $contacts->get('id', $data['contracts']['ckcontact_id'], false);

        // contact will have a notes child record, we let the cabin notes overwrite it
        $data = array_merge($contact, $data);

        $tasks = new TasksModel($pdo);
        $data['tasks'] = $tasks->getChildren('cabins', $_GET['cabin_id'], false);
        $modals = ['task-single.php'];
        break;

    case 15:
        // single task record
        break;

    case 16:
        // bad debts management
        break;

    case 17:
        // Message Templates
        break;

    default:
        // main dash
        // TODO
        // replace dummy data with real data
        $stock = [];
        $start = new DateTime(date('Y-m-d', strtotime('Monday last week'))); // Your start date
        $end = new DateTime(date('Y-m-d', strtotime('Friday next week')));   // Your end date
        $today = date('Y-m-d');

        for ($date = $start; $date <= $end; $date->modify('+1 day')) {
            $vals = [];
            foreach (json_decode(TENANCIES, true) as $row) {
                if ($row['active']) {
                    $vals[] = [
                        'shortname' => $row['shortname'],
                        'data' => ['1', '2', '3'],
                        'colour' => $row['colour']
                    ];
                }
            }
            if ($date->format('Y-m-d') === $today)
                $stock[] = [
                    'label' => 'Today',
                    'vals' => $vals,
                    'id' => 'today'
                ];
            else
                $stock[] = [
                    'label' => $date->format('D j') . '<sup>' . $date->format('S') . '</sup>',
                    'vals' => $vals,
                    'id' => $date->format('Ymd')
                ];
        }

        break;
}

require_once(SITE_ROOT . '/Views/header.php');

?>

<div>
    <?php

    switch ($action) {
        case 1:
            include 'Views/organisations_list.php';
            break;
        case 5:
            include 'Views/contacts_index.php';
            break;
        case 9:
            include 'Views/invoices_index.php';
            break;
        case 10:
            include 'Views/enquiry-edit.php';
            break;
        case 11:
            include 'Views/cabin-locations.php';
            break;
        case 12:
            include 'Views/invoice_single.php';
            break;
        case 13:
            include 'Views/cabins-index.php';
            break;
        case 14:
            include 'Views/forms/cabin-edit.php';
            break;
        case 16:
            include 'Views/bad_debts_index.php';
            break;
        case 17:
            include 'Views/templates_index.php';
            break;
        default:
            include 'Views/home.php';
            break;
    }
    ?>
</div>
<?php
require_once('Views/footer.php');
?>
