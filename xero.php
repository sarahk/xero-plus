<?php
/*
 * Handles direct calls to Xero
 * Does not return json - saves the information into the mysql database
 *
 * TODO
 * Remove unused functions
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//$path = '/var/www/vhosts/caravanforhire.co.nz/httpdocs/git/xero/vendor/autoload.php';
$path = 'vendor/autoload.php';
require $path;

require_once('storage.php');
require_once('XeroClass.php');
require_once('config.php');
require_once('utilities.php');

// Storage Class uses sessions for storing token > extend to your DB of choice
$storage = new StorageClass();


$xeroTenantId = (string)$storage->getSession()['tenant_id'];

// Check if Access Token is expired
// if so - refresh token
if ($storage->getHasExpired()) {
    $provider = getProvider();
   /* $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId' => $clientId,
        'clientSecret' => $clientSecret,
        'redirectUri' => $redirectUri,
        'urlAuthorize' => 'https://login.xero.com/identity/connect/authorize',
        'urlAccessToken' => 'https://identity.xero.com/connect/token',
        'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
    ]);*/

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
$xero = new XeroClass($apiInstance, $xeroTenantId);
//$json = new JsonClass();
//$xero->setup($apiInstance);

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
                    echo $xero->getCabins();
                    break;
                case 'Single':
                    echo $xero->getCabinSingle();
                    break;
            }
            break;

        case "Accounts":
            switch ($action) {
                case "Create":
                    echo $xero->createAccount($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getAccount($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateAccount($xeroTenantId, $apiInstance);
                    break;

                case "Archive":
                    echo $xero->archiveAccount($xeroTenantId, $apiInstance);
                    break;
                case "Attachment":
                    echo $xero->attachmentAccount($xeroTenantId, $apiInstance);
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
                    $xero->getContactRefresh();
                    break;
                case "Create":
                    echo $xero->createContact($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createContacts($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getContact($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateContact($xeroTenantId, $apiInstance);
                    break;
                case "Archive":
                    echo $xero->archiveContact($xeroTenantId, $apiInstance);
                    break;
                case 'Single':
                    echo $xero->getContactSingle();
                case 'Search':
                case 'search';
                    echo $xero->getSearchContacts();
                    break;
                default:
                    echo "[{$endpoint}] {$action}: action not supported in API";
            }
            break;

        case "ContactGroups":
            switch ($action) {
                case "Create":
                    echo $xero->createContactGroup($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getContactGroup($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateContactGroup($xeroTenantId, $apiInstance);
                    break;
                case "Archive":
                    echo $xero->archiveContactGroup($xeroTenantId, $apiInstance);
                    break;

                case "AddContact":
                    echo $xero->createContactGroupContacts($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "CreditNotes":
            switch ($action) {
                case "Create":
                    echo $xero->createCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createCreditNotes($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "Allocate":
                    //echo $xero->allocateCreditNote($xeroTenantId, $apiInstance);
                    break;
                case "Refund":
                    //echo $xero->refundCreditNote($xeroTenantId, $apiInstance);
                    break;

                case "Void":
                    echo $xero->voidCreditNote($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "ExpenseClaims":
            switch ($action) {
                case "Create":
                    echo $xero->createExpenseClaim($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getExpenseClaim($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateExpenseClaim($xeroTenantId, $apiInstance);
                    //echo $action . " action is supported in API but not SDK (no setStatus)";
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Invoices":
        case 'invoices':
            switch ($action) {
                case 'Refresh':
                case 'refresh':
                    $tenancy = filter_input(INPUT_GET, 'tenancy');
                    $xero->getInvoiceRefresh($tenancy);
                    break;
                case "Create":
                    echo $xero->createInvoice($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createInvoices($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getInvoice();
                    break;
                case "Update":
                    echo $xero->updateInvoice($xeroTenantId, $apiInstance);
                    break;

                case "Void":
                    echo $xero->voidInvoice($xeroTenantId, $apiInstance);
                    break;

                default:
                    echo $action . " action not supported in API [Invoices]";
            }
            break;

        case "InvoiceReminders":
            switch ($action) {
                case "Read":
                    echo $xero->getInvoiceReminder($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Items":
            switch ($action) {
                case "Create":
                    echo $xero->createItem($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createItems($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getItem($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateItem($xeroTenantId, $apiInstance);
                    break;

                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Journals":
            switch ($action) {
                case "Read":
                    echo $xero->getJournal($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "LinkedTransactions":
            switch ($action) {
                case "Create":
                    echo $xero->createLinkedTransaction($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getLinkedTransaction($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateLinkedTransaction($xeroTenantId, $apiInstance);
                    break;

                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "ManualJournals":
            switch ($action) {
                case "Create":
                    echo $xero->createManualJournal($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createManualJournals($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getManualJournal($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateManualJournal($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Organisations":
        case 'organisations':
            switch ($action) {
                case "Read":
                    echo $xero->getOrganisation($xeroTenantId, $apiInstance);
                    break;
                case 'List':
                case 'list':
                    echo $xero->getOrganisationList();
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Overpayments":
            switch ($action) {
                case "Read":
                    //echo $xero->getOverpayment($xeroTenantId, $apiInstance);
                    break;
                case "Create":
                    //echo $xero->createOverpayment($xeroTenantId, $apiInstance);
                    break;
                case "Allocate":
                    //echo $xero->allocateOverpayment($xeroTenantId, $apiInstance);
                    break;
                case "AllocateMulti":
                    //echo $xero->allocateOverpayments($xeroTenantId, $apiInstance);
                    break;
                case "Refund":
                    //echo $xero->refundOverpayment($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Payments":
            switch ($action) {
                case "Create":
                    echo $xero->createPayment($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createPayments($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getPayment($xeroTenantId, $apiInstance);
                    break;
                case "Delete":
                    echo $xero->deletePayment($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Prepayments":
            switch ($action) {
                case "Read":
                    echo $xero->getPrepayment($xeroTenantId, $apiInstance);
                    break;
                case "Create":
                    echo $xero->createPrepayment($xeroTenantId, $apiInstance);
                    break;
                case "Allocate":
                    echo $xero->allocatePrepayment($xeroTenantId, $apiInstance);
                    break;
                case "Refund":
                    echo $xero->refundPrepayment($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "PurchaseOrders":
            switch ($action) {
                case "Create":
                    //echo $xero->createPurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                case "CreateMulti":
                    //echo $xero->createPurchaseOrders($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    //echo $xero->getPurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    //echo $xero->updatePurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                case "Delete":
                    //echo $xero->deletePurchaseOrder($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Receipts":
            switch ($action) {
                case "Create":
                    echo $xero->createReceipt($xeroTenantId, $apiInstance);
                    break;
                case "Read":
                    echo $xero->getReceipt($xeroTenantId, $apiInstance);
                    break;
                case "Update":
                    echo $xero->updateReceipt($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "RepeatingInvoices":
            switch ($action) {
                case "Read":
                    echo $xero->getRepeatingInvoice($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;

        case "Reports":
            switch ($action) {
                case "TenNinetyNine":
                    //echo $xero->getTenNinetyNine($xeroTenantId, $apiInstance);
                    break;
                case "AgedPayablesByContact":
                    //echo $xero->getAgedPayablesByContact($xeroTenantId, $apiInstance);
                    break;
                case "AgedReceivablesByContact":
                   // echo $xero->getAgedReceivablesByContact($xeroTenantId, $apiInstance);
                    break;
                case "BalanceSheet":
                    //echo $xero->getBalanceSheet($xeroTenantId, $apiInstance);
                    break;
                case "BankStatement":
                    //echo $xero->getBankStatement($xeroTenantId, $apiInstance);
                    break;
                case "BankSummary":
                    //echo $xero->getBankSummary($xeroTenantId, $apiInstance);
                    break;
                case "BudgetSummary":
                    //echo $xero->getBudgetSummary($xeroTenantId, $apiInstance);
                    break;
                case "ExecutiveSummary":
                    //echo $xero->getExecutiveSummary($xeroTenantId, $apiInstance);
                    break;
                case "ProfitAndLoss":
                    //echo $xero->getProfitAndLoss($xeroTenantId, $apiInstance);
                    break;
                case "TrialBalance":
                    echo $xero->getTrialBalance($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;


        case "Vehicles":
            switch ($action) {
                case 'Read':
                default:
                    echo $xero->getVehiclesLogList();
                    break;
            }
            break;


        case "Users":
            switch ($action) {
                case "Read":
                    echo $xero->getUser($xeroTenantId, $apiInstance);
                    break;
                default:
                    echo $action . " action not supported in API";
            }
            break;
    }
} catch (Exception $e) {
    echo 'Exception when calling AccountingApi: ', $e->getMessage(), PHP_EOL;
}