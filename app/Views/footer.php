<?php
namespace App\Views\Footer;

use App\Classes\ExtraFunctions;
use App\Classes\Loader;

?>
</div>
</div>
<!--SIDEBAR-RIGHT-->
<?php
$loggedOut = !defined('\App\LOGGEDOUT') || (bool)\App\LOGGEDOUT;

if (!$loggedOut) {
    include 'Layouts/sidebar-right.php';
}
?>
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

$loader->outputJS('footer');
$loader->outputJSModule();


if (!$loggedOut) {
    echo '<!-- logged in, output modals -->';
    $loader->outputModals();
} else {
    echo '<!-- logged out, no modals -->';
}
?>

<script defer type="text/javascript" src="/JS/functions.js"></script>
<script defer type="text/javascript" src="/JS/menu.js"></script>
<script defer type="text/javascript" src="/JS/home.js"></script>
<script defer type="text/javascript" src="/JS/activity.js"></script>
<script defer type="module" src="/JS/combo.js"></script>
<script defer type="text/javascript" src="/JS/contacts.js"></script>
<script defer type="text/javascript" src="/JS/enquiry.js"></script>
<script defer type="text/javascript" src="/JS/invoices.js"></script>
<script defer type="module" src="/JS/bad_debts.js"></script>
<script defer type="text/javascript" src="/JS/templates.js"></script>
<!--<script type="text/javascript" src="/JS/vehicles.js"></script>-->
<script defer type="module" src="/JS/widgets.js"></script>
<script defer type="text/javascript" src="/assets/js/custom.js"></script>
<script defer type="module" src="/JS/fixes.js"></script>

<?php if (!\App\LOGGEDOUT) {
    // todo autorun is throwing api errors
    //echo '<script type="module" src="/JS/autorun.js"></script>';
}
?>

</body>
</html>
