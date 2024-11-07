<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Message Templates</h3>
                <button type="button" class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#templateModal"
                        data-template_id='0'
                >New Template
                </button>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom w-100" id="tTemplates">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Label</th>
                            <th>Preview</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Button trigger modal -->

<?php require 'modals/template-single.php'; ?>
