<?php

namespace App\Views\forms;

use App\Models\Enums\CabinOwners;
use App\Models\Enums\CabinStatus;
use App\Models\Enums\CabinStyle;

?>
<div class='modal fade' id='cabinSingleModal' tabindex='-1' role='dialog' aria-labelledby='cabinSingleLabel'
     aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header' id="modal-header">
                <h4 class='modal-title' id='cabinSingleLabel'>Cabin <strong><span id="cabinId"></span></strong></h4>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <div class='modal-body'>
                <div class='container'>
                    <div class="form-group">
                        <label for="cabinnumber">Number</label>
                        <input type="text" class="form-control" id="cabinnumber" aria-describedby="cabinnumberHelp"
                               placeholder="Enter the new cabin's number"
                               value="<?= $data['cabins']['cabinnumber']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="cabinstyle">Style</label>
                        <select id='cabinstyle' name='cabinstyle' class="form-control">
                            <?php echo CabinStyle::getSelectOptions($data['cabins']['style']) ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cabinstatus">Status</label>
                        <select id='cabinstatus' name='cabinstatus' class="form-control">
                            <?php echo CabinStatus::getSelectOptions($data['cabins']['status']) ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="xerotenant_id">Operator</label>
                        <select id='xerotenant_id' name='xerotenant_id' class="form-control">
                            <?php foreach (json_decode(TENANCIES, true) as $row) {
                                echo "<option value='{$row['tenant_id']}'>{$row['name']}</option>";
                            } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="xerotenant_id">Owner (when not the operator)</label>
                        <select id='xerotenant_id' name='xerotenant_id' class="form-control">
                            <?php echo CabinOwners::getSelectOptions($data['cabins']['owner']) ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lastupdated">Last Updated</label>
                        <input type="text" class="form-control" id="lastupdated" disabled="disabled">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
