<?php
namespace App\Views\Footer;

use App\ExtraFunctions;
use App\Loader;

?>
</div>
</div>
<!--SIDEBAR-RIGHT-->
<?php include 'Layouts/sidebar-right.php'; ?>

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
        include("Views/Modals/$filename");
    }
}
?>

<?php
// scripts not specified by the theme
include 'Layouts/scripts.php';
?>

<!-- SPARKLINE JS-->
<!-- tiny bar charts to use in Datatable for bad debts etc -->
<!--<script src="/assets/JS/jquery.sparkline.min.JS"></script>-->
<!---->
<!--<!-- CHART-CIRCLE JS-->-->
<!--<script src="/assets/JS/circle-progress.min.JS"></script>-->

<!--<!-- CHARTJS CHART JS-->-->
<!--<script src="/assets/plugins/chart/Chart.bundle.JS"></script>-->
<!--<script src="/assets/plugins/chart/utils.JS"></script>-->
<!---->
<!--<!-- PIETY CHART JS-->-->
<!--<script src="/assets/plugins/peitychart/jquery.peity.min.JS"></script>-->
<!--<script src="/assets/plugins/peitychart/peitychart.init.JS"></script>-->
<!---->
<!--<!-- INTERNAL SELECT2 JS -->-->
<!--<script src="/assets/plugins/select2/select2.full.min.JS"></script>-->
<!---->
<!--<!-- ECHART JS-->-->
<!--<script src="/assets/plugins/echarts/echarts.JS"></script>-->

<?php
if (!isset($loader)) {
    $loader = new Loader();
}
$loader->outputJS();
?>


<script type="text/javascript" src="/JS/functions.js"></script>
<script type="text/javascript" src="/JS/menu.js"></script>
<script type="text/javascript" src="/JS/home.js"></script>
<script type="text/javascript" src="/JS/activity.js"></script>
<script type="text/javascript" src="/JS/cabins.js"></script>
<script type="module" src="/JS/combo.js"></script>
<script type="text/javascript" src="/JS/contacts.js"></script>
<script type="text/javascript" src="/JS/contracts.js"></script>
<script type="text/javascript" src="/JS/enquiry.js"></script>
<script type="text/javascript" src="/JS/invoices.js"></script>
<script type="module" src="/JS/bad_debts.js"></script>
<script type="text/javascript" src="/JS/templates.js"></script>
<script type="text/javascript" src="/JS/tasks.js"></script>
<script type="text/javascript" src="/JS/vehicles.js"></script>
<script type="module" src="/JS/widgets.js"></script>
<script type="module" src="/JS/autorun.js"></script>

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
    // todo - is this obsolete?
    $('input[name="dates"]').mouseup(getInvoiceRedraw);

    function getInvoiceRedraw() {
        let getInvoiceTable = $('#getInvoiceTable').DataTable();

        setTimeout(function () {
            getInvoiceTable.draw(false);
        }, 100);
    }
</script>
</body>

</html>
