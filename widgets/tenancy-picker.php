<?php
//$tenancies = json_decode(file_get_contents('../json.php?endpoint=Organisations&action=list'));
//$t = json_decode(file_get_contents('https://cabinkingmanagement:8890/json.php?endpoint=Organisations&action=list'));
//


require_once 'JsonClass.php';
$json1 = new JsonClass($apiInstance, $xeroTenantId);


//echo $json1->getOrganisationList(true);
$tenancies = $json1->getOrganisationList(true);
?>

<style>
<?php foreach ($tenancies as $row): ?>
    .custom-checkbox .<?= $row['shortname']; ?>::before {
        border-radius: 3px;
        background-color: var(--bs-<?= $row['colour']; ?>) !important;
    }
<?php endforeach; ?>
</style>

<div class="form-group ml-4">
    <div class="form-label">Working With:</div>
    <div class="custom-controls-stacked">
        <?php foreach ($tenancies as $row):
            $disabled = ($row['disabled'] == 1) ? 'disabled' : '';
            $checked = ($row['active'] == 1) ? 'checked' : '';
            ?>
            <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input <?= $row['shortname']; ?>-input" id="tenancy-<?= $row['shortname']; ?>"
                       name="tenancy-<?= $row['shortname']; ?>" value="Y" <?= $checked; ?><?= $disabled; ?>>
                <span class="custom-control-label text-<?= $row['colour']; ?> <?= $row['shortname']; ?>"><b>
                        <?= $row['name']; ?>
                    </b></span>
            </label>
        <?php endforeach; ?>

    </div>
</div>