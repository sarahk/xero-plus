<?php
?>
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div class=""><h1 class="page-title fw-semibold fs-20 mb-0">Dashboard 01</h1>
        <div class="">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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


//debug($stock);
?>

<?php include SITE_ROOT . '/widgets/slick-stock.php'; ?>

<div class="row">
    <?php include SITE_ROOT . '/widgets/home-tasks.php'; ?>
</div>
