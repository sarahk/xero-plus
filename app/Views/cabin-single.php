<?php

namespace App\Views;

use App\ExtraFunctions;
use App\Utilities;

if (empty($data['cabins']['cabinnumber'])) $label = 'New Cabin';
else $label = 'Cabin #' . $data['cabins']['cabinnumber'];
//ExtraFunctions::debug($data);

?>
    <script>
        let cabin_id = <?=$data['cabins']['cabin_id'];?>;
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
                ExtraFunctions::getCard('/Views/widgets/cabin-single-basics.php', 'Basics', 'cabinBasics', $data);
                ?>

            </div>
            <div class="col-md-6">
                <?php ExtraFunctions::getCard('/Views/cabin-current-contract.php', 'Current Contract', 'cabinContract', $data); ?>

                <?php
                $tabs = [
                    ['name' => 'notes', 'label' => 'Notes', 'filename' => '/Views/cabin-notes.php'],
                    ['name' => 'tasks', 'label' => 'Tasks', 'filename' => '/Views/cabin-tasks.php'],
                    ['name' => 'contracts', 'label' => 'Contracts', 'filename' => '/Views/cabin-contracts.php'],
                    ['name' => 'photos', 'label' => 'Photos', 'filename' => '/Views/cabin-photos.php'],
                ];
                ExtraFunctions::getTabs($tabs, 'notes', $data);
                //getCard('/Views/cabin-contracts.php', 'Contracts', $data);
                ?>
            </div>

            <?php //ExtraFunctions::getCard('/Views/cabin-tasks.php', 'Tasks', 'cabinTasks', $data);
            ?>

        </div>
    </div>
    <?php var_dump($data);
