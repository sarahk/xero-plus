function ns_activity() {
    this.idTag = '#tActivity';
    this.dataTable;
    this.currentButton = '';
    // this.templateModal;
    // this.modalTemplateId;
    // this.modalMessagetype;
    // this.modalTemplateStatus;
    // this.modalLabel;
    // this.modalSubject;

    this.init_datatable = function () {

        if ($(this.idTag).length > 0) {
            this.dataTable = $(this.idTag).DataTable(this.dataTableOptions);
            // this.templateModal = $('#templateModal');
            // this.modalTemplateId = $('#template_id');
            // this.modalMessagetype = $('#messagetype');
            // this.modalTemplateStatus = $('#templatestatus');
            // this.modalLabel = $('#templatelabel');
            // this.modalSubject = $('#templatesubject');

            this.setListeners();
        }
    };

    this.setListeners = function () {
        console.log('setListeners');
    };

    this.dataTableOptions = {
        ajax: {
            url: "/json.php",
            data: (d) => {
                // Make sure this is updating every time `ajax.reload()` is called
                d.button = this.currentButton;
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
            {data: "activity_id", name: "activity_id"},
            {data: "date", name: "activity_date"},
            {data: "activity_status", name: "activity_status"},
            {data: "activity_type", name: "activity_type"},
            {data: "name", name: "name"},
            {data: "preview", name: "preview", sortable: false, searchable: false},

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
                        action: () => {
                            //dt.ajax.reload();
                            this.currentButton = '';
                            this.dataTable.ajax.reload();
                        }
                    },
                    {
                        text: 'Email',
                        action: () => {
                            this.currentButton = 'Email';
                            this.dataTable.ajax.reload();
                        }
                    },
                    {
                        text: 'SMS',
                        action: () => {
                            this.currentButton = 'SMS';
                            this.dataTable.ajax.reload();
                        }
                    },
                    {
                        text: 'New',
                        action: () => {
                            currentButton = 'New';
                            this.dataTable.ajax.reload();
                        }
                    },

                ]
            }
        },
        createdRow: (row, data, index) => {
            row.classList.add('bar-' + data.colour);
        },
    };

}

const nsActivity = new ns_activity();
nsActivity.init_datatable();
