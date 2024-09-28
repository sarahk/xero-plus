<?php
//use XeroAPI\XeroPHP\AccountingObjectSerializer;
namespace App;

use App\Models\Traits\DebugTrait;
use App\Models\Traits\FunctionsTrait;
use App\Models\Traits\LoggerTrait;
use App\Models\AddressModel;
use App\Models\ContactModel;
use App\Models\ContractModel;
use App\Models\InvoiceModel;
use App\Models\PhoneModel;
use App\Models\SettingModel;
use App\Models\TenancyModel;
use PDO;
use Exception;

class XeroClass
{
    use DebugTrait;
    use FunctionsTrait;
    use LoggerTrait;

    protected $apiInstance;
    public string $xeroTenantId;
    public array $tenancies = [];
    protected PDO $pdo;

    protected $storage;


    function __construct($apiInstance = '')
    {
        $storage = new StorageClass();
        $this->storage = $storage;

        $config = \XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken((string)$storage->getSession()['token']);
        $this->apiInstance = new \XeroAPI\XeroPHP\Api\AccountingApi(
            new \GuzzleHttp\Client(),
            $config
        );

        //$this->apiInstance = $apiInstance;
        $pdo = Utilities::getPDO();
        $this->pdo = $pdo;

    }

    public function getTenantIdArray()
    {
        $provider = getProvider();

        //$apiResponse = $this->apiInstance->getOrganisations($xeroTenantId);
        //$message = '<p>Organisation Name: ' . $apiResponse->getOrganisations()[0]->getName();
        //$message .= '<p>' . $xeroTenantId;

        $accessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $this->storage->getRefreshToken()
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

        return $provider->getParsedResponse($connectionsResponse);
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
            return "Get accounts total: " . count($result->getAccounts()) . "<br>
                    Get ACTIVE accounts total: " . count($result2->getAccounts()) . "<br>";
        }
    }


    public function getBankTransaction($xeroTenantId, $apiInstance)
    {

        //[BankTransactions:Read]
// READ ALL
        $result = $apiInstance->getBankTransactions($xeroTenantId);
        // READ only ACTIVE
        $where = 'Status=="AUTHORISED"';
        $result2 = $apiInstance->getBankTransactions($xeroTenantId, null, $where);
        //[/BankTransactions:Read]

        return "Get BankTransaction total: " . count($result->getBankTransactions()) . "<br>"
            . "Get ACTIVE BankTransaction total: " . count($result2->getBankTransactions()) . "<br>";
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


    public function getOutput(): array
    {
        $output = ['data' => []];

        $output['draw'] = filter_input(INPUT_GET, 'draw', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 1]]);
        $output['start'] = filter_input(INPUT_GET, 'start', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 1]]);
        $output['length'] = filter_input(INPUT_GET, 'length', FILTER_SANITIZE_NUMBER_INT, ['options' => ['default' => 10]]);
        //$output['search'] = filter_input(INPUT_GET, 'search', FILTER_DEFAULT, ['options' => ['default' => '']]);
        //$output['order'] = filter_input(INPUT_GET, 'order', FILTER_DEFAULT, ['options' => ['default' => '']]);
        $output['order'] = $_GET['order'] ?? [0 => ['column' => '0', 'dir' => 'ASC']];
        $output['search'] = $_GET['search']['value'] ?? '';
        // getInvoice
        $output['invoice_status'] = filter_input(INPUT_GET, 'invoice_status');
        $output['dates'] = filter_input(INPUT_GET, 'dates');
        $output['contact_status'] = filter_input(INPUT_GET, 'dates');
        $output['button'] = filter_input(INPUT_GET, 'button');

        // prima
        $output['key'] = filter_input(INPUT_GET, 'key', FILTER_DEFAULT);

        return $output;
    }

    public function saveXeroTimestamp($category, $xeroTenantId)
    {
        $objSetting = new SettingModel($this->pdo);
        $objSetting->save([
            'xeroTenantId' => $xeroTenantId,
            'category' => $category,
            'key' => 'xerocheck',
            'value' => time() // should this be time or something else?
        ]);

    }

    protected function getAddressesFromXeroObject($list): array
    {
        $addresses = [];
        foreach ($list as $address) {
            $address_line_1 = $address->getAddressLine1();

            if (!empty($address_line_1)) {
                $addresses[] = [
                    'address_type' => $address->getAddressType(),
                    'address_line_1' => $address_line_1,
                    'address_line_2' => $address->getAddressLine2(),
                    'city' => $address->getCity(),
                    'postal_code' => $address->getPostalCode()
                ];
            }
        }
        return $addresses;
    }

    protected function getPhonesFromXeroObject($list): array
    {
        $phones = [];
        foreach ($list as $phone) {
            $number = $phone->getPhoneNumber();
            if (!empty($number)) {
                $phones[] = [
                    'phone_type' => $phone->getPhoneType(),
                    'phone_number' => $number,
                    'phone_area_code' => $phone->getPhoneAreaCode(),
                    'phone_country_code' => $phone->getPhoneCountryCode()
                ];
            }
        }
        return $phones;
    }

    // internal call
    // to see the result data
    // https://api-explorer.xero.com/accounting/contacts/getcontact?path-contactid=e3e88c63-d089-4e4f-b665-a87e9796d66b
    public function getSingleContact($xeroTenantId, $contact_id): array
    {
        $result = $this->apiInstance->getContact($xeroTenantId, $contact_id);

        //$this->debug($result);
        if (count($result)) {

            $row = $result->getContacts();
            $addresses = $this->getAddressesFromXeroObject($row[0]->getAddresses());
            $phones = $this->getPhonesFromXeroObject($row[0]->getPhones());


            $save = ['contact_id' => $contact_id,
                'first_name' => $row[0]->getFirstName(),
                'last_name' => $row[0]->getLastName(),
                'email_address' => $row[0]->getEmailAddress(),
                'xero_status' => $row[0]->getContactStatus(),
                'addresses' => $addresses,
                'phones' => $phones,
                'xerotenant_id' => $xeroTenantId,
                'stub' => 0,
                'xeroRefresh' => true
            ];
            //$this->debug(['save' => $save]);
            $contact = new ContactModel($this->pdo);
            $id = $contact->getContactId($contact_id);
            if ($id) {
                $save['id'] = $id;
                $save['xeroRefresh'] = false;
            }

            $save['id'] = $contact->prepAndSave(['contact' => $save]);
            return $save;
        }
        return ['id' => 0];
    }


    public function getSingleContactId($xeroTenantId, $contact_id): int
    {
        $result = $this->getSingleContact($xeroTenantId, $contact_id);
        return $result['id'];
    }

    protected function getSingleContactStub($xeroTenantId, $row)
    {
        $contact = $row['contact'];

        $save = [
            'contact_id' => $contact->getContactId(),
            'name' => $contact->getName(),
            'xerotenant_id' => $xeroTenantId,
            'stub' => 1
        ];
        $contact = new ContactModel($this->pdo);
        return $contact->prepAndSave(['contact' => $save]);
    }

    public function getContactRefresh(): void// auckland,waikato,bop
    {
        $k = 1;
        $tenantName = $_GET['tenancy'];
        $xeroTenantId = $this->getXeroTenantId($this->pdo, $tenantName);
        // public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)

        // shown in full for readability
        $contact = new ContactModel($this->pdo);
        $updated_date_utc = $contact->getUpdatedDate($xeroTenantId);
        $where = $order = $ids = null;
        $page = 1;
        $include_archived = true;
        // unused $summary_only = false, $search_term = null)


        $result = $this->apiInstance->getContacts($xeroTenantId, $updated_date_utc, $where, $order, $ids, $page, $include_archived);
        $data = $result->getContacts();

        if (count($data)) {
            foreach ($data as $k => $row) {
                //if ($row->getIsCustomer() || true) {
                $this->saveContactRow($row);
                //}
            }
        }
        $this->saveXeroTimestamp('Contact', $xeroTenantId);

    }


    public function saveContactRow($row)
    {
        $values = [
            'contact' => [
                'id' => null,
                'contact_id' => $row->getContactId(),
                'contact_status' => $row->getContactStatus(),
                'name' => $row->getName(),
                'first_name' => $row->getFirstName(),
                'last_name' => $row->getLastName(),
                'email_address' => $row->getEmailAddress(),
                'is_supplier' => $row->getIsSupplier(),
                'is_customer' => $row->getIsCustomer(),
                'updated_date_utc' => getDateFromXero($row['updated_date_utc'])
            ]
        ];
        foreach ($row['addresses'] as $addy) {
            $values['addresses'][] = [
                'ckcontact_id' => $values['contact']['id'],
                'contact_id' => $values['contact']['contact_id'],
                'address_type' => $addy->getAddressType(),
                'address_line1' => $addy->getAddressLine1(),
                'address_line2' => $addy->getAddressLine2(),
                'city' => $addy->getCity(),
                'postal_code' => $addy->getPostalCode(),
            ];
        }
        foreach ($row['phones'] as $phone) {
            $values['phones'][] = [
                'contact_id' => $values['contact']['contact_id'],
                'phone_type' => $phone->getPhoneType(),
                'phone_number' => $phone->getPhoneNumber(),
                'phone_area_code' => $phone->getPhoneAreaCode(),
                'phone_country_code' => $phone->getPhoneCountryCode()
            ];
        }
        $this->debug([$values['contact']['name'], $values['contact']['first_name'], $values['contact']['last_name'], $values['contact']['contact_id']]);

        $contact = new ContactModel($this->pdo);
        $contact->prepAndSave($values);
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
                $this->debug($apiResponse->getContacts());
            } else {
                $message = "No contacts found matching filter criteria";
            }
        } catch (Exception $e) {
            echo 'Exception when calling AccountingApi->getContacts: ', $e->getMessage(), PHP_EOL;
        }
    }

    // get all the tenancies from the database
    // and return the ones that are active
    public function getTenancies(): array
    {
        if (count($this->tenancies) == 0) {
            $tenancy = new TenancyModel($this->pdo);
            $this->tenancies = $tenancy->list();
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
        $tenancies = $this->getTenancyList($this->pdo);

        foreach ($tenancies as $row) {
            if ($row['shortname'] == $tenancy) {
                return $row['tenant_id'];
            }
        }
        return null;
    }

    // use the list of tenancies to create the where sql
    public function getXeroTenantClause(): string
    {
        $tenancies = $this->getTenancies($this->pdo);

        return " (`contacts`.`xerotenant_id` = '" . implode("' OR `contacts`.`xerotenant_id` = '", $tenancies) . "') ";
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


    /*
     * calls
     * public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)
     */

    public function createContact($xeroTenantId, $apiInstance, $returnObj = false)
    {
        $str = '';

        //[Contacts:Create]
        $person = new \XeroAPI\XeroPHP\Models\Accounting\ContactPerson;
        $person->setFirstName("John")
            ->setLastName("Smith")
            ->setEmailAddress("john.smith@24locks.com")
            ->setIncludeInEmails(true);

        $persons = [];
        array_push($persons, $person);

        $contact = new \XeroAPI\XeroPHP\Models\Accounting\Contact;
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

        $contact_1 = new \XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact_1->setName('FooBar' . $this->getRandNum())
            ->setFirstName("Foo" . $this->getRandNum())
            ->setLastName("Bar" . $this->getRandNum())
            ->setEmailAddress("ben.bowden@24locks.com");
        array_push($arr_contacts, $contact_1);

        $contact_2 = new \XeroAPI\XeroPHP\Models\Accounting\Contact;
        $contact_2->setName('FooBar' . $this->getRandNum())
            ->setFirstName("Foo" . $this->getRandNum())
            ->setLastName("Bar" . $this->getRandNum())
            ->setEmailAddress("ben.bowden@24locks.com");
        array_push($arr_contacts, $contact_2);

        $contacts = new \XeroAPI\XeroPHP\Models\Accounting\Contacts;
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
        $contact = new \XeroAPI\XeroPHP\Models\Accounting\Contact;
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
        $contact = new \XeroAPI\XeroPHP\Models\Accounting\Contact;
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
        $external_link = new \XeroAPI\XeroPHP\Models\Accounting\ExternalLink;
        $external_link->setUrl("http://twitter.com/#!/search/Homer+Simpson");

        $employee = new \XeroAPI\XeroPHP\Models\Accounting\Employee;
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

    public function getSingleInvoice($invoice_id)
    {

    }


    // https://cabinkingmanagement:8890/xero.php?endpoint=Invoices&action=refresh&tenancy=auckland
    // does the return value get used?
    public function getInvoiceRefresh($tenancy): int
    {
        $xeroTenantId = $this->getXeroTenantId($tenancy);
        $objInvoice = new InvoiceModel($this->pdo);

        $updated_date_utc = $objInvoice->getUpdatedDate($xeroTenantId);

        $objInvoice->getStatement();

        //    public function getInvoices($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $invoice_numbers = null, $contact_ids = null, $statuses = null, $page = null, $include_archived = null, $created_by_my_app = null, $unitdp = null)
        // xero: getInvoices($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $ids = null, $invoice_numbers = null, $contact_ids = null, $statuses = null, $page = null, $include_archived = null, $created_by_my_app = null, $unitdp = null, $summary_only = false)
        // https://developer.xero.com/documentation/api/accounting/invoices
        $where = $order = $ids = $invoice_numbers = $contact_ids = $statuses = $unitdp = null;
        $summary_only = $include_archived = true;
        $created_by_my_app = false;
        $page = 1;

        $result = $this->apiInstance->getInvoices($xeroTenantId, $updated_date_utc, $where, $order, $ids, $invoice_numbers, $contact_ids, $statuses, $page, $include_archived, $created_by_my_app, $unitdp, $summary_only);

        $data = $result->getInvoices();

        $k = 0;

        foreach ($data as $k => $row) {

            $repeating_invoice_id = $row->getRepeatingInvoiceId();

            // only save the repeating_invoices
            if ($repeating_invoice_id) {
                // these don't return anything, they just ensure we have the records in our
                // local database
                $contact_id = $row->getContact()->getContactId();
                //$ckcontact_id = $this->getSingleContact($xeroTenantId, $contact_id);
                $ckcontact_id = $this->getSingleContactStub($xeroTenantId, $row);
                //$contract_id = $this->getSingleRepeatingInvoice($xeroTenantId, $row->getRepeatingInvoiceId());
                $contract_id = $this->getSingleRepeatingInvoiceStub($xeroTenantId, $row);

                // values for this specific invoice
                $values = [
                    'invoice_id' => $row['invoice_id'],
                    'contact_id' => $contact_id,
                    'ckcontact_id' => $ckcontact_id,
                    'status' => $row['status'],
                    'invoice_number' => $row['invoice_number'],
                    'reference' => substr($row['reference'], 0, 20),
                    'total' => $row['total'],
                    'amount_due' => $row['amount_due'],
                    'amount_paid' => $row['amount_paid'],
                    'date' => getDateFromXero($row['date']),
                    'due_date' => getDateFromXero($row['due_date']),
                    'repeating_invoice_id' => $repeating_invoice_id,
                    'contract_id' => $contract_id,
                    'xero_status' => $row['status'],
                    'updated_date_utc' => getDateFromXero($row['updated_date_utc']),
                    'xerotenant_id' => $xeroTenantId
                ];

                $objInvoice->prepAndSave(['invoice' => $values]);

            }
        }

        return $k;
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
        $item = new \XeroAPI\XeroPHP\Models\Accounting\Item;

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

        $item_1 = new \XeroAPI\XeroPHP\Models\Accounting\Item;
        $item_1->setName('My Item-' . $this->getRandNum())
            ->setCode($this->getRandNum())
            ->setDescription("This is my Item description.")
            ->setIsTrackedAsInventory(false);
        array_push($arr_items, $item_1);

        $item_2 = new \XeroAPI\XeroPHP\Models\Accounting\Item;
        $item_2->setName('My Item-' . $this->getRandNum())
            ->setCode($this->getRandNum())
            ->setDescription("This is my Item description.")
            ->setIsTrackedAsInventory(false);
        array_push($arr_items, $item_2);

        $items = new \XeroAPI\XeroPHP\Models\Accounting\Items;
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
        $item = new \XeroAPI\XeroPHP\Models\Accounting\Item;
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

    public function getOrganisationList($returnObj = false): void
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

        echo json_encode($tenancies);
    }

    // this needs to be saved in the session
    private function getTenanciesforUser()
    {
        if (array_key_exists('tenancies', $_SESSION)) {
            return $_SESSION['tenancies'];
        } else {
            $provider = getProvider();
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

    public function getRepeatingInvoice($xeroTenantId, $repeating_invoice_id)
    {
        $str = '';

        //[RepeatingInvoices:Read]
        // getRepeatingInvoices($xero_tenant_id, $where = null, $order = null)
        //
        $where = '' . $repeating_invoice_id;
        $order = null;

        $result = $this->apiInstance->getRepeatingInvoices($xeroTenantId, $where, $order);
        //[/RepeatingInvoices:Read]
        $str = $str . "Get RepeatingInvoices: " . count($result->getRepeatingInvoices()) . "<br>";
        $this->debug($result->getRepeatingInvoices());

        return $result->getRepeatingInvoices()[0];

    }

    /*
     * "Schedule": {
        "Period": 1,
        "Unit": "MONTHLY",
        "DueDate": 20,
        "DueDateType": "OFFOLLOWINGMONTH",
        "StartDate": "\/Date(1406851200000+0000)\/",
        "NextScheduledDate": "\/Date(1696118400000+0000)\/",
        "NextScheduledDateString": "2023-10-01"
      }
     */
    protected function getScheduleFromXeroObject($schedule): array
    {
        // for now we only need the unit
        $output = [
            'schedule_unit' => $schedule->getUnit()
        ];
        return $output;
    }

    // the equivalent of a contract
    public function getSingleRepeatingInvoice($xeroTenantId, $repeating_invoice_id): int
    {
        $save = $this->getSingleRepeatingInvoiceData($xeroTenantId, $repeating_invoice_id);

        $contract = new ContractModel($this->pdo);
        return $contract->prepAndSave(['contract' => $contract]);
    }

    public function getSingleRepeatingInvoiceData($xeroTenantId, $repeating_invoice_id): array
    {
        $result = $this->apiInstance->getRepeatingInvoice($xeroTenantId, $repeating_invoice_id);
        //[/RepeatingInvoices:Read]
        $contact_id = $result[0]->getContact()->getContactId();

        $output = [
            'repeating_invoice_id' => $repeating_invoice_id,
            'contact_id' => $contact_id,
            'ckcontact_id' => $this->getSingleContactId($xeroTenantId, $contact_id),
            'reference' => $result[0]->getReference(),
            'total' => $result[0]->getTotal(),
            'xeroRefresh' => true,
            'stub' => 0
        ];
        return array_merge($output, $this->getScheduleFromXeroObject($result[0]['schedule']));
    }


    public function getSingleRepeatingInvoiceStub($xeroTenantId, $row): int
    {

        $contact_id = $row['contact']->getContactId();
        $contact = $this->contact->get('contact_id', $contact_id);
        //debug($contact);
        $contract = [
            'repeating_invoice_id' => $row->getRepeatingInvoiceId(),
            'contact_id' => $contact_id,
            'ckcontact_id' => $contact['contacts']['id'],
            'xerotenant_id' => $xeroTenantId,
            'stub' => 1
        ];

        return $this->contract->prepAndSave(['contract' => $contract]);
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


}
