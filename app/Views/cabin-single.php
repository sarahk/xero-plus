<?php

namespace App\Views;

use App\Classes\ExtraFunctions;
use App\Classes\Utilities;
use App\Classes\ViewFunctions;

if (empty($data['cabins']['cabinnumber'])) $label = 'New Cabin';
else $label = 'Cabin #' . $data['cabins']['cabinnumber'];
//ExtraFunctions::debug($data);

?>
<script>
    window.cabin_id = <?=$data['cabins']['cabin_id'];?>;
    window.parent_type = 'cabins';
</script>
<div class='main-container container-fluid'>
    <div class="page-header">
        <div><h1 class="page-title"><?= $label; ?></h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/page.php?action=13">Cabins</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $label; ?></li>
            </ol>
        </div>
    </div>


    <div class="row">
        <div class="col-md-6">

            <?php
            $data['xerotenant_id'] = $data['cabins']['xerotenant_id'];
            $options = ['label' => 'The Basics', 'cardId' => 'cabinBasics', 'filename' => 'Widgets/cabin-single-basics.php'];
            echo ViewFunctions::getCard($options, $data);
            ?>

        </div>
        <div class="col-md-6">
            <?php
            $options = ['label' => 'Current Contract', 'cardId' => 'cabinContract', 'filename' => 'Widgets/cabin-current-contract.php'];
            echo ViewFunctions::getCard($options, $data);

            $tabs = [
                ['name' => 'notes', 'label' => 'Notes', 'filename' => '/cabin-notes.php'],
                ['name' => 'tasks', 'label' => 'Tasks', 'filename' => '/Widgets/cabin-tasks.php'],
                ['name' => 'contracts', 'label' => 'Contracts', 'filename' => '/Widgets/cabin-contracts.php'],
                ['name' => 'photos', 'label' => 'Photos', 'filename' => '/cabin-photos.php'],
            ];
            echo ViewFunctions::getTabs($tabs, 'notes', $data);
            ?>
        </div>
    </div>
</div>

