<?php
/** @var array $newNote */
/** @var \App\Classes\Loader $loader */
?>
<div class="card custom-card" id="notesCard"
     data-foreign-id="<?= $newNote['foreign_id']; ?>"
     data-parent="<?= $newNote['parent'] ?? 'contact'; ?>"
     data-form-createdby="<?= $_SESSION['user_id']; ?>"
     data-form-createdby-name="<?= $_SESSION['user_name']; ?>"
     data-form-created="<?= date('Y-m-d H:i:s'); ?>">
    <div class="card-header">
        <div class="card-title">Notes</div>
    </div>
    <div class="card-body">
        <form id="notesCardForm">
            <table class="table table-bordered table-sm border-primary" id="notesCardTable">
                <thead>
                <tr>
                    <th>Note</th>
                    <th>Date</th>
                    <th>By</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="2"><label for="notesCardText">Add a Note</label><br/>

                        <textarea class="form-control" rows="3" data-gramm="false" name="data[note]"
                                  id="notesCardText"></textarea>


                    <td>&nbsp;<br/>
                        <button type="submit" class="btn btn-primary mt-2" form="notesCardForm"
                                id="notesCardSubmit">
                            Save
                        </button>
                    </td>
                </tr>

                </tbody>
            </table>
        </form>
    </div>
</div>

<script type="module">
    import {initNotesWidget} from '/JS/widgets/notesWidget.js';

    // Run once the DOM is ready
    window.addEventListener('DOMContentLoaded', () => {
        initNotesWidget();
    });
</script>