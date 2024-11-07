<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">New message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="template_editor">
                    <input type="hidden" name="id" id="template_id" value=""/>
                    <div class="row mb-4">
                        <label for="recipient-name" class="col-sm-3 col-form-label">TemplateType:</label>
                        <select name="messagetype" id="messagetype" class="col-sm-9 form-select">
                            <option value="SMS">SMS</option>
                            <option value="email">email</option>
                        </select>
                    </div>
                    <div class="row mb-4">
                        <label for="recipient-name" class="col-sm-3 col-form-label">Status:</label>
                        <select name="status" id="templatestatus" class="col-sm-9 form-select">
                            <option value="1">Active</option>
                            <option value="0">Archived</option>
                        </select>
                    </div>
                    <div class="row mb-4">
                        <label for="recipient-name" class="col-sm-3 col-form-label">Label:</label>
                        <input type="text" name='label' class="col-sm-9 form-control" id="templatelabel" value=""
                               placeholder="Label">
                    </div>
                    <div class="row mb-4">
                        <label for="recipient-name" class="col-sm-3 col-form-label">Subject:</label>
                        <input type="text" name='subject' class="col-sm-9 form-control" id="templatesubject" value=""
                               placeholder="Email Subject Line">
                    </div>
                    <div class="mb-4">
                        <label for="message-text" class="col-form-label">Body:</label>
                        <textarea class="form-control" name='body' id="templatebody"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button id='saveTemplateButton' type="submit" form='template_editor' class="btn btn-primary"
                        data-bs-dismiss="modal">Save
                </button>
            </div>
        </div>
    </div>
</div>
