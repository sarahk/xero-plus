<?php

namespace App\Views\Widgets;

use App\ExtraFunctions;
use App\Models\Enums\CabinOwners;
use App\Models\Enums\CabinPainted;
use App\Models\Enums\CabinStatus;
use App\Models\Enums\CabinStyle;

$tenancies = json_decode(TENANCIES, true);
$tenantsById = array_column($tenancies, null, 'tenant_id');
$operator = $tenantsById[$data['cabins']['xerotenant_id']]['name'];

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
        <td>Operator</td>
        <td><?= $operator; ?></td>
    </tr>
    <tr>
        <td>Owner</td>
        <td><?php echo CabinOwners::getLabel($data['cabins']['owner']) ?></td>
    </tr>
    <tr>
        <td>Last Updated</td>
        <td><?= ExtraFunctions::toNormalDate($data['cabins']['updated']); ?></td>
    </tr>
    <tr>
        <td colspan="2" class=" text-xl-right"><a href="#" class="btn btn-primary">Edit</a></td>
    </tr>
</table>
