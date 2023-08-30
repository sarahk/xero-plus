<?php
include_once 'widgets/FormBuilder.php';
?>
<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Enquiry</h1>
        <!--        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
            <li class="breadcrumb-item active" aria-current="page">Form-Elements</li>
        </ol>-->
    </div>

</div>
<!-- PAGE-HEADER END -->

<!-- ROW OPEN -->
<div class="row">
    <div class="col-lg-12 col-md-12">
        <form action="/authorizedResource.php?action=10" method="post">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Details </h3>
                </div>
                <div class="card-body pb-2">
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
                            <div class='form-group'>
                                <label class=" form-label" for='status'>Status</label>

                                <select class="form-control" id='status' name='data[status]'
                                        data-bs-placeholder="Choose One" tabindex="-1">
                                    <option label="Choose one"></option>
                                    <option value='New'>New</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                    <option value="Call">Call Back</option>

                                </select>

                            </div>
                            <div class="form-group ">
                                <div class="form-label">Winz Form</div>
                                <div class="custom-controls-stacked">
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[winz]" value="No"
                                               checked="">
                                        <span class="custom-control-label">No</span>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[winz]"
                                               value="Requested">
                                        <span class="custom-control-label">Requested</span>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[winz]" value="Sent">
                                        <span class="custom-control-label">Sent</span>
                                    </label>

                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="form-label">How did they hear about us?</div>
                                <div class="custom-controls-stacked">
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[hdyh]"
                                               value="search"
                                               checked="">
                                        <span class="custom-control-label">Search</span>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[hdyh]"
                                               value="facebook">
                                        <span class="custom-control-label">Facebook</span>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[hdyh]" value="WoM">
                                        <span class="custom-control-label">Word of Mouth</span>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="data[hdyh]"
                                               value="other">
                                        <span class="custom-control-label">Other</span>
                                    </label>
                                </div>
                            </div>
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
                            ]); ?>

                        </div>
                    </div>

                </div>
            </div>
            <div class="card">
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
                                <label class='form-label' for='delivery-date'>Requested Delivery Date</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="fa fa-calendar tx-16 lh-0 op-6"></span>
                                    </div>
                                    <input class="form-control hasDatepicker" id="delivery-date"
                                           name='data[delivery-date]' placeholder="DD/MM/YYYY" type="text">
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='form-label' for='actual-delivery-date'>Scheduled Delivery Date</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <span class="fa fa-calendar tx-16 lh-0 op-6"></span>
                                    </div>
                                    <input class="form-control hasDatepicker" id="actual-delivery-date"
                                           name='data[actual-delivery-date]' placeholder="DD/MM/YYYY" type="text">
                                    <span id='mark-as-delivered'
                                          class="input-group-text btn btn-warning">Delivered</span>
                                </div>
                            </div>
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
                                        <input type="checkbox" class="custom-control-input" name="data[hiab]"
                                               value="hiab"
                                               checked="">
                                        <span class="custom-control-label">Hiab</span>
                                    </label>
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="data[painted]"
                                               value="painted">
                                        <span class="custom-control-label">Painted Inside</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END ROW -->