<?php
function getVehicleList($dbh)
{
    $sql = "select `id`, `numberplate` from `vehicles` order by `numberplate` ASC";

    $vehicles = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    return $vehicles;
}

/*
Xero send the date like this... "/Date(1589414400000+0000)/"
this function turns it into a nice date time string.
*/
function getDateFromXero($str)
{
    $raw = intval(substr($str, 6, -7) / 1000);
    return date('Y-m-d H:i:s', $raw);
}


function debug($val)
{
    echo '<ul>';
    showValue('', $val);

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
function showValue($k, $val)
{
    $ul = "<ul style='list-style-type: disc; padding: 1em;'>";
    echo '<li>';
    if (is_array($val)) {
        echo $k . $ul;

        foreach ($val as $key => $row) {
            showValue($key, $row);
        }
        echo '</ul></li>';
    } else if (is_object($val)) {
        echo $k . $ul;

        foreach ((array)$val as $key => $row) {
            showValue($key, $row);
        }
        $methods = get_class_methods($val);
        foreach ($methods as $v) {
            echo "<li><i>$v</i></li>";
        }
        echo '</ul></li>';
    } else {
        echo(strlen($k) ? "{$k}: " : ''), is_string($val) ? '"' . $val . '"' : $val;
    }
    echo "</li>";
}

/**
 * @param array $keys
 * @param array $array
 * @param string $match
 * @return bool
 */
function array_keys_exist(array $keys, array $array, string $match = 'any'): bool
{
    if (!array($keys) || !array($array)) {
        return false;
    }

    $arrayKeys = array_keys($array);
    foreach ($keys as $v) {
        // is the key in there? does it have a value?
        if (in_array($v, $arrayKeys) && $array[$v]) {
            if ($match === 'any') {
                return true;
            }
        } else if ($match === 'all') {
            return false;
        }
    }
    return true;
}

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
