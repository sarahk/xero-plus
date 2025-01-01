<?php

namespace App;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

Utilities::checkAccessToken();


$xero = new XeroClass();
$auckland = 'ae75d056-4af7-484d-b709-94439130faa4';
$waikato = 'e95df930-c903-4c58-aee9-bbc21b78bde7';
$bop = 'eafd3b39-46c7-41e4-ba4e-6ea6685e39f7';

echo '<h4>Auckland</h4>';
$organisation = $xero->getOrganisation(($auckland));
print_r($organisation[0]->getShortcode());
echo '<hr>';
echo '<h4>Waikato</h4>';
$organisation = $xero->getOrganisation(($waikato));
print_r($organisation[0]->getShortcode());
echo '<hr>';
echo '<h4>BoP</h4>';
$organisation = $xero->getOrganisation(($bop));
print_r($organisation[0]->getShortcode());
echo '<hr>';
exit;
//$xero->getInvoiceRefresh('auckland');
$data = $xero->getXeroInvoices($auckland, date('Y-m-d', strtotime('yesterday')));
echo count($data);
echo '<hr>';

echo '<h3>looking for phones</h3>';
foreach ($data as $k => $row) {
    echo 'Row: ' . $k . '<ul>';

    $contact_id = $row->getContact()->getContactId();
    echo "<li>$contact_id</li>";

    $full_contact = $xero->getContact($auckland, $contact_id);
    $xero->getContactRefresh($auckland, $contact_id);
//    echo "<li>";
//    var_dump($full_contact[0]);
//    echo '</li>';
    $phone_array = $full_contact[0]->getPhones();
//    echo "<li>";
//    ExtraFunctions::debug($phone_array[0]);
//    echo '</li>';
    if (count($phone_array) > 0) {
        foreach ($phone_array as $phone) {
            $phone_number = trim($phone->getPhoneAreaCode() . ' ' . $phone->getPhoneNumber());
            $phone_type = $phone->getPhoneType();
            $mobile_prefixes = ['021', '022', '027'];
            if ($phone_type !== 'MOBILE' && in_array($phone->getPhoneAreaCode(), $mobile_prefixes)) {
                $phone_type = 'MOBILE';
            }
            if (!empty($phone_number)) {
                echo "<li>$phone_type: $phone_number</li>";
            }

        }
    }
    echo '</ul>';
    if ($k === 10)
        exit;
}
