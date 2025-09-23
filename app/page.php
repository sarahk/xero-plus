<?php
declare(strict_types=1);
namespace App;

use App\Models\CabinModel;
use App\Models\ComboModel;
use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\InvoiceModel;
use App\Models\TasksModel;
use DateTime;

//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

//require_once '../vendor/autoload.php';
//Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

require __DIR__ . '/../bootstrap/runtime.php';

//var_dump($_GET);
//exit;


$pdo = Utilities::getPDO();
$storage = new StorageClass();
$storage->saveUrl($_SERVER['REQUEST_URI']);
$provider = Utilities::getProvider();

// if not logged in, will show the login button
Utilities::checkAccessToken();
const LOGGEDOUT = false;

$message = "no API calls";

$action = intval($_GET['action'] ?? 0);
$debug = $_GET['debug'] ?? 0;
$id = intval($_GET['id'] ?? 0);

//$userId = $_SESSION['user']['user_id'];

$json = new JsonClass();
define('TENANCIES', $json->getOrganisationList());
unset($json);

// manages modals, javascript and css
$loader = new Loader();


switch ($action) {
    case 1:
        // Get Organisation details
        $xero = new XeroClass();
        $xeroTenantIdArray = $xero->getTenantIdArray();

        break;

    case 9: // invoice_index
    case 90: // combo_index

        // don't actually need to do anything?
        break;


    case 10:
        // enquiries
        //$contacts = new ContactModel($pdo);
        //$data = $contacts->get('id', $id);
        //$data['contact'] = $data['contacts'];

        //$users = new UserModel($pdo);
        //$xeroUserId = $_SESSION['xero_user_id'] ?? 0;
//        if (!$xeroUserId) {
//            header('/');
//        }
//        $user = $users->get('user_id', $_SESSION['xero_user_id']);

        $contracts = new ContractModel($pdo);
        $contract_id = $_GET['contract_id'] ?? 0;

        $raw = $contracts->get('contract_id', $contract_id);

        $data['Contract'] = $raw['contracts'];

        $data['Contact'] = $contracts->getContactsAndPhone($contract_id);

        $keys = ['contract' => ['contract_id' => $contract_id]];

        // we're opening in enquiry mode, but this takes us to the overview mode

        $link_to_contract = $contracts->getContractOverviewLink('91', ['contract_id' => $contract_id], 'btn btn-secondary');
        $loader->addGoogleMaps();
        if ($debug) {
            //debug($_SESSION);

            ExtraFunctions::debug($data);
            exit;
        }
        break;

    case 100:
        // list contracts and enquiries
        // ajax data only
    case 11:
        // map of cabin locations
        // do nothing?
        break;

    case 12:
        // look at info related to an invoice or payment

        $invoice = new InvoiceModel($pdo);
        $invoice_id = $_GET['invoice_id'] ?? 0;

        $data = $invoice->get('invoice_id', $invoice_id);
        $contact = new ContactModel($pdo);
        $data['contact']['id'] = $contact->field('id', 'contact_id', $data['invoices']['contact_id']);

        $keys = [
            'invoice' => [
                'invoice_id' => $data['invoices']['invoice_id'] ?? 0,
                'repeating_invoice_id' => $data['invoices']['repeating_invoice_id'] ?? 0,
                'contract_id' => $data['invoices']['contract_id'] ?? 0,
            ],
            'contact' => [
                'id' => $data['contacts']['ckcontact_id'] ?? 0,
                'contact_id' => $data['contacts']['ckcontact_id'] ?? 0,
            ]];

        unset($invoice);
        unset($contact);
        break;

    case 13:
        // list of cabins
        $loader->addModal('cabin-single.php');
        break;

    case 14:
        // single cabin record
        $cabin = new CabinModel($pdo);
        $cabin_id = $_GET['cabin_id'] ?? 0;
        if (!$cabin_id) {
            //reload the dashboard
            header('Location: page.php');
            exit;
        }


        $data = $cabin->get('cabin_id', $cabin_id, false);

        //$contracts = new ContractModel($pdo);
        $data['contracts'] = $cabin->getCurrentContract($cabin_id);
        if (count($data['contracts']) > 0) {
            $contacts = new ContactModel($pdo);
            $contact = $contacts->get('id', $data['contracts']['ckcontact_id'], false);

            // contact will have a notes child record, we let the cabin notes overwrite it
            $data = array_merge($contact, $data);

        }

        $tasks = new TasksModel($pdo);
        $data['tasks'] = $tasks->getChildren('cabins', $_GET['cabin_id'], false);

        $loader->addModal('task-single.php');
        $loader->addModal('cabin-edit-basics.php');
        $loader->addJS('/JS/Widgets/notesTabAccordionWidget.js');
        break;

    case 15:
        // single task record
    case 16:
        // bad debts management
        break;
    case 17:
        // Message Templates
        $loader->addTinyMCE();
        $loader->addModal('template-single.php');
        break;
    case 18:
        // activity
        break;

    case 91:
        // contract single
        // set up some keys for the javascript to run off
        $keys = [
            'invoice' => [
                'invoice_id' => $_GET['invoice_id'] ?? 0
            ],
            'contact' => [
                'id' => $_GET['ckcontact_id'] ?? 0,
                'contact_id' => $_GET['contact_id'] ?? 0,
                'name' => $_GET['contact_name'] ?? '',
            ],
            'contract' => [
                'contract_id' => $_GET['contract_id'] ?? 0
            ]
        ];

        $data = $_GET;
        $contract = new ContractModel(Utilities::getPDO());

        // a wee bit of data cleaning
        if (empty($keys['contact']['id']) & !empty($keys['contract']['contract_id'])) {
            $keys['contact']['id'] = $contract->field('ckcontact_id', 'contract_id', $keys['contract']['contract_id']);
        }
        if (empty($keys['contract']['repeating_invoice_id']) && $_GET['contract_id'] ?? 0 > 0) {
            $keys['contract']['repeating_invoice_id'] = $contract->field('repeating_invoice_id', 'contract_id', $_GET['contract_id']);
        }
        $link_to_enquiry = $contract->getContractOverviewLink('10', array_merge($keys['contact'], $keys['contract']), 'btn btn-secondary');

        unset($contract);
        break;
    case 200:
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
        $loader->addSlickSlider();
        //$loader->addOwlCarousel();

        break;
}

require_once(__DIR__ . '/Views/header.php');

$view = __DIR__ . '/' . match ($action) {
        1 => 'Views/organisations_list.php',
        5 => 'Views/contacts_index.php',
        9 => 'Views/invoices_index.php',
        90 => 'Views/combo-index.php',
        91 => 'Views/contract_single.php',
        10 => 'Views/enquiry-edit.php',
        100 => 'Views/contracts_index.php',
        11 => 'Views/cabin-locations.php',
        12 => 'Views/invoice_single.php',
        13 => 'Views/cabins-index.php',
        14 => 'Views/cabin-single.php',
        16 => 'Views/bad_debts_index.php',
        160 => 'Views/bad_debts_management.php',
        17 => 'Views/templates-index.php',
        18 => 'Views/activity-index.php',
        200 => 'Views/home2.php',
        default => 'Views/home.php',
    };

?>

<div>
    <?php
    require $view;
    ?>
</div>
<?php
require_once 'Views/footer.php';
?>
