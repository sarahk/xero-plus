<?php

namespace App\Views;

use App\ExtraFunctions;

?>
    <div class="row">
        <form id="addNote">
            <div class="form-group ">
                <label class="form-label" for="notes">New Note</label>
                <textarea class="form-control" id="notes" name="data[note][note]" placeholder="Notes" rows="3"
                          data-gramm="false" spellcheck="false"></textarea>
            </div>

            <div class=" text-xl-right">
                <a href="#" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add</a>
            </div>
        </form>
    </div>
    <?php


if (count($data['Note'])) :
    ?>
    <div class="accordion" id="cabinNotesList">
        <?php
        foreach ($data['Note'] as $k => $note) {
            $active = ($k === 0) ? ' active ' : '';
            $date = date('d M, y', strtotime($note['created']));
            $label = $date . ' ' . substr($note['note'], 0, 100);
            $body = "<p><strong>$date</strong></p>
                    <p>{$note['note']}</p>";
            ExtraFunctions::getAccordionItem($k, 'cabinNotesList', $label, $body);
        }
        ?>
    </div>
<?php endif;
