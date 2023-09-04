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
    echo '<li>';
    if (is_array($val)) {
        echo $k.'<ul>';
        foreach ($val as $k => $row) {
            showValue($k, $row);
        }
        echo '</li></ul>';
    } else {
        echo (strlen($k) ? "{$k}: " : ''), $val;
    }
    echo "</li>";
}