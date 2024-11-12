<?php

namespace App\Models;

use App\Models\AddressModel;
use App\Models\Enums\AmountDue;
use App\Models\Enums\ContractStatus;
use App\Models\PhoneModel;
use App\Models\NoteModel;

class ContactModel extends BaseModel
{
    protected array $nullable = ['id', 'contact_id', 'updated_date_utc', 'stub'];
    protected array $saveKeys = [
        'id', 'contact_id', 'contact_status',
        'name', 'first_name', 'last_name', 'email_address', 'best_way_to_contact',
        'updated_date_utc', 'xerotenant_id', 'stub'];
    protected array $updateKeys = [
        'contact_status',
        'name', 'first_name', 'last_name', 'email_address', 'best_way_to_contact',
        'updated_date_utc', 'stub'];

    protected string $table = 'contacts';
    protected string $primaryKey = 'id';

    protected array $hasMany = ['Phone', 'Address', 'Note'];

    protected array $orderByColumns = [
        0 => 'last_name DIR, first_name ASC',
        2 => 'total_due DIR',
        3 => 'last_name DIR, first_name ASC',
    ];


    protected bool $hasStub = true;

    function __construct($pdo)
    {
        parent::__construct($pdo);

        $this->buildInsertSQL();


    }

    public function saveXeroStub(array $data): int
    {
// if it's a new contact id will be null
        if (!empty($data['id'])) {
            $save = [
                'id' => $data['id'],
                'name' => $data['name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email_address' => $data['email_address'],
                'xero_status' => $data['xero_status'],
            ];

            $sql = "UPDATE `contacts` set `name` = :name,
                        `first_name` = :first_name, 
                        `last_name` = :last_name, 
                        `email_address` = :email_address,
                        `xero_status` = :xero_status
                        WHERE `id` = :id ";
            $this->runQuery($sql, $save, 'update');
            $this->logInfo('saveXeroStub-existing', $save);
            $this->logInfo('saveXeroStub-existing', $data['id']);
            return $data['id'];
        }

        $sql = "INSERT INTO `contacts` (
            `id`,
            `contact_id`,
            `name` ,
            `first_name`,
            `last_name`,
            `email_address` ,
            `xero_status` ,
            `xerotenant_id`,
            `stub`) 
            VALUES (:id, :contact_id,:name,:first_name,:last_name,:email_address,:xero_status,:xerotenant_id,:stub)";
        //$save = array_merge($data, $dupes);

        $newId = $this->runQuery($sql, $data, 'insert');
        $this->logInfo('saveXeroStub-new', $data);
        $this->logInfo('saveXeroStub-new', $newId);
        return $newId;
    }

