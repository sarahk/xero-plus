<?php
//debug($data);
if (empty($data['cabins']['cabinnumber'])) $label = 'New Cabin';
else $label = 'Cabin #' . $data['cabins']['cabinnumber'];
debug($data);
echo '<pre>' . var_export($data) . '</pre>';
?>
<script>
    let cabin_id = <?=$data['cabins']['cabin_id'];?>;
</script>
<div class='main-container container-fluid'>
    <div class="page-header">
        <div><h1 class="page-title"><?= $label; ?></h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/authorizedResource.php?action=13">Cabins</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $label; ?></li>
            </ol>
        </div>
    </div>


    <div class="row">
        <div class="col-md-6">
            <form type='GET' id='modalForm'>
                <?php
                getCard('/Views/forms/cabin-edit-basics.php', 'Basics', $data);
                ?>
            </form>
        </div>
        <div class="col-md-6">
            <?php getCard('/Views/cabin-current-contract.php', 'Current Contract', $data); ?>
        </div>
        <div class="col-md-6">
            <?php
            $tabs = [
                ['name' => 'notes', 'label' => 'Notes', 'filename' => '/Views/cabin-notes.php'],
                ['name' => 'contracts', 'label' => 'Contracts', 'filename' => '/Views/cabin-contracts.php'],
                ['name' => 'photos', 'label' => 'Photos', 'filename' => '/Views/cabin-contracts.php'],
            ];
            getTabs($tabs, 'notes', $data);
            //getCard('/Views/cabin-contracts.php', 'Contracts', $data); ?>
        </div>
        <div class="col-md-6">
            <?php getCard('/Views/cabin-tasks.php', 'Tasks', $data); ?>
        </div>
    </div>

</div>
