//import Swal from "sweetalert2";

let currentButtonValue = '';
let $badDebtsTitle = $('#badDebtsTitle');
let $smsBody = $('#smsBody');

if ($('#tBadDebts').length) {

    let $tBadDebts = $('#tBadDebts').DataTable({
        ajax: {
            url: "/json.php",
            data: function (d) {
                // Make sure this is updating every time `ajax.reload()` is called
                d.button = currentButtonValue;
                d.endpoint = 'Invoices';
                d.action = 'BadDebts';


                $badDebtsTitle.text(currentButtonValue);
            }
        },
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        rowId: 'DT_RowId',
        columns: [
            {
                data: null,
                targets: 0,
                searchable: false,
                orderable: false,
                render: DataTable.render.select(),
            },
            {data: "contact", name: "contact"},
            {data: "amount_due", name: "amount_due"},
            {data: "weeks_due", name: "weeks_due"},
            {data: "total_weeks", name: "total_weeks"},
            {data: 'chart', orderable: false,}
        ],
        fixedColumns: {
            start: 1
        },
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        layout: {
            topStart: {
                buttons: [
                    'pageLength',
                    {
                        extend: 'csv',
                        text: 'Export',
                        split: ['copy', 'excel', 'pdf', 'print']
                    },
                    {
                        text: 'All',
                        action: function () {
                            //dt.ajax.reload();
                            currentButtonValue = '';
                            $tBadDebts.ajax.reload();
                        }
                    },
                    {
                        text: '1 week',
                        action: function () {
                            currentButtonValue = '1week';
                            $tBadDebts.ajax.reload();
                        }
                    },
                    {
                        text: '2 weeks',
                        action: function () {
                            currentButtonValue = '2weeks';
                            $tBadDebts.ajax.reload();
                        }
                    },
                    {
                        text: '3 weeks',
                        action: function () {
                            currentButtonValue = '3weeks';
                            $tBadDebts.ajax.reload();
                        }
                    },
                    {
                        text: 'Older',
                        action: function () {
                            currentButtonValue = 'older';
                            $tBadDebts.ajax.reload();
                        }
                    },
                    {
                        text: 'SMS',
                        action: function () {
                            let saveSmsModal = new bootstrap.Modal(document.getElementById('saveSmsRequest'));
                            saveSmsModal.show();
                        }
                    },
                ]
            }
        },
        createdRow: (row, data, index) => {
            row.classList.add('bar-' + data.colour);
        },
    });
}


// to run the saveSms modal - either from the bad debts screen or a contract page
function ns_SaveSms() {
    this.$tBadDebts;
    this.$smsBody;
    this.popType = 'contract';

    this.init = function () {
        if ($('#saveSmsRequest').length) {

            this.$smsBody = $('#smsBody');

            if ($('#tBadDebts').length) {
                this.$tBadDebts = $('#tBadDebts');
                this.popType = 'datatable';
            }
            this.setListeners();
        }
    };
    this.setListeners = function () {

        //set up the modal
        $('#saveSmsRequest').on('show.bs.modal', this.showSmsModal);

        // save the sms requests
        $('#smsSendButton').on('click', this.sendSms);

        // change the template
        $('#templateId').on('change', this.getTemplateBody);

        // select all the rows in the datatable
        $('#selectAll').on('click', function (e) {
            e.preventDefault();
            this.selectAll();
        });

        // the text in the sms has changed, show the character count
        $smsBody.on('input', this.updateCharCounter);

    };


    this.showSmsModal = function () {
        $('#saveSmsGroupLabel').text(currentButtonValue);

// Get the count of selected rows
        if (this.popType == 'datatable') {
            let info = this.$tBadDebts.page.info();
            let selectedRowCount = this.$tBadDebts.rows({selected: true}).count();
            let recordsDisplay = info.recordsDisplay;
            let unselectedCount = recordsDisplay - selectedRowCount;
            console.log([info, unselectedCount, recordsDisplay, selectedRowCount]);

            if (unselectedCount) {
                $('#unselected').text(unselectedCount);
                $('#showAddAll').show();
            }
            $('#smsCount').text(selectedRowCount);
        }

    };

    this.selectAll = function () {
        let info = this.$tBadDebts.page.info();
        let recordsDisplay = info.recordsDisplay;

        if (info.pages > 1) {
            let newLength = getBiggerTableLength(recordsDisplay);
            this.$tBadDebts.page.len(newLength).draw();

            // Wait for the datatable draw to complete, then select all rows
            this.$tBadDebts.on('draw', () => {
                this.$tBadDebts.rows().select();
                // Remove the event listener to avoid multiple triggers
                this.$tBadDebts.off('draw');
            });

        } else {
            this.$tBadDebts.rows().select();
        }
        $('#showAddAll').hide();
        $('#smsCount').text(recordsDisplay);
    };


    this.getTemplateBody = function () {

        let payload = {
            endpoint: 'Templates',
            action: 'Single',
            id: $('#templateId').val()
        };

        $.getJSON('/json.php',
            payload)
            .done(function (data) {
                console.log(this);
                // todo fix this is the dropdown, not the namespace
                this.$smsBody.val(data.templates.body);
                this.updateCharCounter();
            }.bind(this));
    };


    this.sendSms = function () {
        let selectedRowIds = this.$tBadDebts.rows({selected: true}).ids().toArray();
        let payload = {
            endpoint: 'Activity',
            action: 'SaveManySMS',
            repeatingInvoiceIds: selectedRowIds,
            smsBody: $smsBody.val(),
        };
        console.log('Payload:', payload);
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

    };

    this.updateCharCounter = function () {
        let text = $smsBody.val();
        let blocks = this.splitIntoBlocks(text);
        let last = blocks[blocks.length - 1];
        let msg = `${blocks.length} SMS,  ${last.length}/160`;
        $('#charCounter').text(msg);
    };


    this.splitIntoBlocks = function (text, blockSize = 160) {
        let blocks = [];
        let start = 0;

        while (start < text.length) {
            // Slice up to the blockSize limit
            let end = start + blockSize;
            let slice = text.slice(start, end);

            // If slice length exceeds the limit due to multi-byte characters (like emojis), adjust the end
            if (slice.length > blockSize) {
                end = findLastSpace(text, start, end);
                slice = text.slice(start, end); // Re-slice the text within adjusted bounds
            }

            blocks.push(slice);
            start = end;
        }

        return blocks;
    };

// Helper function to find the last space within the block size limit
    this.findLastSpace = function (text, start, end) {
        const subText = text.slice(start, end);
        const lastSpace = subText.lastIndexOf(' ');

        return lastSpace === -1 ? end : start + lastSpace;
    };


    this.getBiggerTableLength = function (currentLength) {
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
    };

//Get the Count of Selected Rows: Use table.rows({ selected: true }).count().
//Get the Row IDs of Selected Rows: Use table.rows({ selected: true }).ids().toArray().
//let info = table.page.info();

// todo
// show how many in group
// option to select all
// if more than will show on the page then change the page length first
// can all that be done behind a modal?

}

const nsSaveSms = new ns_SaveSms();
nsSaveSms.init();
