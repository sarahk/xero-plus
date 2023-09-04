<?php

require_once(SITE_ROOT . '/models/BaseModel.php');

class InvoiceModel extends BaseModel
{
    public function list($params)
    {

        $where = $statuses = null;
        /*
        <th>#</th>
        <th>Contact</th>
        <th>Ref</th>
        <th>Total</th>
        <th>Due</th>
        <th>Date</th>
*/
        $tenancies = '(';
        foreach ($params['tenancies'] as $k => $val) {
            if ($k > 0) {
                $tenancies .= ' OR ';
            }
            $tenancies .= "`invoices`.`xerotenant_id` = '{$val}'";
        }
        $tenancies .= ') ';

        if (is_array($params['order'])) {
            switch ($params['order'][0]['column']) {
                case 0:
                    $order = "invoices.invoice_number {$params['order'][0]['dir']}";
                    break;
                case 1:
                    $order = "contacts.last_name {$params['order'][0]['dir']}, contacts.first_name ASC";
                    break;
                case 2:
                    $order = "invoices.reference {$params['order'][0]['dir']}";
                    break;
                case 3:
                    $order = "invoices.total {$params['order'][0]['dir']}";
                    break;
                case 4: // amount due
                    $order = "invoices.amount_due {$params['order'][0]['dir']}";
                    break;
                case 5:
                default:
                    $direction = $params['order'][0]['dir'] ?? ' DESC ';
                    $order = "invoices.due_date {$direction}";
                    break;
            }
        } else {
            $order = "invoices.due_date DESC";
        }

        $conditions = [$tenancies];
        if (!empty($params['search'])) {
            $search = [
                "`contacts`.`name` like '%{$params['search']}%'",
                "`contacts`.`last_name` like '%{$params['search']}%'",
                "`contacts`.`first_name` like '%{$params['search']}%'",
                "`invoices`.`invoice_number` like '%{$params['search']}%'"
            ];
            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }
        if (!empty($params['button'])) {
            $status = strtoupper($params['button']);
            $conditions[] = "`invoices`.`status` = '{$status}'";
        } else {
            //$conditions[] = "`invoices`.`status` = 'AUTHORISED'";
        }


        $fields = [
            'invoices.invoice_id',
            'invoices.status',
            'invoices.invoice_number',
            'invoices.reference',
            'invoices.total',
            'invoices.amount_paid',
            'invoices.amount_due',
            'invoices.due_date',
            'invoices.contact_id',
            'contacts.name'
        ];


        $sql = "SELECT " . implode(',', $fields) . " FROM `invoices` 
        LEFT JOIN `contacts` ON (`invoices`.`contact_id` = `contacts`.`contact_id`) 
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY {$order} 
        LIMIT {$params['start']}, {$params['length']}";

        $invoices = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $output = $params;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(*) FROM invoices 
                WHERE $tenancies";

        $recordsFiltered = "SELECT count(*) FROM invoices 
                LEFT JOIN `contacts` ON (`invoices`.`contact_id` = `contacts`.`contact_id`) 
                WHERE  " . implode(' AND ', $conditions);

        $output['recordsTotal'] = $this->pdo->query($recordsTotal)->fetchColumn();
        $output['recordsFiltered'] = $this->pdo->query($recordsFiltered)->fetchColumn();
        //$output['refreshInvoice'] = $refreshInvoice;
        // $output['refreshContact'] = $refreshContact;


        if (count($invoices)) {
            foreach ($invoices as $k => $row) {
                if (empty($row['name'])) {
                    $contactName = "<a href='#' data-toggle='modal' data-target='#contactSingle' data-contactid='{$row['contact_id']}'>{$row['contact_id']}</a>";
                } else {
                    $contactName = "<a href='#' data-toggle='modal' data-target='#contactSingle' data-contactid='{$row['contact_id']}'>{$row['name']}</a>";
                }
                $output['data'][] = [
                    'number' => $row['invoice_number'],
                    'reference' => $row['reference'],
                    'contact' => $contactName,
                    'status' => "<a href='https://go.xero.com/AccountsReceivable/View.aspx?InvoiceID={$row['invoice_id']}' target='_blank'>{$row['status']}</a>",
                    'total' => $row['total'],
                    'amount_paid' => $row['amount_paid'],
                    'amount_due' => $row['amount_due'],
                    'due_date' => $row['due_date']
                ];
            }
            $output['row'] = $row;
        }
        return $output;
    }
}