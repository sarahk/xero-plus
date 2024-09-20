<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

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


    protected string $table = 'templates';
    protected string $primaryKey = 'id';

    protected array $hasMany = [];
    protected bool $hasStub = false;

    function __construct($pdo)
    {
        parent::__construct($pdo);

        $this->buildInsertSQL();
    }


    public function prepAndSave($data): int
    {
        if ($data['messagetype'] === 'SMS') {
            $data['body'] = strip_tags($data['body']);
        }
        return $this->save($data);
    }


    /*
     * TODO
     * need to provide the option to limit it to a particular tenancy
     * add weighting in the future?
     */
    public function search(): string
    {
        $searchFields = ['status', 'messagetype', 'label'];
        $conditions = $values = [];

        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 0;

        foreach ($searchFields as $var) {
            if (!empty($_GET[$var])) {
                $conditions[] = " `$var` LIKE :$var ";
                $values[':' . $var] = $_GET[$var];
            }
        }

        $sql = "SELECT *
            FROM `templates`
             WHERE  `id` is not null "
            . (count($conditions) ? " AND (" . implode(' AND ', $conditions) . ")" : '')
            . " LIMIT $start, $length";


        $this->getStatement($sql);
        $output = [];
        try {
            $this->statement->execute($values);
            $list = $this->statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($list as $row) {

                $subject = $row['subject'];
                $body = $row['body'];

                $output[] = [
                    'id' => $row['id'],
                    'messagetype' => $row['messagetype'],
                    'status' => $row['status'],
                    'subject' => $subject,
                    'body' => $body,
                    'preview' => $this->getPreview($row['messagetype'], $subject, $body),
                    'label' => $this->getLabelModal($row['id'], $row['label'])
                ];

            }
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getMessage() . "\n";
            $this->statement->debugDumpParams();
        }

        return json_encode([
            'count' => count($output),
            'draw' => $_GET['draw'],
            'data' => $output
        ]);
    }

    protected function getPreview($messagetype, $subject, $body): string
    {
        if ($messagetype === 'SMS') {
            return $body;
        } else {
            return "<b>$subject</b><br/>$body";
        }
    }

    protected function getLabelModal($id, $label): string
    {
        return "<a href='#' class='templateRow' 
                    data-bs-toggle='modal' 
                    data-bs-target='#templateModal'
                    data-template_id='$id' 
                    >$label</a>";
    }
}
