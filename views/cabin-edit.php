<!-- Modal -->
<div class='modal fade' id='cabinSingle' tabindex='-1' role='dialog' aria-labelledby='cabinSingleLabel'
     aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title' id='cabinSingleLabel'>Contact</h5>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <div class='modal-body'>
                <div class='container'>
                    <form type='GET' id='modalForm'>
                        <div class="form-group">
                            <label for="cabinnumber">Number</label>
                            <input type="text" class="form-control" id="cabinnumber" aria-describedby="cabinnumberHelp"
                                   placeholder="Enter the new cabin's number">
                        </div>
                        <div class="form-group">
                            <label for="cabinstyle">Style</label>
                            <select id='cabinstyle' name='cabinstyle' class="form-control">
                                <option value='Left'>Left Window</option>
                                <option value='Right'>Right Window</option>
                                <option value='Large'>Large</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cabinstatus">Status</label>
                            <select id='cabinstatus' name='cabinstatus' class="form-control">
                                <option value='NEW'>New</option>
                                <option value='ACTIVE'>Active</option>
                                <option value='REPAIRS'>Needs Repairs</option>
                                <option value='DISPOSED'>Disposed/Sold</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lastupdated">Last Updated</label>
                            <input type="text" class="form-control" id="lastupdated" disabled="disabled">
                        </div>

                    </form>
                </div>
            </div>
            <div class='modal-footer'>
                <div class="spinner-grow text-warning" role="status" id='modalSpinner'
                     style='display: none; font-size: 2em;padding: 1em;'>
                    <span class="sr-only">Loading...</span>
                </div>
                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                <button type='button' class='btn btn-primary' id='save'>Save</button>
            </div>
        </div>
    </div>
</div>
<!-- /Modal -->