    public function getContactName(array $data): string
    {
        return ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '');
    }

    /**
     * @param array $data
     * @return array
     */
    public function prepAndSaveMany(array $data): array
    {
        $output = [];
        foreach ($data['contact'] as $k => $contact) {
            $output[$k] = $this->prepAndSave(['contact' => $contact]);
        }
        return $output;
    }

    /**
     * @param array $data
     * @return int
     */
    public function prepAndSave(array $data): string
    {
        //$this->debug(['contact prepAndSave', $data]);

        $this->logInfo('prepAndSave: ', $data);

        $data['contact']['name'] = $data['contact']['name'] ?? $this->getContactName($data['contact']);


        // these can't be empty strings, either a value or null
        $checked = $this->checkNullableValues($data['contact']);
        $save = $this->getSaveValues($checked);
        // we can't pass extra variables
        $this->buildInsertSQL();
        $newId = $this->runQuery($this->insert, $save, 'insert');

        //$newId = $this->save($save);

        // save the id back to the data array and save the child records
        if ($newId > 0) $data['contact']['id'] = $newId;
        $addresses = new AddressModel($this->pdo);
        $addresses->prepAndSave($data);

        if (array_key_exists('phone', $data['contact'])) {
            $phone = new PhoneModel($this->pdo);
            foreach ($data['contact']['phone'] as $phoneData) {
                if (!empty($phoneData['phone'])) {
                }
            }
        }

        if (array_key_exists('note', $data['contact'])) {
            // add key info to the note so we know where it came from
            $data['note']['foreign_id'] = $data['contact']['id'];
            $data['note']['parent'] = 'contacts';
            $notes = new NoteModel($this->pdo);
            $notes->prepAndSave($data);
        }


        if (array_key_exists('phones', $data['contact']) && count($data['contact']['phones'])) {
            foreach ($data['contact']['phones'] as $phone) {
                if (!empty($phone['phone_number'])) {
                    $phone['ckcontact_id'] = $data['contact']['id'];
                    $phone['contact_id'] = $data['contact']['contact_id'];
                    $phoneModel = new PhoneModel($this->pdo);
                    $phoneModel->prepAndSave([
                        'contact' => ['id' => $data['contact']['id']],
                        'phone' => $phone
                    ]);
                }
            }
        }
        return $data['contact']['id'];
    }

    /**
     *  contacts have 2 ids, one internal, one from xero
     *  this uses the xero id to get the internal id
     *  returns the integer id or null
     * @param string $contact_id
     * @return int|null
     */
    public function getContactId(string $contact_id): int|null
    {
        return $this->field('id', 'contact_id', $contact_id);
    }

    public function list(array $params): array
    {
        $this->table = 'vcontacts';
        $tenancies = $this->getTenanciesWhere($params);
        $order_by = $this->getOrderBy($params);

        $search_fields = [
            'vcontacts.name', 'vcontacts.first_name', 'vcontacts.last_name',
            'vcontacts.email_address', 'vcontacts.address_line1', 'vcontacts.phone_number'];
        $conditions = $search_values = [];


        if (!empty($params['search'])) {
            foreach ($search_fields as $var) {
                $conditions[] = " $var LIKE :search ";
            }
            $search_values['search'] = "%{$params['search']}%";
        }

// uses the view to make searching on phone number and address easier
        $conds = (count($conditions) ? ' AND (' . implode(' OR ', $conditions) . ')' : '');

// if a contact has more than one cabin this may show high
        $sql = "SELECT 
                tenancies.tenant_id,
                tenancies.colour,
                vcontacts.*,
                SUM(vamount_due.total_due) AS total_due,
                max(contracts.enquiry_rating) AS enquiry_rating,
                max(contracts.delivery_date) AS delivery_date,
                max(contracts.pickup_date) AS pickup_date,
                max(contracts.contract_id) as contract_id
            FROM tenancies
            LEFT JOIN vcontacts ON vcontacts.xerotenant_id = tenancies.tenant_id
            LEFT JOIN vamount_due ON vcontacts.contact_id = vamount_due.contact_id
            LEFT JOIN contracts ON contracts.ckcontact_id = vcontacts.id
            WHERE vcontacts.is_customer = 1 
                AND $tenancies $conds  
            GROUP BY tenancies.tenant_id, vcontacts.id, vcontacts.last_name, vcontacts.first_name
            ORDER BY $order_by " . $this->getListLimits($params);

        $result = $this->runQuery($sql, $search_values);
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['name'] = $this->getContractOverviewLink(91, $result[$i]) . $result[$i]['name'] . '</a>';
            $result[$i]['life_cycle'] = ContractStatus::getIconHtml($result[$i]);
            $result[$i]['total_due_icon'] = AmountDue::getIconHtml($result[$i]['total_due'] ?? 0);
            $result[$i]['action'] = ''; // what do we want here?
        }


        $recordsTotal = 'SELECT count(*) FROM vcontacts WHERE vcontacts.is_customer = 1 AND ' . $tenancies;
        $recordsFiltered = "SELECT count(*) 
                                FROM vcontacts 
                                WHERE vcontacts.is_customer = 1 AND $tenancies $conds";


        return [
            'count' => count($result),
            'draw' => $params['draw'],
            'recordsTotal' => $this->runQuery($recordsTotal, $search_values, 'column'),
            'recordsFiltered' => $this->runQuery($recordsFiltered, $search_values, 'column'),
            'data' => $result
        ];
    }

    // for use in the Pest Test Suite
    public function getTestData($limit): array
    {
        $sql = "select contact_id, contacts.`xerotenant_id`,
                    first_name, last_name, email_address 
                    from contacts 
                    order by rand() desc 
                    limit 4";

        return $this->runQuery($sql, []);
    }

    public function getAllJoins($join_type, $foreign_id): array
    {
        $sql = "SELECT `contacts`.*
                    FROM `contacts`
                    LEFT JOIN `contactjoins` ON `contactjoins`.`ckcontact_id` = `contacts`.`id`
                    WHERE `contactjoins`.`foreign_id` = :foreign_id";
        return $this->runQuery($sql, ['foreign_id' => $foreign_id]);
    }

    public function getOtherContacts($params)
    {
        $sql = 'SELECT contacts.id as ckcontact_id, contacts.name, contactjoins.foreign_id AS contract_id
                FROM contacts
                LEFT JOIN contactjoins ON contacts.id = contactjoins.ckcontact_id 
                                              AND contactjoins.`join_type` = "contract"
                WHERE contacts.id != :ckcontact_id
                AND contactjoins.foreign_id = :contract_id';
        $result = $this->runQuery($sql, $params);

        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['alink'] = $this->getContractOverviewLink('91', $result[$i]);
        }
        // todo add a hyperlink to the name
        return $result;
    }
}
