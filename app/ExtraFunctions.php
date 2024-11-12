<?php

namespace App;

use DateTime;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class ExtraFunctions
{


    /*
    Xero send the date like this... "/Date(1589414400000+0000)/"
    this function turns it into a nice date time string.
    */
    public static function getDateFromXero($str): string
    {
        $raw = intval(substr($str, 6, -7) / 1000);
        return date('Y-m-d H:i:s', $raw);
    }

// if using one of the classes use $this->debug rather than this function
    public static function debug($val): void
    {
        echo '<ul>';
        self::showValue('', $val);

        // show where debug was called from
        $bt = debug_backtrace();

        $caller = array_shift($bt);
        echo "
    <li>{$caller['file']}</li>
    <li>{$caller['line']}</li>
</ul>
<hr>\n";

    }

// don't call showValue directly, use debug
    public static function showValue($k, $val)
    {
        $ul = "<ul style='list-style-type: disc; padding: 1em;'>";
        echo '<li>';
        if (is_array($val)) {
            echo $k . $ul;

            foreach ($val as $key => $row) {
                self::showValue($key, $row);
            }
            echo '</ul></li>';
        } else if (is_object($val)) {
            echo $k . $ul;

            foreach ((array)$val as $key => $row) {
                self::showValue($key, $row);
            }
            $methods = get_class_methods($val);
            foreach ($methods as $v) {
                echo "<li><i>$v</i></li>";
            }
            echo '</ul></li>';
        } else {
            echo(strlen($k) ? "$k: " : ''), is_string($val) ? '"' . $val . '"' : $val;
        }
        echo "</li>";
    }

    public static function getElapsedTime($start, $end = null): string
    {
        $startDT = new DateTime($start);

        if (is_null($end)) $endDT = new DateTime();
        else $endDT = new DateTime($end);

        $interval = $endDT->diff($startDT);

        $elapsed = [];
        if ($interval->y == 1) $elapsed[] = '1 year';
        else if ($interval->y > 1) $elapsed[] = "{$interval->y}  years";

        if ($interval->m == 1) $elapsed[] = '1 month';
        else if ($interval->m > 1) $elapsed[] = "{$interval->m} months";

        if ($interval->d == 1) $elapsed[] = '1 day';
        else $elapsed[] = "{$interval->d} days";

        return implode(', ', $elapsed);
    }


    public static function getCard(string $filename, string $label, string $cardId, array $data): void
    {
        ?>
        <div class="card" id="$cardId">
            <div class="card-header">
                <h3 class="card-title"><?= $label; ?><span class="cardHeaderExtra"></span></h3>
            </div>
            <div class="card-body">
                <?php include SITE_ROOT . $filename; ?>
            </div>
        </div>
        <?php
    }

    public static function getEmailDisplay($email): string
    {
        if (preg_match("/^[\w\.-]+@[\w\.-]+\.\w+$/", $email)) {
            return "<i class='fa-solid fa-at text-success'></i> <a href='mailto:$email'> $email</a>";
        } else {
            return "<i class='fa-solid fa-at text-danger'></i> <s class=' text-danger'>$email</s>";
        }
    }

    public static function getPhoneDisplay($row)
    {
        $area = $row['phone_area_code'];
        if (substr($area, 0, 1) !== '0') $area = "0{$area}";
        return "<a href='tel:{$area}{$row['phone_number']}'>$area {$row['phone_number']}</a>";
    }

    public static function getAddressDisplay($row)
    {
        $address = [];
        if (!empty($row['address_line1']))
            $address[] = $row['address_line1'];
        if (!empty($row['address_line2']))
            $address[] = $row['address_line2'];

        if (!empty($row['city']))
            $address[] = $row['city'];

        if (!empty($row['region']))
            $address[] = $row['region'] . ' ' . $row['postal_code'];

        return implode('<br>', $address);
    }

    public static function getTabs($tabList, $active, $data): void
    {
        ?>
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-pay">
                        <ul class="nav tabs-menu">
                            <?php
                            foreach ($tabList as $tab) {
                                $class = ($tab['name'] === $active) ? ' active ' : '';
                                echo "<li><a href='#tab-{$tab['name']}' class='{$class}' data-bs-toggle='tab'>{$tab['label']}</a></li>";
                            }
                            ?>
                        </ul>
                    </div>

                    <div class="panel-body tabs-menu-body">
                        <div class="tab-content">
                            <?php
                            foreach ($tabList as $tab) {
                                $class = ($tab['name'] === $active) ? ' active ' : '';
                                echo "<div class='tab-pane {$class}' id='tab-{$tab['name']}'>";
                                include(SITE_ROOT . $tab['filename']);
                                echo '</div>';
                            } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public static function hasIdValue(string $name, string $fld = ''): bool
    {

        if (stristr($name, '_id')) {
            if (!empty($fld)) return true;
        }
        return false;
    }

    public static function hasAnyValues(array $row): bool
    {
        foreach ($row as $name => $fld) {
            if (self::hasIdValue($name, "$fld")) {
                return true;
            }
        }

        return false;
    }

    public static function getCount(null|array $array = []): string
    {
        $count = 0;
        if (is_array($array)) {
            foreach ($array as $row) {
                if (is_array($row) && self::hasAnyValues($row)) $count++;
            }
        }
        if ($count) {
            return "({$count})";
        }
        return '';
    }

    public static function getAccordionItem(int $counter, string $parent, string $label, string $body): void
    {
        ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $counter; ?>">
                <button class="accordion-button collapsed {$active}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse<?= $counter; ?>" aria-expanded="false"
                        aria-controls="collapse<?= $counter; ?>"><?= $label; ?>
                </button>
            </h2>
            <div id="collapse<?= $counter; ?>" class="accordion-collapse collapse"
                 aria-labelledby="heading<?= $counter; ?>"
                 data-bs-parent="#<?= $parent; ?>" style="">
                <div class="accordion-body">
                    <?= $body; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /* duplicate of the ones in the FunctionsTrait */
    public static function toNormalDate(null|string $val): string
    {
        if (empty($val)) return '';

        $date = date_create($val);
        return date_format($date, "d-M-Y");
    }

    public static function getPestLogger(string $name)
    {
        $output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
        $dateFormat = "Y-n-j, g:i a";

        $formatter = new LineFormatter(
            $output, // Format of message in log
            $dateFormat, // Datetime format
            true, // allowInlineLineBreaks option, default false
            true  // discard empty Square brackets in the end, default false
        );
        $logger = new Logger('Pest Logger');

        $stream_handler = new StreamHandler(__DIR__ . "/monolog/$name.log", Level::Debug);
        $stream_handler->setFormatter($formatter);
        $logger->pushHandler($stream_handler);
        $logger->log('info', 'New Setup');
        return $logger;
    }

    public static function outputKeysAsJs(array $keys = []): void
    {
        $js = json_encode($keys, JSON_PRETTY_PRINT);
        ?>
        <script>
            const keys = <?= $js ?>;
        </script>
        <?php
    }
}
/*
// sample repeating invoice data
$list = [
    [
        "Schedule" => [
            "Period" => 1,
            "Unit" => "WEEKLY",
            "DueDate" => 0,
            "DueDateType" => "DAYSAFTERBILLDATE",
            "StartDate" => "/Date(1573603200000+0000)/",
            "NextScheduledDate" => "/Date(1695772800000+0000)/",
            "NextScheduledDateString" => "2023-09-27"
        ],
        "RepeatingInvoiceID" => "9ef0752c-161c-482d-af8d-438c780042b8",
        "Type" => "ACCREC",
        "Reference" => "107",
        "HasAttachments" => false,
        "ID" => "9ef0752c-161c-482d-af8d-438c780042b8",
        "ApprovedForSending" => false,
        "IncludePDF" => true,
        "Contact" => [
            "ContactID" => "ea6652f1-5911-4a0e-baf1-29d2ada97ef8",
            "Name" => "Kevin Cassidy",
            "Addresses" => [],
            "Phones" => [],
            "ContactGroups" => [],
            "ContactPersons" => [],
            "HasValidationErrors" => false
        ],
        "Status" => "AUTHORISED",
        "LineAmountTypes" => "Exclusive",
        "LineItems" => [
            [
                "Description" => "Rent",
                "UnitAmount" => 50,
                "TaxType" => "NONE",
                "TaxAmount" => 0,
                "LineAmount" => 50,
                "AccountCode" => "200",
                "Tracking" => [],
                "Quantity" => 1,
                "LineItemID" => "374666a6-a74e-4654-9c0f-9a05d2cdbf25",
                "AccountID" => "ba9781fa-af5d-4d96-9718-a875bdf1b557"
            ]
        ],
        "SubTotal" => 50,
        "TotalTax" => 0,
        "Total" => 50,
        "CurrencyCode" => "NZD"
    ],

    [
        "Schedule" => [
            "Period" => 1,
            "Unit" => "MONTHLY",
            "DueDate" => 20,
            "DueDateType" => "OFCURRENTMONTH",
            "StartDate" => "/Date(1685836800000+0000)/",
            "NextScheduledDate" => "/Date(1693785600000+0000)/",
            "NextScheduledDateString" => "2023-09-04"
        ],
        "RepeatingInvoiceID" => "8a4c6a5f-8928-4ec1-9f76-79858191c3e3",
        "Type" => "ACCREC",
        "Reference" => "401",
        "HasAttachments" => false,
        "ID" => "8a4c6a5f-8928-4ec1-9f76-79858191c3e3",
        "ApprovedForSending" => true,
        "SendCopy" => false,
        "MarkAsSent" => true,
        "IncludePDF" => true,
        "Contact" => [
            "ContactID" => "0435e68f-29d7-4b08-86b8-248a50956e77",
            "Name" => "Knights Security Ltd",
            "Addresses" => [],
            "Phones" => [],
            "ContactGroups" => [],
            "ContactPersons" => [],
            "HasValidationErrors" => false
        ],
        "Status" => "DELETED",
        "LineAmountTypes" => "Inclusive",
        "LineItems" => [
            [
                "Description" => "Cabin rent",
                "UnitAmount" => 398.66,
                "TaxType" => "OUTPUT2",
                "TaxAmount" => 52,
                "LineAmount" => 398.66,
                "AccountCode" => "205",
                "Tracking" => [],
                "Quantity" => 1,
                "LineItemID" => "2f586fc4-c9d9-4c61-8daa-ec7acbae7cf9",
                "AccountID" => "1eb4b034-7f12-47d9-a84f-3662355a0e56"
            ]
        ],
        "SubTotal" => 346.66,
        "TotalTax" => 52,
        "Total" => 398.66,
        "UpdatedDateUTC" => "/Date(1691366544110+0000)/",
        "CurrencyCode" => "NZD"
    ],
    [
        "Schedule" => [
            "Period" => 1,
            "Unit" => "WEEKLY",
            "DueDate" => 0,
            "DueDateType" => "DAYSAFTERBILLDATE",
            "StartDate" => "/Date(1644451200000+0000)/",
            "NextScheduledDate" => "/Date(1691625600000+0000)/",
            "NextScheduledDateString" => "2023-08-10"
        ],
        "RepeatingInvoiceID" => "60579df5-1ffa-4b38-8269-bb4208647611",
        "Type" => "ACCREC",
        "Reference" => "132",
        "HasAttachments" => false,
        "ID" => "60579df5-1ffa-4b38-8269-bb4208647611",
        "ApprovedForSending" => false,
        "IncludePDF" => true,
        "Contact" => [
            "ContactID" => "5f5f90d8-c563-48f9-b878-df8ca31f491d",
            "Name" => "Chris De Martin (Faber)",
            "Addresses" => [],
            "Phones" => [],
            "ContactGroups" => [],
            "ContactPersons" => [],
            "HasValidationErrors" => false
        ],
        "Status" => "DELETED",
        "LineAmountTypes" => "Inclusive",
        "LineItems" => [
            [
                "Description" => "Cabin rent",
                "UnitAmount" => 55,
                "TaxType" => "NONE",
                "TaxAmount" => 0,
                "LineAmount" => 55,
                "AccountCode" => "200",
                "Tracking" => [],
                "Quantity" => 1,
                "LineItemID" => "1972f41b-056c-44ed-8043-8ea9d5b5d9f0",
                "AccountID" => "ba9781fa-af5d-4d96-9718-a875bdf1b557"
            ]
        ],
        "SubTotal" => 55,
        "TotalTax" => 0,
        "Total" => 55,
        "UpdatedDateUTC" => "/Date(1691546484777+0000)/",
        "CurrencyCode" => "NZD"
    ],

    [
        "Schedule" => [
            "Period" => 1,
            "Unit" => "MONTHLY",
            "DueDate" => 0,
            "DueDateType" => "DAYSAFTERBILLDATE",
            "StartDate" => "/Date(1645574400000+0000)/",
            "NextScheduledDate" => "/Date(1695427200000+0000)/",
            "NextScheduledDateString" => "2023-09-23"
        ],
        "RepeatingInvoiceID" => "8e45751e-d43c-4b69-9aa4-7080d670840c",
        "Type" => "ACCREC",
        "Reference" => "149",
        "HasAttachments" => false,
        "ID" => "8e45751e-d43c-4b69-9aa4-7080d670840c",
        "ApprovedForSending" => true,
        "SendCopy" => false,
        "MarkAsSent" => true,
        "IncludePDF" => true,
        "Contact" => [
            "ContactID" => "d8582f85-4aff-4ddd-8b3e-381b2d7383bd",
            "Name" => "David Herrick",
            "Addresses" => [],
            "Phones" => [],
            "ContactGroups" => [],
            "ContactPersons" => [],
            "HasValidationErrors" => false
        ],
        "Status" => "DELETED",
        "LineAmountTypes" => "Inclusive",
        "LineItems" => [
            [
                "Description" => "Cabin rent",
                "UnitAmount" => 274.08,
                "TaxType" => "OUTPUT2",
                "TaxAmount" => 35.75,
                "LineAmount" => 274.08,
                "AccountCode" => "205",
                "Tracking" => [],
                "Quantity" => 1,
                "LineItemID" => "8e90d8d1-a59c-496a-b964-c93df511e51f",
                "AccountID" => "1eb4b034-7f12-47d9-a84f-3662355a0e56"
            ]
        ],
        "SubTotal" => 238.33,
        "TotalTax" => 35.75,
        "Total" => 274.08,
        "UpdatedDateUTC" => "/Date(1695078581763+0000)/",
        "CurrencyCode" => "NZD"
    ],
    [
        "Schedule" => [
            "Period" => 1,
            "Unit" => "WEEKLY",
            "DueDate" => 0,
            "DueDateType" => "DAYSAFTERBILLDATE",
            "StartDate" => "/Date(1670544000000+0000)/",
            "NextScheduledDate" => "/Date(1695340800000+0000)/",
            "NextScheduledDateString" => "2023-09-22"
        ],
        "RepeatingInvoiceID" => "5b28e790-73d7-4000-aabb-49da135e287a",
        "Type" => "ACCREC",
        "Reference" => "301",
        "HasAttachments" => false,
        "ID" => "5b28e790-73d7-4000-aabb-49da135e287a",
        "ApprovedForSending" => false,
        "IncludePDF" => true,
        "Contact" => [
            "ContactID" => "efc1d5f9-3780-4c59-803f-82f50df2a52c",
            "Name" => "Amelia Hill",
            "Addresses" => [],
            "Phones" => [],
            "ContactGroups" => [],
            "ContactPersons" => [],
            "HasValidationErrors" => false
        ],
        "Status" => "DELETED",
        "LineAmountTypes" => "Inclusive",
        "LineItems" => [
            [
                "Description" => "Cabin lease",
                "UnitAmount" => 80,
                "TaxType" => "NONE",
                "TaxAmount" => 0,
                "LineAmount" => 80,
                "AccountCode" => "200",
                "Tracking" => [],
                "Quantity" => 1,
                "LineItemID" => "15774f74-f912-40c2-a5e2-c6f4c0ea4ad2",
                "AccountID" => "ba9781fa-af5d-4d96-9718-a875bdf1b557"
            ]
        ],
        "SubTotal" => 80,
        "TotalTax" => 0,
        "Total" => 80,
        "UpdatedDateUTC" => "/Date(1695078929347+0000)/",
        "CurrencyCode" => "NZD"
    ]
];
*/
