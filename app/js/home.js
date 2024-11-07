$(document).ready(function () {

    if ($('#tHomeTasks').length) {
        $('#tHomeTasks').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: 'Tasks',
                    action: 'ListHome',
                }
            },
            processing: true,
            stateSave: true,
            serverSide: true,
            columns: [
                {data: "icon"},
                {data: "id"},
                {data: 'status'},
                {data: "name"},
                {data: "due_date",},
            ],
            paging: true,
        });
    }

    if ($('#tasksOverdue').length) {
        $.get("/json.php?endpoint=Tasks&action=Counts", function (data) {
            $('#tasksOverdue').text(data.overdue);
            $('#tasksDue').text(data.due);
            $('#tasksComplete').text(data.complete);
            $('#tasksProgressBar').addClass(data.progressBarClass);
        });
    }

    // N E W   E N Q U I R I E S

    if ($('#tHomeEnquiries').length) {

        $('#tHomeEnquiries').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: "Contracts",
                    action: "List",
                    subset: 'New'
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            searching: false,
            info: false,
            stateSave: true,
            columns: [
                {data: "date"},
                {data: "details"},
                {data: "rating"},
            ],
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },

        });
    }

    // W A I T I N G   F O R   A   C A B I N

    if ($('#tHomeWaitlist').length) {

        $('#tHomeWaitlist').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: "Contracts",
                    action: "List",
                    subset: 'Waiting'
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            searching: false,
            info: false,
            stateSave: true,
            columns: [
                {data: "scheduled_delivery_date"},
                {data: "details"},
                {data: "rating"},

            ],
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },

        });
    }


    // W A T C H   L I S T


    if ($('#watchlistTotal').length) {
        let payload = {
            endpoint: 'Invoices',
            action: 'BadDebtTotal',
        };

        $.getJSON("/json.php", payload, function (data) {
            $('#watchlistTotal').text(data.total);
        });

    }


    if ($('#tHomeWatchlist').length) {

        $('#tHomeWatchlist').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: "Invoices",
                    action: "BadDebts",
                }
            },
            processing: true,
            serverSide: true,
            paging: true,
            searching: false,
            info: false,
            stateSave: true,
            columns: [
                {data: "name"},
                {data: "amount_due"},
                {data: "weeks_due"},

            ],
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },

        });
    }
});
