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
            paging: false,
            lengthChange: false,
            searching: false,
            info: false,
            stateSave: true,
            columns: [
                {data: "date"},
                {data: "details", orderable: false},
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
            paging: false,
            lengthChange: false,
            searching: false,
            info: false,
            stateSave: true,
            columns: [
                {data: "scheduled_delivery_date"},
                {data: "details", orderable: false},
                {data: "rating"},

            ],
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },

        });
    }


    // W A T C H   L I S T
    var nsWatchList = nsWatchList || {};

    nsWatchList.WatchlistManager = (function ($) {

        return {
            init: function () {
                if ($('#watchlistTotal').length) {
                    this.updateTotal();
                }

                if ($('#tHomeWatchlist').length) {
                    this.initializeWatchlistTable();
                }
            },

            updateTotal: function () {
                let payload = {
                    endpoint: 'Invoices',
                    action: 'BadDebtTotal',
                };

                $.getJSON("/json.php", payload, function (data) {
                    $('#watchlistTotal').text(data.total);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    console.error('Failed to fetch watchlist total:', textStatus, errorThrown);
                });
            },

            initializeWatchlistTable: function () {
                $('#tHomeWatchlist').DataTable({
                    ajax: {
                        url: "/json.php",
                        data: {
                            endpoint: "Invoices",
                            action: "HomeWatchList",
                        }
                    },
                    processing: true,
                    serverSide: true,
                    paging: false,
                    searching: false,
                    info: false,
                    stateSave: true,
                    lengthChange: false,
                    columns: [
                        {data: "name", orderable: false},
                        {data: "amount_due", orderable: false},
                        {data: "weeks_due", orderable: false}
                    ],
                    createdRow: (row, data, index) => {
                        row.classList.add('bar-' + data.colour);
                    },
                });
            }
        };
    })(jQuery);

// Initialize the WatchlistManager
    nsWatchList.WatchlistManager.init();
});
