<div class="card-header">
    <h3 class="card-title">Customer Details </h3>
</div>

<form action="/authorizedSave.php?action=10" method="get" class="needs-validation" novalidate>
    <?php

    FormBuilder::hidden('action', 'action', '10');
    /** @var Array $data */
    FormBuilder::hidden('id', 'data[contact][id]', $data['contacts']['id']);
    FormBuilder::hidden('contact_id', 'data[contact][contact_id]', $data['contacts']['contact_id']);
    FormBuilder::hidden('contact_status', 'data[contact][contact_status]', $data['contacts']['contact_status']);
    FormBuilder::hidden('contact_status', 'data[contact][contact_status]', $data['contacts']['contact_status']);

    FormBuilder::hidden('contract_id', 'data[contract][contract_id]', $data['contracts'][0]['contract_id']);
    FormBuilder::hidden('address_line1', 'data[contract][address_line1]', $data['contracts'][0]['address_line1']);
    FormBuilder::hidden('address_line2', 'data[contract][address_line2]', $data['contracts'][0]['address_line2']);
    FormBuilder::hidden('postal_code', 'data[contract][postal_code]', $data['contracts'][0]['postal_code']);
    FormBuilder::hidden('city', 'data[contract][city]', $data['contracts'][0]['city']);
    FormBuilder::hidden('lat', 'data[contract][lat]', $data['contracts'][0]['lat']);
    FormBuilder::hidden('long', 'data[contract][long]', $data['contracts'][0]['long']);
    FormBuilder::hidden('place_id', 'data[contract][place_id]', $data['contracts'][0]['place_id']);

    ?>

    <div class="row row-sm">
        <div class="col-md-6">

            <?php FormBuilder::inputs('Name', [
                ['id' => 'first_name', 'name' => 'data[contact][first_name]', 'type' => 'text', 'value' => $data['contacts']['first_name']],
                ['id' => 'last_name', 'name' => 'data[contact][last_name]', 'type' => 'text', 'value' => $data['contacts']['last_name']]
            ], true); ?>
            <?php

            foreach ($data['phones'] as $k => $row) {
                if (!empty($row['phone_number']) || $row['phone_type'] === 'MOBILE' || $row['phone_type'] === 'DEFAULT') {
                    $label = ($row['phone_type'] === 'DEFAULT') ? 'Phone' : ucfirst(strtolower($row['phone_type']));
                    FormBuilder::input('phone_type', 'data[phone][' . strtolower($row['phone_type']) . ']', $label, false, 'tel', $row['phone']);
                }
            }
            FormBuilder::input('email', 'data[contact][email]', 'Email', false, 'email', $data['contacts']['email_address']);
            FormBuilder::textarea('notes', 'data[notes][note]', 'Notes', ''); ?>

        </div>

        <div class="col-md-3">
            <?php
            FormBuilder::select('status', 'data[contract][status]', 'Status', FormBuilder::getStatusOptions(), $data['contracts'][0]['status']);
            ?>
            <div id="doyoumean"></div>
        </div>

        <div class="col-md-3">
            <?php FormBuilder::radio('data[contact][best_way_to_contact]', 'Best way to contact', [
                ['name' => 'phone', 'label' => 'Phone'],
                ['name' => 'email', 'label' => 'Email'],
                ['name' => 'text', 'label' => 'Text/SMS'],
                ['name' => 'nopref', 'label' => 'Whatever is easiest']
            ], $data['contacts']['best_way_to_contact']
            );
            FormBuilder::radio('data[contract][winz]', 'WINZ Form',
                [['name' => 'No'], ['name' => 'Requested'], ['name' => 'Sent']],
                $data['contracts'][0]['winz']
            );
            FormBuilder::radio('data[contact][how_did_you_hear]', 'How did you  hear about us?',
                [['name' => 'search', 'label' => 'Web Search'],
                    ['name' => 'facebook', 'label' => 'Facebook'],
                    ['name' => 'wom', 'label' => 'Word of Mouth'],
                    ['name' => 'other', 'label' => 'Other']],
                $data['contacts']['how_did_you_hear']);
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
                            <?php foreach(TENANCIES as $row):
                            //https://blog.jim-nielsen.com/2021/css-relative-colors/
                            $colour = "var(--bs-{$row['colour']})";
                            ?>
                            .selectgroup-input.<?=$row['shortname'];?>:checked + .selectgroup-button {
                                border-color: <?=$colour;?>;
                                color: <?=$colour;?>;
                                background-color: hsl(from <?=$colour; ?> h s l / .5);
                            }

                            <?php endforeach;?>
                        </style>
                        <?php
                        // tenancies should still be a variable from the sidebar
                        foreach (TENANCIES as $k => $row):
                            $checked = ($data['contacts']['xerotenant_id'] === $row['tenant_id'] ? ' checked="true" ' : '');
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
                               value="<?= getBestAddress($data['contracts']); ?>">
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
                               value='<?= $data['contracts'][0]['scheduled_delivery_date']; ?>'
                               type="text">

                    </div>
                </div>
                <?php FormBuilder::select('time', 'data[contract][delivery_time]', 'Scheduled Delivery Time', FormBuilder::getTimes(), $data['contracts'][0]['delivery_time']); ?>
                <div class='form-group col-md-4'>
                    <input type="submit" value="Save" class="btn btn-lg btn-primary">
                </div>
            </div>
            <div class="col-md-3">
                <?php FormBuilder::radio('data[contract][cabin_type]', 'Cabin Type', [
                    ['name' => 'std', 'label' => 'Standard'],
                    ['name' => 'std-left', 'label' => 'Standard, Left'],
                    ['name' => 'std-right', 'label' => 'Standard, Right'],
                    ['name' => 'large', 'label' => 'Large'],
                ],
                    $data['contracts'][0]['cabin_type']); ?>


                <div class="form-group m-0">
                    <div class="form-label">Extras</div>
                    <div class="custom-controls-stacked">
                        <?php FormBuilder::radio('data[contract][hiab]', 'Hiab?', [
                            ['name' => 'No'], ['name' => 'Yes']
                        ], $data['contracts'][0]['hiab']);
                        FormBuilder::radio('data[contract][painted]', 'Painted?', [
                            ['name' => '---', 'label' => 'No preference'],
                            ['name' => 'unpainted', 'label' => 'Unpainted'],
                            ['name' => 'painted', 'label' => 'Painted']
                        ], $data['contracts'][0]['painted']);
                        //FormBuilder::checkbox('hiab', 'data[contract][hiab]', 'Hiab?', 'hiab', $data['contracts']['hiab']);
                        //FormBuilder::checkbox('painted', 'data[contract][painted]', 'Painted?', 'painted', $data['contracts']['painted']);?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>