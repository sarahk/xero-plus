$(document).ready(function () {


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

    if ($('.owl-carousel').length) {
        let homeScreen = getScreenBreakpoint();
        // let homeCarousel = (homeScreen === 'xs' || homeScreen === 'sm') ? 1 : 5;

        $(".owl-carousel").owlCarousel({
            URLhashListener: true,
            margin: 5,
            startPosition: '#today',
            responsiveClass: true,
            responsive: {
                0: {items: 1},
                400: {items: 2},
                740: {items: 5},
                940: {items: 5}
            },
            nav: true,
            stagePadding: 50,
        });
    }
    // Initialize slick slider if it exists
    // shows the cabin movement records
    if ($('.slick-stock').length) {
        $('.slick-stock').slick({
            dots: false,
            arrows: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            centerMode: true,
            initialSlide: daysSincePreviousMonday(),
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        dots: true
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ],
        });
    }

});
