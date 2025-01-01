export default class SendSmsReminders {

    sendSmsModal;
    tBadDebts;
    smsBody;
    popType = 'contract';
    readyToUse = false;

    constructor() {
        if ($('#saveSmsRequest').length) {
            this.sendSmsModal = new bootstrap.Modal($('#saveSmsRequest'));

            this.smsBody = $('#smsBody');

            this.setListeners();
            this.readyToUse = true;
        }
    }

    setListeners() {

        //set up the modal
        //$('#saveSmsRequest').on('show.bs.modal', () => this.showSmsModal());

        // save the sms requests
        $('#smsSendButton').on('click', (e) => this.sendSms(e));


        // change the template
        $('#templateId').on('change', (e) => {
            this.getTemplateBody(e);
        });


        // select all the rows in the datatable
        $('#selectAll').on('click', (e) => {
            e.preventDefault();
            this.selectAll();
        });

        // the text in the sms has changed, show the character count
        this.smsBody.on('input', (e) => this.updateCharCounter());

    }

    showModal(groupName) {

        if (!this.readyToUse) {
            console.log('not ready to use');
            return;
        }
        console.log('about to call saveSmsModal');
        this.sendSmsModal.show();
        console.log('should be showing');
        $('#saveSmsGroupLabel').text(groupName);


        if ($('#tBadDebts').length) {
            this.tBadDebts = $('#tBadDebts').DataTable();
            this.popType = 'datatable';
        }


// Get the count of selected rows
        if (this.popType == 'datatable') {
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


    getTemplateBody() {

        let payload = {
            endpoint: 'Templates',
            action: 'Single',
            id: $('#templateId').val()
        };

        $.getJSON('/json.php', payload)
            .done((data) => { // Use arrow function

                this.smsBody.val(data.templates.body);
                this.updateCharCounter();
            });
    }


    sendSms() {
        console.log('table', this.tBadDebts);
        console.log('rows', this.tBadDebts.rows());
        console.log('selected', this.tBadDebts.rows({selected: true}));
        console.log('ids', this.tBadDebts.rows({selected: true}).ids());
        let selectedRowIds = this.tBadDebts.rows({selected: true}).ids().toArray();
        let payload = {
            endpoint: 'Activity',
            action: 'SaveManySMS',
            repeatingInvoiceIds: selectedRowIds,
            smsBody: this.smsBody.val(),
        };
        console.log('sendSms Payload:', payload);
        $.ajax({
            url: '/run.php',
            data: payload,
            method: "POST",
        })
            .done(function (msg) {
                alert("Data Saved: " + msg);
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
