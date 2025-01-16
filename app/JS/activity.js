class ActivityList {
    idTag = '#tActivity';
    dataTable;
    currentButton = '';

    constructor() {
        if ($(this.idTag).length > 0) {
            this.dataTable = $(this.idTag).DataTable(this.getDataTableOptions());
            this.setListeners();
        }
    }

    setListeners() {
        console.log('setListeners');
    }

    getDataTableOptions() {
        return {
            ajax: {
                url: "/json.php",
                data: (d) => {
                    d.button = this.currentButton;
                    d.endpoint = 'Activity';
                    d.action = 'List';
                },
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
            ], layout: {
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
                            //dt.ajax.reload();
                            action: () =>
                                this.getFilteredData('All')
                        },
                        {
                            text: 'Email',
                            action: () =>
                                this.getFilteredData('Email')
                        },
                        {
                            text: 'SMS',
                            action: () =>
                                this.getFilteredData('SMS')
                        },
                        {
                            text: 'New',
                            action: () =>
                                this.getFilteredData('New')
                        },

                    ]
                }
            },
            createdRow: (row, data, index) => {
                if (data.colour) {
                    row.classList.add('bar-' + data.colour);
                }
            }
        };
    }

    getFilteredData(buttonValue) {
        console.log('this inside button action', this);
        this.currentButton = buttonValue;
        //this.dataTable.text(buttonValue);
        this.highlightActiveButton(buttonValue);
        this.updateFilterDisplay();
        this.dataTable.ajax.reload();
    }

    highlightActiveButton(activeButton) {
        $('.dt-buttons button').removeClass('active');
        $(`.dt-buttons button:contains(${activeButton})`).addClass('active');
    }

    updateFilterDisplay() {
        $('#filterDisplay').text(`Current Filter: ${this.currentButton || 'All'}`);
    }
}

const nsActivity = new ActivityList();
