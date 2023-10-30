<!doctype html>
<html lang="en">
<head>
    <title>Cabin King Addons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
    include_once 'layouts/styles.php';
    //create constants for the xerotenant_id info

    if (!isset($loggedOut) || !$loggedOut) {
        ?>
        <script>
            const tenancies = <?php echo TENANCIES;?>;
        </script>
        <?php
    } ?>
</head>

<body class="app sidebar-mini ltr light-mode">

<!-- GLOBAL-LOADER -->
<!--<div id="global-loader">
    <img src="/assets/images/loader.svg" class="loader-img" alt="Loader">
</div>-->
<!-- /GLOBAL-LOADER -->

<!-- PAGE -->
<div class="page">
    <div class="page-main">

        <!-- APP-HEADER -->
        <?php
        if (SIDE_BAR) {
            include SITE_ROOT . '/layouts/app-header.php';
        }
        ?>
        <!-- /APP-HEADER -->

        <!--APP-SIDEBAR-->
        <?php

        if (SIDE_BAR) {
            include SITE_ROOT . '/layouts/app-sidebar.php';
        }
        ?>

        <!--/APP-SIDEBAR-->

        <!-- APP-CONTENT OPEN -->
        <div class="main-content app-content mt-0">

            <div class="side-app">

                <!-- CONTAINER -->
                <div class="main-container container-fluid">
