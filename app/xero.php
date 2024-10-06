<?php

namespace App;

use App\XeroClass;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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

require_once '../vendor/autoload.php';

$log = new Logger('Index');
$log->pushHandler(new StreamHandler('monolog.index.log', Level::Info));
$log->info('New Xero Import', [date('d-m-Y-H-i-s')]);

//require_once('config.php');


// Storage Class uses sessions for storing token > extend to your DB of choice
//require_once('authorizedXero.php');


$storage = new StorageClass();
$provider = Utilities::getProvider();

$xero = new XeroClass();
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
        case "Contacts":
        case 'contacts':
            switch ($action) {
                case 'Refresh':
                case 'refresh':
                    $xero->getContactRefresh();
                    break;
                case "Create":
                    echo $xero->createContact($apiInstance);
                    break;
                case "CreateMulti":
                    echo $xero->createContacts($apiInstance);
                    break;
                case "Read":
                    echo $xero->getContact($apiInstance);
                    break;
                case "Update":
                    echo $xero->updateContact($apiInstance);
                    break;
                case "Archive":
                    echo $xero->archiveContact($apiInstance);
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
                    // this is called automatically by the footer on every page
                    //$tenancy = filter_input(INPUT_GET, 'tenancy');
                    $tenancy = $_GET['tenancy'] ?? '';
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
                    $xero->getOrganisationList();
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
        case 'payments':
            switch ($action) {
                case 'ReadAll':
                case 'readAll':
                case 'readall':
                    //https://ckm:8825/xero.php?endpoint=payments&action=readAll&tenancy=ae75d056-4af7-484d-b709-94439130faa4

                    $tenancy = $_GET['tenancy'] ?? '';
                    echo $xero->getPayments($tenancy);
                    break;

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
                    // https://cabinkingmanagement:8890/xero.php?endpoint=RepeatingInvoices&action=Read
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
