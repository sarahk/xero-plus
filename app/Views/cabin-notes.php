<div class="row">
    <div class="col-md-9">
        <div class="card-header">
            <h3 class="card-title">Notes</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="text-xl-right"><a href="emailservices.html" class="btn btn-primary">Add</a></div>
    </div>
</div>

<?php
if (count($data['notes'])) :
    ?>
    <div class="accordion" id="cabinNotesList">
        <?php
        foreach ($data['notes'] as $k => $note):
            $active = ($k === 0) ? ' active ' : '';
            $date = date('d M, y', strtotime($note['created']));
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $k; ?>">
                    <button class="accordion-button collapsed {$active}" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse<?= $k; ?>" aria-expanded="false"
                            aria-controls="collapse<?= $k; ?>"><?= $date . ' ' . substr($note['note'], 0, 100); ?>
                    </button>
                </h2>
                <div id="collapse<?= $k; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $k; ?>"
                     data-bs-parent="#cabinNotesList" style="">
                    <div class="accordion-body">
                        <p><strong><?= $date; ?></strong></p>
                        <p><?= $note['note']; ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach;
        ?>
    </div>
<?php endif;
