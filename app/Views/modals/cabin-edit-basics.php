<?php

use App\Models\Enums\CabinOwners;
use App\Models\Enums\CabinStatus;
use App\Models\Enums\CabinStyle;

?>
<div class='modal fade' id='cabinEditBasicsModal' tabindex='-1' role='dialog' aria-labelledby='cabinEditBasicsModal'
     aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class="modal-content">
            <form id="cabinEditForm">
                <div class='modal-header' id="modal-header">
                    <h4 class='modal-title' id='cabinSingleLabel'>Cabin <strong><span
                                    id="cabinNumberDisplay"></span></strong></h4>
                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <div class='modal-body'>
                    <div class="alert alert-danger d-none" id="formAlertCEB" role="alert" aria-live="polite"></div>
                    <div class="form-group">
                        <label for="cabinnumber" class="form-label">Number</label>
                        <input type="text" class="form-control" id="cabinnumber" aria-describedby="cabinnumberHelp"
                               placeholder="Enter the new cabin's number"
                               value="">
                    </div>
                    <div class="form-group">
                        <label for="cabinstyle" class="form-label">Style</label>
                        <select id='cabinstyle' name='cabinstyle' class="form-control"></select>
                    </div>

                    <div class="form-group">
                        <label for="cabinstatus" class="form-label">Status</label>
                        <select id='cabinstatus' name='cabinstatus' class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label for="xerotenant_id" class="form-label">Operator</label>
                        <select id='xerotenant_id' name='xerotenant_id' class="form-control"></select>
                    </div>

                    <div class="form-group">
                        <label for="owner" class="form-label">Owner (when not the operator)</label>
                        <select id='owner' name='owner' class="form-control"></select>
                    </div>

                    <div class="form-group">
                        <label for="lastupdated" class="form-label">Last Updated</label>
                        <input type="text" class="form-control" id="lastupdated" disabled="disabled">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>


            </form>
        </div>
    </div>
</div>
