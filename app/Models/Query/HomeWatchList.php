<?php

namespace App\Models\Query;

use App\Models\Query\Traits\BadDebtsTrait;

class HomeWatchList extends BaseQueryModel
{
    use BadDebtsTrait;

    public function list(): string
    {
        $tenancyList = $this->getTenancyList();

        $search_values = [];
        $tenancies = $this->getTenanciesWhere($this->params, 'vold_debts');
        $conditions[] = $tenancies;

        // this clause defines what a bad debt actually is
        $conditions[] = "vold_debts.amount_due > 0";


        // use the view
        // cut down, no bells, need to improve speed
        $sql = 'SELECT vold_debts.*,
            contacts.id as ckcontact_id, contacts.name, contacts.first_name, contacts.last_name,
            tenancies.xero_shortcode
            FROM `vold_debts`
            LEFT JOIN contacts on (vold_debts.contact_id = contacts.contact_id)
            LEFT JOIN tenancies on (vold_debts.xerotenant_id = tenancies.tenant_id)
            WHERE ' . implode(' AND ', $conditions) . "
            ORDER BY amount_due DESC 
            LIMIT 0, 10";


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
}
