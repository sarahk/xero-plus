<?php
declare(strict_types=1);

use App\Classes\Utilities;
use App\Classes\FormBuilder;
use App\Models\TemplateModel;

$template_model = new TemplateModel(Utilities::getPDO());
$templates = $template_model->getSelectChoices('sms');

?>
<div class="modal fade" id="saveSmsRequest" tabindex="-1" aria-labelledby="saveSmsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveSmsLabel">Send SMS Messages</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="sendSmsForm">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3" id="sendFromList">
                                <p>Group: <span id="saveSmsGroupLabel" class="fw-bold"></span></p>
                                <p>SMS to send: <span id="smsCount" class="fw-bold"></span></p>

                                <p class="d-none" id="showAddAll">
                                    There are <span id="unselected" class="fw-bold"></span> more.
                                    Click here to
                                    <button id="selectAll" type="button" class="btn btn-sm btn-primary">Select All
                                    </button>
                                </p>
                            </div>

                            <div class="mb-3" id="sendFromSingle">
                                <p class="fw-bold mb-1">Sending to a single contact</p>
                                <p id="sendSMSname" class="mb-0"></p>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <?php echo FormBuilder::select('templateId', 'data[activity][template_id]', 'SMS Template', $templates); ?>
                            </div>
                        </div>

                        <div class="col-6">
                            <textarea class="form-control" name="body" id="smsBody" rows="7"></textarea>

                            <div class="row mt-2">
                                <div class="col-6">
                                    <a href="https://getemoji.com/" target="_blank" rel="noopener" class="small">Find
                                        More Emojis</a>
                                </div>
                                <div class="col-6 text-end">
                                    <span id="charCounter" class="small" aria-live="polite"></span>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <p class="mb-1">You can personalise with:</p>
                                    <ul class="mb-1">
                                        <li>[first_name]</li>
                                        <li class="text-muted text-decoration-line-through">[cabin]</li>
                                    </ul>
                                    <p class="small mb-0">The swap length may result in additional costs to send</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="smsSendButton">Send</button>
            </div>
        </div>
    </div>
</div>
<script type="module">
    import {initSendSmsReminders} from '/JS/Modals/sendSmsReminders.js?v=<?=time();?>';

    window.addEventListener('DOMContentLoaded', () => initSendSmsReminders('saveSmsRequest'));
</script>
