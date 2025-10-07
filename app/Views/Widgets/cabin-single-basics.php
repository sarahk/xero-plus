<?php

use App\Classes\ExtraFunctions;
use App\Models\Enums\CabinOwners;
use App\Models\Enums\CabinPainted;
use App\Models\Enums\CabinStatus;
use App\Models\Enums\CabinStyle;

?>

<dl class="row mb-0">
    <dt class="col-sm-4 text-muted">Number</dt>
    <dd class="col-sm-8 mb-2"><?= $data['cabins']['cabinnumber']; ?></dd>

    <dt class="col-sm-4 text-muted">Style</dt>
    <dd class="col-sm-8 mb-2"><?= CabinStyle::getLabel($data['cabins']['style']) ?></dd>

    <dt class="col-sm-4 text-muted">Status</dt>
    <dd class="col-sm-8 mb-2"><?= CabinStatus::getLabel($data['cabins']['status']) ?></dd>

    <dt class="col-sm-4 text-muted">Painted</dt>
    <dd class="col-sm-8 mb-2"><?= CabinPainted::getLabel($data['cabins']['paintinside']); ?></dd>

    <dt class="col-sm-4 text-muted">Region</dt>
    <dd class="col-sm-8 mb-2"><?= CabinOwners::getLabelPlus($data['cabins']['owner'], $data['xerotenant_id']) ?></dd>

    <dt class="col-sm-4 text-muted">Last Updated</dt>
    <dd class="col-sm-8 mb-3"><?= ExtraFunctions::toNormalDate($data['cabins']['updated']); ?></dd>

    <dt class="col-sm-4"></dt>
    <dd class="col-sm-8 text-end">
        <a href="#"
           data-bs-toggle="modal"
           data-bs-target="#cabinEditBasicsModal"
           data-key="<?= $data['cabins']['cabin_id']; ?>"
           class="btn btn-primary">
            <i class="fa-solid fa-pen me-2"></i>Edit
        </a>
    </dd>
</dl>

