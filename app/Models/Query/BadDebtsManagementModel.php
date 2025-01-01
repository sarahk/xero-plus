<?php
declare(strict_types=1);

namespace App\Models\Query;

use App\Models\Query\BaseQueryModel;
use App\Models\Query\Traits\BadDebtsTrait;

class BadDebtsManagementModel extends BaseQueryModel
{
    use BadDebtsTrait;

    protected array $orderByColumns = [
        1 => 'name DIR',
        2 => 'amount_due DIR',
        3 => 'weeks_due DIR',
        4 => 'total_weeks DIR'
    ];

    protected int $defaultOrderByColumn = 2;

    /**
     * @return array<mixed>
     */
    public function list(): string
    {
        //$tenancyList = $this->getTenancyList();

        $search_values = [];
        $tenancies = $this->getTenanciesWhere($this->params, 'vbad_debts_management');
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

            $clauses = [
                'Weekly' => '(vbad_debts_management.schedule_unit = "Weekly" AND vbad_debts_management.is_unpaid >= 3)',
                'Fortnightly' => '(vbad_debts_management.schedule_unit = "FORTNIGHTLY" AND vbad_debts_management.is_unpaid >= 2)',
                'Monthly' => '(vbad_debts_management.schedule_unit = "MONTHLY" AND vbad_debts_management.is_unpaid >= 1)',
                'Other' => '(vbad_debts_management.schedule_unit NOT IN ("WEEKLY", "FORTNIGHTLY", "MONTHLY") AND vbad_debts_management.is_unpaid >= 1)'
            ];
            // cant I change this to a match?
            if ($this->params['button'] === 'All') {
                $conditions[] = '(' . implode(' OR ', $clauses) . ')';
            } else if (array_key_exists($this->params['button'], $clauses)) {
                $conditions[] = $clauses[$this->params['button']];
            }
        }

        if (isset($_GET['repeating_invoice_id'])) {
            $conditions[] = 'contracts.repeating_invoice_id = :repeating_invoice_id';
            $search_values['repeating_invoice_id'] = $_GET['repeating_invoice_id'];
        }


        // use the view
        $baseSql = ['select' => 'SELECT  
                       tenancies.xero_shortcode, tenancies.colour,
                       vbad_debts_management.*,
                       contacts.name, contacts.first_name, contacts.last_name,
                       contacts.email_address,
                       contacts.contact_id',
            'joins' => ' FROM tenancies
                        LEFT JOIN vbad_debts_management on tenancies.tenant_id = vbad_debts_management.xerotenant_id
                        LEFT JOIN contacts on (vbad_debts_management.ckcontact_id = contacts.id)',
            'where' => ' WHERE ' . implode(' AND ', $conditions),
            'order' => " ORDER BY $order 
                         LIMIT {$this->params['start']}, {$this->params['length']}"
        ];
// 'group' => ' GROUP BY tenancies.tenant_id, contracts.contract_id, contacts.id',
        $sql = implode(' ', $baseSql);

        $bad_debts = $this->runQuery($sql, $search_values);

        $output = $this->params;
        $output['mainquery'] = $this->cleanSql($sql);
        $output['mainsearchvals'] = $search_values;
        // adds in tenancies because it doesn't use $conditions
        $recordsTotal = "SELECT count(vbad_debts_management.contract_id)"
            . $baseSql['joins']
            . ' WHERE ' . $tenancies;


        $recordsFiltered = "SELECT count(vbad_debts_management.contract_id)"
            . $baseSql['joins']
            . $baseSql['where'];

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
                    'total_weeks' => $row['total_weeks'],
                    'weeks_due' => $row['weeks_due'],
                    'colour' => $row['colour'],
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

    public function getButtonCounts($tenancies): array
    {
        $sql = "SELECT 
                    SUM(IF(vbad_debts_management.schedule_unit = 'Weekly' AND vbad_debts_management.is_unpaid >= 3,1,0)) AS weekly,
                    SUM(IF(vbad_debts_management.schedule_unit = 'Fortnightly' AND vbad_debts_management.is_unpaid >= 2,1,0)) AS fortnightly,
                    SUM(IF(vbad_debts_management.schedule_unit = 'Monthly' AND vbad_debts_management.is_unpaid >= 1,1,0)) AS monthly,
                    SUM(IF(vbad_debts_management.schedule_unit not in('Weekly','Fortnightly', 'Monthly')
                        AND vbad_debts_management.is_unpaid >= 1,1,0)) AS other
                FROM
                    tenancies
                        LEFT JOIN
                    vbad_debts_management ON vbad_debts_management.xerotenant_id = tenancies.tenant_id
                        LEFT JOIN
                    contacts ON (vbad_debts_management.ckcontact_id = contacts.id)
                WHERE $tenancies";
        $result = $this->runQuery($sql);
        $output = $result[0];
        $output['all'] = $output['weekly'] + $output['fortnightly'] + $output['monthly'] + $output['other'];
        return $output;
    }
}
