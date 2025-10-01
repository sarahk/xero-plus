<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tasks</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-nowrap border-bottom w-100" id="tTasksIndex">
                        <caption class="visually-hidden">Tasks table</caption>
                        <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Type</th>
                            <th scope="col">Cabin</th>
                            <th scope="col">Name</th>
                            <th scope="col">Due Date</th>
                            <th scope="col">Scheduled</th>
                            <th scope="col">Assigned</th>
                            <th scope="col" class="no-sort">Set Task As...</th>

                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {TasksIndexTable} from '/JS/DataTables/tasksIndex.js';

    window.addEventListener('DOMContentLoaded', () => TasksIndexTable.init());
</script>