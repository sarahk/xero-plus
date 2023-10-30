<?php
/*
 * Handles local database requests and returns json
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");

//$path = '/var/www/vhosts/caravanforhire.co.nz/httpdocs/git/xero/vendor/autoload.php';
$path = 'vendor/autoload.php';
require $path;

require_once('storage.php');
require_once('JsonClass.php');
require_once('config.php');
require_once('utilities.php');

// Storage Class uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();


$xeroTenantId = (string)$storage->getSession()['tenant_id'];

// Check if Access Token is expired
// if so - refresh token
if ($storage->getHasExpired()) {
    $provider = getProvider();

    $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $storage->getRefreshToken()
    ]);
    // Save my token, expiration and refresh token
    $storage->setToken(
        $newAccessToken->getToken(), $newAccessToken->getExpires(),
        $xeroTenantId, $newAccessToken->getRefreshToken(), $newAccessToken->getValues()["id_token"]
    );
}

$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);

$config->setHost("https://api.xero.com/api.xro/2.0");

$apiInstance = new XeroAPI\XeroPHP\Api\AccountingApi(
    new GuzzleHttp\Client(),
    $config
);

// ALL methods are demonstrated using this class
$json = new JsonClass($apiInstance, $xeroTenantId);
//$json = new JsonClass();
//$json->setup($apiInstance);

$endpoint = filter_input(INPUT_GET, 'endpoint', FILTER_DEFAULT);
$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT);
if (is_null($endpoint)) {
    $endpoint = 'Accounts';
}
if (is_null($action)) {
    $action = 'none';
}


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

            }
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
                    echo $action . " action not supported in API";
            }
            break;


        case "Contacts":
        case 'contacts':
            switch ($action) {
                case 'Refresh':
                case 'refresh':
                    $json->getContactRefresh();
                    break;
                case "Create":
                    echo $json->createContact($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createContacts($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getContact($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateContact($xeroTenantId, $apiInstance);
                    break;
                case "Archive":
                    echo $json->archiveContact($xeroTenantId, $apiInstance);
                    break;
                case 'Single':
                    echo $json->getContactSingle();
                case 'Search':
                case 'search';
                    echo $json->getSearchContacts();
                    break;
                default:
                    echo "[{$endpoint}] {$action}: action not supported in API";
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
                    echo $action . " action not supported in API";
            }
            break;

        case "CreditNotes":
            switch ($action) {
                case "Create":
                    echo $json->createCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createCreditNotes($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "Allocate":
                    echo $json->allocateCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "Refund":
                    echo $json->refundCreditNote($xeroTenantId, $apiInstance);
                    break;

                case "Void":
                    echo $json->voidCreditNote($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "ExpenseClaims":
            switch ($action) {
                case "Create":
                    echo $json->createExpenseClaim($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getExpenseClaim($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateExpenseClaim($xeroTenantId, $apiInstance);
                    //echo $action . " action is supported in API but not SDK (no setStatus)";
                    break;
                default:
                    echo $action . " action not supported in API";
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
                    echo $json->getInvoiceList();
                    break;
                case 'Contract':
                case 'contract':
                    echo $json->getInvoiceList();
                    break;
                case "Update":
                    echo $json->updateInvoice($xeroTenantId, $apiInstance);
                    break;

                case "Void":
                    echo $json->voidInvoice($xeroTenantId, $apiInstance);
                    break;

                default:
                    echo $action . " action not supported in API [Invoices]";
            }
            break;

        case "InvoiceReminders":
            switch ($action) {
                case "Read":
                    echo $json->getInvoiceReminder($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Items":
            switch ($action) {
                case "Create":
                    echo $json->createItem($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createItems($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getItem($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateItem($xeroTenantId, $apiInstance);
                    break;

                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Journals":
            switch ($action) {
                case "Read":
                    echo $json->getJournal($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "LinkedTransactions":
            switch ($action) {
                case "Create":
                    echo $json->createLinkedTransaction($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getLinkedTransaction($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateLinkedTransaction($xeroTenantId, $apiInstance);
                    break;

                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "ManualJournals":
            switch ($action) {
                case "Create":
                    echo $json->createManualJournal($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createManualJournals($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getManualJournal($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $json->updateManualJournal($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
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
                    echo $action . " action not supported in API";
            }
            break;

        case "Overpayments":
            switch ($action) {
                case "Read":
                    echo $json->getOverpayment($xeroTenantId, $apiInstance);
                    break;
                case "Create":
                    echo $json->createOverpayment($xeroTenantId, $apiInstance);
                    break;
                case "Allocate":
                    echo $json->allocateOverpayment($xeroTenantId, $apiInstance);
                    break;
                case "AllocateMulti":
                    echo $json->allocateOverpayments($xeroTenantId, $apiInstance);
                    break;
                case "Refund":
                    echo $json->refundOverpayment($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Payments":
            switch ($action) {
                case "Create":
                    echo $json->createPayment($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $json->createPayments($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $json->getPayment($xeroTenantId, $apiInstance);
                    break;
                case "Delete":
                    echo $json->deletePayment($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
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
                    echo $action . " action not supported in API";
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
                    echo $action . " action not supported in API";
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
                    echo $action . " action not supported in API";
            }
            break;

        case "RepeatingInvoices":
            switch ($action) {
                case "Read":
                    echo $json->getRepeatingInvoice($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
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
                    echo $action . " action not supported in API";
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
                case 'Single':
                    echo $json->getTaskSingle();
            }
            break;

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
                    echo $action . " action not supported in API";
            }
            break;
    }
} catch (Exception $e) {
    echo 'Exception when calling AccountingApi: ', $e->getMessage(), PHP_EOL;
}
