<?php
declare(strict_types=1);

namespace App\Models\Query;

class ActivityQueryModel extends BaseQueryModel
{
    protected array $orderByColumns = [
        0 => "activity_id DIR",
        1 => "activity_date DIR",
        2 => "activity_status DIR",
        3 => "activity_type DIR",
    ];

    protected int $defaultOrderByColumn = 2;

    public function list(): string
    {

        $search_values = [];

        $tenancies = $this->getTenanciesWhere($this->params, 'contracts');
        $conditions = [$tenancies];
        $order = $this->getOrderBy($this->params);


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

        $button = $this->params['button'] ?? '';

        if (in_array($button, ['SMS', 'Email'])) {
            $search_values['activity_type'] = $button;
            $conditions[] = "activity_type = :activity_type ";
        } else if ($button == "New") {
            $search_values['activity_status'] = 'New';
            $conditions[] = "activity_status = :activity_status ";
        }


        $conds = implode(' AND ', $conditions);
        $sql = "SELECT activity.*, contacts.name, contacts.xerotenant_id, tenancies.colour,
                    activity_id as DT_RowId
                    FROM activity
                    LEFT JOIN contacts ON contacts.id = activity.ckcontact_id
                    LEFT JOIN contracts on contracts.contract_id = activity.contract_id
                    LEFT JOIN tenancies ON contacts.xerotenant_id = tenancies.tenant_id
                    WHERE  $conds
                    ORDER BY $order 
                    LIMIT {$this->params['start']}, {$this->params['length']}";

        $result = $this->runQuery($sql, $search_values);

        $output = $this->params;
        $output['mainquery'] = $this->cleanSql($sql);
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions

        $recordsTotal = "SELECT count(*) FROM activity LEFT JOIN contracts ON contracts.contract_id = activity.contract_id WHERE $tenancies";
        $recordsFiltered = "SELECT count(*) 
                                FROM activity 
                                    LEFT JOIN contacts ON contacts.id = activity.ckcontact_id
                                    LEFT JOIN contracts ON contracts.contract_id = activity.contract_id 
                                WHERE $conds";

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

        return json_encode($output);
    }

    protected function getPreview(string $messagetype, string $subject, string $body): string
    {
        if ($messagetype === 'SMS') {
            return $body;
        } else {
            return "<b>$subject</b><br/>$body";
        }
    }
}
