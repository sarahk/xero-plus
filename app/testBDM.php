<?php
declare(strict_types=1);

namespace App;

use App\Models\InvoiceModel;
use App\Models\PaymentModel;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';


$invoices = new InvoiceModel(Utilities::getPDO());
$payments = new PaymentModel(Utilities::getPDO());
$payments->repairContractId();

//$contract_id = 190;
$contract_id = 1023;
echo "<h1>$contract_id</h1>";

$sql = "SELECT 
            *
        FROM
            vbad_debts_management
        WHERE
            contract_id = $contract_id";

$basic_results = $invoices->testQuery($sql);

ExtraFunctions::debugTable($basic_results);

$sql = "SELECT 
            `contracts`.`contract_id` AS `contract_id`,
            `contracts`.`schedule_unit` AS `schedule_unit`,
            invoices.date,invoices.total, invoices.amount_paid,
            ISRECENTANDUNPAID(`contracts`.`contract_id`,
                    `contracts`.`schedule_unit`,
                    `invoices`.`invoice_id`,
                    `invoices`.`date`,
                    `invoices`.`amount_paid`) AS `is_unpaid`
        FROM
            (`contracts`
            LEFT JOIN `invoices` ON ((`contracts`.`contract_id` = `invoices`.`contract_id`)))
        WHERE
            contracts.contract_id = $contract_id
        ORDER BY invoices.date DESC
        LIMIT 16";

$test_results = $invoices->testQuery($sql);
ExtraFunctions::debugTable($test_results);

$chartSql = "SELECT 
                    weeks.week_number,
                        (
                        SELECT SUM(invoices.total)
                        FROM invoices 
                        WHERE invoices.contract_id = $contract_id
                          AND FLOOR(DATEDIFF(CURDATE(), invoices.date) / 7) >= weeks.week_number
                    ) AS `owing`,
                    (
                        SELECT SUM(payments.amount) 
                        FROM payments 
                        WHERE payments.contract_id = $contract_id
                          AND FLOOR(DATEDIFF(CURDATE(), payments.date) / 7) >= weeks.week_number
                    ) AS `paid`
                FROM 
                    weeks
                    ORDER BY week_number ASC";
$chart_results = $invoices->testQuery($chartSql);
for ($i = 0; $i < count($chart_results); $i++) {
    $chart_results[$i]['diff'] = $chart_results[$i]['owing'] - $chart_results[$i]['paid'];
}
ExtraFunctions::debugTable($chart_results);

?>
    <p>Graph</p>
    <img src="/run.php?endpoint=image&amp;imageType=baddebt&amp;contract_id=<?= $contract_id ?>"/>
    <hr>
    <?php
$chartData = $invoices->getChartData("$contract_id");
var_dump($chartData);
//ExtraFunctions::debugTable($chartData);
