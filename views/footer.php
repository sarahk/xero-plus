</div>
</div>
<!--SIDEBAR-RIGHT-->
<?php include 'layouts/sidebar-right.php'; ?>

<!--/SIDEBAR-RIGHT-->


<footer class="footer">
    <div class="container">
        <div class="row align-items-center flex-row-reverse">
            <div class="col-md-12 col-sm-12 text-center">
                Copyright © <span id="year"></span> <a href="javascript:void(0);">Zanex</a>. Designed with <span
                        class="fa fa-heart text-danger"></span> by <a href="javascript:void(0);"> Spruko </a> All rights
                reserved
            </div>
        </div>
    </div>
</footer>
</div>

<?php include 'layouts/scripts.php'; ?>

<!-- SPARKLINE JS-->
<script src="/assets/js/jquery.sparkline.min.js"></script>

<!-- CHART-CIRCLE JS-->
<script src="/assets/js/circle-progress.min.js"></script>

<!-- CHARTJS CHART JS-->
<script src="/assets/plugins/chart/Chart.bundle.js"></script>
<script src="/assets/plugins/chart/utils.js"></script>

<!-- PIETY CHART JS-->
<script src="/assets/plugins/peitychart/jquery.peity.min.js"></script>
<script src="/assets/plugins/peitychart/peitychart.init.js"></script>

<!-- INTERNAL SELECT2 JS -->
<script src="/assets/plugins/select2/select2.full.min.js"></script>

<!-- INTERNAL DATA TABLES JS -->
<script src="/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
<script src="/assets/plugins/datatable/dataTables.responsive.min.js"></script>

<!-- ECHART JS-->
<script src="/assets/plugins/echarts/echarts.js"></script>

<!-- APEXCHART JS -->
<script src="/assets/js/apexcharts.js"></script>

<!-- INDEX JS -->
<!--<script src="/assets/js/index1.js"></script>-->

<!-- COOKIES JS -->
<script src="/node_modules/js-cookie/dist/js.cookie.min.js"></script>

<script type="text/javascript" src="/js/invoices.js"></script>
<script type="text/javascript" src="/js/vehicles.js"></script>

<?php if (intval($_GET['action'] ?? 0) === 10):
    include_once 'addins/footer-edit-js.php' ?>

    <script type="text/javascript" src="/js/enquiry.js"></script>
<?php endif; ?>

<script type="text/javascript">
    <?php /*
    document.addEventListener("DOMContentLoaded", function () {
    //    loadGet("<?php echo implode('","', $loadGet); ?>");
    //});  */ ?>

    <?php
    //  if (file_exists("js/{$endpoint}{$action}.js")):
    //      echo "jQuery.getScript('/js/{$endpoint}{$action}.js');";
    //  endif;
    ?>

    $(document).ready(function () {

        var tContacts = $('#tContacts').DataTable({
            ajax: {
                "url": "/json.php?endpoint=Contacts&action=Read"
            },
            processing: true,
            serverSide: true,
            columns: [
                {data: "checkbox", orderable: false},
                {data: "theyowe", className: 'dt-right'},
                {data: "name"},
                {data: "phone", orderable: false},
                {data: "email"},
                {data: "address", orderable: false},
                {data: "action", orderable: false}
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
                {data: "number"},
                {data: "style"},
                {data: "status"},
                {data: "contact",},
                {data: "paintgrey"},
                {data: "paintinside"},
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

        // refresh data from Xero
        // but only for one tenancy
        function loadInvoicesFromXero(tenancy) {
            //The load button
            console.log('loadInvoicesFromXero: ' + tenancy);
            $('#loadfromxerospinner').show();//Load button clicked show spinner
            $.ajax({
                url: "/xero.php?endpoint=Invoices&action=refresh&tenancy=" + tenancy,
                type: 'GET',
                dataType: 'json',

                complete: function () {
                    $('#loadfromxerospinner').hide();//Request is complete so hide spinner
                }
            });
        }

        function loadContactsFromXero(tenancy) {
            console.log('loadContactsFromXero: ' + tenancy);
            $.ajax({
                url: "/xero.php?endpoint=Contacts&action=refresh&tenancy=" + tenancy,
                type: 'GET',
                dataType: 'json',
            });
        }

        //$("#loadfromxero").click(loadFromXero);


        // every minute
        setInterval(function () {
            const tenancies = ['auckland', 'waikato', 'bop'];
            for (let i = 0; i < tenancies.length; i++) {
                if (Cookies.get(tenancies[i]) === 'true') {
                    console.log('setInterval loadInvoicesFromXero: ' + tenancies[i]);
                    //loadInvoicesFromXero(tenancies[i]);
                    //loadContactsFromXero(tenancies[i]);
                }
            }
        }, 60 * 1000);

// save the working with choices
// https://github.com/js-cookie/js-cookie/tree/main
        $('#tenancy-auckland').change(function () {
            Cookies.set('auckland', $('#tenancy-auckland')[0].checked);
            console.log('auckland cookie');
        });
        $('#tenancy-waikato').change(function () {
            Cookies.set('waikato', $('#tenancy-waikato')[0].checked);
            console.log('waikato cookie');
        });
        $('#tenancy-bop').change(function () {
            Cookies.set('bop', $('#tenancy-bop')[0].checked);
            console.log('bop cookie');
        });

// set checkboxes to how they were last time
        // this should be set up in TenancyModel
        /*   if (Cookies.get('auckland') === 'true') {
               $("#tenancy-auckland").prop("checked", true);
           }
           if (Cookies.get('waikato') === 'true') {
               $("#tenancy-waikato").prop("checked", true);
           }
           if (Cookies.get('bop') === 'true') {
               $("#tenancy-bop").prop("checked", true);
           }*/
    });
</script>
</body>

</html>
