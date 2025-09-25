<?php
?>
<div class="row mb-3">
    <div class="table-responsive px-0">
        <table class='table table-striped table-bordered' id='tCabinTasks' style='width:100%'>
            <thead>
            <tr>
                <th class="th-sm"></th>
                <th class="th-sm"></th>
                <th class="th-sm"></th>
                <th>Name</th>
                <th>Due Date</th>
                <th></th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<div class="row mb-3">
    <div class="text-xl-right">
        <button type="button" class="btn btn-primary"
                data-bs-toggle="modal" data-bs-target="#cabinTaskEditModal"
                data-key="">
            <i class="fa-solid fa-plus me-2"></i>Add
        </button>
    </div>
</div>
<script type="module">
    import {CabinTasksTable} from '/JS/DataTables/cabinTasks.js';

    CabinTasksTable.init({
        table: '#tCabinTasks',
        badge: '#tab-tasksBadge',
        cabin: String(window.cabin_id ?? ''),
    });
</script>