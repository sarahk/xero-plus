import {fetchJSON} from "/JS/ui/helpers.js";

export default class SendSmsReminders {
    constructor() {
        this.sendSmsModal = null;
        this.tBadDebts = null;
        this.smsBody = null;
        this.popType = 'contract';
        this.readyToUse = false;

        const el = document.getElementById('saveSmsRequest'); // DOM element, not jQuery
        if (el) {
            this.sendSmsModal = new bootstrap.Modal(el);
            this.smsBody = $('#smsBody');
            this.setListeners();       // keep your other listeners (send, select-all, etc.)
            this.readyToUse = true;
        }
    }

    setListeners() {
        // REMOVE the show.bs.modal handler that calls showModal()
        // $('#saveSmsRequest').off('show.bs.modal')...

        $('#smsSendButton').off('click').on('click', (e) => this.sendSms(e));
        $('#templateId').off('change').on('change', () => this.getTemplateBody());
        $('#selectAll').off('click').on('click', (e) => {
            e.preventDefault();
            this.selectAll();
        });
        this.smsBody?.off('input').on('input', () => this.updateCharCounter());
    }

    // New entry point: populate, then actually show
    open(groupName) {
        if (!this.readyToUse) return;

        $('#saveSmsGroupLabel').text(groupName ?? '');

        // Capture table if present
        if ($('#tBadDebts').length) {
            this.tBadDebts = $('#tBadDebts').DataTable();
            this.popType = 'datatable';
        } else {
            this.popType = 'contract';
        }

        if (this.popType === 'datatable') {
            $('#sendFromList').show();
            $('#sendSMSname').hide();

            const info = this.tBadDebts.page.info();
            const selected = this.tBadDebts.rows({selected: true}).count();
            const unselected = info.recordsDisplay - selected;

            if (unselected) {
                $('#unselected').text(unselected);
                $('#showAddAll').show();
            }
            $('#smsCount').text(selected);
        } else {
            $('#sendSMSname').show();
            $('#sendFromList').hide();
        }

        this.sendSmsModal.show();  // <-- actually open the modal
    }

    showModal(groupName) {

        if (!this.readyToUse) {
            console.log('not ready to use');
            return;
        }
        console.log('about to call saveSmsModal');
        //this.sendSmsModal.show();
        console.log('should be showing');
        $('#saveSmsGroupLabel').text(groupName);


        if ($('#tBadDebts').length) {
            this.tBadDebts = $('#tBadDebts').DataTable();
            this.popType = 'datatable';
        }


// Get the count of selected rows
        if (this.popType == 'datatable') {
            $('#sendFromList').show();
            $('#sendSMSname').hide();
            let info = this.tBadDebts.page.info();
            let selectedRowCount = this.tBadDebts.rows({selected: true}).count();
            let recordsDisplay = info.recordsDisplay;
            let unselectedCount = recordsDisplay - selectedRowCount;
            console.log([info, unselectedCount, recordsDisplay, selectedRowCount]);

            if (unselectedCount) {
                $('#unselected').text(unselectedCount);
                $('#showAddAll').show();
            }
            $('#smsCount').text(selectedRowCount);
        } else {
            $('#sendSMSname').show();
            $('#sendFromList').hide();
        }

    }

    selectAll() {

        let info = this.tBadDebts.page.info();
        let recordsDisplay = info.recordsDisplay;

        if (info.pages > 1) {
            let newLength = this.getBiggerTableLength(recordsDisplay);
            this.tBadDebts.page.len(newLength).draw();

            // Wait for the datatable draw to complete, then select all rows
            this.tBadDebts.on('draw', () => {
                this.tBadDebts.rows().select();
                // Remove the event listener to avoid multiple triggers
                this.tBadDebts.off('draw');
            });

        } else {
            this.tBadDebts.rows().select();
        }
        $('#showAddAll').hide();
        $('#smsCount').text(recordsDisplay);
    }


    // sendSmsReminders.js
    async getTemplateBody() {
        const id = $('#templateId').val();
        if (!id) return;

        const qs = new URLSearchParams({
            endpoint: 'Templates',
            action: 'Single',
            id
        });

        try {
            const data = await fetchJSON(`/json.php?${qs.toString()}`);
            this.smsBody.val(data?.templates?.body || '');
            this.updateCharCounter();
        } catch (err) {
            console.error('getTemplateBody failed:', err);
        }
    }


    sendSms() {
        let payload = {
            endpoint: 'Activity',
            action: 'SaveManySMS',
            smsBody: this.smsBody.val()
        };

        if (this.popType == 'datatable') {
            console.log('table', this.tBadDebts);
            console.log('rows', this.tBadDebts.rows());
            console.log('selected', this.tBadDebts.rows({selected: true}));
            console.log('ids', this.tBadDebts.rows({selected: true}).ids());
            let selectedRowIds = this.tBadDebts.rows({selected: true}).ids().toArray();
            payload.repeatingInvoiceIds = selectedRowIds;

        } else {
            console.log(keys);
            payload.repeatingInvoiceIds = [keys.contract.repeating_invoice_id];
        }
        console.log('sendSms Payload:', payload);
        $.ajax({
            url: '/run.php',
            data: payload,
            method: "POST",
        })
            .done(function (msg) {

                Swal.fire({
                    title: "Good job!",
                    text: "Successfully Queued",
                    icon: "success"
                });
            });

    }

    updateCharCounter() {
        let text = this.smsBody.val();
        let blocks = this.splitIntoBlocks(text);
        let last = blocks[blocks.length - 1];
        let msg = `${blocks.length} SMS,  ${last.length}/160`;
        $('#charCounter').text(msg);
    }


    splitIntoBlocks(text, blockSize = 160) {
        let blocks = [];
        let start = 0;

        while (start < text.length) {
            // Slice up to the blockSize limit
            let end = start + blockSize;
            let slice = text.slice(start, end);

            // If slice length exceeds the limit due to multi-byte characters (like emojis), adjust the end
            if (slice.length > blockSize) {
                end = this.findLastSpace(text, start, end);
                slice = text.slice(start, end); // Re-slice the text within adjusted bounds
            }

            blocks.push(slice);
            start = end;
        }

        return blocks;
    }

// Helper function to find the last space within the block size limit
    findLastSpace(text, start, end) {
        const subText = text.slice(start, end);
        const lastSpace = subText.lastIndexOf(' ');

        return lastSpace === -1 ? end : start + lastSpace;
    }


    getBiggerTableLength(currentLength) {
        let lengths = [10, 25, 50, 100];

        if (currentLength > 100) {
            return Math.ceil(currentLength / 25) * 25;
        }

        for (let length of lengths) {
            if (length > currentLength) {
                return length;
            }
        }
        return 100;
    }

//Get the Count of Selected Rows: Use table.rows({ selected: true }).count().
//Get the Row IDs of Selected Rows: Use table.rows({ selected: true }).ids().toArray().
//let info = table.page.info();

// todo
// show how many in group
// option to select all
// if more than will show on the page then change the page length first
// can all that be done behind a modal?

}
