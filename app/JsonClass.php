<?php

namespace App;

//use App\Models\AddressModel;
use App\Models\CabinModel;
use App\Models\ActivityModel;
use App\Models\ComboModel;
use App\Models\ContactModel;
use App\Models\ContractModel;

use App\Models\Enums\CabinStatus;
use App\Models\InvoiceModel;

//use App\Models\PhoneModel;
use App\Models\TasksModel;

//use App\Models\TenancyModel;


// E N U M S
use App\Models\Enums\CabinStyle;
use App\Models\Enums\CabinOwners;


use App\Models\PaymentModel;
use App\Models\TenancyModel;
use App\Models\Traits\DebugTrait;
use App\Models\Traits\LoggerTrait;
use Hoa\Math\Util;
use PDO;
use DateTime;
use DateTimeImmutable;

use \XeroAPI\XeroPHP\Api\AccountingApi;


class JsonClass
{
    use LoggerTrait;
    use DebugTrait;

    public \XeroAPI\XeroPHP\Api\AccountingApi $apiInstance;
    public string $xeroTenantId;

    public array $tenancies = [];
    public PDO $pdo;

    private array $colInvoice = ['contact_id', 'status', 'invoice_number', 'reference', 'total', 'amount_due', 'amount_paid', 'date', 'due_date', 'updated_date_utc'];
    private array $colContact = ['contact_status', 'name', 'first_name', 'last_name', 'email_address', 'is_supplier', 'is_customer', 'updated_date_utc'];
    private array $colAddress = ['address_line1', 'address_line2', 'address_line3', 'address_line4', 'city', 'region', 'postal_code', 'country', 'attention_to'];
    private array $colPhone = ['phone_number', 'phone_area_code', 'phone_country_code'];

    private array $statements = [];

    public $addressOptions = ['address_line1', 'address_line2', 'city', 'postal_code'];

    //function __construct($apiInstance = '', $xeroTenantId = '')
    function __construct()
    {
        $storage = new StorageClass();
        $config = \XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);
        $this->apiInstance = new \XeroAPI\XeroPHP\Api\AccountingApi(
            new \GuzzleHttp\Client(),
            $config
        );

        //$this->apiInstance = $apiInstance;
        $this->pdo = Utilities::getPDO();

