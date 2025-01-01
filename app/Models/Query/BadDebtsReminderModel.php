<?php
declare(strict_types=1);

namespace App\Models\Query;

use App\Models\Query\BaseQueryModel;
use App\Models\Query\Traits\BadDebtsTrait;

class BadDebtsReminderModel extends BaseQueryModel
{
    use BadDebtsTrait;

    protected array $orderByColumns = [
        1 => 'name DIR',
        2 => 'amount_due DIR',
    ];

    protected int $defaultOrderByColumn = 2;

    /**
     * @return array<mixed>
     */
    public function list(): string
    {

        $tenancyList = $this->getTenancyList();

        $search_values = [];
        $tenancies = $this->getTenanciesWhere($this->params, 'vold_debts');

        $order = $this->getOrderBy();


        $conditions = [$tenancies];
        if (!empty($this->params['search'])) {
            $search = [
                "`contacts`.`name` LIKE :search ",
                "`contacts`.`last_name` LIKE :search ",
                "`contacts`.`first_name` LIKE :search "
            ];
            $search_values['search'] = '%' . $this->params['search'] . '%';

            $conditions[] = ' (' . implode(' OR ', $search) . ') ';
        }


        if (!empty($this->params['button'])) {
            $search_values = array_merge($search_values, [
                '1week' => date('Y-m-d', strtotime('-7 days')),
                '2weeks' => date('Y-m-d', strtotime('-14 days')),
                '3weeks' => date('Y-m-d', strtotime('-21 days'))
            ]);
//'older' => date('Y-m-d', strtotime('-30 days')),

            // cant I change this to a match?
            switch ($this->params['button']) {
                case 'All':
                    $conditions[] = '(unpaidweek1 = 1 OR unpaidweek2 = 1 OR unpaidweek3 = 1)';
                    break;
                // reminders options
                case '1 Week':
                    // they have an invoice that is 1 week old and it's unpaid
                    //$conditions[] = 'vold_debts.newest <= :1week AND vold_debts.newest > :2weeks';
                    $conditions[] = 'unpaidweek1 = 1';
                    break;
                case '2 Weeks':
                    // they have an invoice that is 2 weeks old and it is unpaid
                    //$conditions[] = 'vold_debts.newest <= :2weeks AND vold_debts.newest > :3weeks';
                    $conditions[] = 'unpaidweek2 = 1';
                    break;
                case '3 Weeks':
                    // they have an invoice that is 3 weeks old and it is unpaid
                    //$conditions[] = 'vold_debts.newest <= :3weeks AND vold_debts.newest > :older ';
                    $conditions[] = 'unpaidweek3 = 1';
                    break;

            }

        }

        if (isset($_GET['repeating_invoice_id'])) {
            $conditions[] = "`vold_debts`.`repeating_invoice_id` = :repeating_invoice_id";
            $search_values['repeating_invoice_id'] = $_GET['repeating_invoice_id'];
        }

        // this clause defines what a bad debt actually is
        $conditions[] = "vold_debts.amount_due > 0";


        // use the view
        $sql = 'SELECT vold_debts.*,
            contacts.id as ckcontact_id, contacts.name, contacts.first_name, contacts.last_name,
            tenancies.xero_shortcode
            FROM `vold_debts`
            LEFT JOIN contacts on (vold_debts.contact_id = contacts.contact_id)
            LEFT JOIN tenancies on (vold_debts.xerotenant_id = tenancies.tenant_id)
            WHERE ' . implode(' AND ', $conditions) . "
            ORDER BY $order 
            LIMIT {$this->params['start']}, {$this->params['length']}";


        //  (SELECT CONCAT(phones.phone_area_code, ' ', phones.phone_number) as `phone` from `phones` WHERE phones.ckcontact_id = contacts.id ORDER BY `phone_type` DESC LIMIT 1) AS phone,

        $bad_debts = $this->runQuery($sql, $search_values);

        $output = $this->params;
        $output['mainquery'] = $this->cleanSql($sql);
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(repeating_invoice_id) FROM `vold_debts` 
                WHERE $tenancies 
                AND (unpaidweek1 = 1 OR unpaidweek2 = 1 OR unpaidweek3 = 1)";

        $recordsFiltered = "SELECT count(repeating_invoice_id) as `filtered` 
                FROM `vold_debts` 
                WHERE " . implode(' AND ', $conditions);


        $output['recordsTotal'] = $this->runQuery($recordsTotal, [], 'column');
        $output['recordsFiltered'] = $this->runQuery($recordsFiltered, $search_values, 'column');
        $output['buttonCounts'] = $this->getButtonCounts($tenancies);

        if (count($bad_debts) > 0) {
            foreach ($bad_debts as $row) {

                // overview page
                $link = $this->getContractOverviewLink('91', $row);

                $output['data'][] = [
                    'DT_RowId' => $row['repeating_invoice_id'],
                    'contact' => $this->getFormattedContactCell($row),
                    'name' => $row['name'],
                    'amount_due' => $link . $row['amount_due'] . '</a>',
                    'weeks_due' => $row['weeks_due'],
                    'total_weeks' => $row['total_weeks'],
                    'colour' => $tenancyList[$row['xerotenant_id']]['colour'],
                    'flags' => '',
                    'chart' => "$link<img src='/run.php?endpoint=image&imageType=baddebt&contract_id={$row['contract_id']}' 
                                    alt=\"Bad Debt history for {$row['name']}\" 
                                    width='300' height='125'/></a>",
                    'sent' => $this->getSentToday($row['contact_id']),
                    'actions' => "<a href='#' data-bs-toggle='modal' data-bs-target='#contactSingle' 
                        data-tenancyid='{$row['xerotenant_id']}' data-contactid='{$row['contact_id']}' 
                        data-contractid='{$row['contract_id']}' class='text-end'>View</a><br/>
                        
                        <a href='" . $this->getXeroDeeplink('Contact', $row) . "' target='_blank'>Open in Xero</a>"
                ];
                // for debugging
                $output['row'] = $row;
            }

        }
        return json_encode($output);
    }

    public function getButtonCounts($tenancies)
    {

        $sql = "SELECT 
                    SUM(if(unpaidweek1 = 1 or unpaidweek2 = 1 or unpaidweek3 = 1,1,0)) as total,
                    SUM(unpaidweek1) AS week1,
                    SUM(unpaidweek2) AS week2,
                    SUM(unpaidweek3) AS week3
                FROM
                    vold_debts
                WHERE $tenancies";
        $result = $this->runQuery($sql);
        return $result[0];
    }
}
