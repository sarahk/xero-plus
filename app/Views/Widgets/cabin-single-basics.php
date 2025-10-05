<?php

use App\Classes\ExtraFunctions;
use App\Models\Enums\CabinOwners;
use App\Models\Enums\CabinPainted;
use App\Models\Enums\CabinStatus;
use App\Models\Enums\CabinStyle;

?>
<table class="table">
    <tr>
        <td>Number</td>
        <td><?= $data['cabins']['cabinnumber']; ?></td>
    </tr>
    <tr>
        <td>Style</td>
        <td><?php echo CabinStyle::getLabel($data['cabins']['style']) ?></td>
    </tr>
    <tr>
        <td>Status</td>
        <td><?= CabinStatus::getLabel($data['cabins']['status']) ?></td>
    </tr>
    <tr>
        <td>Painted</td>
        <td><?= CabinPainted::getLabel($data['cabins']['paintinside']); ?></td>
    </tr>
    <tr>
        <td>Region</td>
        <td><?php echo CabinOwners::getLabelPlus($data['cabins']['owner'], $data['xerotenant_id']) ?></td>
    </tr>

    <tr>
        <td>Last Updated</td>
        <td><?= ExtraFunctions::toNormalDate($data['cabins']['updated']); ?></td>
    </tr>
    <tr>
        <td colspan="2" class=" text-end"><a href="#" data-bs-toggle='modal' data-bs-target='#cabinEditBasicsModal'
                                             data-key='<?= $data['cabins']['cabin_id']; ?>'
                                             class="btn btn-primary"><i class="fa-solid fa-pen me-2"></i>Edit</a></td>
    </tr>
</table>
