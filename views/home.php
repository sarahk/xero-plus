<?php
?>
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div class=""><h1 class="page-title fw-semibold fs-20 mb-0">Dashboard 01</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard 01</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="ms-auto pageheader-btn">
        <button type="button" class="btn btn-primary btn-wave waves-effect waves-light me-2"><i
                    class="fe fe-plus mx-1 align-middle"></i>Add Account
        </button>
        <button type="button" class="btn btn-success btn-wave waves-effect waves-light"><i
                    class="fe fe-log-in mx-1 align-middle"></i>Export
        </button>
    </div>
</div>
<?php
function stockWidget($row): string
{
    $output = [
        '<div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">',
        '<div class="card custom-card overflow-hidden">',
        '<div class="card-body">'
    ];

    $output[] = stockWidgetRow(
        "<span class='fs-18'>{$row['label']}</span>",
        [
            '<i class="fa-solid fa-right-to-bracket"></i>',
            '<i class="fa-solid fa-right-from-bracket"></i>',
            '<i class="fa-solid fa-inbox"></i>'
        ],
        'black');
    //debug(json_decode(TENANCIES, true));
    foreach ($row['vals'] as $vals) {
        $output[] = stockWidgetRow(
            ucfirst($vals['shortname']),
            $vals['data'],
            $vals['colour']);
    }

    $output[] = '</div></div></div>';

    return implode($output);
}

function stockWidgetRow($label, $vals, $colour): string
{
    return "<div class='row'>
                  <div class='col-6 text-{$colour} border-bottom-{$colour}'>{$label}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[0]}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[1]}</div>
                  <div class='col-2 border-bottom-{$colour}'>{$vals[2]}</div>
      </div>";
}

//debug($stock);
?>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xl-12">
        <div class="row">
            <?php
            foreach ($stock as $row)
                echo stockWidget($row);
            ?>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card custom-card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col"><h6 class="fw-normal fs-14">Total Leads</h6>
                                <h3 class="mb-2 number-font fs-24">56,992</h3>
                                <p class="text-muted mb-0"><span class="text-secondary"> <i
                                                class="ri-arrow-up-s-line bg-secondary text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i> 3%</span>
                                    last month </p></div>
                            <div class="col col-auto mt-2">
                                <div class="counter-icon bg-danger-gradient box-shadow-danger rounded-circle  ms-auto mb-0">
                                    <i class="ri-rocket-line mb-5  "></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card custom-card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col"><h6 class="fw-normal fs-14">Total Profit</h6>
                                <h3 class="mb-2 number-font fs-24">$42,567</h3>
                                <p class="text-muted mb-0"><span class="text-success"> <i
                                                class="ri-arrow-down-s-line bg-primary text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i> 0.5%</span>
                                    last month </p></div>
                            <div class="col col-auto mt-2">
                                <div class="counter-icon bg-secondary-gradient box-shadow-secondary rounded-circle ms-auto mb-0">
                                    <i class="fe fe-dollar-sign  mb-5 "></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card custom-card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col"><h6 class="fw-normal fs-14">Total Cost</h6>
                                <h3 class="mb-2 number-font fs-24">$34,789</h3>
                                <p class="text-muted mb-0"><span class="text-danger"> <i
                                                class="ri-arrow-down-s-line bg-danger text-white rounded-circle fs-13 p-0 fw-semibold align-bottom"></i> 0.2%</span>
                                    last month </p></div>
                            <div class="col col-auto mt-2">
                                <div class="counter-icon bg-success-gradient box-shadow-success rounded-circle  ms-auto mb-0">
                                    <i class="fe fe-briefcase mb-5 "></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
