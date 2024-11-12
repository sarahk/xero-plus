<?php
namespace App\Views\Widgets;
?>
<div class="card custom-card" id="notesCard">
    <div class="card-header">
        <div class="card-title">Notes</div>
    </div>
    <div class="card-body">
        <table class="table table-bordered border-primary" id="notesCardTable">
            <thead>
            <tr>
                <th>Note</th>
                <th>Date</th>
                <th>By</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="2">Add a Note<br/>
                    <form id="notesCardForm">
                        <textarea class="form-control" rows="3" data-gramm="false" id="notesCardText"></textarea>
                        <input type="hidden" name="parent" id="notesFormParent" value="<?= $newNote['parent']; ?>">
                        <input type="hidden" name="foreign_id" id="notesFormForeignid"
                               value="<?= $newNote['foreign_id']; ?>">
                        <input type="hidden" name="createdby" id="notesFormCreatedby"
                               value="<?= $_SESSION['user_id']; ?>">
                        <input type="hidden" name="createdbyanme" id="notesFormCreatedbyname"
                               value="<?= $_SESSION['user_name']; ?>">
                        <input type="hidden" name="created" id="notesFormCreated" value="<?= date('Y-m-d H:i:s'); ?>"
                    </form>

                <td><?= $_SESSION['user_name']; ?><br/>
                    <button type="submit" class="btn btn-primary mt-2" form="notesCardForm" id="notesCardSubmit">Save
                    </button>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
</div>
