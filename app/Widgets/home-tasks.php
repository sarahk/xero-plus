<?php
?>
<div class="col-md-6">
    <div class="card">

        <div class="card-body">
            <h5 class="card-title"><i class="fa-solid fa-list"></i> Tasks</h5>

            <div class="row">
                <div class="text-center">

                    <div class="progress h-1 mt-0 mb-2">
                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                             id="tasksProgressBar"></div>
                    </div>
                    <div class="row mt-4">
                        <div class="col text-center"><span class="text-muted">Overdue</span> <h4
                                    class="fw-normal mt-2 mb-0 number-font1" id="tasksOverdue"></h4></div>
                        <div class="col text-center"><span class="text-muted">Due</span> <h4
                                    class="fw-normal mt-2 mb-0 number-font2" id="tasksDue"></h4></div>
                        <div class="col text-center"><span class="text-muted">Complete</span> <h4
                                    class="fw-normal mt-2 mb-0 number-font2" id="tasksComplete"></h4></div>

                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class='table table-striped table-bordered' id='tHomeTasks' style='width:100%'>
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>#</th>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Due Date</th>
                    </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>
