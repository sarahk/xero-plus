<div class="sidebar sidebar-right sidebar-animate">
    <div class="panel panel-primary card mb-0 shadow-none border-0">
        <div class="tab-menu-heading border-0 d-flex p-3">
            <div class="card-title mb-0">This Week</div>
            <div class="card-options ms-auto">
                <a href="javascript:void(0);" class="sidebar-icon text-end float-end me-1"
                   data-bs-toggle="sidebar-right" data-target=".sidebar-right"><i class="fe fe-x text-white"></i></a>
            </div>
        </div>
        <div class="panel-body tabs-menu-body latest-tasks p-0 border-0">

            <div class="tab-content">
                <div class="tab-pane active" id="side1">
                    <div class="card-body text-center">
                        put list of tasks & deliveries here
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import {Sidebars} from '/JS/sidebar.js';
    // Wire up data-API and outside-click autohide
    Sidebars.init();

</script>
