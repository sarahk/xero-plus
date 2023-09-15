<!doctype html>
<html lang="en">
<head>
    <title>Cabin King Addons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.1/b-print-1.6.1/r-2.2.3/sp-1.0.1/sl-1.3.1/datatables.min.css"/>
    <?php include 'layouts/styles.php'; ?>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <?php
    //create constants for the xerotenant_id info
    if (!isset($loggedOut) || !$loggedOut){
    require_once 'JsonClass.php';
    $json1 = new JsonClass();
    //echo $json1->getOrganisationList(true);
    define("TENANCIES", $json1->getOrganisationList(true));
    ?>
    <script>
        const tenancies = <?php echo json_encode(TENANCIES);?>;
    </script>
    <?php
    } ?>
</head>

<body class="app sidebar-mini ltr light-mode">

<!-- GLOBAL-LOADER -->
<div id="global-loader">
    <img src="/assets/images/loader.svg" class="loader-img" alt="Loader">
</div>
<!-- /GLOBAL-LOADER -->

<!-- PAGE -->
<div class="page">
    <div class="page-main">

        <!-- APP-HEADER -->
        <?php
        if (!isset($nosidebar) || !$nosidebar) {
            include 'layouts/app-header.php';
        }
        ?>

        <!-- /APP-HEADER -->

        <!--APP-SIDEBAR-->
        <?php
        if (!isset($nosidebar) || !$nosidebar) {
            include 'layouts/app-sidebar.php';
        }
        ?>

        <!--/APP-SIDEBAR-->

        <!-- APP-CONTENT OPEN -->
        <div class="main-content app-content mt-0">

            <div class="side-app">

                <!-- CONTAINER -->
                <div class="main-container container-fluid">