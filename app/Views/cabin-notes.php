<?php

use App\ExtraFunctions;

?>
<div class="row mb-2">
    <form id="addNote">
        <input type="hidden" name="action" value="<?= $_GET['action'] ?>">
        <input type="hidden" name="formType" value="notes">
        <input type="hidden" name="data[note][foreign_id]" value="">
        <input type="hidden" name="data[note][parent]" value="cabins">
        <label class="form-label" for="notes">New Note</label>
        <textarea class="form-control" id="notes" name="data[note][note]" rows="3"></textarea>

        <div class="text-xl-right">

            <button type="submit" class="btn btn-primary" data-role="submit">
                <i class="fa-solid fa-plus me-2"></i>Add
            </button>

        </div>
    </form>
</div>
<script type="module">
    import {NotesTabWidget} from '/JS/Widgets/notesTabWidget.js';

    NotesTabWidget.init('#addNote');
</script>
<div class="row">
    <div class="accordion" id="cabinNotesList">
    </div>
</div>
