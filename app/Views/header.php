<?php
namespace App\Views;
?>
<!doctype html>
<html lang="en">
<head>
    <title>Cabin King Addons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
    include_once 'Layouts/styles.php';

    //create constants for the xerotenant_id info

    if (!\APP\LOGGEDOUT) {
        ?>
        <script>
            const tenancies = <?php echo TENANCIES;?>;
        </script>
        <?php
    } ?>

    <script src="https://cdn.tiny.cloud/1/358b52j7udus5vp44svtm76psq44rezjrxzt0m3gwfosew62/tinymce/7/tinymce.min.js"
            referrerpolicy="origin"></script>

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
        if (\App\SIDE_BAR) {
            include \App\SITE_ROOT . '/Layouts/app-header.php';
        }
        ?>
        <!-- /APP-HEADER -->

        <!--APP-SIDEBAR-->
        <?php

        if (\App\SIDE_BAR) {
            include \App\SITE_ROOT . '/Layouts/app-sidebar.php';
        }
        ?>

        <!--/APP-SIDEBAR-->

        <!-- APP-CONTENT OPEN -->
        <div class="main-content app-content mt-0">

            <div class="side-app">

                <!-- CONTAINER -->
                <div class="main-container container-fluid">
