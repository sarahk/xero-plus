<?php

use XeroAPI\XeroPHP\AccountingObjectSerializer;

require_once('utilities.php');
require_once('functions.php');
require_once('models/AddressModel.php');
require_once('models/ContactModel.php');
require_once('models/InvoiceModel.php');
require_once('models/PhoneModel.php');
require_once('models/SettingModel.php');
require_once('models/TenancyModel.php');

class XeroClass
{
    public $apiInstance;
    public string $xeroTenantId;
    public array $tenancies = [];

    function __construct($apiInstance, $xeroTenantId = '')
    {
        $this->apiInstance = $apiInstance;
    }

    public function init($arg)
    {
        $apiInstance = $arg;
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
        $output['invoice_status'] = filter_input(INPUT_GET, 'invoice_status', FILTER_DEFAULT);
        $output['dates'] = filter_input(INPUT_GET, 'dates', FILTER_DEFAULT);
        $output['contact_status'] = filter_input(INPUT_GET, 'dates', FILTER_DEFAULT);
        $output['button'] = filter_input(INPUT_GET, 'button', FILTER_DEFAULT);

        // prima
        $output['key'] = filter_input(INPUT_GET, 'key', FILTER_DEFAULT);

        return $output;
    }

    public function saveXeroTimestamp($category, $xeroTenantId)
    {
        $objSetting = new SettingModel();
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
    public function getSingleContact($xeroTenantId, $contact_id): int
    {
        $ids = [$contact_id];
        // public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)
        $objContact = new ContactModel();

        // shown in full for readability
        $updated_date_utc = null;
        $where = $order = null;
        $page = 1;
        $include_archived = true;

        $result = $this->apiInstance->getContacts($xeroTenantId, $updated_date_utc, $where, $order, $ids, $page, $include_archived);
        $data = $result->getContacts();

        if (count($data)) {

            $row = $result->getContacts();
            $addresses = $this->getAddressesFromXeroObject($row[0]->getAddresses());
            $phones = $this->getPhonesFromXeroObject($row[0]->getPhones());

            $save = ['contact_id' => $contact_id,
                'first_name' => $row[0]->getFirstName(),
                'last_name' => $row[0]->getLastName(),
                'email_address' => $row[0]->getFirstName(),
                'xero_status' => $row[0]->getContactStatus(),
                'addresses' => $addresses,
                'phones' => $phones,
                'xerotenant_id' => $xeroTenantId
            ];

            return $objContact->prepAndSave(['contact' => $save]);
        }
        return 0;
    }

    public function getContactRefresh(): void// auckland,waikato,bop
    {
        $objContact = new ContactModel();

        $k = 1;
        $tenantName = $_GET['tenancy'];
        $xeroTenantId = $this->getXeroTenantId($tenantName);
        // public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)

        // shown in full for readability
        $updated_date_utc = $objContact->getUpdatedDate($xeroTenantId);
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
        debug([$values['contact']['name'], $values['contact']['first_name'], $values['contact']['last_name'], $values['contact']['contact_id']]);
        $objContact = new ContactModel();
        $objContact->prepAndSave($values);
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
                debug($apiResponse->getContacts());
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
            $tenancies = new TenancyModel();
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
        $tenancies = new TenancyModel();
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
        $tenancies = $this->getTenancyList();

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


    /*
     * calls
     * public function getContacts($xero_tenant_id, $if_modified_since = null, $where = null, $order = null, $i_ds = null, $page = null, $include_archived = null)
     */

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

        var_dump($result);
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


    // https://cabinkingmanagement:8890/xero.php?endpoint=Invoices&action=refresh&tenancy=auckland
    // does the return value get used?
    public function getInvoiceRefresh($tenancy): int
    {
        $xeroTenantId = $this->getXeroTenantId($tenancy);
        $objInvoice = new InvoiceModel();

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
                $ckcontact_id = $this->getSingleContact($xeroTenantId, $contact_id);
                $contract_id = $this->getSingleRepeatingInvoice($xeroTenantId, $row->getRepeatingInvoiceId());

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
                    'updated_date_utc' => getDateFromXero($row['updated_date_utc'])
                ];
                $x = $objInvoice->prepAndSave(['contract' => $values]);
            }
        }
        return $k;
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

    public function getOrganisationList($returnObj = false)
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
        if ($returnObj) {
            return $tenancies;
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
        // getRepeatingInvoices($xero_tenant_id, $where = null, $order = null)
        //
        $where = '';
        $order = null;

        $result = $apiInstance->getRepeatingInvoices($xeroTenantId, $where, $order);
        //[/RepeatingInvoices:Read]
        $str = $str . "Get RepeatingInvoices: " . count($result->getRepeatingInvoices()) . "<br>";
        debug($result->getRepeatingInvoices());
        if ($returnObj) {
            return $result->getRepeatingInvoices()[0];
        } else {
            return $str;
        }
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

    public function getSingleRepeatingInvoice($xeroTenantId, $repeating_invoice_id): int
    {
        $result = $this->apiInstance->getRepeatingInvoice($xeroTenantId, $repeating_invoice_id);
        //[/RepeatingInvoices:Read]
        $contact_id = $result[0]->getContact()->getContactId();

        $contract = [
            'repeating_invoice_id' => $repeating_invoice_id,
            'contact_id' => $contact_id,
            'ckcontact_id' => $this->getSingleContact($xeroTenantId, $contact_id),
            'reference' => $result[0]->getReference(),
            'total' => $result[0]->getTotal()
        ];
        $contract = array_merge($contract, $this->getScheduleFromXeroObject($result[0]['schedule']));

        $objContract = new ContractModel();
        return $objContract->prepAndSave(['contract' => $contract]);
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
}