        //$this->xeroTenantId = $xeroTenantId;
    }

    public function init($arg)
    {
        $apiInstance = $arg;
    }

    /**
     * Gives access to the field function on a model
     * @param string $modelName
     * @return string
     */
    public function getField(string $modelName = ''): string
    {
        $field = $_GET['field'] ?? '';
        $key = $_GET['key'] ?? '';
        $keyVal = $_GET['keyVal'] ?? '';

        if (empty($field) || empty($key) || empty($keyVal) || empty($modelName)) {
            return '';
        }
        $fullModelName = "App\\Models\\{$modelName}";
        $model = new $fullModelName($this->pdo);
        return json_encode($model->field($field, $key, $keyVal));
    }

    public function getEnquiryCabinList()
    {
        $cabins = new CabinModel($this->pdo);
        $params = $this->getParams();
        $params['xerotenant_id'] = $_GET['xerotenant_id'] ?? '';
        $params['cabin_id'] = $_GET['cabin_id'] ?? '';
        $params['painted'] = $_GET['painted'] ?? '';
        $params['scheduledDate'] = $_GET['scheduledDate'] ?? '';
        $params['cabinType'] = $_GET['cabinType'] ?? '';
        return json_encode($cabins->enquiryList($params));
    }

    public function getCabins()
    {
        $cabins = new CabinModel($this->pdo);
        $params = $this->getParams();
        return $cabins->list($params);
    }

    public function getCabinSingle(): string
    {
        $cabins = new CabinModel($this->pdo);
        $params = $this->getParams();

        $cabin = $cabins->get('cabin_id', $params['key'])['cabins'];

        $cabin['cabinstyle'] = CabinStyle::getLabel($cabin['style'] ?? '');
        $cabin['ownername'] = CabinOwners::getLabel($cabin['owner'] ?? '');


        $tenancies = new Models\TenancyModel($this->pdo);
        $tenancy = $tenancies->get('tenant_id', $cabin['xerotenant_id'])['tenancies'];
        $cabin['tenancy'] = $tenancy['name'];
        $cabin['tenancycolour'] = $tenancy['colour'];
        $cabin['tenancyshortname'] = $tenancy['shortname'];

        $tasks = new TasksModel($this->pdo);
        $cabin['tasklist'] = $tasks->getCurrentCabin($params['key']);
        $cabin['wof'] = $tasks->getLastWOFDate($params['key']);
        // todo - add a wof status and a wof status colour

        return json_encode($cabin);
    }

    public function getCabinEditData(): string
    {
        $cabins = new CabinModel($this->pdo);
        $params = $this->getParams();

        $cabin = $cabins->get('cabin_id', $params['key']);

        // C A B I N   S T Y L E
        $cabin['cabinstyleoptions'] = CabinStyle::allowedNextAsArray($cabin['cabins']['style'] ?? '');
        if (self::allowRollBack($cabin['cabins']['style_old'] ?? '', $cabin['cabins']['style_change'])) {
            $old = ['value' => $cabin['cabins']['style_old'], 'label' => CabinStyle::getLabel($cabin['cabins']['style_old'])];
            $cabin['cabinstyleoptions'] = array_merge($old, $cabin['cabinstyleoptions']);
        }

        // C A B I N   S T A T U S
        $cabin['cabinstatusoptions'] = CabinStatus::allowedNextAsArray($cabin['cabins']['status'] ?? '');
        if (self::allowRollBack($cabin['cabins']['status_old'] ?? '', $cabin['cabins']['status_change'])) {
            $old = ['value' => $cabin['cabins']['status_old'], 'label' => CabinStatus::getLabel($cabin['cabins']['status_old'])];
            $cabin['cabinstatusoptions'] = array_merge($old, $cabin['cabinstatusoptions']);
        }

        $cabin['cabinOwners'] = CabinOwners::getSelectOptionsArray($cabin['cabins']['owner'] ?? '');
        $cabin['tenancyoptions'] = [];
        foreach ($this->tenancies as $tenant) {
            $cabin['tenancyoptions'][] = ['value' => $tenant['tenant_id'], 'label' => $tenant['name']];
        }
        return json_encode($cabin);
    }

    private function allowRollBack($oldVal, $changeDate): bool
    {
        if (empty($oldVal) || empty($changeDate)) {
            return false;
        }

        $now = new DateTimeImmutable('today');   // start of today
        $change = new DateTimeImmutable($changeDate);    // start of that day

        $diff = $change->diff($now);
        return ($diff->invert === 0) && ($diff->days < 3);
    }

    public function getActivityList()
    {
        $params = Utilities::getParams();
        $activity = new ActivityModel($this->pdo);
        return json_encode($activity->list($params));
    }

    public function getComboList()
    {
        $params = Utilities::getParams();
        $params['contact_id'] = $_GET['contact_id'] ?? 0;
        // do we want a generic list or one filtered for a contract or contact?
        if (empty($params['contact_id'])) {
            $id = $_GET['ckcontact_id'] ?? 0;
            if (!empty($id)) {
                $contact = new ContactModel($this->pdo);
                $params['contact_id'] = $contact->field('contact_id', 'contact_id', $id);
            }
        }
        if (empty($params['contract_id'])) {
            $params['repeating_invoice_id'] = $_GET['repeating_invoice_id'] ?? 0;
            if (!empty($params['repeating_invoice_id'])) {
                $contract = new ContractModel($this->pdo);
                $params['contract_id'] = $contract->field('contract_id', 'repeating_invoice_id', $params['repeating_invoice_id']);
            }
        }
        $combo = new ComboModel($this->pdo);
        return json_encode($combo->list($params));
    }

    public function getContractList()
    {
        $params = Utilities::getParams();
        $params['subset'] = $_GET['subset'] ?? '';
        $contract = new ContractModel($this->pdo);
        return json_encode($contract->list($params));
    }

    public function getTemplateList(): string
    {
        $params = Utilities::getParams();
        $template = new Models\TemplateModel($this->pdo);
        return $template->search($params);
    }

    public function getTemplate($id): string
    {
        $template = new Models\TemplateModel($this->pdo);
        return json_encode($template->get('id', $id));
    }


    public function closeTask()
    {
        $params = $this->getParams();
        $tasks = new Models\TasksModel($this->pdo);
        return $tasks->closeTask($params);
    }

    public function ListTasks($for)
    {
        $params = $this->getParams();
        $params['specialise'] = $for;
        $tasks = new Models\TasksModel($this->pdo);
        return json_encode($tasks->list($params));
    }

    public function ListTasksForCabin(): string
    {
        $params = $this->getParams();
        $params['specialise'] = 'cabin';
        $tasks = new Models\TasksModel($this->pdo);
        return json_encode($tasks->List($params));
        //return json_encode($params);
    }

    public function getTaskSingle()
    {
        $tasks = new Models\TasksModel($this->pdo);
        $params = $this->getParams();

        $record = $tasks->get('task_id', $params['key'])['tasks'];
        if ($params['key']) {
            $task[0] = $record;
        } else {
            $task[0] = $record[0];

        }

        $task['tasktypesoptions'] = Models\Enums\TaskType::getSelectOptionsArray();
        $task['statusoptions'] = Models\Enums\TaskStatus::getSelectOptionsArray();
        $user = new Models\UserModel($this->pdo);
        $task['assignedtooptions'] = $user->getSelectOptionsArray();

        //$task['type'] = Models\Enums\TaskType::tryFrom($task['task_type']);

        $tenancies = $this->getTenancyList();
        $task['tenancies'] = $tenancies;
        //$tenancy = $tenancies->get('tenant_id', $task['xerotenant_id'])['tenancies'];
        //$task['tenancyshortname'] = $tenancy['name'] ?? '';
        $map = array_column($tenancies, 'shortname', 'tenant_id');

        $task['tenancyshortname'] = $map[$task[0]['xerotenant_id']] ?? null;
        $task['tenancyoptions'] = array_map(
            fn($t) => ['label' => $t['name'], 'value' => $t['tenant_id']],
            $tenancies
        );

        if (!empty($task['cabin_id'])) {
            $cabins = new Models\CabinModel($this->pdo);
            $task['cabin'] = $cabins->getCurrentContract($task['cabin_id']);

            if (!empty($task['cabin']['ckcontact_id'])) {
                $contacts = new Models\ContactModel($this->pdo);
                $task['contact'] = $contacts->get('id', $task['cabin']['ckcontact_id'], false);
            }
        }

        return json_encode($task);
    }

    public function getTaskCounts(): string
    {
        $tasks = new Models\TasksModel($this->pdo);
        $counts = $tasks->getCounts();
        return json_encode($counts);
    }

    public function getPaymentsList(): string
    {
        $payments = new PaymentModel($this->pdo);
        $params = Utilities::getParams();
        $params['invoice_id'] = $_GET['invoice_id'] ?? 0;
        return json_encode($payments->list($params));
    }

    public function getInvoiceSummary(): string
    {
        $contracts = new ContractModel($this->pdo);
        $params = Utilities::getParams();
        if ($params['contract_id'] == 0) {
            $params['repeating_invoice_id'] = $_GET['repeating_invoice_id'] ?? 0;
            if (empty($params['repeating_invoice_id'])) {
                return '';
            }
            $params['contract_id'] = $contracts->field('contract_id', 'repeating_invoice_id', $params['repeating_invoice_id']);
        }
        return json_encode($contracts->getPaymentSummary($params));
    }

    public function getAccount($xeroTenantId, $apiInstance, $returnObj = false)
    {

        $str = '';
        //[Accounts:Read]
// READ ALL
        $result = $apiInstance->getAccounts($xeroTenantId);
        // READ only ACTIVE
        $where = 'Status=="ACTIVE"';
        $result2 = $apiInstance->getAccounts($xeroTenantId, null, $where);
        //[/Accounts:Read]

        if ($returnObj) {
            return $result;
        } else {
            $str = $str . "Get accounts total: " . count($result->getAccounts()) . "<br>";
            $str = $str . "Get ACTIVE accounts total: " . count($result2->getAccounts()) . "<br>";
            return $str;
        }
    }

    public function createAccount($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Accounts:Create]
        $account = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $account->setCode($this->getRandNum());
        $account->setName("Foo" . $this->getRandNum());
        $account->setType("EXPENSE");
        $account->setDescription("Hello World");
        $result = $apiInstance->createAccount($xeroTenantId, $account);
        //[/Accounts:Create]

        $str = $str . "Create Account: " . $result->getAccounts()[0]->getName() . "<br>";
        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateAccount($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createAccount($xeroTenantId, $apiInstance, true);
        $guid = $new->getAccounts()[0]->getAccountId();

        //[Accounts:Update]
        $account = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $account->setStatus(NULL);
        $account->setDescription("Goodbye World");
        $result = $apiInstance->updateAccount($xeroTenantId, $guid, $account);
        //[/Accounts:Update]

        $str = $str . "Update Account: " . $result->getAccounts()[0]->getName() . "<br>";

        return $str;
    }

    public function archiveAccount($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createAccount($xeroTenantId, $apiInstance, true);
        $guid = $new->getAccounts()[0]->getAccountId();

        //[Accounts:Archive]
        $account = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $account->setStatus("ARCHIVED");
        $result = $apiInstance->updateAccount($xeroTenantId, $guid, $account);
        //[/Accounts:Archive]

        $str = $str . "Archive Account: " . $result->getAccounts()[0]->getName() . "<br>";

        return $str;
    }


    public function attachmentAccount($xeroTenantId, $apiInstance)
    {
        $str = '';

        $account = $this->getAccount($xeroTenantId, $apiInstance, true);
        //[Accounts:Attachment]
        $guid = $account->getAccounts()[2]->getAccountId();

        $filename = "./helo-heros.jpg";
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        $result = $apiInstance->createAccountAttachmentByFileName($xeroTenantId, $guid, "helo-heros.jpg", $contents);
        //[/Accounts:Attachment]
        $str = "Account (" . $result->getAttachments()[0]->getFileName() . ") attachment url:";
        $str = $str . $result->getAttachments()[0]->getUrl();

        return $str;
    }

    public function getBankTransaction($xeroTenantId, $apiInstance)
    {
        $str = '';
        //[BankTransactions:Read]
// READ ALL
        $result = $apiInstance->getBankTransactions($xeroTenantId);
        // READ only ACTIVE
        $where = 'Status=="AUTHORISED"';
        $result2 = $apiInstance->getBankTransactions($xeroTenantId, null, $where);
        //[/BankTransactions:Read]

        $str = $str . "Get BankTransaction total: " . count($result->getBankTransactions()) . "<br>";
        $str = $str . "Get ACTIVE BankTransaction total: " . count($result2->getBankTransactions()) . "<br>";

        return $str;
    }


    public function getBankTransfer($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[BankTransfers:Read]
// READ ALL
        $result = $apiInstance->getBankTransfers($xeroTenantId);
        //[/BankTransfers:Read]

        $str = $str . "Get BankTransaction total: " . count($result->getBankTransfers()) . "<br>";

        return $str;
    }


    public function getParams()
    {
        $output = ['data' => []];

        $output['draw'] = $_GET['draw'] ?? 1;
        $output['start'] = $_GET['start'] ?? 1;
        $output['length'] = $_GET['length'] ?? 10;
        $output['order'] = $_GET['order'] ?? [0 => ['column' => '0', 'dir' => 'ASC']];
        $output['search'] = $_GET['search']['value'] ?? '';
        // getInvoice
        $output['invoice_status'] = $_GET['invoice_status'] ?? '';

        $output['dates'] = $_GET['search']['dates'] ?? '';
        $output['contact_status'] = $_GET['search']['dates'] ?? '';
        $output['button'] = $_GET['search']['button'] ?? '';
        $output['tenancies'] = $this->getTenancies();
        // prima
        $output['key'] = $_GET['search']['key'] ?? '';

        return $output;
    }

    public function initPDOContact()
    {
        $statements = [];


        $sqlContact = 'INSERT INTO `contacts` ('
            . '`contact_id`, `' . implode('`,`', $this->colContact) . '`, `xerotenant_id`)'
            . ' values (:contact_id, :' . implode(',:', $this->colContact) . ", '{$this->xeroTenantId}')"
            . " ON DUPLICATE KEY UPDATE ";
        foreach ($this->colContact as $k => $v) {
            $join = ($k > 0) ? ', ' : '';

            $sqlContact .= "{$join}`{$v}` = :upd8{$v}";
        }
        $this->statements['contact'] = $this->pdo->prepare($sqlContact);

        /* ==================== */


        $sqlAddress = 'INSERT INTO `addresses` ('
            . '`contact_id`, `address_type`, `' . implode('`,`', $this->colAddress) . '`)'
            . ' values (:contact_id, :address_type, :' . implode(',:', $this->colAddress) . ")"
            . " ON DUPLICATE KEY UPDATE ";
        foreach ($this->colAddress as $k => $v) {
            $join = ($k > 0) ? ', ' : '';

            $sqlAddress .= "{$join}`{$v}` = :upd8{$v}";
        }
        $this->statements['Address'] = $this->pdo->prepare($sqlAddress);

        /* ==================== */

        $colPhone = ['phone_number', 'phone_area_code', 'phone_country_code'];

        $sqlPhone = 'INSERT INTO `phones` ('
            . '`contact_id`, `phone_type`, `' . implode('`,`', $colPhone) . '`)'
            . ' values (:contact_id, :phone_type, :' . implode(',:', $colPhone) . ")"
            . " ON DUPLICATE KEY UPDATE ";
        foreach ($colPhone as $k => $v) {
            $join = ($k > 0) ? ', ' : '';

            $sqlPhone .= "{$join}`{$v}` = :upd8{$v}";
        }

        $this->statements['Phone'] = $this->pdo->prepare($sqlPhone);

    }

    public function getXeroTimestamp($category)
    {
        $sql = "SELECT `value` FROM `settings` WHERE `category` = '{$category}' AND `key` = 'xerocheck' AND `xerotenant_id` = '{$this->xeroTenantId}' LIMIT 1";
        $timeCheck = $this->pdo->query($sql)->fetchColumn();

        if ($timeCheck) {
            $minutes = (time() - $timeCheck) / 60;
        } else {
            return 60;
        }
        return $minutes;
    }

    public function saveXeroTimestamp($category, $xeroTenantId)
    {
        $sql = "INSERT INTO `settings` (`xerotenant_id`,`category`,`key`,`value`) 
            VALUES (:xeroTenantId, :category, 'xerocheck', :xerotime)
            ON DUPLICATE KEY UPDATE `value` = :xerotime2";

        $vals = [
            'xeroTenantId' => $xeroTenantId,
            'category' => $category,
            'xerotime' => time(),
            'xerotime2' => time()
        ];
        $this->pdo->prepare($sql)->execute($vals);
    }

    public function getContactRefresh($tenancy)
    {

        $k = 1;
        $xeroTenantId = $this->getXeroTenantId($tenancy);
        //      public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)

        if ($this->getXeroTimestamp('Contact', $xeroTenantId) > 10) {
            $updated_date_utc = $this->getUpdatedDate('Contact');

            $this->initPDOContact();

            $result = $this->apiInstance->getContacts($xeroTenantId, $updated_date_utc, null, null, null, 1, true);
            $data = $result->getContacts();

            if (count($data)) {
                foreach ($data as $k => $row) {
                    $this->saveContactRow($row);
                }
            }
            $this->saveXeroTimestamp('Contact', $xeroTenantId);
        }

        return $k;
    }

    /*
     * used by the enquiry form to see if the contact already exists
     */
    public function getSearchContacts()
    {
        $contact = new App\Model\ContactModel();
        $result = $contact->search();

        return json_encode($result);
    }

    public function saveContactRow($row)
    {

        $valuesContact = [
            'contact_id' => $row->getContactId(),
            'contact_status' => $row->getContactStatus(),
            'name' => $row->getName(),
            'first_name' => $row->getFirstName(),
            'last_name' => $row->getLastName(),
            'email_address' => $row->getEmailAddress(),
            'is_supplier' => $row->getIsSupplier(),
            'is_customer' => $row->getIsCustomer(),
            'updated_date_utc' => getDateFromXero($row['updated_date_utc'])
        ];
        foreach ($this->colContact as $v) {
            $valuesContact["upd8{$v}"] = $valuesContact[$v];
        }

        $this->statements['contact']->execute($valuesContact);


        foreach ($row['addresses'] as $addy) {
            $valuesAddress = [
                'contact_id' => $valuesContact['contact_id'],
                'address_type' => $addy->getAddressType(),
                'address_line1' => $addy->getAddressLine1(),
                'address_line2' => $addy->getAddressLine2(),
                'address_line3' => $addy->getAddressLine3(),
                'address_line4' => $addy->getAddressLine4(),
                'city' => $addy->getCity(),
                'region' => $addy->getRegion(),
                'postal_code' => $addy->getPostalCode(),
                'country' => $addy->getCountry(),
                'attention_to' => $addy->getAttentionTo()
            ];
            foreach ($this->colAddress as $v) {
                $valuesAddress["upd8{$v}"] = $valuesAddress[$v];
            }

            $this->statements['address']->execute($valuesAddress);
        }

        foreach ($row['phones'] as $phone) {
            $valuesPhone = [
                'contact_id' => $valuesContact['contact_id'],
                'phone_type' => $phone->getPhoneType(),
                'phone_number' => $phone->getPhoneNumber(),
                'phone_area_code' => $phone->getPhoneAreaCode(),
                'phone_country_code' => $phone->getPhoneCountryCode()
            ];
            foreach ($this->colPhone as $v) {
                $valuesPhone["upd8{$v}"] = $valuesPhone[$v];
            }

            $this->statements['phone']->execute($valuesPhone);
        }

    }


    public function getContactRefreshx()
    {
        $if_modified_since = new \DateTime("2019-01-02T19:20:30+01:00"); // \DateTime | Only records created or modified since this timestamp will be returned
        $where = null;
        $order = null; // string
        $ids = null; // string[] | Filter by a comma-separated list of Invoice Ids.
        $page = 1; // int | e.g. page=1 – Up to 100 invoices will be returned in a single API call with line items
        $include_archived = null; // bool | e.g. includeArchived=true - Contacts with a status of ARCHIVED will be included

        try {
            $apiResponse = $this->apiInstance->getContacts($this->xeroTenantId, $if_modified_since, $where, $order, $ids, $page, $include_archived);
            if (count($apiResponse->getContacts()) > 0) {
                $message = 'Total contacts found: ' . count($apiResponse->getContacts());
            } else {
                $message = "No contacts found matching filter criteria";
            }
        } catch (Exception $e) {
            echo 'Exception when calling AccountingApi->getContacts: ', $e->getMessage(), PHP_EOL;
        }
    }

    // get all the tenancies from the database
    // and return the ones that are active
    public function getTenancies()
    {

        if (count($this->tenancies) == 0) {
            $tenancies = new TenancyModel($this->pdo);
            $this->tenancies = $tenancies->list();
        }

        $output = [];
        foreach ($this->tenancies as $row) {
            if ($row['active']) {
                $output[] = $row['tenant_id'];
            }
        }

        return $output;
    }

    // get all the tenancies and show if they're active or not
    public function getTenancyList()
    {
        $tenancies = new TenancyModel($this->pdo);
        $this->tenancies = $tenancies->list();
        return $this->tenancies;
    }

    public function isCookieTrue($tenancy)
    {
        return (array_key_exists($tenancy, $_COOKIE) && $_COOKIE[$tenancy] == 'true');
    }


    // using the short name of the tenancy, get the Xero TenantId
    public function getXeroTenantId($tenancy)
    {
        $tenancies = $this->getTenancies();
        foreach ($tenancies as $row) {
            if ($row['shortname'] == $tenancy) {
                return $row['tenant_id'];
            }
        }
        return null;
    }

    // use the list of tenancies to create the where sql
    public function getXeroTenantClause()
    {
        //debug($this->xeroTenantId);
        $tenancies = $this->getTenancies();
        //   "contacts.xerotenant_id = '{$this->xeroTenantId}'";

        $output = " (`contacts`.`xerotenant_id` = '" . implode("' OR `contacts`.`xerotenant_id` = '", $tenancies) . "') ";
        return $output;
    }

    public function getTenancyColour($prefix, $xeroTenantId)
    {
        foreach ($this->tenancies as $row) {
            if ($row['tenant_id'] == $xeroTenantId) {
                return "{$prefix}{$row['colour']}";
            }
        }
        return "{$prefix}black";
    }

