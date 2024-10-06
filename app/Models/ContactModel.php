<?php

namespace App\Models;

use App\XeroClass;

class ContactModel extends BaseModel
{
    protected array $nullable = ['id', 'contact_id', 'updated_date_utc', 'stub'];
    protected array $saveKeys = [
        'id', 'contact_id', 'contact_status',
        'name', 'first_name', 'last_name', 'email_address', 'best_way_to_contact',
        'how_did_you_hear',
        'updated_date_utc', 'xerotenant_id', 'stub'];
    protected array $updateKeys = [
        'contact_status',
        'name', 'first_name', 'last_name', 'email_address', 'best_way_to_contact',
        'how_did_you_hear',
        'updated_date_utc', 'stub'];

    protected AddressModel $addresses;
    protected PhoneModel $phones;
    protected NoteModel $notes;

    protected string $table = 'contacts';
    protected string $primaryKey = 'id';

    protected array $hasMany = ['Phone', 'Address', 'Note'];
    protected bool $hasStub = true;

    function __construct($pdo)
    {
        parent::__construct($pdo);

        $this->buildInsertSQL();

        $this->addresses = new AddressModel($pdo);
        $this->notes = new NoteModel($pdo);
        $this->phones = new PhoneModel($pdo);
    }

    public function saveXeroStub(array $data): int
    {

        $sql = "INSERT INTO `contacts` (`contact_id`,
            `name` ,
            `first_name`,
            `last_name`,
            `email_address` ,
            `xero_status` ,
            `xerotenant_id`,
            `stub`) 
            VALUES (:contact_id,:name,:first_name,:last_name,:email_address,:xero_status,:xerotenant_id,:stub)";

        return $this->runQuery($sql, $data, 'insert');
    }

    public function getContactName(array $data): string
    {
        return ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '');
    }

    public function prepAndSave(array $data): int
    {
        //$this->debug(['contact prepAndSave', $data]);

        $data['contact']['name'] = $data['contact']['name'] ?? $this->getContactName($data['contact']);


        // these can't be empty strings, either a value or null
        $checked = $this->checkNullableValues($data);

        // we can't pass extra variables
        $save = $this->getSaveValues($checked);

        $newId = $this->save($save);

        // save the id back to the data array and save the child records
        if ($newId > 0) $data['contact']['id'] = $newId;

        $this->addresses->prepAndSave($data);

        // add key info to the note so we know where it came from
        $data['note']['foreign_id'] = $data['contact']['id'];
        $data['note']['parent'] = 'contacts';
        $this->notes->prepAndSave($data);


        if (array_key_exists('phones', $data['contact']) && count($data['contact']['phones'])) {
            foreach ($data['contact']['phones'] as $phone) {
                if (!empty($phone['phone_number'])) {
                    $phone['ckcontact_id'] = $data['contact']['id'];
                    $phone['contact_id'] = $data['contact']['contact_id'];

                    $this->phones->prepAndSave([
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

    protected function getFromXero($data): void
    {
        parent::getFromXero($data); // should be nothing there but let's check
        if (empty($data['contact_id'])) {
            return;
        }

        $xero = new XeroClass();
        $contact_id = $data['contact_id'];
        $xeroTenantId = $data['xerotenant_id'];

        $xero->getSingleContact($xeroTenantId, $contact_id);
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
}
