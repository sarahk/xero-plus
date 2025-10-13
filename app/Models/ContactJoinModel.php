<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Manages joins between contacts and contracts,  etc
 */
class ContactJoinModel extends BaseModel
{
    protected string $table = 'contactjoins';
    protected string $primaryKey = 'id';
    protected array $hasMany = [];

    protected array $nullable = [];
    protected array $saveKeys = ['ckcontact_id', 'join_type', 'foreign_id', 'updated'];
    protected array $updateKeys = ['foreign_id', 'updated'];
    protected string $insert = 'INSERT INTO contactjoins
                                    ( `ckcontact_id`, `join_type`, `foreign_id`, `updated`) 
                                    VALUES (:ckcontact_id, :join_type, :foreign_id, :updated);';


    /**
     * @param array $data <int>
     * @return void
     */
    public function prepAndSaveMany(string $join_type, string $foreign_id, array $contact_ids): void
    {
        //$where = array_map(fn($k) => ':id' . $k, array_keys($contact_ids));

        // Prepare search values with placeholders
        //$search_values = array_combine($where, $contact_ids);
        $search_values = [
            'join_type' => $join_type,
            'foreign_id' => $foreign_id
        ];

        $check_sql = 'SELECT `ckcontact_id`
                            FROM `contactjoins`
                            WHERE `join_type` = :join_type
                            AND `foreign_id` = :foreign_id';


        $result = $this->runQuery($check_sql, $search_values);
        $id_list = array_map(function ($item) {
            return $item['ckcontact_id'];
        }, $result);


        $updated = date('Y-m-d H:i:s');

        foreach ($contact_ids as $ckcontact_id) {

            if (!in_array($ckcontact_id, $id_list)) {
                $this->runQuery($this->insert, [
                    'ckcontact_id' => $ckcontact_id,
                    'join_type' => $join_type,
                    'foreign_id' => $foreign_id,
                    'updated' => $updated
                ],
                    'insert');
                $this->logInfo('Saved: ' . $ckcontact_id);
            }
        }
    }

    public function prepAndSave(array $data): string
    {
// todo this is a rough setup of prepandsave - it needs testing
        // now convert data into saveable values
        $save = $this->getSaveValues($data);
        $save['updated'] = $save['upd8_updated'] = date('Y-m-d H:i:s');

        $save = $this->checkNullableValues($save);

        $result = $this->runQuery($this->insert, $save, 'insert');
        return "$result";
    }
}
