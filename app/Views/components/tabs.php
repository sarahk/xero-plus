<?php
/** @var string|null $tabs */
/** @var string|null $tab_body */
?>
<div class="card px-0">
    <div class="card-body">

        <ul class="nav nav-tabs" id="cabinTabs" role="tablist">
            <?= $tabs; ?>
        </ul>

        <div class="tab-content" id="cabinTabsContent">
            <?= $tab_body; ?>
        </div>
    </div>
</div>