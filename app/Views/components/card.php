<?php
/** @var string|null $cardId */
/** @var string|null $label */
/** @var string|null $bodyHTML */
/** @var string|null $class */
?>
<div class="card" id="<?= $cardId; ?>">
    <div class="card-header <?= $class ?>">
        <h3 class="card-title"><?= $label; ?><span class="cardHeaderExtra"></span></h3>
    </div>
    <div class="card-body">
        <?php echo $bodyHTML; ?>
    </div>
</div>