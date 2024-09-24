<!-- Modal -->
<style>
    <?php
    foreach (json_decode(TENANCIES, true) as $row):
    $colour = "var(--bs-{$row['colour']})";
    $class = ".{$row['shortname']}";
    echo "
    .modal-header$class {
        border-bottom: 4px solid $colour;
    }
";
     endforeach; ?>
</style>
<div class='modal fade' id='taskSingle' tabindex='-1' role='dialog' aria-labelledby='taskSingleLabel'
     aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header' id="modal-header">
                <h4 class='modal-title' id='taskSingleLabel'>Task <strong><span id="taskId"></span></strong></h4>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <div class='modal-body'>
                <div class='container'>
                    <ul class="list-group" id="taskdetails">
                    </ul>
                </div>

            </div>
            <div class='modal-footer'>
                <div class="spinner-grow text-warning" role="status" id='modalSpinnerTask'
                     style='display: none; font-size: 2em;padding: 1em;'>
                    <span class="sr-only">Loading...</span>
                </div>

                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                <button type='button' class='btn btn-primary' id='cabinedit'>Edit</button>
            </div>
        </div>
    </div>
</div>
<!-- /Modal -->
