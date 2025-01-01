<?php

namespace App\Models;

use Twilio\Rest\Client;

class ActivityModel extends BaseModel
{
    protected string $insert = 'INSERT INTO activity
        (activity_type, activity_date, activity_status, ckcontact_id, contact_id,subject, body)
        VALUES
        (:activity_type, :activity_date, :activity_status, :ckcontact_id, :contact_id, :subject, :body)';

    protected string $table = 'activity';
    protected array $joins = [];
    protected array $virtualFields = [];

    protected array $orderByColumns = [
        0 => "activity_id DIR",
        1 => "activity_status DIR",
        2 => "activity_type DIR",

    ];
    protected string $account_sid = 'ACcca2973426cdb7e4d07756ace82be488';
    protected string $auth_token = '1389609818fc296c08a5f624234cecb8';
    protected string $fromNumber = '+15407798990';

    public function list($params)
    {


        $conditions = $search_values = [];

        $tenancies = $this->getTenanciesWhere($params, 'contracts');
        $order = $this->getOrderBy($params);


        if (!empty($params['search'])) {
            $search = [
                "contacts.name LIKE :search",
            ];

            $search_values['search'] = '%' . $params['search'] . '%';
            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }

        $contact_id = $_GET['contact_id'] ?? '';
        if (!empty($contact_id)) {
            // added to tenancies because we need it to run on the total and filter count queries
            $tenancies .= " AND `contact_id` = :contact_id";
            $search_values['contact_id'] = $contact_id;
        }

        if (!empty($params['contract_id'])) {
            $tenancies .= " AND `contract_id` = :contract_id";
            $search_values['contract_id'] = $params['contract_id'];
        }

        if (!empty($params['button']) && $params['button'] !== 'read') {
            if ($params['button'] == 'SMS' || $params['button'] !== 'Email') {
                $search_values['activity_type'] = $params['button'];
                $conditions[] = "activity_type = :activity_type ";

            } else {
                $search_values['activity_status'] = 'New';
                $conditions[] = "activity_status = :activity_status ";
            }
        }

        $conds = count($conditions) > 0 ? ' AND ' . implode(' AND ', $conditions) : null;
        $sql = "SELECT activity.*, contacts.name, contacts.xerotenant_id, tenancies.colour,
                    activity_id as DT_RowId
                    FROM activity
                    LEFT JOIN contacts ON contacts.id = activity.ckcontact_id
                    LEFT JOIN contracts on contracts.contract_id = activity.contract_id
                    LEFT JOIN tenancies ON contacts.xerotenant_id = tenancies.tenant_id
                    WHERE $tenancies $conds
                    ORDER BY $order 
                    LIMIT {$params['start']}, {$params['length']}";

        $result = $this->runQuery($sql, $search_values);

        $output = $params;
        $output['mainquery'] = $sql;
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $recordsTotal = "SELECT count(*) FROM activity LEFT JOIN contracts ON contracts.contract_id = activity.contract_id WHERE $tenancies";
        $recordsFiltered = "SELECT count(*) 
                                FROM activity 
                                    LEFT JOIN contacts ON contacts.id = activity.ckcontact_id
                                    LEFT JOIN contracts ON contracts.contract_id = activity.contract_id 
                                WHERE $tenancies $conds";

        $output['recordsTotal'] = $this->runQuery($recordsTotal, $search_values, 'column');
        $output['recordsFiltered'] = $this->runQuery($recordsFiltered, $search_values, 'column');

        if (count($result) > 0) {
            foreach ($result as $row) {
                $output['data'][] = array_merge($row, [
                    'date' => $this->getPrettyDate($row['activity_date']),
                    'preview' => $this->getPreview($row['activity_type'], $row['subject'], $row['body']),
                ]);
// for debugging
                $output['row'] = $row;
            }

        }

        return $output;
    }

    protected function getPreview(string $messagetype, string $subject, string $body): string
    {
        if ($messagetype === 'SMS') {
            return $body;
        } else {
            return "<b>$subject</b><br/>$body";
        }
    }

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
            //$save['contract_id'] = $row['contract_id'];
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
}
