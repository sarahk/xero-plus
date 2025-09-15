<?php
/*
 * Handles local database requests and returns json
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/runtime.php';

use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\NoteModel;
use App\Models\Query\ActivityQueryModel;
use App\Models\Query\BadDebtsManagementModel;
use App\Models\Query\BadDebtsReminderModel;
use App\Models\Query\HomeWatchList;
use App\StorageClass;
use App\JsonClass;
use App\Models\TemplateModel;
use \XeroAPI\XeroPHP\Configuration;
use \XeroAPI\XeroPHP\Api\AccountingApi;
use App\Utilities;

header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");

//require_once('CKMProvider.php');

$provider = Utilities::getProvider();
// Storage Class uses sessions for storing token
$storage = new StorageClass();
$xeroTenantId = (string)$storage->getSession()['tenant_id'] ?? '';

// Check if Access Token is expired
// if so - refresh token
if ($storage->hasExpired()) {
    //$provider = Utilities::getProvider();

    $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $storage->getRefreshToken()
    ]);
    // Save my token, expiration and refresh token
    $storage->setToken(
        $newAccessToken->getToken(), $newAccessToken->getExpires(),
        $xeroTenantId, $newAccessToken->getRefreshToken(), $newAccessToken->getValues()["id_token"]
    );
}

//$config = \XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);
//
//$config->setHost("https://api.xero.com/api.xro/2.0");
//
//$apiInstance = new \XeroAPI\XeroPHP\Api\AccountingApi(
//    new \GuzzleHttp\Client(),
//    $config
//);

// ALL methods are demonstrated using this class
//$json = new JsonClass($apiInstance, $xeroTenantId);
$json = new JsonClass();
//$json = new JsonClass();
//$json->setup($apiInstance);

$endpoint = $_GET['endpoint'] ?? 'Accounts';
$action = $_GET['action'] ?? 'none';

$loadGet = ["xero-php-oauth2-app", "disconnect.php", "json.php", $endpoint, $action];

try {
    switch ($endpoint) {
        case "Cabins":
            switch ($action) {
                case "Read":
                    echo $json->getCabins();
                    break;
                case 'Single':
                    echo $json->getCabinSingle();
                    break;
                case 'Enquiry':
                    echo $json->getEnquiryCabinList();
            }
            break;

        case 'Activity':
        case 'activity':
            switch ($action) {
                case 'List':
                case 'list':
                    $activity = new ActivityQueryModel();
                    echo $activity->list();
                    //echo $json->getActivityList();
                    break;
            }
            break;

        case 'Combo':
        case 'combo':
            switch ($action) {
                case 'List':
                case 'list':
                    echo $json->getComboList();
                    break;


            }
            break;

        case 'Enums':
            // The name of the enum class in a variable
            switch ($action) {
                case 'getAllAsArray':

                    $fullEnumName = "App\\Models\\Enums\\{$_GET['enumClass']}";
                    $output = $fullEnumName::getAllAsArray();
                    echo json_encode($output);

            }
            break;

        case 'Notes':
        case 'notes':
            switch ($action) {
                case 'ListAssociated':
                    $note = new NoteModel(Utilities::getPDO());
                    echo json_encode($note->listAssociated(Utilities::getParams()));
                    break;
            }
            break;

        case 'Xero':
        case 'xero':
            echo json_encode(['result' => Utilities::refreshAccessTokenJs()]);

            break;

        case "Accounts":
            switch ($action) {
                case "Create":
                    echo $json->createAccount($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getAccount($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateAccount($xeroTenantId, $apiInstance);
                    break;

                case "Archive":
                    echo $json->archiveAccount($xeroTenantId, $apiInstance);
                    break;
                case "Attachment":
                    echo $json->attachmentAccount($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;


        case "Contacts":
        case 'contacts':
            switch ($action) {
                case 'Refresh':
                case 'refresh':
                    $json->getContactRefresh('is this being used?');
                    break;
                case 'RefreshSingle':
                case 'refreshSingle':
                    $contact_id = $_GET['contact_id'];

                    echo $json->getRefreshContactSingle($xeroTenantId, $contact_id);
                    break;
                case "Create":
                    echo $json->createContact($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createContacts($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                case 'List':
                    echo $json->getContactList();
                    break;
                case "Update":
                    echo $json->updateContact($xeroTenantId, $apiInstance);
                    break;
                case "Archive":
                    echo $json->archiveContact($xeroTenantId, $apiInstance);
                    break;
                case 'Single':
                    // gets from xero
                    echo $json->getContactSingle();
                    break;
                case 'Singleton':
                case 'singleton':
                    //gets locally
                    echo $json->getContactSingleton();
                    break;
                case 'Search':
                case 'search';
                    echo $json->getSearchContacts();
                    break;
                case 'Field':
                case 'field':
                    echo $json->getField('ContactModel');
                    break;

                case 'getOtherContacts':
                    $contact = new ContactModel(Utilities::getPDO());
                    echo json_encode($contact->getOtherContacts(Utilities::getParamsPlus(['ckcontact_id'])));
                    break;
                default:
                    echo json_encode("[{$endpoint}] {$action}: action not supported in API");
            }
            break;

        case "ContactGroups":
            switch ($action) {
                case "Create":
                    echo $json->createContactGroup($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getContactGroup($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateContactGroup($xeroTenantId, $apiInstance);
                    break;
                case "Archive":
                    echo $json->archiveContactGroup($xeroTenantId, $apiInstance);
                    break;

                case "AddContact":
                    echo $json->createContactGroupContacts($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case 'Contracts':
        case 'contracts':
            switch ($action) {
                case 'List':
                case 'list':

                    echo $json->getContractList();
                    break;

                case 'Singleton':
                case 'singleton':
                    echo $json->getContractSingleton();
                    break;

                case 'Summary':
                case 'summary':
                    echo $json->getInvoiceSummary();
                    break;

                case 'otherContracts':
                    $contracts = new ContractModel(Utilities::getPDO());
                    echo json_encode($contracts->getOtherContracts(Utilities::getParamsPlus(['ckcontact_id'])));
                    break;

                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;


        case "Invoices":
        case 'invoices':
            switch ($action) {

                case "Create":
                    echo $json->createInvoice($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createInvoices($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                case 'read':
                case 'List':
                case 'list':
                case 'Contract':
                case 'contract':
                    echo $json->getInvoiceList();
                    break;
                case 'BadDebts':
                    $objBDR = new BadDebtsReminderModel();
                    echo $objBDR->list();
                    break;
                case 'HomeWatchList':
                    $objHWL = new HomeWatchList();
                    echo $objHWL->list();
                    break;
                case 'BadDebtsManagement':
                    $objBDM = new BadDebtsManagementModel();
                    echo $objBDM->list();
                    break;

                case 'BadDebtTotal':
                    echo $json->getBadDebtTotal();
                    break;

                case "Update":
                    echo $json->updateInvoice($xeroTenantId, $apiInstance);
                    break;

                case "Void":
                    echo $json->voidInvoice($xeroTenantId, $apiInstance);
                    break;


                default:
                    echo json_encode($action . " action not supported in API [Invoices]");
            }
            break;

        case "InvoiceReminders":
            switch ($action) {
                case "Read":
                    echo $json->getInvoiceReminder($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;


        case "Organisations":
        case 'organisations':
            switch ($action) {
                case "Read":
                    echo $json->getOrganisation($xeroTenantId, $apiInstance);
                    break;
                case 'List':
                case 'list':
                    echo $json->getOrganisationList();
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;


        case "Payments":
            switch ($action) {
                case "List":
                case 'list':
                    echo $json->getPaymentsList();
                    break;

                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case 'Phones':
            switch ($action) {
                case 'List':
                case 'list':
                    echo $json->listPhonesForContact();
                    break;

                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case "Prepayments":
            switch ($action) {
                case "Read":
                    echo $json->getPrepayment($xeroTenantId, $apiInstance);
                    break;
                case "Create":
                    echo $json->createPrepayment($xeroTenantId, $apiInstance);
                    break;
                case "Allocate":
                    echo $json->allocatePrepayment($xeroTenantId, $apiInstance);
                    break;
                case "Refund":
                    echo $json->refundPrepayment($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case "PurchaseOrders":
            switch ($action) {
                case "Create":
                    echo $json->createPurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createPurchaseOrders($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getPurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updatePurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                case "Delete":
                    echo $json->deletePurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case "Receipts":
            switch ($action) {
                case "Create":
                    echo $json->createReceipt($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getReceipt($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateReceipt($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case "RepeatingInvoices":
            switch ($action) {
                case "Read":
                    echo $json->getRepeatingInvoice($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case "Reports":
            switch ($action) {
                case "TenNinetyNine":
                    echo $json->getTenNinetyNine($xeroTenantId, $apiInstance);
                    break;
                case "AgedPayablesByContact":
                    echo $json->getAgedPayablesByContact($xeroTenantId, $apiInstance);
                    break;
                case "AgedReceivablesByContact":
                    echo $json->getAgedReceivablesByContact($xeroTenantId, $apiInstance);
                    break;
                case "BalanceSheet":
                    echo $json->getBalanceSheet($xeroTenantId, $apiInstance);
                    break;
                case "BankStatement":
                    echo $json->getBankStatement($xeroTenantId, $apiInstance);
                    break;
                case "BankSummary":
                    echo $json->getBankSummary($xeroTenantId, $apiInstance);
                    break;
                case "BudgetSummary":
                    echo $json->getBudgetSummary($xeroTenantId, $apiInstance);
                    break;
                case "ExecutiveSummary":
                    echo $json->getExecutiveSummary($xeroTenantId, $apiInstance);
                    break;
                case "ProfitAndLoss":
                    echo $json->getProfitAndLoss($xeroTenantId, $apiInstance);
                    break;
                case "TrialBalance":
                    echo $json->getTrialBalance($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        case 'Tasks':
            switch ($action) {
                case 'Close':
                    echo $json->closeTask();
                    break;
                case 'List':
                    echo $json->ListTasksForCabin();
                    break;
                case 'ListHome':
                    echo $json->ListTasks('home');
                    break;
                case 'Single':
                    echo $json->getTaskSingle();
                    break;
                case 'Counts':
                    echo $json->getTaskCounts();
                    break;
            }
            break;

        case 'Templates':
        {
            switch ($action) {
                case 'List':
                case 'list':
                    echo $json->getTemplateList();
                    break;

                case 'Single':
                case 'single':
                    echo $json->getTemplate(intval($_GET['id']) ?? 0);
                    break;

                case 'Save':
                case 'save':
                    echo $json->saveTemplate();
            }
            break;
        }

        case "Vehicles":
            switch ($action) {
                case 'Read':
                default:
                    echo $json->getVehiclesLogList();
                    break;
            }
            break;


        case "Users":
            switch ($action) {
                case "Read":
                    echo $json->getUser($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo json_encode($action . " action not supported in API");
            }
            break;

        default:
            echo json_encode("Action $action is not recognised");
    }
} catch (Exception $e) {
    echo json_encode('Exception when calling AccountingApi: ', $e->getMessage(), PHP_EOL);
}
