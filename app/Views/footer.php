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
                Copyright © <span id="year"><?php echo date('Y'); ?></span> <a href="https://cabinking.nz/">Cabin King
                    NZ Ltd</a>.

            </div>
        </div>
    </div>
</footer>
</div>
</div>
</div>
</div>
<!-- BACK-TO-TOP -->
<!--<a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>-->

<?php
//include 'widgets/scroll_to_top.php';
?>

<div id="responsive-overlay"></div>
<?php
if (!isset($loader)) {
    $loader = new Loader();
}
$loader->outputModals();

//todo remove code below, should be handled by loader
//if (isset($modals) && is_array($modals) && count($modals)) {
//    foreach ($modals as $filename) {
//        include("Views/Modals/$filename");
//    }
//}
?>

<?php

//$loader->outputJS();

// scripts not specified by the theme
//include __DIR__ . '/../Layouts/scripts.php';

?>


<script defer type="text/javascript" src="/JS/functions.js"></script>
<script defer type="text/javascript" src="/JS/menu.js"></script>
<script defer type="text/javascript" src="/JS/home.js"></script>
<script defer type="text/javascript" src="/JS/activity.js"></script>
<script defer type="text/javascript" src="/JS/cabins.js"></script>
<script defer type="module" src="/JS/combo.js"></script>
<script defer type="text/javascript" src="/JS/contacts.js"></script>
<script defer type="text/javascript" src="/JS/contracts.js"></script>
<script defer type="text/javascript" src="/JS/enquiry.js"></script>
<script defer type="text/javascript" src="/JS/invoices.js"></script>
<script defer type="module" src="/JS/bad_debts.js"></script>
<script defer type="text/javascript" src="/JS/templates.js"></script>
<script defer type="text/javascript" src="/JS/tasks.js"></script>
<!--<script type="text/javascript" src="/JS/vehicles.js"></script>-->
<script defer type="module" src="/JS/widgets.js"></script>

<?php if (!\App\LOGGEDOUT) {
    echo '<script type="module" src="/JS/autorun.js"></script>';
}
?>

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
    // $('input[name="dates"]').mouseup(getInvoiceRedraw);
    //
    // function getInvoiceRedraw() {
    //     let getInvoiceTable = $('#getInvoiceTable').DataTable();
    //
    //     setTimeout(function () {
    //         getInvoiceTable.draw(false);
    //     }, 100);
    // }
</script>
</body>

</html>
