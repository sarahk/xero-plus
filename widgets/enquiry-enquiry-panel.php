<div class="card-header">
    <h3 class="card-title">Customer Details </h3>
</div>
<form action="/authorizedResource.php?action=10" method="post" class="needs-validation" novalidate>
    <?php
    FormBuilder::hidden('id');
    FormBuilder::hidden('contact_id');
    FormBuilder::hidden('contact_status');
    ?>
    <div class="row row-sm">
        <div class="col-md-6">

            <?php FormBuilder::inputs('Name', [
                ['name' => 'first_name', 'type' => 'text', 'value' => ''],
                ['name' => 'last_name', 'type' => 'text', 'value' => '']
            ], true); ?>
            <?php FormBuilder::input('phone', 'Phone', true, 'tel'); ?>
            <?php FormBuilder::input('email', 'Email', false, 'email'); ?>
            <?php FormBuilder::textarea('notes', 'Notes'); ?>

        </div>
        <div class="col-md-3">
            <?php
            FormBuilder::select('status', 'Status', FormBuilder::getStatusOptions());
            ?>
            <div id="doyoumean"></div>


            <div class=''>
                <?= date('Y-m-d H:i'); ?>
            </div>
        </div>
        <div class="col-md-3">
            <?php FormBuilder::radio('bwtc', 'Best way to contact', [
                ['name' => 'phone', 'label' => 'Phone'],
                ['name' => 'email', 'label' => 'Email'],
                ['name' => 'text', 'label' => 'Text/SMS'],
                ['name' => 'nopref', 'label' => 'Whatever is easiest']
            ]);
            FormBuilder::radio('winz', 'WINZ Form',
                [['name' => 'No'], ['name' => 'Requested'], ['name' => 'Sent']],
                'No'
            );
            FormBuilder::radio('hdyh', 'How did you  hear about us?',
                [['name' => 'search', 'label' => 'Web Search'],
                    ['name' => 'facebook', 'label' => 'Facebook'],
                    ['name' => 'wom', 'label' => 'Word of Mouth'],
                    ['name' => 'other', 'label' => 'Other']],
                '');
            ?>

        </div>
    </div>


    <div class="card-header">
        <h3 class="card-title">Cabin Details </h3>
    </div>
    <div class="card-body pb-2">
        <div class="row row-sm">
            <div class="col-md-6">
                <div class='form-group'>
                    <label class='form-label' for='name'>Delivery Address</label>
                    <div class='input-group'>
                        <input class="form-control" id='name' name='data[address]'
                               placeholder="Delivery Address" type="text">
                        <span id='open-in-maps' class="input-group-text btn btn-warning">Maps</span>
                    </div>
                </div>

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
                        <span id='mark-as-delivered'
                              class="input-group-text btn btn-warning">Delivered</span>
                    </div>
                </div>
                <?php FormBuilder::select('time', 'Scheduled Delivery Time', FormBuilder::getTimes()); ?>
                <div class='form-group col-md-4'>
                    <input type="submit" value="Save" class="btn btn-lg btn-primary">
                </div>
            </div>
            <div class="col-md-6">
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