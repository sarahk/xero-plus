<?php

namespace App\Models;

use Twilio\Rest\Client;

class ActivityModel extends BaseModel
{
    protected string $insert = 'INSERT INTO activity
        (activity_type, activity_date, activity_status, contract_id, ckcontact_id, contact_id,subject, body)
        VALUES
        (:activity_type, :activity_date, :activity_status, :contract_id, :ckcontact_id, :contact_id, :subject, :body)';

    protected string $table = 'activity';
    protected array $joins = [];
    protected array $virtualFields = [];


    protected string $account_sid = 'ACcca2973426cdb7e4d07756ace82be488';
    protected string $auth_token = '1389609818fc296c08a5f624234cecb8';
    protected string $fromNumber = '+15407798990';


    public function processQueue(): void
    {
        $queue = $this->getSMSQueue();

        $client = new Client($this->account_sid, $this->auth_token);

        foreach ($queue as $k => $message) {
            $client->messages->create(
                '+64273711298',// $message['mobile']
                [
                    'from' => $this->fromNumber,
                    'body' => $message['body']
                ]
            );
            $this->markAsSent($message);
        }
    }

    protected function markAsSent(array $message): void
    {
        $sql = "UPDATE activity 
                SET activity_status = 'Sent'
                WHERE activity_id = :activity_id";
        $this->runQuery($sql, ['activity_id' => $message['activity_id']]);
    }

    protected function getSMSQueue(): array
    {
        $sql = "SELECT activity.*, concat(phone_area_code,phone_number) AS mobile
                    FROM activity
                    LEFT JOIN phones ON activity.`ckcontact_id` = phones.ckcontact_id AND phone_type = 'MOBILE'
                    WHERE activity_status = 'New'
                    AND activity_type = 'SMS'
                    LIMIT 10";

        return $this->runQuery($sql, []);
    }

    public function prepAndSaveMany(array $data): void
    {
        $sql = "SELECT contracts.ckcontact_id, contracts.contact_id, 
                contacts.first_name, contacts.name, contracts.contract_id
                 FROM contracts 
                 LEFT JOIN contacts ON contacts.id = contracts.ckcontact_id 
                WHERE repeating_invoice_id IN('" . implode("','", $data['repeating_invoice_ids']) . "')";
        $result = $this->runQuery($sql, []);

        $this->logInfo('Sql', [$sql]);
        $this->logInfo('Result', $result);

        $save = [
            'activity_type' => 'SMS',
            'activity_date' => date('Y-m-d H:i:s'),
            'activity_status' => 'New',
            'subject' => ''
        ];
        foreach ($result as $row) {
            $this->logInfo('Row', $row);
            $save['contract_id'] = $row['contract_id'];
            $save['ckcontact_id'] = $row['ckcontact_id'];
            $save['contact_id'] = $row['contact_id'];
            $save['body'] = str_replace('[first_name]', $row['first_name'] ?? $row['name'] ?? '', $data['sms_body']);
            $this->runQuery($this->insert, $save, 'insert');
        }
    }


    protected function updateStatus(): void
    {
//todo
    }

    public function getLastMessageDate($contact_id): string
    {
        $sql = 'select max(activity_date) as activity_date from activity where contact_id = :contact_id';
        $result = $this->runQuery($sql, ['contact_id' => $contact_id], 'column');

        if ($result) {
            return $this->getPrettyDate($result);
        }
        return '---';
    }

    public function getSentToday($contact_id): bool
    {
        $last_message_date = $this->getLastMessageDate($contact_id);

        if ($last_message_date == '---') {
            return false;
        }

        if (substr($last_message_date, 0, 10) === date('Y-m-d')) {
            return true;
        }
        return false;
    }

    public function getLatestActivity(array $contact_ids): array
    {
        $sql = "SELECT 
                    contact_id, MAX(activity_date) AS activity_date
                FROM
                    activity
                WHERE
                    contact_id IN ('" . implode("','", $contact_ids) . "')
                GROUP BY contact_id;";

        $result = $this->runQuery($sql, []);
        $output = [];
        foreach ($result as $row) {
            $output[$row['contact_id']] = $row['activity_date'];
        }
        return $output;
    }

    public function prepAndSave(array $data): string
    {
        // TODO: Implement prepAndSave() method.
        $save = $this->getSaveValues($data);
        $save['updated'] = date('Y-m-d H:i:s');

        $result = $this->runQuery($this->insert, $save, 'insert');
        return $result;
    }
}
