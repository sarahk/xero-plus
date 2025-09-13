<?php
namespace App\Layouts;
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
                    }
                    ?>
                    <li class="slide">
                        <a href="/authorizedResource.php?action=10"
                           class="btn btn-md btn-success btn-block tracking-wide">
                            <i class="side-menu__icon fa-solid fa-plus"></i>
                            <span class='side-menu__label'><strong>Add An Enquiry</strong></span></a></li>
                    <!-- btn-block m-3 p-2 btn-success -->

                    <li class="sub-category">
                        <h3>Main</h3>
                    </li>

                    <li class="slide active">
                        <a href="/authorizedResource.php" class="side-menu__item active"> <i
                                    class="fe fe-home side-menu__icon"></i>
                            <span class="side-menu__label">Dashboard</span>
                        </a></li>

                    <li class="slide"><a class="side-menu__item" href="/authorizedResource.php?action=100"><i
                                    class="side-menu__icon fe fe-help-circle"></i><span
                                    class="side-menu__label">Enquiries</span></a></li>
                    <?php
                    function simpleMenuItem($action, $icon, $label): string
                    {
                        return fullMenuItem("/authorizedResource.php?action=$action", '', $icon, $label);
                    }

                    function fullMenuItem($url, $id, $icon, $label): string
                    {
                        $id_string = (empty($id)) ? '' : "id='$id'";
                        return "
                            <li class='slide'>
                                <a class='side-menu__item' $id_string href='$url'>
                                    <i class='side-menu__icon $icon'></i>
                                    <span class='side-menu__label'>$label</span>
                                </a>
                            </li>
                            ";
                    }

                    echo simpleMenuItem('90', 'fa-solid fa-file-invoice-dollar', 'Invoices &amp; Payments');
                    ?>

                    <li class="slide has-sub is-expanded">
                        <a class="side-menu__item" data-bs-toggle="slide"
                           href="/authorizedResource.php?action=16"><i
                                    class="side-menu__icon fe fe-alert-circle"></i><span
                                    class="side-menu__label">Outstanding Rents</span><i
                                    class="angle fa fa-angle-right"></i></a>

                        <ul class="slide-menu open">
                            <?php
                            // todo: remove the list-item-style
                            echo simpleMenuItem('16', 'fa-solid fa-bell', 'Reminders');
                            echo simpleMenuItem('168', 'fa-solid fa-phone', 'Management');
                            ?>
                        </ul>
                    </li>

                    <?php
                    echo simpleMenuItem('13', 'fa-solid fa-square', 'Cabins');
                    echo simpleMenuItem('17', 'fa-regular fa-message', 'Message Templates');
                    echo simpleMenuItem('18', 'fa-solid fa-message', 'Messages Sent');
                    echo simpleMenuItem('5', 'fa-solid fa-person', 'Customers');
                    echo simpleMenuItem('11', 'fa-solid fa-map-location-dot', 'Cabin Locations');

                    echo fullMenuItem('/index.php?action=logoff', '', 'fa-solid fa-power-off', 'Log Off');
                    ?>

                    <!--<li class="sub-category">
                        <h3>Incomplete</h3>
                    </li>

                    <li><a class="side-menu__item" href="/vehicleLog.php?action=1">Vehicle Log</a></li>
                    <li><a class="side-menu__item" href="/vehicleLog.php?action=2">Add Trip</a></li>-->

                    <li class="sub-category">
                        <h3 title="Sarah Only">Admin</h3></li>
                    <?php
                    echo simpleMenuItem('6', 'fa-solid fa-triangle-exclamation', 'Get JWT Claims');
                    echo simpleMenuItem('1', 'fa-solid fa-triangle-exclamation', 'Get Organisation');
                    echo simpleMenuItem('9', 'fa-solid fa-triangle-exclamation', 'Invoices');
                    ?>

                    <!-- this one has extra bits -->
                    <li class="slide">
                        <a class="side-menu__item" data-bs-toggle="slide" href="#" id="rebuildMTables"><span
                                    class="side-menu__label">Refresh M Tables</span>
                            <span class="badge bg-success side-badge d-none" id="rebuildSuccess"><i
                                        class="fa-solid fa-check"></i></span>
                            <span class="badge bg-error side-badge d-none" id="rebuildError"><i
                                        class="fa-solid fa-xmark"></i></span>
                        </a>
                    </li>
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
