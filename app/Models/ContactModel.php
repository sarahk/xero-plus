<?php

namespace App\Models;

use App\Models\AddressModel;
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
    public function prepAndSave(array $data): int
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


    /*
     * need to provide the option to limit it to a particular tenancy
     * add weighting in the future?
     */
    public function search()
    {
        $searchFields = ['first_name', 'last_name', 'email_address', 'phone'];
        $conditions = $values = [];


        foreach ($searchFields as $var) {
            if (!empty($_GET[$var])) {
                $conditions[] = " `{$var}` LIKE :$var ";
                $values[':' . $var] = $_GET[$var];
            }
        }

        $sql = "SELECT `contacts`.`id`, `contacts`.`contact_id`, `contacts`.`status`
            `contacts`.`name`, `first_name`, `last_name`, 
            `email_address`, `phone_area_code`, `phone_number`, 
            `xerotenant_id`, `contacts`.`updated_date_utc`,
            `tenancies`.`colour`
            FROM `contacts`
            LEFT JOIN `phones` on `contacts`.`id` = `phones`.`ckcontact_id` 
            LEFT JOIN `tenancies` on `contacts`.`xerotenant_id` = `tenancies`.`tenant_id`
            WHERE `is_customer` = 1 
              AND (" . implode(' AND ', $conditions) .
            ') LIMIT 10';

        $list = $this->runQuery($sql, $values);
//        $this->getStatement($sql);
//        try {
//            $this->statement->execute($values);
//            $list = $this->statement->fetchAll(PDO::FETCH_ASSOC);
//        } catch (PDOException $e) {
//            echo "Error Message: " . $e->getMessage() . "\n";
//            $this->statement->debugDumpParams();
//        }
        return [
            'count' => count($list),
            'draw' => $_GET['draw'],
            'data' => $list
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
}