// https://ckm:8825/json.php?endpoint=Contacts&action=refreshSingle&tenancy=auckland&contact_id=59552109-942b-4de3-931d-5ae843f95d79
    public function getRefreshContactSingle($tenancy_id, $contact_id)
    {
        $xero = new XeroClass();

        $output = $xero->getSingleContact($tenancy_id, $contact_id);

        return json_encode($output);
    }

    public function getContactList(): string
    {
        $params = Utilities::getParams();

        $contacts = new ContactModel($this->pdo);
        return json_encode($contacts->list($params));
    }

    /*
     * calls
     * public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)
     */
    public function getContactSingle()
    {
        return $this->getContactSingleton();
//        $this->initPDOContact();
//        $output = $this->getParams();
//
//        //check the age of the record first?
//
//        $result = $this->apiInstance->getContacts($this->xeroTenantId, null, null, null, $output['key'], null, true);
//        $data = $result->getContacts();
//
//
//        if (count($data)) {
//            foreach ($data as $k => $row) {
//                $this->saveContactRow($row);
//            }
//        }
//
//        $xeroTenantId = $this->xeroTenantId;
//        $contact_id = $output['key'];
//        $fields = [
//            'contacts.contact_id',
//            'contacts.name',
//            'contacts.firstname',
//            'contacts.lastname',
//            'contacts.email_address',
//            'contacts.contact_status',
//            'contacts.is_supplier',
//            'contacts.is_customer',
//            'addresses.address_line1',
//            'addresses.address_line2',
//            'addresses.city',
//            'addresses.postal_code',
//            'phones.phone_number',
//            'phones.phone_area_code',
//            'contacts.updated_date_utc'
//        ];
//        $fields[] = "SUM(invoices.amount_due) as amount_due";
//
//        $conditions = [
//            "contacts.xerotenant_id = '$xeroTenantId'",
//            "contacts.contact_id = '$contact_id'"
//        ];
//
//        $sql = "SELECT " . implode(', ', $fields)
//            . " FROM `contacts` "
//            . " LEFT JOIN invoices ON (contacts.contact_id = invoices.contact_id) "
//            . " LEFT JOIN addresses ON (contacts.contact_id = addresses.contact_id and addresses.address_type = 'STREET') "
//            . " LEFT JOIN phones ON (contacts.contact_id = phones.contact_id and phones.phone_type = 'MOBILE') "
//            . " WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
//
//        $contact = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
//
//        $address = [];
//        foreach ($this->addressOptions as $key) {
//            if (!empty($contact[$key])) {
//                $address[] = $contact[$key];
//            }
//        }
//        $contact['address'] = implode(', ', $address);
//
//        $phone = '';
//
//        if (!empty($contact['phone_number'])) {
//            $phone_number = str_replace(' ', '&nbsp;', $contact['phone_number']);
//            $phone = "<a href='tel:{$contact['phone_area_code']}{$row['phone_number']}'>{$contact['phone_area_code']}&nbsp;{$phone_number}</a>";
//        }
//        $contact['phone'] = $phone;
//
//        $contact['email'] = "<a href='mailto:{$contact['email_address']}' target='_blank'>{$contact['email_address']}</a>";
//
//        echo json_encode($contact);
//        exit;
    }

    public function getContactSingleton(): string
    {

        //$xeroTenantId = $_GET['xeroTenantId'];

        $contact_id = $_GET['contact_id'] ?? 0;
        $id = $_GET['id'] ?? 0;
        $contact = new ContactModel($this->pdo);

        if ($contact_id) {
            $output = $contact->get('contact_id', $contact_id);
        } else {
            $output = $contact->get('id', $id);
        }

        return json_encode($output);
    }

    public function listPhonesForContact(): string
    {
        $contact_id = $_GET['contact_id'];
        $phone = new PhoneModel($this->pdo);
        $output = $phone->get('contact_id', $contact_id);
        return json_encode($output);
    }

    public function createContact($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Contacts:Create]
        $person = new XeroAPI\XeroPHP\Models\Accounting\ContactPerson;
        $person->setFirstName("John")
            ->setLastName("Smith")
            ->setEmailAddress("john.smith@24locks.com")
            ->setIncludeInEmails(true);

        $persons = [];
        array_push($persons, $person);

        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setName('FooBar' . $this->getRandNum())
            ->setFirstName("Foo" . $this->getRandNum())
            ->setLastName("Bar" . $this->getRandNum())
            ->setEmailAddress("ben.bowden@24locks.com")
            ->setContactPersons($persons);
        $result = $apiInstance->createContact($xeroTenantId, $contact);
        //[/Contacts:Create]

        $str = $str . "Create Contact: " . $result->getContacts()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createContacts($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Contacts:CreateMulti]
        $arr_contacts = [];

        $contact_1 = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact_1->setName('FooBar' . $this->getRandNum())
            ->setFirstName("Foo" . $this->getRandNum())
            ->setLastName("Bar" . $this->getRandNum())
            ->setEmailAddress("ben.bowden@24locks.com");
        array_push($arr_contacts, $contact_1);

        $contact_2 = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact_2->setName('FooBar' . $this->getRandNum())
            ->setFirstName("Foo" . $this->getRandNum())
            ->setLastName("Bar" . $this->getRandNum())
            ->setEmailAddress("ben.bowden@24locks.com");
        array_push($arr_contacts, $contact_2);

        $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
        $contacts->setContacts($arr_contacts);

        $result = $apiInstance->createContacts($xeroTenantId, $contacts);
        //[/Contacts:CreateMulti]

        $str = $str . "Create Contact 1: " . $result->getContacts()[0]->getName() . " --- Create Contact 2: " . $result->getContacts()[1]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateContact($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createContact($xeroTenantId, $apiInstance, true);
        $contactId = $new->getContacts()[0]->getContactId();

        //[Contacts:Update]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setName("Goodbye" . $this->getRandNum());
        $result = $apiInstance->updateContact($xeroTenantId, $contactId, $contact);
        //[/Contacts:Update]

        $str = $str . "Update Contacts: " . $result->getContacts()[0]->getName() . "<br>";

        return $str;
    }

    public function archiveContact($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createContact($xeroTenantId, $apiInstance, true);
        $contactId = $new->getContacts()[0]->getContactId();

        //[Contacts:Archive]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactStatus(\XeroAPI\XeroPHP\Models\Accounting\Contact::CONTACT_STATUS_ARCHIVED);
        $result = $apiInstance->updateContact($xeroTenantId, $contactId, $contact);
        //[/Contacts:Archive]

        $str = $str . "Archive Contacts: " . $result->getContacts()[0]->getName() . "<br>";

        return $str;
    }

    public function getContactGroup($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[ContactGroups:Read]
        $result = $apiInstance->getContactGroups($xeroTenantId);
        //[/ContactGroups:Read]

        $str = $str . "Get Contacts Total: " . count($result->getContactGroups()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createContactGroup($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $new->getContacts()[0]->getContactId();

        //[ContactGroups:Create]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactID($contactId);
        $contacts = [];
        array_push($contacts, $contact);

        $contactgroup = new XeroAPI\XeroPHP\Models\Accounting\ContactGroup;
        $contactgroup->setName('Rebels-' . $this->getRandNum())
            ->setContacts($contacts);

        $result = $apiInstance->createContactGroup($xeroTenantId, $contactgroup);
        //[/ContactGroups:Create]

        $str = $str . "Create ContactGroups: " . $result->getContactGroups()[0]->getName() . "<br>";
        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateContactGroup($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createContactGroup($xeroTenantId, $apiInstance, true);
        $contactgroupId = $new->getContactGroups()[0]->getContactGroupId();

        //[ContactGroups:Update]
        $contactgroup = new XeroAPI\XeroPHP\Models\Accounting\ContactGroup;
        $contactgroup->setName("Goodbye" . $this->getRandNum());
        $result = $apiInstance->updateContactGroup($xeroTenantId, $contactgroupId, $contactgroup);
        //[/ContactGroups:Update]

        $str = $str . "Update ContactGroup: " . $result->getContactGroups()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function archiveContactGroup($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createContactGroup($xeroTenantId, $apiInstance, true);
        $contactgroupId = $new->getContactGroups()[0]->getContactGroupID();

        //[ContactGroups:Archive]
        $contactgroup = new XeroAPI\XeroPHP\Models\Accounting\ContactGroup;
        $contactgroup->setStatus(XeroAPI\XeroPHP\Models\Accounting\ContactGroup::STATUS_DELETED);
        $result = $apiInstance->updateContactGroup($xeroTenantId, $contactgroupId, $contactgroup);
        //[/ContactGroups:Archive]

        $str = $str . "Set Status to DELETE for ContactGroup: " . $new->getContactGroups()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createContactGroupContacts($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $new->getContacts()[0]->getContactId();

        $newContactGroup = $this->getContactGroup($xeroTenantId, $apiInstance, true);
        $contactgroupId = $newContactGroup->getContactGroups()[0]->getContactGroupId();

        //[ContactGroups:AddContact]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactID($contactId);
        $arr_contacts = [];
        array_push($arr_contacts, $contact);
        $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
        $contacts->setContacts($arr_contacts);

        $result = $apiInstance->createContactGroupContacts($xeroTenantId, $contactgroupId, $contacts);
        //[/ContactGroups:AddContact]

        $str = $str . "Add " . count($result->getContacts()) . " Contacts <br>";
        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function removeContactFromContactGroup($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();
        $getContactGroup = $this->getContactGroup($xeroTenantId, $apiInstance, true);
        $contactgroupId = $getContactGroup->getContactGroups()[0]->getContactGroupID();

        //[ContactGroups:Remove]
        $result = $apiInstance->deleteContactGroupContact($xeroTenantId, $contactgroupId, $contactId);
        //[/ContactGroups:Remove]

        $str = $str . "Deleted Contact from Group<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    // used by the contract card widget
    public function getContractSingleton(): string
    {
        $contract = new ContractModel($this->pdo);

        $contract_id = $_GET['contract_id'] ?? 0;
        $repeating_invoice_id = $_GET['repeating_invoice_id'] ?? 0;
        if ($contract_id) {
            $output = $contract->get('contract_id', $contract_id);
        } else {
            $output = $contract->get('repeating_invoice_id', $repeating_invoice_id);
        }

        return json_encode($output);
    }

    public function getCreditNote($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[CreditNotes:Read]
// READ ALL
        $result = $apiInstance->getCreditNotes($xeroTenantId);

        // READ only ACTIVE
        $where = 'Status=="' . \XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_DRAFT . '"';
        $result2 = $apiInstance->getCreditNotes($xeroTenantId, null, $where);
        //[/CreditNotes:Read]

        $str = $str . "Get CreditNotes Total: " . count($result->getCreditNotes()) . "<br>";
        $str = $str . "Get ACTIVE CreditNotes Total: " . count($result2->getCreditNotes()) . "<br>";

        if ($returnObj) {
            return $result->getCreditNotes()[0];
        } else {
            return $str;
        }
    }

    public function createCreditNote($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[CreditNotes:Create]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $creditnote = new XeroAPI\XeroPHP\Models\Accounting\CreditNote;

        $creditnote->setDate(new DateTime('2017-01-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\CreditNote::TYPE_ACCPAYCREDIT);
        $result = $apiInstance->createCreditNote($xeroTenantId, $creditnote);
        //[/CreditNotes:Create]

        $str = $str . "Create CreditNote: " . $result->getCreditNotes()[0]->getTotal() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createCreditNotes($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[CreditNotes:CreateMulti]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $arr_creditnotes = [];

        $creditnote_1 = new XeroAPI\XeroPHP\Models\Accounting\CreditNote;
        $creditnote_1->setDate(new DateTime('2019-12-15'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\CreditNote::TYPE_ACCPAYCREDIT);
        array_push($arr_creditnotes, $creditnote_1);

        $creditnote_2 = new XeroAPI\XeroPHP\Models\Accounting\CreditNote;
        $creditnote_2->setDate(new DateTime('2019-12-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\CreditNote::TYPE_ACCPAYCREDIT);
        array_push($arr_creditnotes, $creditnote_2);

        $creditnotes = new XeroAPI\XeroPHP\Models\Accounting\CreditNotes;
        $creditnotes->setCreditNotes($arr_creditnotes);

        $result = $apiInstance->createCreditNotes($xeroTenantId, $creditnotes);
        //[/CreditNotes:CreateMulti]

        $str = $str . "Create CreditNote 1: " . $result->getCreditNotes()[0]->getTotal() . " --- Create CreditNote 2: " . $result->getCreditNotes()[1]->getTotal() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateCreditNote($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createCreditNote($xeroTenantId, $apiInstance, true);
        $creditnoteId = $new->getCreditNotes()[0]->getCreditNoteId();

        //[CreditNotes:Update]
        $creditnote = new XeroAPI\XeroPHP\Models\Accounting\CreditNote;
        $creditnote->setDate(new DateTime('2020-01-02'));
        $result = $apiInstance->updateCreditNote($xeroTenantId, $creditnoteId, $creditnote);
        //[/CreditNotes:Update]

        $str = $str . "Update CreditNote: $" . $result->getCreditNotes()[0]->getTotal() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }


    public function voidCreditNote($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createCreditNoteAuthorised($xeroTenantId, $apiInstance, true);
        $creditnoteId = $new->getCreditNotes()[0]->getCreditNoteID();

        //[CreditNotes:Void]
        $creditnote = new XeroAPI\XeroPHP\Models\Accounting\CreditNote;
        $creditnote->setCreditNoteID($creditnoteId)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_VOIDED);
        $result = $apiInstance->updateCreditNote($xeroTenantId, $creditnoteId, $creditnote);
        //[/CreditNotes:Void]

        $str = $str . "Void CreditNote: " . $result->getCreditNotes()[0]->getCreditNoteID() . "<br>";

        return $str;
    }


    public function getEmployee($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Employees:Read]
        $result = $apiInstance->getEmployees($xeroTenantId);

        // READ only ACTIVE
        $where = 'Status=="ACTIVE"';
        $result2 = $apiInstance->getEmployees($xeroTenantId, null, $where);
        //[/Employees:Read]

        $str = $str . "Get Employees Total: " . count($result->getEmployees()) . "<br>";
        $str = $str . "Get ACTIVE Employees Total: " . count($result2->getEmployees()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }


    public function updateEmployee($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->getEmployee($xeroTenantId, $apiInstance, true);
        $employeeId = $new->getEmployees()[3]->getEmployeeID();
        $firstName = $new->getEmployees()[0]->getFirstName();
        $lastName = $new->getEmployees()[0]->getLastName();

        //[Employees:Update]
        $external_link = new XeroAPI\XeroPHP\Models\Accounting\ExternalLink;
        $external_link->setUrl("http://twitter.com/#!/search/Homer+Simpson");

        $employee = new XeroAPI\XeroPHP\Models\Accounting\Employee;
        $employee->setExternalLink($external_link);
        $employee->setFirstName($firstName);
        $employee->setLastName($lastName);

        $result = $apiInstance->updateEmployee($xeroTenantId, $employeeId, $employee);
        //[/Employees:Update]


        //$str = $str . "Update Employee: " . $employee["FirstName"] . "  " . $employee["LastName"]   . "<br>" ;

        return $str;
    }

    public function getExpenseClaim($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[ExpenseClaims:Read]
// READ ALL
        $result = $apiInstance->getExpenseClaims($xeroTenantId);
        // READ only ACTIVE
        $where = 'Status=="SUBMITTED"';
        $result2 = $apiInstance->getExpenseClaims($xeroTenantId, null, $where);
        //[/ExpenseClaims:Read]

        $str = $str . "Get ExpenseClaim total: " . count($result->getExpenseClaims()) . "<br>";
        $str = $str . "Get ACTIVE ExpenseClaim total: " . count($result2->getExpenseClaims()) . "<br>";

        if ($returnObj) {
            return $result->getExpenseClaims()[0];
        } else {
            return $str;
        }
    }

    public function createExpenseClaim($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $allUsers = $this->getUser($xeroTenantId, $apiInstance, true);
        $userId = $allUsers->getUsers()[0]->getUserID();

        $lineitem = $this->getLineItemForReceipt($xeroTenantId, $apiInstance);

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        if (count($allUsers->getUsers())) {
            //[ExpenseClaims:Create]
            $lineitems = [];
            array_push($lineitems, $lineitem);
            $user = new XeroAPI\XeroPHP\Models\Accounting\User;
            $user->setUserID($userId);

            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setContactId($contactId);

            // CREATE RECEIPT
            $receipt = new XeroAPI\XeroPHP\Models\Accounting\Receipt;
            $receipt->setDate(new DateTime('2017-01-02'))
                ->setLineItems($lineitems)
                ->setContact($contact)
                ->setTotal(20.00)
                ->setUser($user);

            $receipts = new XeroAPI\XeroPHP\Models\Accounting\Receipts;
            $arr_receipts = [];
            array_push($arr_receipts, $receipt);
            $receipts->setReceipts($arr_receipts);
            $new_receipt = $apiInstance->createReceipt($xeroTenantId, $receipts);

            // CREATE EXPENSE CLAIM
            $expenseclaim = new XeroAPI\XeroPHP\Models\Accounting\ExpenseClaim;
            $expenseclaim->setUser($user)
                ->setReceipts($new_receipt->getReceipts());

            $expenseclaims = new XeroAPI\XeroPHP\Models\Accounting\ExpenseClaims;
            $arr_expenseclaims = [];
            array_push($arr_expenseclaims, $expenseclaim);
            $expenseclaims->setExpenseClaims($arr_expenseclaims);

            $result = $apiInstance->createExpenseClaims($xeroTenantId, $expenseclaims);
            //[/ExpenseClaims:Create]

            $str = $str . "Created a new Expense Claim: " . $result->getExpenseClaims()[0]->getExpenseClaimID() . "<br>";
        }

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateExpenseClaim($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createExpenseClaim($xeroTenantId, $apiInstance, true);
        $guid = $new->getExpenseClaims()[0]->getExpenseClaimID();

        //[ExpenseClaims:Update]
        $expenseclaim = new XeroAPI\XeroPHP\Models\Accounting\ExpenseClaim;
        $expenseclaim->setStatus(XeroAPI\XeroPHP\Models\Accounting\ExpenseClaim::STATUS_AUTHORISED);
        $expenseclaim->setExpenseClaimId($guid);

        $result = $apiInstance->updateExpenseClaim($xeroTenantId, $guid, $expenseclaim);
        //[/ExpenseClaims:Update]

        $str = $str . "Updated a Expense Claim: " . $result->getExpenseClaims()[0]->getExpenseClaimID() . "<br>";

        return $str;
    }

    public function getUpdatedDate($table, $xeroTenantId)
    {
        // 3 days before we started using xero
        $updated_date_utc = '2017-10-10 00:00:00';
        $tables = ['invoices', 'payments', 'contacts'];

        if (!in_array($table, $tables)) {
            return $updated_date_utc;
        }
        // getting number of rows in the table utilizing method chaining
//$count = $pdo->query("SELECT count(*) FROM table")->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT max(`updated_date_utc`) as `updated_date_utc` FROM `{$table}` where `xerotenant_id` = :xerotenant_id");

        $stmt->execute(['xerotenant_id' => $xeroTenantId]);
        $row = $stmt->fetch();

        if ($row) {
            $updated_date_utc = $row['updated_date_utc'];
        }

        return $updated_date_utc;
    }

    /* somehow multiple transactions can have the same time */
    public function isItNew($id, $table)
    {
        $fldId = substr($table, 0, -1) . '_id';

        $stmt = $this->pdo->query("SELECT COUNT({$fldId}) FROM {$table} WHERE {$fldId} = '{$id}'");

        if ($stmt->fetchColumn() > 0) {
            return false;

        }
        return true;
    }

    public function initPDOInvoice()
    {

        $sql = 'INSERT INTO `invoices` ('
            . '`invoice_id`, `' . implode('`, `', $this->colInvoice) . '`, `xerotenant_id`)'
            . ' VALUES (:invoice_id, :' . implode(', :', $this->colInvoice) . ", '{$this->xeroTenantId}')"
            . " ON DUPLICATE KEY UPDATE ";

        // this is the on duplicate part of the statement
        foreach ($this->colInvoice as $k => $v) {
            if ($k > 0) {
                $sql .= ', ';
            }
            $sql .= "`{$v}` = :upd8{$v}";
        }

        $this->statements['invoice'] = $this->pdo->prepare($sql);
    }

    // https://cabinkingmanagement:8890/json.php?endpoint=Invoices&action=refresh&tenancy=auckland
    public function getInvoiceRefreshX($tenancy)
    {
        $testing = true;
        $k = 0;
        if ($this->getXeroTimestamp('Invoice') > 10 || $testing) {
            $xeroTenantId = $this->getXeroTenantId($tenancy);

            $updated_date_utc = $this->getUpdatedDate('invoices', $xeroTenantId);

            $this->initPDOInvoice();

            //    public function getInvoices($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $invoice_numbers = null, $contact_i_ds = null, $statuses = null, $page = null, $include_archived = null, $created_by_my_app = null, $unitdp = null)
            $result = $this->apiInstance->getInvoices($xeroTenantId, $updated_date_utc, null, null, null, null, null, null, 1);

            $data = $result->getInvoices();

            foreach ($data as $k => $row) {

                $contact = $row->getContact();


                $values = [
                    'invoice_id' => $row['invoice_id'],
                    'contact_id' => $contact['contact_id'],
                    'status' => $row['status'],
                    'invoice_number' => $row['invoice_number'],
                    'reference' => substr($row['reference'], 0, 20),
                    'total' => $row['total'],
                    'amount_due' => $row['amount_due'],
                    'amount_paid' => $row['amount_paid'],
                    'date' => getDateFromXero($row['date']),
                    'due_date' => getDateFromXero($row['due_date']),
                    'updated_date_utc' => getDateFromXero($row['updated_date_utc'])
                ];

                // give the values to the on duplicate part of the statement
                foreach ($this->colInvoice as $v) {
                    $values["upd8{$v}"] = $values[$v];
                }


                $x = $this->statements['invoice']->execute($values);

                //$this->statements['invoice']->debugDumpParams();
            }

            $this->saveXeroTimestamp('Invoice', $xeroTenantId);
        }

        return $k;
    }

    public function getInvoiceList($returnObj = false)
    {
        $params = $this->getParams();
        $invoice = new InvoiceModel($this->pdo);
        $output = $invoice->list($params);
        return json_encode($output);

    }

    public function getBadDebtsList($action): string
    {
        $params = Utilities::getParams();
        $invoice = new InvoiceModel($this->pdo);
        if ($action === 'BadDebts') {
            $output = $invoice->listBadDebts($params);
        } else {
            $output = $invoice->listBadDebtsManagement($params);
        }
        return json_encode($output);
    }

    public function getBadDebtTotal(): string
    {
        $params = $this->getParams();
        $invoice = new InvoiceModel($this->pdo);
        return json_encode($invoice->getBadDebtTotal($params));
    }

    public function getVehiclesLogList($returnObj = false)
    {
        $output = $this->getOutput();

        if (is_array($output['order'])) {
            switch ($output['order'][0]['column']) {
                case 2:
                    $order = "contacts.last_name {$output['order'][0]['dir']}, contacts.first_name ASC";
                    break;

                case 4: // amount due
                    $order = "invoices.amount_due {$output['order'][0]['dir']}";
                    break;

                case 6:
                default:
                    $order = "invoices.due_date {$output['order'][0]['dir']}";
                    break;
            }
        } else {
            $order = "vehicle_log.start_time DESC";
        }

        $conditions = [];
        if (!empty($output['search']['value'])) {
            $conditions[] = "`vehicles`.`numberplate` like '%{$output['search']['value']}%'";
        }
        if (!empty($output['button'])) {
            $status = strtoupper($output['button']);
            $conditions[] = "`vehicles`.`status` = '{$status}'";
        } else {
            $conditions[] = "`vehicles`.`status` = 'Active'";
        }

        $fields = [
            'vehicle_log.id',
            'vehicles.numberplate',
            'vehicle_log.user_id',
            'vehicle_log.start_time',
            'vehicle_log.start_kilometres',
            'vehicle_log.end_time',
            'vehicle_log.end_kilometres',
            'vehicle_log.used_for',
            'vehicle_log.notes'
        ];

        $sql = "SELECT " . implode(',', $fields) . " FROM `vehicles` 
        LEFT JOIN `vehicle_log` ON (vehicles.id = vehicle_log.vehicle_id) 
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY {$order} 
        LIMIT {$output['start']}, {$output['length']}";

        $vehicle_log = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $output['recordsTotal'] = $this->pdo->query("SELECT count(*) FROM vehicle_log")->fetchColumn();
        $output['recordsFiltered'] = $this->pdo->query("SELECT count(*) FROM vehicles left join vehicle_log on vehicles.id= vehicle_log.vehicle_id WHERE " . implode(' AND ', $conditions))->fetchColumn();


        if (count($vehicle_log)) {
            foreach ($vehicle_log as $k => $row) {

                $output['data'][] = [
                    'id' => $row['id'],
                    'numberplate' => $row['numberplate'],
                    'user_id' => $row['user_id'],
                    'start_time' => $row['start_time'],
                    'start_kilometres' => $row['start_kilometres'],
                    'end_time' => $row['end_time'],
                    'end_kilometres' => $row['end_kilometres'],
                    'used_for' => $row['used_for'],
                    'notes' => $row['notes']
                ];
            }
            $output['row'] = $row;
        }

        return json_encode($output);
    }

    public function createInvoice($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[Invoices:Create]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setReference('Ref-' . $this->getRandNum())
            ->setDueDate(new DateTime('2017-01-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCPAY)
            ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
        $result = $apiInstance->createInvoice($xeroTenantId, $invoice);
        //[/Invoices:Create]

        $str = $str . "Create Invoice total amount: " . $result->getInvoices()[0]->getTotal() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createInvoices($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[Invoices:CreateMulti]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $arr_invoices = [];

        $invoice_1 = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice_1->setReference('Ref-' . $this->getRandNum())
            ->setDueDate(new DateTime('2019-12-10'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCPAY)
            ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
        array_push($arr_invoices, $invoice_1);

        $invoice_2 = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice_2->setReference('Ref-' . $this->getRandNum())
            ->setDueDate(new DateTime('2019-12-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCPAY)
            ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
        array_push($arr_invoices, $invoice_2);

        $invoices = new XeroAPI\XeroPHP\Models\Accounting\Invoices;
        $invoices->setInvoices($arr_invoices);

        $result = $apiInstance->createInvoices($xeroTenantId, $invoices);
        //[/Invoices:CreateMulti]

        $str = $str . "Create Invoice 1 total amount: " . $result->getInvoices()[0]->getTotal() . " and Create Invoice 2 total amount: " . $result->getInvoices()[1]->getTotal() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateInvoice($xeroTenantId, $apiInstance)
    {
        $str = '';
        $new = $this->createInvoice($xeroTenantId, $apiInstance, true);
        $guid = $new->getInvoices()[0]->getInvoiceID();

        //[Invoices:Update]
        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setReference('Ref-' . $this->getRandNum());
        $result = $apiInstance->updateInvoice($xeroTenantId, $guid, $invoice);
        //[/Invoices:Update]

        $str = $str . "Update Invoice: " . $result->getInvoices()[0]->getReference() . "<br>";

        return $str;
    }

    public function deleteInvoice($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createInvoiceDraft($xeroTenantId, $apiInstance, true);
        $invoiceId = $new->getInvoices()[0]->getInvoiceID();

        //[Invoices:Delete]
        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_DELETED);
        $result = $apiInstance->updateInvoice($xeroTenantId, $invoiceId, $invoice);
        //[/Invoices:Delete]

        $str = $str . "Delete Invoice";

        return $str;
    }

    public function voidInvoice($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createInvoice($xeroTenantId, $apiInstance, true);
        $invoiceId = $new->getInvoices()[0]->getInvoiceID();

        //[Invoices:Void]
        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_VOIDED);
        $result = $apiInstance->updateInvoice($xeroTenantId, $invoiceId, $invoice);
        //[/Invoices:Void]

        $str = $str . "Void Invoice";

        return $str;
    }

    public function getInvoiceReminder($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[InvoiceReminders:Read]
// READ
        $result = $apiInstance->getInvoiceReminders($xeroTenantId);
        //[/InvoiceReminders:Read]

        $str = $str . "Invoice Reminder Enabled?: ";
        if ($result->getInvoiceReminders()[0]->getEnabled() == 1) {
            $str = $str . "YES";
        } else {
            $str = $str . "NO";
        }

        return $str;
    }

    public function getItem($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Items:Read]
// READ ALL
        $result = $apiInstance->getItems($xeroTenantId);
        //[/Items:Read]

        $str = $str . "Get Items total: " . count($result->getItems()) . "<br>";

        if ($returnObj) {
            return $result->getItems()[0];
        } else {
            return $str;
        }
    }

    public function createItem($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Items:Create]
        $item = new XeroAPI\XeroPHP\Models\Accounting\Item;

        $item->setName('My Item-' . $this->getRandNum())
            ->setCode($this->getRandNum())
            ->setDescription("This is my Item description.")
            ->setIsTrackedAsInventory(false);
        $result = $apiInstance->createItem($xeroTenantId, $item);
        //[/Items:Create]

        $str = $str . "Create item: " . $result->getItems()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createItems($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Items:CreateMulti]
        $arr_items = [];

        $item_1 = new XeroAPI\XeroPHP\Models\Accounting\Item;
        $item_1->setName('My Item-' . $this->getRandNum())
            ->setCode($this->getRandNum())
            ->setDescription("This is my Item description.")
            ->setIsTrackedAsInventory(false);
        array_push($arr_items, $item_1);

        $item_2 = new XeroAPI\XeroPHP\Models\Accounting\Item;
        $item_2->setName('My Item-' . $this->getRandNum())
            ->setCode($this->getRandNum())
            ->setDescription("This is my Item description.")
            ->setIsTrackedAsInventory(false);
        array_push($arr_items, $item_2);

        $items = new XeroAPI\XeroPHP\Models\Accounting\Items;
        $items->setItems($arr_items);

        $result = $apiInstance->createItems($xeroTenantId, $items);
        //[/Items:CreateMulti]

        $str = $str . "Create item 1: " . $result->getItems()[0]->getName() . " and Create item 2: " . $result->getItems()[1]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateItem($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createItem($xeroTenantId, $apiInstance, true);
        $itemId = $new->getItems()[0]->getItemId();
        $code = $new->getItems()[0]->getCode();

        //[Items:Update]
        $item = new XeroAPI\XeroPHP\Models\Accounting\Item;
        $item->setName('Change Item-' . $this->getRandNum())
            ->setCode($code);
        $result = $apiInstance->updateItem($xeroTenantId, $itemId, $item);
        //[/Items:Update]

        $str = $str . "Update item: " . $result->getItems()[0]->getName() . "<br>";

        return $str;
    }

    public function deleteItem($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createItem($xeroTenantId, $apiInstance, true);
        $itemId = $new->getItems()[0]->getItemId();

        //[Items:Delete]
        $result = $apiInstance->deleteItem($xeroTenantId, $itemId);
        //[/Items:Delete]

        $str = $str . "Item deleted <br>";

        return $str;
    }

    public function getJournal($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';
        //[Journals:Read]
// READ ALL
        $result = $apiInstance->getJournals($xeroTenantId);
        //[/Journals:Read]
        $str = $str . "Get Journals total: " . count($result->getJournals()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function getLinkedTransaction($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[LinkedTransactions:Read]
// READ ALL
        $result = $apiInstance->getLinkedTransactions($xeroTenantId);
        //[/LinkedTransactions:Read]

        $str = $str . "Get LinkedTransactions total: " . count($result->getLinkedTransactions()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createLinkedTransaction($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createInvoiceAccPay($xeroTenantId, $apiInstance, true);
        $guid = $new->getInvoices()[0]->getInvoiceID();
        $lineitemid = $new->getInvoices()[0]->getLineItems()[0]->getLineItemId();

        //[LinkedTransactions:Create]
        $linkedtransaction = new XeroAPI\XeroPHP\Models\Accounting\LinkedTransaction;
        $linkedtransaction->setSourceTransactionID($guid)
            ->setSourceLineItemID($lineitemid);

        $result = $apiInstance->createLinkedTransaction($xeroTenantId, $linkedtransaction);
        //[/LinkedTransactions:Create]

        $str = $str . "Created LinkedTransaction ID: " . $result->getLinkedTransactions()[0]->getLinkedTransactionID();

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateLinkedTransaction($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $new = $this->createLinkedTransaction($xeroTenantId, $apiInstance, true);
        $linkedtransactionId = $new->getLinkedTransactions()[0]->getLinkedTransactionID();

        $invNew = $this->createInvoiceAccRec($xeroTenantId, $apiInstance, true);
        $invoiceId = $invNew->getInvoices()[0]->getInvoiceID();
        $lineitemid = $invNew->getInvoices()[0]->getLineItems()[0]->getLineItemId();
        $contactid = $invNew->getInvoices()[0]->getContact()->getContactId();

        //[LinkedTransactions:Update]
        $linkedtransaction = new XeroAPI\XeroPHP\Models\Accounting\LinkedTransaction;
        $linkedtransaction->setTargetTransactionID($invoiceId)
            ->setTargetLineItemID($lineitemid)
            ->setContactID($contactid);

        $linkedtransactions = new XeroAPI\XeroPHP\Models\Accounting\LinkedTransactions;
        $arr_linkedtransactions = [];
        array_push($arr_linkedtransactions, $linkedtransaction);
        $linkedtransactions->setLinkedTransactions($arr_linkedtransactions);

        $result = $apiInstance->updateLinkedTransaction($xeroTenantId, $linkedtransactionId, $linkedtransactions);
        //[/LinkedTransactions:Update]

        $str = $str . "Updated LinkedTransaction ID: " . $result->getLinkedTransactions()[0]->getLinkedTransactionID();

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function deleteLinkedTransaction($xeroTenantId, $apiInstance)
    {
        $str = '';

        // Need a linked transaction to work with ... so create one.
        $new = $this->createLinkedTransaction($xeroTenantId, $apiInstance, true);
        $linkedtransactionId = $new->getLinkedTransactions()[0]->getLinkedTransactionID();

        //[LinkedTransactions:Delete]
        $result = $apiInstance->deleteLinkedTransaction($xeroTenantId, $linkedtransactionId);
        //[/LinkedTransactions:Delete]

        $str = $str . "LinkedTransaction Deleted";

        return $str;
    }

    public function getManualJournal($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[ManualJournals:Read]
        $result = $apiInstance->getManualJournals($xeroTenantId);
        //[/ManualJournals:Read]
        $str = $str . "Get ManualJournals: " . count($result->getManualJournals()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createManualJournal($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $credit = $this->getJournalLineCredit();
        $debit = $this->getJournalLineDebit();

        //[ManualJournals:Create]
        $manualjournal = new XeroAPI\XeroPHP\Models\Accounting\ManualJournal;

        $arr_journallines = [];
        array_push($arr_journallines, $credit);
        array_push($arr_journallines, $debit);

        $manualjournal->setNarration('MJ from SDK -' . $this->getRandNum())
            ->setJournalLines($arr_journallines);

        $result = $apiInstance->createManualJournal($xeroTenantId, $manualjournal);
        //[/ManualJournals:Create]

        $str = $str . "Create ManualJournal: " . $result->getManualJournals()[0]->getNarration() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createManualJournals($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $credit = $this->getJournalLineCredit();
        $debit = $this->getJournalLineDebit();

        //[ManualJournals:CreateMulti]
        $arr_journallines = [];
        array_push($arr_journallines, $credit);
        array_push($arr_journallines, $debit);

        $arr_manualjournals = [];
        $manualjournal_1 = new XeroAPI\XeroPHP\Models\Accounting\ManualJournal;
        $manualjournal_1->setNarration('MJ from SDK -' . $this->getRandNum())
            ->setJournalLines($arr_journallines);
        array_push($arr_manualjournals, $manualjournal_1);

        $manualjournal_2 = new XeroAPI\XeroPHP\Models\Accounting\ManualJournal;
        $manualjournal_2->setNarration('MJ from SDK -' . $this->getRandNum())
            ->setJournalLines($arr_journallines);
        array_push($arr_manualjournals, $manualjournal_2);

        $manualjournals = new XeroAPI\XeroPHP\Models\Accounting\ManualJournals;
        $manualjournals->setManualJournals($arr_manualjournals);

        $result = $apiInstance->createManualJournal($xeroTenantId, $manualjournals);
        //[/ManualJournals:CreateMulti]

        $str = $str . "Create ManualJournal 1: " . $result->getManualJournals()[0]->getNarration() . " and Create ManualJournal 2: " . $result->getManualJournals()[1]->getNarration() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateManualJournal($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createManualJournal($xeroTenantId, $apiInstance, true);
        $manualjournalId = $new->getManualJournals()[0]->getManualJournalID();

        //[ManualJournals:Update]
        $manualjournal = new XeroAPI\XeroPHP\Models\Accounting\ManualJournal;
        $manualjournal->setNarration('MJ from SDK -' . $this->getRandNum());

        $manualjournals = new XeroAPI\XeroPHP\Models\Accounting\ManualJournals;
        $arr_manualjournals = [];
        array_push($arr_manualjournals, $manualjournal);
        $manualjournals->setManualJournals($arr_manualjournals);

        $result = $apiInstance->updateManualJournal($xeroTenantId, $manualjournalId, $manualjournals);
        //[/ManualJournals:Update]

        $str = $str . "Update ManualJournal: " . $result->getManualJournals()[0]->getNarration() . "<br>";

        return $str;
    }

    public function getOrganisation($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Organisations:Read]
        $result = $apiInstance->getOrganisations($xeroTenantId);
        //[/Organisations:Read]

        $str = $str . "Get Organisations: " . $result->getOrganisations()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result->getOrganisations()[0];
        } else {
            return $str;
        }
    }

    public function getOrganisationList(): string
    {
        $tenancies = $this->getTenancyList();
        $userTenancies = $this->getTenanciesforUser();

        foreach ($tenancies as $k => $row) {
            $disabled = 1;
            foreach ($userTenancies as $userRow) {
                if ($row['tenant_id'] == $userRow['tenantId']) {
                    $disabled = 0;
                }
            }
            $tenancies[$k]['disabled'] = $disabled;
        }

        return json_encode($tenancies);
    }

    // this needs to be saved in the session
    private function getTenanciesforUser()
    {
        if (array_key_exists('tenancies', $_SESSION)) {
            return $_SESSION['tenancies'];
        } else {
            $provider = Utilities::getProvider();
            $storage = new StorageClass();
            $accessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $storage->getRefreshToken()
            ]);
            $options = [
                'scope' => ['openid email profile offline_access accounting.transactions accounting.settings']
            ];
            $connectionsResponse = $provider->getAuthenticatedRequest(
                'GET',
                'https://api.xero.com/Connections',
                $accessToken->getToken(),
                $options
            );

            $xeroTenantIdArray = $provider->getParsedResponse($connectionsResponse);
            $_SESSION['tenancies'] = $xeroTenantIdArray;
            return $xeroTenantIdArray;
        }
    }

    public function getOverpayment($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Overpayments:Read]
        $result = $apiInstance->getOverpayments($xeroTenantId);
        //[/Overpayments:Read]

        $str = $str . "Get Overpayments: " . count($result->getOverpayments()) . "<br>";

        if ($returnObj) {
            return $result->getOverpayments()[0];
        } else {
            return $str;
        }
    }


    public function getPayment($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Payments:Read]
        $result = $apiInstance->getPayments($xeroTenantId);
        //[/Payments:Read]

        $str = $str . "Get Payments: " . count($result->getPayments()) . "<br>";

        if ($returnObj) {
            return $result->getPayments()[0];
        } else {
            return $str;
        }
    }

    public function createPayment($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $newInv = $this->createInvoiceAccRec($xeroTenantId, $apiInstance, true);
        $invoiceId = $newInv->getInvoices()[0]->getInvoiceID();
        $newAcct = $this->getBankAccount($xeroTenantId, $apiInstance);
        $accountId = $newAcct->getAccounts()[0]->getAccountId();

        //[Payments:Create]
        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setInvoiceID($invoiceId);

        $bankaccount = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $bankaccount->setAccountID($accountId);

        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setInvoice($invoice)
            ->setAccount($bankaccount)
            ->setAmount("2.00");

        $result = $apiInstance->createPayment($xeroTenantId, $payment);
        //[/Payments:Create]

        $str = $str . "Create Payment ID: " . $result->getPayments()[0]->getPaymentID() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createPayments($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $newInv = $this->createInvoiceAccRec($xeroTenantId, $apiInstance, true);
        $invoiceId = $newInv->getInvoices()[0]->getInvoiceID();
        $newAcct = $this->getBankAccount($xeroTenantId, $apiInstance);
        $accountId = $newAcct->getAccounts()[0]->getAccountId();

        //[Payments:CreateMulti]
        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setInvoiceID($invoiceId);

        $bankaccount = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $bankaccount->setAccountID($accountId);

        $arr_payments = [];

        $payment_1 = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment_1->setInvoice($invoice)
            ->setAccount($bankaccount)
            ->setAmount("2.00");
        array_push($arr_payments, $payment_1);

        $payment_2 = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment_2->setInvoice($invoice)
            ->setAccount($bankaccount)
            ->setAmount("2.00");
        array_push($arr_payments, $payment_2);

        $payments = new XeroAPI\XeroPHP\Models\Accounting\Payments;
        $payments->setPayments($arr_payments);

        $result = $apiInstance->createPayment($xeroTenantId, $payments);
        //[/Payments:CreateMulti]

        $str = $str . "Create Payment ID: " . $result->getPayments()[0]->getPaymentID() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function deletePayment($xeroTenantId, $apiInstance)
    {
        $str = '';

        $newPayment = $this->createPayment($xeroTenantId, $apiInstance, true);
        $paymentId = $newPayment->getPayments()[0]->getPaymentID();

        //[Payments:Delete]
        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setPaymentID($paymentId)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\PAYMENT::STATUS_DELETED);

        $result = $apiInstance->deletePayment($xeroTenantId, $paymentId, $payment);
        //[/Payments:Delete]

        $str = $str . "Payment deleted ID: " . $result->getPayments()[0]->getPaymentId() . "<br>";

        return $str;
    }

    public function getPrepayment($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Prepayments:Read]
// READ ALL
        $result = $apiInstance->getPrepayments($xeroTenantId);
        //[/Prepayments:Read]
        $str = $str . "Get Prepayments: " . count($result->getPrepayments()) . "<br>";

        if ($returnObj) {
            return $result->getPrepayments()[0];
        } else {
            return $str;
        }
    }

    public function createPrepayment($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitem = $this->getLineItemForPrepayment($xeroTenantId, $apiInstance);
        $lineitems = [];
        array_push($lineitems, $lineitem);

        $getAccount = $this->getBankAccount($xeroTenantId, $apiInstance);
        $accountId = $getAccount->getAccounts()[0]->getAccountId();
        $accountCode = $getAccount->getAccounts()[0]->getCode();

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        if (count($getAccount->getAccounts())) {

            //[Prepayments:Create]
            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setContactId($contactId);

            $bankAccount = new XeroAPI\XeroPHP\Models\Accounting\Account;
            $bankAccount->setCode($accountCode)
                ->setAccountId($accountId);

            $prepayment = new XeroAPI\XeroPHP\Models\Accounting\BankTransaction;
            $prepayment->setReference('Ref-' . $this->getRandNum())
                ->setDate(new DateTime('2017-01-02'))
                ->setType(XeroAPI\XeroPHP\Models\Accounting\BankTransaction::TYPE_RECEIVE_PREPAYMENT)
                ->setLineItems($lineitems)
                ->setContact($contact)
                ->setLineAmountTypes("NoTax")
                ->setBankAccount($bankAccount)
                ->setReference("Sid Prepayment 2");

            $result = $apiInstance->createBankTransaction($xeroTenantId, $prepayment);
            //[/Prepayments:Create]
        }

        $str = $str . "Created prepayment ID: " . $result->getBankTransactions()[0]->getPrepaymentId() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function allocatePrepayment($xeroTenantId, $apiInstance)
    {
        $str = '';

        $invNew = $this->createInvoiceAccRec($xeroTenantId, $apiInstance, true);
        $invoiceId = $invNew->getInvoices()[0]->getInvoiceID();
        $newPrepayement = $this->createPrepayment($xeroTenantId, $apiInstance, true);
        $prepaymentId = $newPrepayement->getBankTransactions()[0]->getPrepaymentId();

        //[Prepayments:Allocate]
        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setInvoiceID($invoiceId);

        $prepayment = new XeroAPI\XeroPHP\Models\Accounting\Prepayment;
        $prepayment->setPrepaymentID($prepaymentId);

        $allocation = new XeroAPI\XeroPHP\Models\Accounting\Allocation;
        $allocation->setInvoice($invoice)
            ->setAmount("1.00");
        $arr_allocation = [];
        array_push($arr_allocation, $allocation);

        $allocations = new XeroAPI\XeroPHP\Models\Accounting\Allocations;
        $allocations->setAllocations($arr_allocation);

        $result = $apiInstance->createPrepaymentAllocation($xeroTenantId, $prepaymentId, $allocation);
        //[/Prepayments:Allocate]

        $str = $str . "Allocate Prepayment amount: " . $result->getAllocations()[0]->getAmount() . "<br>";

        return $str;
    }

    public function refundPrepayment($xeroTenantId, $apiInstance)
    {
        $str = '';

        $account = $this->getBankAccount($xeroTenantId, $apiInstance);
        $accountId = $account->getAccounts()[0]->getAccountId();
        $newPrepayment = $this->createPrepayment($xeroTenantId, $apiInstance, true);
        $prepaymentId = $newPrepayment->getBankTransactions()[0]->getPrepaymentID();

        //[Prepayments:Refund]
        $bankaccount = new XeroAPI\XeroPHP\Models\Accounting\Account;
        $bankaccount->setAccountId($accountId);

        $prepayment = new XeroAPI\XeroPHP\Models\Accounting\Prepayment;
        $prepayment->setPrepaymentId($prepaymentId);

        $payment = new XeroAPI\XeroPHP\Models\Accounting\Payment;
        $payment->setPrepayment($prepayment)
            ->setAccount($bankaccount)
            ->setAmount("2.00");

        $result = $apiInstance->createPayment($xeroTenantId, $payment);
        //[/Prepayments:Refund]

        $str = $str . "Create Prepayment Refund (Payments ID): " . $result->getPayments()[0]->getPaymentId() . " <br>";

        return $str;
    }

    public function getPurchaseOrder($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[PurchaseOrders:Read]
// READ ALL
        $result = $apiInstance->getPurchaseOrders($xeroTenantId);
        //[/PurchaseOrders:Read]

        $str = $str . "Total purchase orders: " . count($result->getPurchaseOrders()) . "<br>";

        if ($returnObj) {
            return $result->getPurchaseOrders()[0];
        } else {
            return $str;
        }
    }

    public function createPurchaseOrder($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitem = $this->getLineItemForPurchaseOrder($xeroTenantId, $apiInstance);
        $lineitems = [];
        array_push($lineitems, $lineitem);

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[PurchaseOrders:Create]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $purchaseorder = new XeroAPI\XeroPHP\Models\Accounting\PurchaseOrder;
        $purchaseorder->setReference('Ref original -' . $this->getRandNum())
            ->setContact($contact)
            ->setLineItems($lineitems);

        $result = $apiInstance->createPurchaseOrder($xeroTenantId, $purchaseorder);
        //[/PurchaseOrders:Create]

        $str = $str . "Created PurchaseOrder Number: " . $result->getPurchaseOrders()[0]->getPurchaseOrderNumber() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }


    public function updatePurchaseOrder($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createPurchaseOrder($xeroTenantId, $apiInstance, true);
        $purchaseorderId = $new->getPurchaseOrders()[0]->getPurchaseOrderID();

        //[PurchaseOrders:Update]
        $purchaseorder = new XeroAPI\XeroPHP\Models\Accounting\PurchaseOrder;
        $purchaseorder->setReference('New Ref -' . $this->getRandNum());
        $result = $apiInstance->updatePurchaseOrder($xeroTenantId, $purchaseorderId, $purchaseorder);
        //[/PurchaseOrders:Update]

        $str = $str . "Updated Purchase Order: " . $result->getPurchaseOrders()[0]->getReference() . "<br>";

        return $str;
    }

    public function deletePurchaseOrder($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createPurchaseOrder($xeroTenantId, $apiInstance, true);
        $purchaseorderId = $new->getPurchaseOrders()[0]->getPurchaseOrderID();

        //[PurchaseOrders:Delete]
        $purchaseorder = new XeroAPI\XeroPHP\Models\Accounting\PurchaseOrder;
        $purchaseorder->setStatus(XeroAPI\XeroPHP\Models\Accounting\PurchaseOrder::STATUS_DELETED);
        $result = $apiInstance->updatePurchaseOrder($xeroTenantId, $purchaseorderId, $purchaseorder);
        //[/PurchaseOrders:Delete]

        $str = $str . "Deleted PurchaseOrder: " . $result->getPurchaseOrders()[0]->getReference() . "<br>";

        return $str;
    }

    public function getReceipt($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Receipts:Read]
// READ ALL
        $result = $apiInstance->getReceipts($xeroTenantId);
        //[/Receipts:Read]
        $str = $str . "Get Receipts: " . count($result->getReceipts()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createReceipt($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $allUsers = $this->getUser($xeroTenantId, $apiInstance, true);
        $userId = $allUsers->getUsers()[0]->getUserID();

        $lineitem = $this->getLineItemForReceipt($xeroTenantId, $apiInstance);

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        if (count($allUsers->getUsers())) {
            //[Receipts:Create]
            $lineitems = [];
            array_push($lineitems, $lineitem);
            $user = new XeroAPI\XeroPHP\Models\Accounting\User;
            $user->setUserID($userId);

            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setContactId($contactId);

            // CREATE RECEIPT
            $receipt = new XeroAPI\XeroPHP\Models\Accounting\Receipt;
            $receipt->setDate(new DateTime('2017-01-02'))
                ->setLineItems($lineitems)
                ->setContact($contact)
                ->setTotal(20.00)
                ->setUser($user);

            $receipts = new XeroAPI\XeroPHP\Models\Accounting\Receipts;
            $arr_receipts = [];
            array_push($arr_receipts, $receipt);
            $receipts->setReceipts($arr_receipts);
            $result = $apiInstance->createReceipt($xeroTenantId, $receipts);
            //[/Receipts:Create]
        }

        $str = $str . "Create Receipt: " . $result->getReceipts()[0]->getReceiptID() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateReceipt($xeroTenantId, $apiInstance)
    {
        $str = '';

        $new = $this->createReceipt($xeroTenantId, $apiInstance, true);
        $receiptId = $new->getReceipts()[0]->getReceiptID();
        $user = new XeroAPI\XeroPHP\Models\Accounting\User;
        $user->setUserID($new->getReceipts()[0]->getUser()->getUserId());

        //[Receipts:Update]
        $receipt = new XeroAPI\XeroPHP\Models\Accounting\Receipt;
        $receipt->setReference('Add Ref to receipt ' . $this->getRandNum())
            ->setUser($user);
        $receipts = new XeroAPI\XeroPHP\Models\Accounting\Receipts;
        $arr_receipts = [];
        array_push($arr_receipts, $receipt);
        $receipts->setReceipts($arr_receipts);
        $result = $apiInstance->updateReceipt($xeroTenantId, $receiptId, $receipts);
        //[/Receipts:Update]

        $str = $str . "Updated Receipt: " . $result->getReceipts()[0]->getReceiptID() . "<br>";

        return $str;
    }

    public function getRepeatingInvoice($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[RepeatingInvoices:Read]
// READ ALL
        $result = $apiInstance->getRepeatingInvoices($xeroTenantId);
        //[/RepeatingInvoices:Read]
        $str = $str . "Get RepeatingInvoices: " . count($result->getRepeatingInvoices()) . "<br>";

        if ($returnObj) {
            return $result->getRepeatingInvoices()[0];
        } else {
            return $str;
        }
    }

    // REPORTS
    public function getTenNinetyNine($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:TenNinetyNine]
        $result = $apiInstance->getReportTenNinetyNine($xeroTenantId, 2018);
        //[/Reports:TenNinetyNine]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportName();

        return $str;
    }

    public function getAgedPayablesByContact($xeroTenantId, $apiInstance)
    {
        $str = '';

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();
        //[Reports:AgedPayablesByContact]
        $result = $apiInstance->getReportAgedPayablesByContact($xeroTenantId, $contactId);
        //[/Reports:AgedPayablesByContact]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getAgedReceivablesByContact($xeroTenantId, $apiInstance)
    {
        $str = '';

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[Reports:AgedReceivablesByContact]
        $result = $apiInstance->getReportAgedReceivablesByContact($xeroTenantId, $contactId);
        //[/Reports:AgedReceivablesByContact]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getBalanceSheet($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:BalanceSheet]
        $result = $apiInstance->getReportBalanceSheet($xeroTenantId);
        //[/Reports:BalanceSheet]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getBankSummary($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:BankSummary]
        $result = $apiInstance->getReportBankSummary($xeroTenantId);
        //[/Reports:BankSummary]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();


        return $str;
    }

    public function getBudgetSummary($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:BudgetSummary]
        $result = $apiInstance->getReportBudgetSummary($xeroTenantId);
        //[/Reports:BudgetSummary]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getExecutiveSummary($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:ExecutiveSummary]
        $result = $apiInstance->getReportExecutiveSummary($xeroTenantId);
        //[/Reports:ExecutiveSummary]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getProfitAndLoss($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:ProfitAndLoss]
        $result = $apiInstance->getReportProfitandLoss($xeroTenantId);
        //[/Reports:ProfitAndLoss]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getTrialBalance($xeroTenantId, $apiInstance)
    {
        $str = '';

        //[Reports:TrialBalance]
        $result = $apiInstance->getReportTrialBalance($xeroTenantId);
        //[/Reports:TrialBalance]

        $str = $str . "Report ID: " . $result->getReports()[0]->getReportId();

        return $str;
    }

    public function getTaxRate($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[TaxRates:Read]
// READ ALL
        $result = $apiInstance->getTaxRates($xeroTenantId);
        //[/TaxRates:Read]
        $str = $str . "Get TaxRates: " . count($result->getTaxRates()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createTaxRates($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[TaxRates:Create]
        $taxcomponent = new XeroAPI\XeroPHP\Models\Accounting\TaxComponent;
        $taxcomponent->setName('Tax-' . $this->getRandNum())
            ->setRate(5);

        $arr_taxcomponent = [];
        array_push($arr_taxcomponent, $taxcomponent);

        $taxrate = new XeroAPI\XeroPHP\Models\Accounting\TaxRate;
        $taxrate->setName('Rate -' . $this->getRandNum())
            ->setTaxComponents($arr_taxcomponent);

        $result = $apiInstance->createTaxRates($xeroTenantId, $taxrate);
        //[/TaxRates:Create]

        $str = $str . "Create TaxRate: " . $result->getTaxRates()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateTaxRate($xeroTenantId, $apiInstance)
    {
        $str = '';

        $newTaxRate = $this->createTaxRates($xeroTenantId, $apiInstance, true);
        $taxName = $newTaxRate->getTaxRates()[0]->getName();

        //[TaxRates:Update]
        $taxrate = new XeroAPI\XeroPHP\Models\Accounting\TaxRate;
        $taxrate->setName($taxName)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\TaxRate::STATUS_DELETED);
        $result = $apiInstance->updateTaxRate($xeroTenantId, $taxrate);
        //[/TaxRates:Update]
        $str = $str . "Update TaxRate: " . $result->getTaxRates()[0]->getName() . "<br>";
        return $str;
    }

    public function getTrackingCategory($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[TrackingCategories:Read]
// READ ALL
        $result = $apiInstance->getTrackingCategories($xeroTenantId);
        //[/TrackingCategories:Read]
        $str = $str . "Get TrackingCategories: " . count($result->getTrackingCategories()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createTrackingCategory($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[TrackingCategories:Create]
        $trackingcategory = new XeroAPI\XeroPHP\Models\Accounting\TrackingCategory;
        $trackingcategory->setName('Avengers -' . $this->getRandNum());
        $result = $apiInstance->createTrackingCategory($xeroTenantId, $trackingcategory);
        //[/TrackingCategories:Create]

        $str = $str . "Create TrackingCategory: " . $result->getTrackingCategories()[0]->getName() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function updateTrackingCategory($xeroTenantId, $apiInstance)
    {
        $str = '';

        $trackingCategories = $this->getTrackingCategory($xeroTenantId, $apiInstance, true);
        $trackingCategory = $trackingCategories->getTrackingCategories()[0];
        $trackingCategoryId = $trackingCategory->getTrackingCategoryId();

        //[TrackingCategories:Update]
        $trackingCategory->setName('Foobar' . $this->getRandNum());
        $result = $apiInstance->updateTrackingCategory($xeroTenantId, $trackingCategoryId, $trackingCategory);
        //[/TrackingCategories:Update]

        $str = $str . "Update TrackingCategory: " . $result->getTrackingCategories()[0]->getName() . "<br>";

        return $str;
    }

    // WEIRD VALIDATION
    //https://api-admin.hosting.xero.com/History/Detail?id=abdb9c2b-1f4c-42d3-bf3e-0665c4a4974c
    public function archiveTrackingCategory($xeroTenantId, $apiInstance)
    {
        $str = '';

        $getTrackingCategories = $this->getTrackingCategory($xeroTenantId, $apiInstance, true);
        $getTrackingCategory = $getTrackingCategories->getTrackingCategories()[0];
        $trackingCategoryId = $getTrackingCategory->getTrackingCategoryId();

        //[TrackingCategories:Archive]
        $trackingcategory = new XeroAPI\XeroPHP\Models\Accounting\TrackingCategory;
        $trackingcategory->setStatus(\XeroAPI\XeroPHP\Models\Accounting\TrackingCategory::STATUS_ARCHIVED);
        $result = $apiInstance->updateTrackingCategory($xeroTenantId, $trackingCategoryId, $trackingcategory);
        //[/TrackingCategories:Archive]

        $str = $str . "Archive TrackingCategory: " . $result->getTrackingCategories()[0]->getName() . "<br>";

        return $str;
    }

    public function deleteTrackingCategory($xeroTenantId, $apiInstance)
    {
        $str = '';

        $trackingCategories = $this->getTrackingCategory($xeroTenantId, $apiInstance, true);
        $trackingCategory = $trackingCategories->getTrackingCategories()[0];
        $trackingCategoryId = $trackingCategory->getTrackingCategoryId();

        //[TrackingCategories:Delete]
        $result = $apiInstance->deleteTrackingCategory($xeroTenantId, $trackingCategoryId);
        //[/TrackingCategories:Delete]
        $str = $str . "Delete TrackingCategory: " . $result->getTrackingCategories()[0]->getName() . "<br>";

        return $str;
    }

    public function createTrackingOptions($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';
        $trackingCategories = $this->getTrackingCategory($xeroTenantId, $apiInstance, true);
        $trackingCategory = $trackingCategories->getTrackingCategories()[0];
        $trackingCategoryId = $trackingCategory->getTrackingCategoryId();

        //[TrackingOptions:Create]
        $option = new XeroAPI\XeroPHP\Models\Accounting\TrackingOption;
        $option->setName('IronMan -' . $this->getRandNum());
        $result = $apiInstance->createTrackingOptions($xeroTenantId, $trackingCategoryId, $option);
        //[/TrackingOptions:Create]

        $str = $str . "Create TrackingOptions now Total: " . count($result->getOptions()) . "<br>";

        return $str;
    }

    public function deleteTrackingOptions($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';
        $trackingCategories = $this->getTrackingCategory($xeroTenantId, $apiInstance, true);
        $trackingCategory = $trackingCategories->getTrackingCategories()[0];
        $trackingCategoryId = $trackingCategory->getTrackingCategoryId();
        $optionId = $trackingCategory->getOptions()[3]->getTrackingOptionId();

        //[TrackingOptions:Delete]
        $result = $apiInstance->deleteTrackingOptions($xeroTenantId, $trackingCategoryId, $optionId);
        //[/TrackingOptions:Delete]
        $str = $str . "Delete TrackingOptions Name: " . $result->getOptions()[0]->getName() . "<br>";

        return $str;
    }

    public function updateTrackingOptions($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $trackingCategories = $this->getTrackingCategory($xeroTenantId, $apiInstance, true);
        $trackingCategory = $trackingCategories->getTrackingCategories()[0];
        $trackingCategoryId = $trackingCategory->getTrackingCategoryId();
        $optionId = $trackingCategory->getOptions()[0]->getTrackingOptionId();

        //[TrackingOptions:Update]
        $option = new XeroAPI\XeroPHP\Models\Accounting\TrackingOption;
        $option->setName('Hello' . $this->getRandNum());
        $result = $apiInstance->updateTrackingOptions($xeroTenantId, $trackingCategoryId, $optionId, $option);
        //[/TrackingOptions:Update]

        $str = $str . "Update TrackingOptions Name: " . $result->getOptions()[0]->getName() . "<br>";

        return $str;
    }

    public function getUser($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Users:Read]
// READ ALL
        $result = $apiInstance->getUsers($xeroTenantId);
        //[/Users:Read]
        $str = $str . "Get Users: " . count($result->getUsers()) . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    // HELPERS
    public function getRandNum()
    {
        $randNum = strval(rand(1000, 100000));

        return $randNum;
    }

    public function getLineItem()
    {

        $lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
        $lineitem->setDescription('Sample Item' . $this->getRandNum())
            ->setQuantity(1)
            ->setUnitAmount(20)
            ->setAccountCode("400");

        return $lineitem;
    }

    public function getLineItemForReceipt($xeroTenantId, $apiInstance)
    {
        $lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
        $lineitem->setDescription('My Receipt 1 -' . $this->getRandNum())
            ->setQuantity(1)
            ->setUnitAmount(20)
            ->setAccountCode("123");

        return $lineitem;
    }

    public function getLineItemForOverpayment($xeroTenantId, $apiInstance)
    {
        $account = $this->getAccRecAccount($xeroTenantId, $apiInstance);

        $lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
        $lineitem->setDescription('INV-' . $this->getRandNum())
            ->setQuantity(1)
            ->setUnitAmount(20)
            ->setAccountCode($account->getAccounts()[0]->getCode());
        return $lineitem;
    }

    public function getLineItemForPrepayment($xeroTenantId, $apiInstance)
    {
        $account = $this->getAccountExpense($xeroTenantId, $apiInstance);

        $lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
        $lineitem->setDescription('Something-' . $this->getRandNum())
            ->setQuantity(1)
            ->setUnitAmount(20)
            ->setAccountCode($account->getAccounts()[0]->getCode());
        return $lineitem;
    }

    public function getLineItemForPurchaseOrder($xeroTenantId, $apiInstance)
    {
        $account = $this->getAccountRevenue($xeroTenantId, $apiInstance);

        $lineitem = new XeroAPI\XeroPHP\Models\Accounting\LineItem;
        $lineitem->setDescription('PO-' . $this->getRandNum())
            ->setQuantity(1)
            ->setUnitAmount(20)
            ->setAccountCode($account->getAccounts()[0]->getCode());
        return $lineitem;
    }

    public function getBankAccount($xeroTenantId, $apiInstance)
    {
        // READ only ACTIVE
        $where = 'Status=="' . \XeroAPI\XeroPHP\Models\Accounting\Account::STATUS_ACTIVE . '" AND Type=="' . \XeroAPI\XeroPHP\Models\Accounting\Account::BANK_ACCOUNT_TYPE_BANK . '"';
        $result = $apiInstance->getAccounts($xeroTenantId, null, $where);

        return $result;
    }

    public function getAccRecAccount($xeroTenantId, $apiInstance)
    {
        $where = 'Status=="' . XeroAPI\XeroPHP\Models\Accounting\Account::STATUS_ACTIVE . '" AND SystemAccount=="' . XeroAPI\XeroPHP\Models\Accounting\Account::SYSTEM_ACCOUNT_DEBTORS . '"';
        $result = $apiInstance->getAccounts($xeroTenantId, null, $where);

        return $result;
    }

    public function getAccountExpense($xeroTenantId, $apiInstance)
    {

        $where = 'Status=="' . XeroAPI\XeroPHP\Models\Accounting\Account::STATUS_ACTIVE . '" AND Type=="' . XeroAPI\XeroPHP\Models\Accounting\Account::MODEL_CLASS_EXPENSE . '"';

        $result = $apiInstance->getAccounts($xeroTenantId, null, $where);

        return $result;
    }

    public function getAccountRevenue($xeroTenantId, $apiInstance)
    {

        $where = 'Status=="' . XeroAPI\XeroPHP\Models\Accounting\Account::STATUS_ACTIVE . '" AND Type=="' . XeroAPI\XeroPHP\Models\Accounting\Account::MODEL_CLASS_REVENUE . '"';

        $result = $apiInstance->getAccounts($xeroTenantId, null, $where);

        return $result;
    }

    public function createInvoiceAccPay($xeroTenantId, $apiInstance, $returnObj = false)
    {

        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;

        $invoice->setReference('Ref-' . $this->getRandNum())
            ->setDueDate(new DateTime('2017-01-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCPAY)
            ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
        $result = $apiInstance->createInvoice($xeroTenantId, $invoice);


        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createInvoiceDraft($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        //[Invoices:Create]
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;
        $invoice->setReference('Ref-' . $this->getRandNum())
            ->setDueDate(new DateTime('2017-01-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_DRAFT)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCPAY)
            ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
        $result = $apiInstance->createInvoice($xeroTenantId, $invoice);
        //[/Invoices:Create]

        $str = $str . "Create Invoice total amount: " . $result->getInvoices()[0]->getTotal() . "<br>";

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function createInvoiceAccRec($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contactId = $getContact->getContacts()[0]->getContactId();

        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($contactId);

        $invoice = new XeroAPI\XeroPHP\Models\Accounting\Invoice;

        $invoice->setReference('Ref-' . $this->getRandNum())
            ->setDueDate(new DateTime('2017-01-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\Invoice::TYPE_ACCREC)
            ->setLineAmountTypes(\XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes::EXCLUSIVE);
        $result = $apiInstance->createInvoice($xeroTenantId, $invoice);

        if ($returnObj) {
            return $result;
        } else {
            return $str;
        }
    }

    public function getJournalLineCredit()
    {
        $journalline = new XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine;
        $journalline->setLineAmount("20.00")
            ->setAccountCode("400");
        return $journalline;
    }

    public function getJournalLineDebit()
    {
        $journalline = new XeroAPI\XeroPHP\Models\Accounting\ManualJournalLine;
        $journalline->setLineAmount("-20.00")
            ->setAccountCode("620");
        return $journalline;
    }

    public function createCreditNoteAuthorised($xeroTenantId, $apiInstance)
    {

        $str = '';

        $lineitems = [];
        array_push($lineitems, $this->getLineItem());

        $getContact = $this->getContact($xeroTenantId, $apiInstance, true);
        $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact->setContactId($getContact->getContacts()[0]->getContactId());

        $creditnote = new XeroAPI\XeroPHP\Models\Accounting\CreditNote;

        $creditnote->setDate(new DateTime('2017-01-02'))
            ->setContact($contact)
            ->setLineItems($lineitems)
            ->setStatus(XeroAPI\XeroPHP\Models\Accounting\Invoice::STATUS_AUTHORISED)
            ->setType(XeroAPI\XeroPHP\Models\Accounting\CreditNote::TYPE_ACCPAYCREDIT);
        $result = $apiInstance->createCreditNote($xeroTenantId, $creditnote);

        return $result;
    }

    public function getTaxComponent($xeroTenantId, $apiInstance)
    {
        $taxcomponent = new \XeroPHP\Models\Accounting\TaxRate\TaxComponent($xeroTenantId, $apiInstance);
        $taxcomponent->setName('Tax-' . $this->getRandNum())
            ->setRate(5);
        return $taxcomponent;
    }

}
