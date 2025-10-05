<?php
declare(strict_types=1);

namespace App\Views;

use App\classes\Loader;

?>
<!doctype html>
<html lang="en">
<head>
    <title>Cabin King Addons</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <?php
    include __DIR__ . '/header-meta-data.php';

    if (!isset($loader)) $loader = new Loader();
    $loader->outputCSS();
    $loader->outputJS('head');

    //create constants for the xerotenant_id info


    if (!\APP\LOGGEDOUT) {
        ?>
        <script>
            const tenancies = <?php echo TENANCIES;?>;
        </script>
        <?php
    } ?>
    <link rel="manifest" href="/site.webmanifest">
    <style>
        <?php
        if (defined('TENANCIES')):
        foreach (json_decode(TENANCIES, true) as $row):
        $colour = "var(--bs-{$row['colour']})";
        $class = ".{$row['shortname']}";
        echo "
        .card-header$class,
        .modal-header$class {
            border-bottom: 4px solid $colour;
        }
    ";
         endforeach;
         endif;
         ?>
    </style>
</head>

<body class="app sidebar-mini ltr light-mode">

<!-- GLOBAL-LOADER -->
<!--<div id="global-loader">
    <img src="/assets/images/loader.svg" class="loader-img" alt="Loader">
</div>-->
<!-- /GLOBAL-LOADER -->
<div id="loader" class="d-none"><img src="/assets/images/media/loader.svg" alt=""></div>
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
                    <?php include __DIR__ . '/Widgets/alert.php'; ?>
