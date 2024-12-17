<?php
namespace App\Views\modals;
?>
<div class='modal fade' id='contactSingle' tabindex='-1' role='dialog' aria-labelledby='contactSingleLabel'
     aria-hidden='true'>
    <div class='modal-dialog modal-dialog-centered' role='document'>
        <div class='modal-content'>
            <div class='modal-header' id="modal-header">
                <h4 class='modal-title' id='contactSingleLabel'>Contact <strong><span
                                id="contactNameLabel"></span></strong>
                </h4>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
            </div>
            <div class='modal-body'>
                <div class='container'>
                    <form id="contactForm">
                        <input type="hidden" id="contactSingleId">
                        <div class="row mb-3">
                            <label for="contactName" class="col-sm-3 col-form-label">Name</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="contactName" value="">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="contactName" class="col-sm-3 col-form-label">First Name</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="contactFirstName">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="contactName" class="col-sm-3 col-form-label">Surname</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="contactLastName">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="contactName" class="col-sm-3 col-form-label">Email</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="contactEmail">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="contactName" class="col-sm-3 col-form-label">Mobile</label>

                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="contactMobile">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="small" id="internalData"></p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                        <button class="btn btn-info-light btn-wave" id="refreshXero">Refresh\Xero</button>
                    </form>
                    <div class="row mt-3">
                        <div class="col-6">
                            <strong>Payment History</strong>
                        </div>
                        <div class="col-6 d-grid d-flex justify-content-end">
                            <a href="#" id="seeInvoices">See Invoices</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col=12">
                            <img src="" id="imgBadDebts">
                        </div>
                    </div>
                </div>

            </div>
            <div class='modal-footer'>
                <div class="spinner-grow text-warning" role="status" id='modalSpinnerContact'
                     style='display: none; font-size: 2em;padding: 1em;'>
                    <span class="sr-only">Loading...</span>
                </div>

                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /Modal -->
