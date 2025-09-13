<style>
    <?php
    foreach (json_decode(TENANCIES, true) as $row):
    //May '25, something changed in how Bootstrap defines its colours! grr!
    //$colour = "var(--bs-{$row['colour']})";
    $colour = "var(--{$row['colour']})";
    $class = ".{$row['shortname']}";
    echo "
    .custom-checkbox $class::before {
        border-radius: 3px;
        background-color: $colour !important;
    }
";
     endforeach; ?>
</style>

<div class="form-group ml-4">
    <div class="form-label">Working With:</div>
    <div class="custom-controls-stacked">
        <?php foreach (json_decode(TENANCIES, true) as $row):
            $disabled = ($row['disabled'] == 1) ? 'disabled' : '';
            $checked = ($row['active'] == 1) ? 'checked' : '';
            ?>
            <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input <?= $row['shortname']; ?>-input"
                       id="tenancy-<?= $row['shortname']; ?>"
                       name="tenancy-<?= $row['shortname']; ?>" value="Y" <?= $checked; ?><?= $disabled; ?>>
                <span class="custom-control-label text-<?= $row['colour']; ?> <?= $row['shortname']; ?>"><b>
                        <?= $row['name']; ?>
                    </b></span>
            </label>
        <?php endforeach; ?>

    </div>
</div>
