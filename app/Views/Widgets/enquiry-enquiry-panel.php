<?php
namespace App\Views\Widgets;

use App\ExtraFunctions;
use App\Models\Enums\BestWayToContact;
use App\Models\Enums\CabinPainted;
use App\Models\Enums\CabinStyle;
use App\Models\Enums\CabinUse;
use App\Models\Enums\DeliveryTimes;
use App\Models\Enums\EnquiryRating;
use App\Models\Enums\EnquiryStatus;
use App\Models\Enums\HowDidYouHear;
use App\Models\Enums\ScheduleUnit;
use App\Models\Enums\WinzStatus;
use App\Models\Enums\YesNoDontKnow;

?>

<div class="card-header">
    <h3 class="card-title">Customer Details </h3>
</div>

<form action="/authorizedSave.php?action=10" id='enquiryForm' method="get" class="needs-validation" novalidate>
    <?php


    FormBuilder::hidden('action', 'action', '10');

    FormBuilder::hidden('id', 'data[contact][id]', $data['contacts']['id'] ?? '');
    FormBuilder::hidden('contact_id', 'data[contact][contact_id]', $data['contacts']['contact_id'] ?? '');
    FormBuilder::hidden('contact_status', 'data[contact][contact_status]', $data['contacts']['contact_status'] ?? '');
    //FormBuilder::hidden('contact_status', 'data[contact][contact_status]', $data['contacts']['contact_status']);

    FormBuilder::hidden('contract_id', 'data[Contract][contract_id]', $data['Contract']['contract_id']);
    FormBuilder::hidden('address_line1', 'data[Contract][address_line1]', $data['Contract']['address_line1']);
    FormBuilder::hidden('address_line2', 'data[Contract][address_line2]', $data['Contract']['address_line2']);
    FormBuilder::hidden('postal_code', 'data[Contract][postal_code]', $data['Contract']['postal_code']);
    FormBuilder::hidden('city', 'data[Contract][city]', $data['Contract']['city']);
    FormBuilder::hidden('lat', 'data[Contract][lat]', $data['Contract']['lat']);
    FormBuilder::hidden('long', 'data[Contract][long]', $data['Contract']['long']);
    FormBuilder::hidden('place_id', 'data[Contract][place_id]', $data['Contract']['place_id']);


    FormBuilder::hidden('note_foreign_id', 'data[Note][foreign_id]', $data['Note'][0]['foreign_id'] ?? '');
    FormBuilder::hidden('note_parent', 'data[Note][parent]', 'contacts');
    FormBuilder::hidden('note_createdby', 'data[Note][createdby]', $_SESSION['user_id']);


    ?>

    <div class="row row-sm">
        <div class="col-md-9">
            <?php
            //ExtraFunctions::debug($data);
            require 'enquiry-contact-panel.php';
            FormBuilder::textarea('notes', 'data[note][note]', 'Notes');
            ?>
        </div>

        <div class="col-md-3">
            <?php
            FormBuilder::select('status', 'data[contract][status]',
                'Status',
                EnquiryStatus::getSelectOptions($data['Contract']['status']),
            );
            ?>
            <div id="doyoumean"></div>

            <?php FormBuilder::radio('data[contact][best_way_to_contact]',
                'Best way to contact',
                BestWayToContact::getAllAsArray(),
                $data['contacts']['best_way_to_contact'] ?? ''
            );
            FormBuilder::radio('data[contract][winz]', 'WINZ Form',
                WinzStatus::getAllAsArray(),
                $data['Contract']['winz']
            );
            FormBuilder::radio('data[contact][how_did_you_hear]', 'How did you  hear about us?',
                HowDidYouHear::getAllAsArray(),
                $data['contracts']['how_did_you_hear'] ?? '');

            FormBuilder::radio('data[contact][enquiry_rating]', 'Enquiry Rating',
                EnquiryRating::getAllAsArray(),
                $data['contracts']['enquiry_rating'] ?? '0');
            ?>

        </div>
    </div>


    <div class="card-header">
        <h3 class="card-title">Cabin Details </h3>
    </div>
    <div class="card-body pb-2">
        <div class="row row-sm">
            <div class="col-md-6">
                <div class="form-group">
                    <?php FormBuilder::buttonRadioButtons($data, TENANCIES);


                    function getBestAddress($addresses)
                    {
                        $best = 0;
                        foreach ($addresses as $k => $row) {
                            if (!empty($row['address_line1'])) {
                                $best = $k;
                            }
                        }
                        return $addresses[$best]['address'];
                    }

                    ?>
                    <div class='form-group'>
                        <label class='form-label' for='deliver-to'>Delivery Address</label>
                        <div class='input-group'>
                            <input class="form-control" id='deliver-to' name='data[contract][address]'
                                   placeholder="Delivery Address" type="text"
                                   autocomplete="chrome-off"
                                   value="<?= $data['Contract']['address']; ?>">
                            <span id='open-in-maps' class="input-group-text btn btn-info">Maps</span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-3">
                <?php
                FormBuilder::datePicker('scheduledDeliveryDate', 'data[contract][scheduled_delivery_date]', 'Scheduled Delivery Date', $data['Contract']['scheduled_delivery_date']);
                ?>

                <?php
                FormBuilder::select('time', 'data[contract][delivery_time]', 'Scheduled Delivery Time', DeliveryTimes::getSelectOptions($data['Contract']['delivery_time'] ?? '9:00'));
                FormBuilder::datePicker('deliveryDate', 'data[contract][delivery_date]', 'Actual Delivery Date', $data['Contract']['delivery_date']);
                FormBuilder::radio('data[contract][schedule_unit]', 'Payment Schedule', ScheduleUnit::getAllAsArray(), $data['Contract']['schedule_unit']);
                FormBuilder::radio('data[contract][text_reminder_invoice]', 'Send SMS reminder on rent day?',
                    YesNoDontKnow::getAllAsArray(),
                    $data['Contract']['text_reminder_invoice']);
                ?>

                <div class='form-group col-md-4'>
                    <input type="submit" value="Save" id='saveEnquiry' class="btn btn-lg btn-primary">
                </div>
            </div>
            <div class="col-md-3">
                <?php
                FormBuilder::radio('data[contract][cabin_type]',
                    'Cabin Type',
                    CabinStyle::getAllAsArray(),
                    $data['Contract']['cabin_type']
                );
                FormBuilder::radio('data[contract][cabin_use]',
                    'Cabin Use',
                    CabinUse::getAllAsArray(),
                    $data['Contract']['cabin_use']
                ); ?>


                <div class="form-group m-0">
                    <div class="form-label">Extras</div>
                    <div class="custom-controls-stacked">
                        <?php FormBuilder::radio('data[contract][hiab]', 'Hiab?',
                            YesNoDontKnow::getAllAsArray(),
                            $data['Contract']['hiab']);
                        FormBuilder::radio('data[contract][painted]', 'Painted?',
                            CabinPainted::getAllAsArray()
                            , $data['Contract']['painted']);
                        //FormBuilder::checkbox('hiab', 'data[contract][hiab]', 'Hiab?', 'hiab', $data['contracts']['hiab']);
                        //FormBuilder::checkbox('painted', 'data[contract][painted]', 'Painted?', 'painted', $data['contracts']['painted']);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
