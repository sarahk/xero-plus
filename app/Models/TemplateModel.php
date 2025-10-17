<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Enums\TemplateStatus;
use App\Models\Enums\TemplateType;


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
                ON DUPLICATE KEY UPDATE `status` = :upd8_status, `label` = :upd8_label, `subject` = :upd8_subject, `body` = :upd8_body, `dateupdate` = :upd8_dateupdate";

    protected array $orderByColumns = [
        0 => 'templates.id DIR',
        1 => 'templates.status DIR',
        2 => 'templates.messagetype DIR',
        3 => 'templates.label DIR',
    ];

    protected string $table = 'templates';
    protected string $primaryKey = 'id';

    //protected array $hasMany = [];
    protected bool $hasStub = false;


    /**
     * @param array $data <mixed>
     * @return string
     */
    public function prepAndSave(array $data): string
    {
        $data['dateupdate'] = date('Y-m-d H:i:s');
        $this->logInfo('Template save data', $data);
        if ($data['messagetype'] === 'sms') {
            $data['body'] = strip_tags($data['body']);
        }
        $data['id'] = (empty($data['id']) ? null : $data['id']);

        $save_values = [];
        foreach ($data as $key => $value) {
            $save_values[$key] = $value;
            $save_values["upd8_$key"] = $value;
        }

        return $this->runQuery($this->insert, $save_values, 'insert');
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

        if (!empty($params['dataFilter'])) {
            switch ($params['dataFilter']) {
                case 'active':
                    $conditions[] = 'templates.status = :template_status ';
                    $search_values['template_status'] = $params['dataFilter'];
                    break;
                case 'sms':
                case 'email':
                    $conditions[] = 'templates.messagetype = :messagetype';
                    $search_values['messagetype'] = $params['dataFilter'];
                default:
            }
        }

        $sql = 'SELECT * FROM `templates`'
            . (count($conditions) ? ' WHERE (' . implode(' AND ', $conditions) . ')' : '')
            . ' ORDER BY ' . $this->getOrderBy($params)
            . " LIMIT {$params['start']}, {$params['length']}";

        $output = [];

        $list = $this->runQuery($sql, $search_values);
        foreach ($list as $row) {

            $output[] = [
                'DT_RowId' => $row['id'],
                'id' => $row['id'],
                'messagetype' => TemplateType::getLabel($row['messagetype']),
                'status' => TemplateStatus::getLabel($row['status']),
                'subject' => $row['subject'],
                'body' => $row['body'],
                'preview' => $this->getPreview($row['messagetype'], $row['subject'], $row['body']),
                'label' => $this->getLabelModal($row['id'], $row['label'])
            ];
        }

        $records_total = 'SELECT count(*) FROM templates';
        //todo this isn't right
        $records_filtered = 'SELECT count(*) FROM templates ' . count($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';

        return json_encode([
            'count' => count($output),
            'draw' => $params['draw'],
            'recordsTotal' => $this->runQuery($records_total, [], 'column'),
            'recordsFiltered' => $this->runQuery($records_filtered, $search_values, 'column'),
            'mainquery' => $sql,
            'mainsearchvals' => $search_values,
            'data' => $output
        ]);
    }

    protected function getPreview(string $messagetype, string $subject, string $body): string
    {
        if ($messagetype === 'sms') {
            return $body;
        } else {
            return "<b>$subject</b><br/>$body";
        }
    }

    protected function getLabelModal(string $id, string $label): string
    {
        return "<a href='#' class='templateRow' 
                    data-bs-toggle='modal' 
                    data-bs-target='#templateEditModal'
                    data-template_id='$id' 
                    >$label</a>";
    }

    public function getSelectChoices($message_type): string
    {
        $sql = "SELECT id, label 
            FROM `templates` 
            WHERE `status` = 'active' 
            AND `messagetype` = :message_type
            ORDER BY label";
        $result = $this->runQuery($sql, ['message_type' => $message_type]);;
        $output = [];
        foreach ($result as $row) {
            $output[] = "<option value='$row[id]'>$row[label]</option>";
        }
        return implode($output);
    }
}
