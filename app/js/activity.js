let currentActivityButton = '';
let $badDebtsTitle = $('#badDebtsTitle');
let $smsBody = $('#smsBody');

if ($('#tActivity').length) {

    let $tActivity = $('#tActivity').DataTable({
        ajax: {
            url: "/json.php",
            data: function (d) {
                // Make sure this is updating every time `ajax.reload()` is called
                d.button = currentActivityButton;
                d.endpoint = 'Activity';
                d.action = 'List';
            }
        },
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        rowId: 'DT_RowId',
        columns: [

            {data: "id", name: "id"},
            {data: "date", name: "activity_date"},
            {data: "activity_status", name: "activity_status"},
            {data: "activity_type", name: "activity_type"},
            {data: "name", name: "name"},
            {data: "preview", name: "preview"},

        ],
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
                            currentActivityButton = '';
                            $tActivity.ajax.reload();
                        }
                    },
                    {
                        text: 'Email',
                        action: function () {
                            currentActivityButton = 'Email';
                            $tActivity.ajax.reload();
                        }
                    },
                    {
                        text: 'SMS',
                        action: function () {
                            currentActivityButton = 'SMS';
                            $tActivity.ajax.reload();
                        }
                    },
                    {
                        text: 'New',
                        action: function () {
                            currentActivityButton = 'New';
                            $tActivity.ajax.reload();
                        }
                    },

                ]
            }
        },
        createdRow: (row, data, index) => {
            row.classList.add('bar-' + data.colour);
        },
    });
