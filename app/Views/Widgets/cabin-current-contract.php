<?php
namespace App\Views\Cabin;

use App\ExtraFunctions;

if (!count($data['contracts'])) {
    echo "<p>No current contract</p>";
    return;
}
?>
<div class="table-responsive">
    <table class="table border text-nowrap text-md-nowrap table-bordered mg-b-0">

        <tbody>
        <tr>
            <td>ID</td>
            <td>
                <?= $data['contracts']['contract_id']; ?></td>
            <td><a href="#" class="btn btn-info"><i
                            class="fa-solid fa-link"></i>
                    Xero</a></td>

            </td>
        </tr>
        <tr>
            <td>Contact</td>
            <td colspan="2">
                <strong><?= $data['contacts']['first_name']; ?></strong> <?= $data['contacts']['last_name']; ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td colspan="2">
                <?= ExtraFunctions::getEmailDisplay($data['contacts']['email_address']); ?>
            </td>
        </tr>
        <tr>
            <td>Phone</td>
            <td colspan="2"><?php
                if (count($data['phones'])) {
                    foreach ($data['phones'] as $row) {
                        echo getPhoneDisplay($row) . '<br>';
                    }
                }
                ?></td>
        </tr>
        <tr>
            <td>Cabin Address</td>
            <td colspan="1"><?= getAddressDisplay($data['contracts']); ?></td>
            <td>
                <a href="https://www.google.com/maps/place/?zoom=13&q=place_id:<?= $data['contracts']['place_id']; ?>"
                   target='_blank' class="btn btn-info">
                    <i class="fa-regular fa-map"></i> Maps</a>
            </td>
        </tr>
        <tr>
            <td>Delivered</td>
            <td colspan="2"><?= date('d M, Y', strtotime($data['contracts']['delivery_date'])); ?></td>
        </tr>
        <tr>
            <td>Pickup</td>
            <td><?php

                if (!empty($data['contracts']['delivery_date'])) {
                    if (!empty($data['contracts']['pickup_date'])) {
                        $end = $data['contracts']['pickup_date'];
                        echo date('d M, Y', strtotime($data['contracts']['pickup_date']));
                    } else if (!empty($data['contracts']['scheduled_pickup_date'])) {
                        echo date('d M, Y', strtotime($data['contracts']['scheduled_pickup_date']));
                        echo " (scheduled)";
                        $end = $data['contracts']['scheduled_pickup_date'];
                    } else {
                        $end = null;

                    }

                    $elapsed = getElapsedTime($data['contracts']['delivery_date'], $end);
                } ?></td>
        </tr>
        <tr>
            <td>Elapsed</td>
            <td><?= $elapsed; ?></td>
            <td><a href="javascript:void(0);" class="btn btn-primary">Edit</a></td>
        </tr>
        </tbody>
    </table>
</div>

<!--
repeating_invoice_id: "91be451b-736a-4611-b497-f1f5cb32ce36"
cabin_id: 2
ckcontact_id: 6
contact_id: "49ddc4a3-ddf2-4068-9a8c-18a57eb3601c"
status: "New"
schedule_unit: "MONTHLY"
reference: "112"
cabin_type:
hiab: "No"
painted: "---"
winz:
delivery_date: "2023-10-01"
scheduled_delivery_date:
delivery_time:
pickup_date:
scheduled_pickup_date:
address_line1:
address_line2:
city:
region:
postal_code:
lat:
long:
place_id:
updated: "2023-10-03 22:32:30"
total: 402
stub: 1
address:
-->
