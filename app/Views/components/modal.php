<?php
/** @var string|null $modalAction */
/** @var string|null $modalStub */
/** @var string|null $title */
/** @var string|null $bodyHtml */
/** @var string|null $jsFunction */
/** @var string|null $jsFile */
?>
<div class="modal fade" id="<?= $modalStub; ?>Modal" tabindex="-1" role="dialog"
     aria-labelledby="<?= $modalStub; ?>Modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" aria-modal="true">
        <div class="modal-content">
            <form id="<?= $modalStub; ?>Form" novalidate data-ck="form">
                <div class="modal-header" id="modal-header" data-ck="header">
                    <h4 class="modal-title">
                        <?= $title; ?> <strong><span data-ck="title-from-db"></span></strong>
                    </h4>
                    <!-- Bootstrap 5 close button -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i
                                class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="<?= $modalStub; ?>Alert" role="alert"
                         aria-live="polite"
                         data-ck="alert"></div>
                    <input type="hidden" name="action" data-ck="action" value="<?= $modalAction; ?>">
                    <?= $bodyHtml; ?>
                </div>

                <div class="modal-footer gap-2">
                        <span data-ck="updated" class="me-auto text-gray">Updated: <span
                                    data-ck="updated-from-db"></span></span>

                    <!-- submit the form -->
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Save
                        changes
                    </button>
                    <!-- close (BS5 attribute) -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i
                                class="fa-solid fa-xmark me-2"></i>Close
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>

<!-- Your module -->
<script type="module">
    import {<?=$jsFunction;?>} from '/JS/<?=$jsFile;?>';

    window.addEventListener('DOMContentLoaded', () => {
        <?=$jsFunction;?>();
        console.log('<?=$jsFunction;?> initialised');
    });
</script>
<!-- end of modal <?= $modalStub; ?> -->
