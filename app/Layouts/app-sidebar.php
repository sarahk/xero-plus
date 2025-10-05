<?php

use App\classes\MenuBuilder;

?>
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <aside class="app-sidebar" style="top: 40px;">
        <?php


        include \App\SITE_ROOT . '/Views/Widgets/branding.php';
        ?>
        <style>
            #sidebar-scroll {
                /* sidebar header is ~40px; adjust to your real header height */
                max-height: calc(100vh - 160px);
            }
        </style>

        <div class="main-sidemenu">
            <div class="main-sidebar" id="sidebar-scroll" data-simplebar>


                <div class="slide-left disabled" id="slide-left">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                         width="24" height="24" viewBox="0 0 24 24">
                        <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"/>
                    </svg>
                </div>
                <ul class="side-menu">
                    <?php
                    if (!\App\LOGGEDOUT) {
                        include \App\SITE_ROOT . '/Views/Widgets/tenancy-picker.php';
                        echo MenuBuilder::buildMenu();
                    }
                    ?>

                </ul>
                <div class="slide-right d-none" id="slide-right">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                         viewBox="0 0 24 24">
                        <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                    </svg>
                </div>

            </div>
        </div>
    </aside>
</div>
