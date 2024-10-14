<div class="card-header">
    <h3 class="card-title">Customer Details </h3>
</div>

<form action="/authorizedSave.php?action=10" method="get" class="needs-validation" novalidate>
    <?php

    FormBuilder::hidden('action', 'action', '10');
    /** @var Array $data */
    FormBuilder::hidden('id', 'data[contact][id]', $data['contacts']['id'] ?? '');
    FormBuilder::hidden('contact_id', 'data[contact][contact_id]', $data['contacts']['contact_id'] ?? '');
    FormBuilder::hidden('contact_status', 'data[contact][contact_status]', $data['contacts']['contact_status'] ?? '');
    //FormBuilder::hidden('contact_status', 'data[contact][contact_status]', $data['contacts']['contact_status']);

    FormBuilder::hidden('contract_id', 'data[contract][contract_id]', $data['Contract'][0]['contract_id']);
    FormBuilder::hidden('address_line1', 'data[contract][address_line1]', $data['Contract'][0]['address_line1']);
    FormBuilder::hidden('address_line2', 'data[contract][address_line2]', $data['Contract'][0]['address_line2']);
    FormBuilder::hidden('postal_code', 'data[contract][postal_code]', $data['Contract'][0]['postal_code']);
    FormBuilder::hidden('city', 'data[contract][city]', $data['Contract'][0]['city']);
    FormBuilder::hidden('lat', 'data[contract][lat]', $data['Contract'][0]['lat']);
    FormBuilder::hidden('long', 'data[contract][long]', $data['Contract'][0]['long']);
    FormBuilder::hidden('place_id', 'data[contract][place_id]', $data['Contract'][0]['place_id']);


    FormBuilder::hidden('note_foreign_id', 'data[note][foreign_id]', $data['Note'][0]['foreign_id'] ?? '');
    FormBuilder::hidden('note_parent', 'data[note][parent]', 'contacts');
    FormBuilder::hidden('note_createdby', 'data[note][createdby]', $_SESSION['user_id']);
    ?>

    <div class="row row-sm">
        <div class="col-md-6">

            <?php FormBuilder::inputs('Name', [
                ['id' => 'first_name', 'name' => 'data[contact][first_name]', 'type' => 'text', 'value' => $data['contacts']['first_name'] ?? FormBuilder::splitName('first', $data['contacts']['name'])],
                ['id' => 'last_name', 'name' => 'data[contact][last_name]', 'type' => 'text', 'value' => $data['contacts']['last_name'] ?? FormBuilder::splitName('last', $data['contacts']['name'])]
            ], true); ?>
            <?php

            foreach ($data['Phone'] as $k => $row) {
                if (!empty($row['phone_number']) || $row['phone_type'] === 'MOBILE' || $row['phone_type'] === 'DEFAULT') {
                    $label = ($row['phone_type'] === 'DEFAULT') ? 'Phone' : ucfirst(strtolower($row['phone_type']));
                    FormBuilder::input('phone_type', "data[phone][$k][" . strtolower($row['phone_type']) . ']', $label, false, 'tel', $row['phone']);
                }
            }
            FormBuilder::input('email_address', 'data[contact][email_address]', 'Email', false, 'email', $data['contacts']['email_address'] ?? '');
            FormBuilder::textarea('notes', 'data[note][note]', 'Notes'); ?>

        </div>

        <div class="col-md-3">
            <?php
            FormBuilder::select('status', 'data[contract][status]',
                'Status',
                App\Models\Enums\EnquiryStatus::getSelectOptions($data['Contract'][0]['status']),
                $data['Contract'][0]['status']);
            ?>
            <div id="doyoumean"></div>
        </div>

        <div class="col-md-3">
            <?php FormBuilder::radio('data[contact][best_way_to_contact]',
                'Best way to contact',
                App\Models\Enums\BestWayToContact::getAllAsArray(),
                $data['contacts']['best_way_to_contact'] ?? ''
            );
            FormBuilder::radio('data[contract][winz]', 'WINZ Form',
                \App\Models\Enums\WinzStatus::getAllAsArray(),
                $data['Contract'][0]['winz']
            );
            FormBuilder::radio('data[contact][how_did_you_hear]', 'How did you  hear about us?',
                App\Models\Enums\HowDidYouHear::getAllAsArray(),
                $data['contacts']['how_did_you_hear'] ?? '');
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
                    <label class="form-label" for="xerotenant_id">
                        Region
                    </label>
                    <div class="selectgroup selectgroup-pills">
                        <style>
                            <?php
                            foreach(json_decode(TENANCIES, true) as $row):
                            //https://blog.jim-nielsen.com/2021/css-relative-colors/
                            $colour = "var(--bs-{$row['colour']})";

                            echo " .selectgroup-input.{$row['shortname']}:checked + .selectgroup-button {
                                border-color: $colour;
                                color: $colour;
                                background-color: hsl(from $colour; h s l / .5); \n";
                             endforeach;
                             ?>
                        </style>
                        <?php
                        // tenancies should still be a variable from the sidebar
                        $xerotenant_id = (array_key_exists('xerotenant_id', $data['contacts'])) ? $data['contacts']['xerotenant_id'] : '';
                        foreach (json_decode(TENANCIES, true) as $k => $row):
                            $checked = ($xerotenant_id === $row['tenant_id'] ? ' checked="true" ' : '');
                            ?>
                            <label class="selectgroup-item">
                                <input type="radio" name="data[contact][xerotenant_id]"
                                       value="<?= $row['tenant_id']; ?>"
                                       class="selectgroup-input <?= $row['shortname']; ?>" <?= $checked; ?>>
                                <span class="selectgroup-button"
                                      id="xerotenant_id<?= $k; ?>"><b><?= $row['name']; ?></b></span>
                            </label>
                        <?php endforeach; ?>


                    </div>

                </div>
                <?php
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
                               value="<?= getBestAddress($data['Contract']); ?>">
                        <span id='open-in-maps' class="input-group-text btn btn-info">Maps</span>
                    </div>
                </div>


            </div>
            <div class="col-md-3">
                <div class='form-group'>
                    <label class='form-label' for='delivery-date'>Requested Delivery
                        Date</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="fa fa-calendar tx-16 lh-0 op-6"></span>
                        </div>
                        <input class="form-control hasDatepicker" id="delivery-date"
                               name='data[contract][delivery_date]' placeholder="DD/MM/YYYY"
                               type="text">
                    </div>
                </div>
                <div class='form-group'>
                    <label class='form-label' for='scheduled-delivery-date'>Scheduled Delivery
                        Date</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="fa fa-calendar tx-16 lh-0 op-6"></span>
                        </div>
                        <input class="form-control hasDatepicker" id="scheduled-delivery-date"
                               name='data[contract][scheduled_delivery_date]' placeholder="DD/MM/YYYY"
                               value='<?= $data['Contract'][0]['scheduled_delivery_date']; ?>'
                               type="text">

                    </div>
                </div>
                <?php FormBuilder::select('time', 'data[contract][delivery_time]', 'Scheduled Delivery Time', App\Models\Enums\DeliveryTimes::getSelectOptions($data['Contract'][0]['delivery_time'] ?? '9:00')); ?>
                <div class='form-group col-md-4'>
                    <input type="submit" value="Save" class="btn btn-lg btn-primary">
                </div>
            </div>
            <div class="col-md-3">
                <?php FormBuilder::radio('data[contract][cabin_type]',
                    'Cabin Type',
                    App\Models\Enums\CabinStyle::getAllAsArray($data['Contract'][0]['cabin_type'])
                ); ?>


                <div class="form-group m-0">
                    <div class="form-label">Extras</div>
                    <div class="custom-controls-stacked">
                        <?php FormBuilder::radio('data[contract][hiab]', 'Hiab?',
                            App\Models\Enums\YesNoDontKnow::getAllAsArray(),
                            $data['Contract'][0]['hiab']);
                        FormBuilder::radio('data[contract][painted]', 'Painted?',
                            App\Models\Enums\CabinPainted::getAllAsArray()
                            , $data['Contract'][0]['painted']);
                        //FormBuilder::checkbox('hiab', 'data[contract][hiab]', 'Hiab?', 'hiab', $data['contracts']['hiab']);
                        //FormBuilder::checkbox('painted', 'data[contract][painted]', 'Painted?', 'painted', $data['contracts']['painted']);?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
