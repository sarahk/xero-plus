<div class="card-header">
    <h3 class="card-title">Customer Details </h3>
</div>

<form action="/authorizedSave.php?action=10" method="get" class="needs-validation" novalidate>
    <?php
    FormBuilder::hidden('id');
    FormBuilder::hidden('contact_id');
    FormBuilder::hidden('contact_status');

    ?>
    <input  type="hidden" id="address_line1" name="data[contract][address_line1]" />
    <input type="hidden" id="address_line2"  name="data[contract][address_line2]"/>
    <input type="hidden" id="postal_code" name="data[contract][postal_code]" />
    <input type="hidden" id="city" name="data[contract][city]">
    <input type="hidden" id="lat" name="data[contract][lat]">
    <input type="hidden" id="long" name="data[contract][long]">
    <input type="hidden" id="place_id" name="data[contract][place_id]">

    <div class="row row-sm">
        <div class="col-md-6">

            <?php FormBuilder::inputs('Name', [
                ['name' => 'first_name', 'type' => 'text', 'value' => $data['contacts']['first_name']],
                ['name' => 'last_name', 'type' => 'text', 'value' => $data['contacts']['last_name']]
            ], true); ?>
            <?php

            foreach ($data['phones'] as $k => $row) {
                if (!empty($row['phone_number']) || $row['phone_type'] === 'MOBILE'||$row['phone_type'] === 'DEFAULT') {
                    $label = ($row['phone_type'] === 'DEFAULT')?'Phone':ucfirst(strtolower($row['phone_type']));
                    FormBuilder::input(strtolower($row['phone_type']), $label, false, 'tel', $row['phone']);
                }
            }
            FormBuilder::input('email', 'Email', false, 'email', $data['contacts']['email_address']);
            FormBuilder::textarea('notes', 'Notes'); ?>

        </div>

        <div class="col-md-3">
            <?php
            FormBuilder::select('status', 'Status', FormBuilder::getStatusOptions());
            ?>
            <div id="doyoumean"></div>
        </div>

        <div class="col-md-3">
            <?php FormBuilder::radio('best_way_to_contact', 'Best way to contact', [
                ['name' => 'phone', 'label' => 'Phone'],
                ['name' => 'email', 'label' => 'Email'],
                ['name' => 'text', 'label' => 'Text/SMS'],
                ['name' => 'nopref', 'label' => 'Whatever is easiest']
            ], $data['contacts']['best_way_to_contact']
            );
            FormBuilder::radio('winz_form', 'WINZ Form',
                [['name' => 'No'], ['name' => 'Requested'], ['name' => 'Sent']],
                $data['contacts']['winz_form']
            );
            FormBuilder::radio('how_did_you_hear', 'How did you  hear about us?',
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
                                <input type="radio" name="data[xerotenant_id]"
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
                    <label class='form-label' for='name'>Delivery Address</label>
                    <div class='input-group'>
                        <input class="form-control" id='deliver-to' name='data[address]'
                               placeholder="Delivery Address" type="text"
                               xautocomplete="chrome-off"
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
                               name='data[delivery-date]' placeholder="DD/MM/YYYY"
                               type="text">
                    </div>
                </div>
                <div class='form-group'>
                    <label class='form-label' for='actual-delivery-date'>Scheduled Delivery
                        Date</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <span class="fa fa-calendar tx-16 lh-0 op-6"></span>
                        </div>
                        <input class="form-control hasDatepicker" id="actual-delivery-date"
                               name='data[actual-delivery-date]' placeholder="DD/MM/YYYY"
                               type="text">

                    </div>
                </div>
                <?php FormBuilder::select('time', 'Scheduled Delivery Time', FormBuilder::getTimes()); ?>
                <div class='form-group col-md-4'>
                    <input type="submit" value="Save" class="btn btn-lg btn-primary">
                </div>
            </div>
            <div class="col-md-3">
                <?php FormBuilder::radio('cabin', 'Cabin Type', [
                    ['name' => 'std', 'label' => 'Standard'],
                    ['name' => 'std-left', 'label' => 'Standard, Left'],
                    ['name' => 'std-right', 'label' => 'Standard, Right'],
                    ['name' => 'large', 'label' => 'Large'],
                ]); ?>


                <div class="form-group m-0">
                    <div class="form-label">Extras</div>
                    <div class="custom-controls-stacked">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input"
                                   name="data[hiab]"
                                   value="hiab"
                                   checked="">
                            <span class="custom-control-label">Hiab</span>
                        </label>
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input"
                                   name="data[painted]"
                                   value="painted">
                            <span class="custom-control-label">Painted Inside</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>