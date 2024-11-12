<?php

namespace App\Models;


use PDO;

class TemplateModel extends BaseModel
{
    protected array $nullable = ['subject'];
    protected array $saveKeys = [
        'id', 'status', 'messagetype', 'label', 'subject', 'body', 'dateupdate'];
    protected array $updateKeys = [
        'status', 'label', 'subject', 'body', 'dateupdate'
    ];
    protected string $insert = "INSERT INTO `templates` (`id`,  `status`, `messagetype`,
                      `label`, `subject`, `body`, `dateupdate`)
                values (:id, :status, :messagetype , :label, :subject, :body,:dateupdate)
                ON DUPLICATE KEY UPDATE `status` = :status, `label` = :label, `subject` = :subject, `body` = :body, `dateupdate` = :dateupdate";

    protected array $orderByColumns = [
        0 => "templates.id DIR",
        1 => "templates.status DIR",
        2 => "templates.messagetype DIR",
        3 => "templates.label DIR",
    ];

    protected string $table = 'templates';
    protected string $primaryKey = 'id';

    //protected array $hasMany = [];
    protected bool $hasStub = false;


    /**
     * @param array $data <mixed>
     * @return int
     */
    public function prepAndSave(array $data): string
    {
        $data['dateupdate'] = date('Y-m-d H:i:s');
        $this->logInfo('Template save data', $data);
        if ($data['messagetype'] === 'SMS') {
            $data['body'] = strip_tags($data['body']);
        }
        $data['id'] = (empty($data['id']) ? null : $data['id']);

        return $this->runQuery($this->insert, $data, 'insert');
    }


    /*
     * TODO
     * need to provide the option to limit it to a particular tenancy
     * add weighting in the future?
     */
    public function search($params): string
    {
        $searchFields = ['status', 'messagetype', 'label'];
        $conditions = $search_values = [];


        foreach ($searchFields as $var) {
            if (!empty($_GET[$var])) {
                $conditions[] = " `$var` LIKE :$var ";
                $search_values[':' . $var] = $_GET[$var];
            }
        }

        if (!empty($params['button'])) {
            switch ($params['button']) {
                case 'active':
                    $conditions[] = 'templates.status = 1';
                    break;
                case 'SMS':
                case 'Email':
                    $conditions[] = 'templates.messagetype = :messagetype';
                    $search_values['messagetype'] = $params['button'];
                default:
            }
        }

        $sql = "SELECT * FROM `templates`"
            . (count($conditions) ? " WHERE (" . implode(' AND ', $conditions) . ")" : '')
            . " ORDER BY " . $this->getOrderBy($params)
            . " LIMIT {$params['start']}, {$params['length']}";

        $output = [];

        $list = $this->runQuery($sql, $search_values);
        foreach ($list as $row) {

            $output[] = [
                'id' => $row['id'],
                'messagetype' => $row['messagetype'],
                'status' => $row['status'] ? 'Active' : 'Archived',
                'subject' => $row['subject'],
                'body' => $row['body'],
                'preview' => $this->getPreview($row['messagetype'], $row['subject'], $row['body']),
                'label' => $this->getLabelModal($row['id'], $row['label'])
            ];
        }

        $recordsTotal = 'SELECT count(*) FROM templates';
        $recordsFiltered = 'SELECT count(*) FROM templates WHERE templates.status = 1';

        return json_encode([
            'count' => count($output),
            'draw' => $params['draw'],
            'recordsTotal' => $this->runQuery($recordsTotal, [], 'column'),
            'recordsFiltered' => $this->runQuery($recordsFiltered, $search_values, 'column'),
            'mainquery' => $sql,
            'mainsearchvals' => $search_values,
            'data' => $output
        ]);
    }

    protected function getPreview(string $messagetype, string $subject, string $body): string
    {
        if ($messagetype === 'SMS') {
            return $body;
        } else {
            return "<b>$subject</b><br/>$body";
        }
    }

    protected function getLabelModal(string $id, string $label): string
    {
        return "<a href='#' class='templateRow' 
                    data-bs-toggle='modal' 
                    data-bs-target='#templateModal'
                    data-template_id='$id' 
                    >$label</a>";
    }

    public function getSelectChoices($message_type): string
    {
        $sql = "SELECT id, label FROM templates WHERE `status` = 1 AND `messagetype` = '$message_type' ORDER BY sortorder";
        $result = $this->runQuery($sql, []);
        $output = [];
        foreach ($result as $row) {
            $output[] = "<option value='$row[id]'>$row[label]</option>";
        }
        return implode($output);
    }
}
