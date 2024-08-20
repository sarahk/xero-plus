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
<?php
if (isset($modals) && is_array($modals) && count($modals)) {
    foreach ($modals as $filename) {
        include("views/modals/$filename");
    }
}
?>

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

<!-- Proper Font Awesome -->
<script src="https://kit.fontawesome.com/816ff03c37.js" crossorigin="anonymous"></script>

<!-- OWL CAROUSEL -->
<script src="/assets/plugins/owl-carousel/owl.carousel.min.js"></script>
<!-- SLICK SLIDER -->
<script type="text/javascript" src="/assets/plugins/slick/slick.min.js"></script>

<!-- SWEET ALERT JS -->
<script src="/node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

<!-- INDEX JS -->
<!--<script src="/assets/js/index1.js"></script>-->

<!-- COOKIES JS -->
<script src="/node_modules/js-cookie/dist/js.cookie.min.js"></script>

<script type="text/javascript" src="/js/functions.js"></script>
<script type="text/javascript" src="/js/home.js"></script>
<script type="text/javascript" src="/js/cabins.js"></script>
<script type="text/javascript" src="/js/contacts.js"></script>
<script type="text/javascript" src="/js/enquiry.js"></script>
<script type="text/javascript" src="/js/invoices.js"></script>
<script type="text/javascript" src="/js/tasks.js"></script>
<script type="text/javascript" src="/js/vehicles.js"></script>

<?php
$action = intval($_GET['action'] ?? 0);
// switch will be expanded over time
switch ($action) {
    case 10:
        include_once 'addins/footer-edit-js.php';
        break;
}
?>


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


        $('input[name="dates"]').mouseup(getInvoiceRedraw);

        function getInvoiceRedraw() {
            let getInvoiceTable = $('#getInvoiceTable').DataTable();

            setTimeout(function () {
                getInvoiceTable.draw(false);
            }, 100);
        }


        $('#contactSingle').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget) // Button that triggered the modal
            let contactid = button.data('contactid') // Extract info from data-* attributes
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
            $('#goXero').on('click', function () {
                window.open('https://go.xero.com/Contacts/View/' + contactid, '_blank');
            });
            //var modal = $(this);
            //modal.find('.modal-title').text('Contact: ' + $('#modalContactName').text());

        });
        if ($('.owl-carousel').length) {
            let homeScreen = getScreenBreakpoint();
            let homeCarousel = (homeScreen === 'xs' || homeScreen === 'sm') ? 1 : 5;
//items: homeCarousel,
            $(".owl-carousel").owlCarousel({
                URLhashListener: true,
                margin: 5,
                startPosition: '#today',
                responsiveClass: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    400: {
                        items: 2
                    },
                    740: {
                        items: 5
                    },
                    940: {
                        items: 5
                    }
                },
                nav: true,
                stagePadding: 50,
            });
        }
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
                    }],
            });
        }

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


        // every minute
        setInterval(function () {
            console.log('setInterval');
            const tenancies = ['auckland', 'waikato', 'bop'];
            for (let i = 0; i < tenancies.length; i++) {
                if (Cookies.get(tenancies[i]) === 'true') {
                    //console.log('setInterval loadInvoicesFromXero: ' + tenancies[i]);
                    loadInvoicesFromXero(tenancies[i]);
                    //loadContactsFromXero(tenancies[i]);
                }
            }
        }, 60 * 1000 * 100);

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

    });
</script>
</body>

</html>
