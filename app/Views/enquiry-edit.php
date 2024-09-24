<?php
include_once 'Widgets/FormBuilder.php';
?>
<!-- PAGE-HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title">Enquiry</h1>
        <!--        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
            <li class="breadcrumb-item active" aria-current="page">Form-Elements</li>
        </ol>-->
    </div>

</div>
<!-- PAGE-HEADER END -->

<!-- ROW OPEN -->
<?php
function hasIdValue($name, $fld)
{
    if (stristr($name, '_id')) {
        if (!empty($fld)) return true;
    }
    return false;
}

function hasAnyValues($row)
{
    if (is_array($row)) {
        foreach ($row as $name => $fld) {
            if (hasIdValue($name, $fld)) {
                return true;
            }
        }
    }
    return false;
}

function getCount($array)
{
    $count = 0;
    foreach ($array as $k => $row) {
        if (hasAnyValues($row)) $count++;
    }
    if ($count) {
        return "({$count})";
    }
    return '';
}


?>
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <!-- <div class="card-header">
                 <h3 class="card-title">Enquiry</h3>
             </div>-->
            <div class="card-body">
                <div class="card-pay">
                    <!-- Tabs -->
                    <ul class="nav tabs-menu">
                        <li><a href="#tab1" class="active " data-bs-toggle="tab">Enquiry</a></li>
                        <li><a href="#tab2" data-bs-toggle="tab" class="">Notes <?= getCount($data['notes']); ?></a>
                        </li>
                        <li><a href="#tab3" data-bs-toggle="tab"
                               class="">Cabins <?= getCount($data['contracts']); ?></a></li>
                        <li><a href="#tab4" data-bs-toggle="tab" class="">Invoices</a></li>
                    </ul>

                </div>
                <div class="panel-body tabs-menu-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <?php
                            include(SITE_ROOT . '/Widgets/enquiry-enquiry-panel.php'); ?>
                        </div>
                        <div class="tab-pane" id="tab2"></div>
                        <div class="tab-pane" id="tab3"></div>
                        <div class="tab-pane" id="tab4"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- END ROW -->
