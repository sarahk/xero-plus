<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card h-100">
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title mb-0"><i class="fa-solid fa-list"></i> Tasks</h5>
            <a class="btn btn-sm btn-primary ms-auto" href="/page.php?action=300">See All</a>
        </div>
        <div class="card-body">


            <div class="text-center mb-3">

                <div class="progress h-1 mt-0 mb-2">
                    <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                         id="tasksProgressBar"></div>
                </div>
                <div class="row text-center">
                    <div class="col "><span class="text-muted">Overdue</span> <h4
                                class="fw-normal mt-2 mb-0 number-font1" id="tasksOverdue"></h4></div>
                    <div class="col "><span class="text-muted">Due</span> <h4
                                class="fw-normal mt-2 mb-0 number-font2" id="tasksDue"></h4></div>
                    <div class="col "><span class="text-muted">Total</span> <h4
                                class="fw-normal mt-2 mb-0 number-font2" id="tasksComplete"></h4></div>

                </div>
            </div>


            <div class="table-responsive mb-3">
                <table class='table table-striped table-bordered mb-0' id='tHomeTasks'>
                    <thead>
                    <tr>

                        <th class="th-sm"></th>
                        <th class="th-sm"></th>
                        <th>Name</th>
                        <th>Due</th>

                    </tr>
                    </thead>
                </table>
            </div>
            <div class="d-flex">
                <button type="button" class="btn btn-primary ms-auto"
                        data-bs-toggle="modal" data-bs-target="#cabinTaskEditModal"
                        data-key="">
                    <i class="fa-solid fa-plus me-2"></i>Add
                </button>

            </div>
        </div>
    </div>
</div>
<script type="module">
    import {HomeTasksTable} from '/JS/DataTables/homeTasks.js';

    window.addEventListener('DOMContentLoaded', () => HomeTasksTable.init());
</script>