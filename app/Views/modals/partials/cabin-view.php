<div class='row mb-0 px-4' id="cabindetails">
    <dt class="col-sm-4 text-muted">Number</dt>
    <dd class="col-sm-8 mb-2" id="cabinNumber"></dd>

    <dt class="col-sm-4 text-muted">Style</dt>
    <dd class="col-sm-8 mb-2" id="cabinStyle"></dd>

    <dt class="col-sm-4 text-muted">Status</dt>
    <dd class="col-sm-8 mb-2" id="cabinStatus"></dd>

    <dt class="col-sm-4 text-muted">Painted Inside</dt>
    <dd class="col-sm-8 mb-2" id="paintinside"></dd>

    <dt class="col-sm-4 text-muted">Electrical WOF</dt>
    <dd class="col-sm-8 mb-2" id="cabinWof"></dd>

    <dt class="col-sm-4 text-muted">Owner</dt>
    <dd class="col-sm-8 mb-2" id="cabinOwner"></dd>

    <dt class="col-sm-4 text-muted">Last Updated</dt>
    <dd class="col-sm-8 mb-3" id="updated"></dd>

</div>
<div class='container mt-3'>
    <p class="card-title"><i class="fa-solid fa-list"></i> Current Tasks</p>
    <div class="table-responsive">
        <table id="cabintasks" class='table table-striped table-bordered table-sm'>
            <thead>
            <th>Task</th>
            <th>Status</th>
            <th>Due</th>
            <th>Mark as</th>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
</div>
<div class='modal-footer'>
    <div class="spinner-grow text-warning" role="status" id='modalSpinnerCabin'
         style='display: none; font-size: 2em;padding: 1em;'>
        <span class="sr-only">Loading...</span>
    </div>

    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#cabinEditBasicsModal" class='btn btn-primary'
            data-key='' id='btnCabinEdit'>Edit
    </button>
    <button type='button' class='btn btn-info' id='addtask'>Add Task</button>
</div>

