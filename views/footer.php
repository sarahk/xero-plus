</main>
<footer class='container'>
    <button class="btn btn-secondary" id='loadfromxero'>
        <span class="spinner-grow spinner-grow-sm" id='loadfromxerospinner'></span>
        Request latest from Xero
    </button>
</footer>


<script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.min.js"
    crossorigin="anonymous"></script>
<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>



<script type="text/javascript"
    src="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.1/b-print-1.6.1/r-2.2.3/sp-1.0.1/sl-1.3.1/datatables.min.js"></script>

<script type="text/javascript" src="/js/invoices.js"></script>
<script type="text/javascript" src="/js/vehicles.js"></script>


<script type="text/javascript">
    <?php /*
    document.addEventListener("DOMContentLoaded", function () {
    //    loadGet("<?php echo implode('","', $loadGet); ?>");
    //});  */ ?>

    <?php
    if (file_exists("js/{$endpoint}{$action}.js")):
        echo "jQuery.getScript('/js/{$endpoint}{$action}.js');";
    endif;
    ?>

    $(document).ready(function () {

        var tContacts = $('#getContactTable').DataTable({
            ajax: {
                "url": "/json.php?endpoint=Contacts&action=Read"
            },
            processing: true,
            serverSide: true,
            columns: [
                { data: "checkbox", orderable: false },
                { data: "theyowe", className: 'dt-right' },
                { data: "name" },
                { data: "phone", orderable: false },
                { data: "email" },
                { data: "address", orderable: false },
                { data: "action", orderable: false }
            ],
            paging: true,
            stateSave: true,
            dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",
            buttons: [
                {
                    text: 'All',
                    className: 'btn mr-1',
                    action: function (e, dt, node, config) {
                        //dt.ajax.reload();
                        tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read').load();
                    }
                }, {
                    text: 'Active',
                    className: 'btn mr-1',
                    action: function (e, dt, node, config) {
                        tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read&button=active').load();
                    }
                }, {
                    text: 'Archived',
                    className: 'btn mr-1',
                    action: function (e, dt, node, config) {
                        tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read&button=archived').load();
                    }
                }]
        });


        $('#getContactTable').on('draw.dt', function () {
            console.log('Table redrawn');
            $('address').each(function () {
                var link = "<a href='http://maps.google.com/maps?q=" + encodeURIComponent($(this).text()) + "' target='_blank'>" + $(this).text() + "</a>";
                $(this).html(link);
            });
        });




        var tCabins = $('#getCabinsTable').DataTable({
            "ajax": {
                "url": "/json.php?endpoint=Cabins&action=Read",
            },
            "processing": true,
            "serverSide": true,
            "columns": [
                { data: "number" },
                { data: "style" },
                { data: "status" },
                { data: "contact", },
                { data: "paintgrey" },
                { data: "paintinside" },
            ],
            "paging": true,
            dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",

            buttons: [{
                text: 'All',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    //dt.ajax.reload();
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read').load();
                }
            }, {
                text: 'New',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=new').load();
                }
            }, {
                text: 'Active',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=active').load();
                }
            }, {
                text: 'Repairs',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=repairs').load();
                }
            }, {
                text: 'Disposed',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=disposed').load();
                }
            }]

        });


        $('input[name="dates"]').mouseup(getInvoiceRedraw);

        function getInvoiceRedraw() {
            var getInvoiceTable = $('#getInvoiceTable').DataTable();

            setTimeout(function () {
                getInvoiceTable.draw(false);
            }, 100);
        }



        $('#contactSingle').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var contactid = button.data('contactid') // Extract info from data-* attributes
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.

            $.getJSON("/json.php?endpoint=Contacts&action=Single&key=" + contactid, function (data) {
                console.log(data);
                $('#modalContactStatus').text(data.contact_status);
                $('#modalContactName').text(data.name);
                $('#modalContactEmail').html(data.email);
                $('#modalContactPhone').html(data.phone);
                $('#modalContactAddress').html(data.address);
                $('#modalContactUpdate').text(data.updated_date_utc);

                $('#contactSingleLabel').text('Contact: ' + data.name);
            });
            $('#goXero').unbind();
            $('#goXero').on('click', function (event) {
                window.open('https://go.xero.com/Contacts/View/' + contactid, '_blank');
            });
            //var modal = $(this);
            //modal.find('.modal-title').text('Contact: ' + $('#modalContactName').text());

        });

    });

</script>
<script>
    $(document).ready(function () {

        function loadFromXero() {
            //The load button
            console.log('loadFromXero');
            $('#loadfromxerospinner').show();//Load button clicked show spinner
            $.ajax({
                url: "/json.php?endpoint=Invoices&action=refresh",
                type: 'GET',
                dataType: 'json',

                complete: function () {
                    $('#loadfromxerospinner').hide();//Request is complete so hide spinner
                }
            });
        }

        $("#loadfromxero").click(loadFromXero);

        // every minute
        setInterval(function () {
            console.log('setInterval loadFromXero');
            loadFromXero();
        }, 60 * 1000);

    });
</script>
</body>

</html>