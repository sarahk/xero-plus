<?php
namespace App\Views\Widgets;
?>
<div class="card custom-card" id="notesCard">
    <div class="card-header">
        <div class="card-title">Notes</div>
    </div>
    <div class="card-body">
        <table class="table table-bordered border-primary" id="notesCardTable">
            <tr>
                <th>Note</th>
                <th>Date</th>
                <th>By</th>
            </tr>
            <tr>
                <td colspan="2">Add a Note<br/>
                    <form>
                        <textarea class="form-control" rows="3" data-gramm="false"></textarea>
                        <input type="hidden" name="parent" value="<?= $newNote['parent']; ?>">
                        <input type="hidden" name="foreign_id" value="<?= $newNote['foreign_id']; ?>">
                        <input type="hidden" name="createdby" value="<?= $_SESSION['user_id']; ?>"
                        <input type="hidden" name="created" value="<?= date('Y-m-d H:i:s'); ?>"
                    </form>

                <td><?= $_SESSION['user_name']; ?><br/>
                    <button type="submit" class="btn btn-primary mt-2">Save</button>
                </td>
            </tr>


        </table>
    </div>
</div>
