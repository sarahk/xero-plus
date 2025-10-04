<style>
    .sidebar .panel-body,
    .sidebar .card-body {
        overflow-y: auto; /* or scroll */
        scrollbar-gutter: stable; /* keeps width constant when bar appears/disappears */
    }
</style>
<div class="sidebar sidebar-right sidebar-animate">
    <div class="panel panel-primary card mb-0 shadow-none border-0">
        <div class="tab-menu-heading border-0 d-flex p-3">
            <div class="card-title mb-0">This Week</div>

            <div class="card-options ms-auto">
                <a href="javascript:void(0);" class="sidebar-icon text-end float-end me-1" id="toggleTaskFiltering"><i
                            class="fa-solid fa-list-check text-white"></i></a>
            </div>
            <div class="card-options ms-auto">
                <a href="javascript:void(0);" class="sidebar-icon text-end float-end me-1"
                   data-bs-toggle="sidebar-right" data-target=".sidebar-right"><i class="fe fe-x text-white"></i></a>
            </div>
        </div>
        <div class="panel-body pt-1 pb-4 px-4 border-bottom shadow" id="taskFiltering">
            <p>Filter tasks by status.<br/>Show only...</p>
            <div class="row">
                <?php

                use App\Models\Enums\TaskStatus;

                $options = TaskStatus::getAllAsArray();
                foreach ($options as $value) {
                    echo "<div class='col-6 text-start'>
                                <div class='form-check mb-2'>
                                            <input class='form-check-input' type='checkbox' value='' id='{$value['name']}Tasks'/>
                                            <label class='form-check-label' for='{$value['name']}Tasks'>{$value['label']}</label>
                                        </div>
                             </div>";
                }
                ?>
            </div>
        </div>
        <div class="panel-body tabs-menu-body latest-tasks p-0 border-0">

            <div class="tab-content">
                <div class="tab-pane active" id="side1">
                    <div class="card-body text-center" id="taskDisplay" xid="card-body">

                        <!--                        <div class="section">-->
                        put list of tasks & deliveries here
                        <!--                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">

    import {initSidebarContent} from '/JS/sidebar_content.js';

    window.addEventListener('DOMContentLoaded', () => initSidebarContent());
</script>
