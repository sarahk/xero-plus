<?php
namespace App;

use App\Models\CabinModel;
use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\InvoiceModel;
use App\Models\TasksModel;
use DateTime;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '../vendor/autoload.php';


$pdo = Utilities::getPDO();
$storage = new StorageClass();
$provider = Utilities::getProvider();
Utilities::checkAccessToken();
const LOGGEDOUT = false;

$message = "no API calls";

$action = intval($_GET['action'] ?? 0);
$debug = $_GET['debug'] ?? 0;
$id = intval($_GET['id'] ?? 0);

//$userId = $_SESSION['user']['user_id'];

$json = new JsonClass();
define("TENANCIES", $json->getOrganisationList());


switch ($action) {
    case 1:
        // Get Organisation details
        $xero = new XeroClass();
        $xeroTenantIdArray = $xero->getTenantIdArray();

        break;

    case 9:
    case 90:
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

        if ($debug) {
            //debug($_SESSION);

            ExtraFunctions::debug($data);
            exit;
        }
        break;

    case 100:
        // list contracts and enquiries
        // ajax data only
        break;

    case 11:
        // map of cabin locations
        // do nothing?

        break;

    case 12:
        // look at info related to an invoice

        /** @var TYPE_NAME $pdo */
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
        $cabin_id = $_GET['cabin_id'] ?? 0;
        if (!$cabin_id) {
            //reload the dashboard
            header('Location: authorizedResource.php');
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
        $modals = ['task-single.php', 'cabin-edit-basics.php'];
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

        break;
}

require_once(SITE_ROOT . '/Views/header.php');

$view = match ($action) {
    1 => 'Views/organisations_list.php',
    5 => 'Views/contacts_index.php',
    9 => 'Views/invoices_index.php',
    90 => 'Views/combo_index.php',
    10 => 'Views/enquiry-edit.php',
    100 => 'Views/contracts_index.php',
    11 => 'Views/cabin-locations.php',
    12 => 'Views/invoice_single.php',
    13 => 'Views/cabins-index.php',
    14 => 'Views/cabin-single.php',
    16 => 'Views/bad_debts_index.php',
    17 => 'Views/templates_index.php',
    200 => 'Views/home2.php',
    default => 'Views/home.php',
};

?>

<div>
    <?php
    include $view;
    ?>
</div>
<?php
require_once('Views/footer.php');
?>
