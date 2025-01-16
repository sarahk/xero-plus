<?php
namespace App\Layouts;
?>
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <aside class="app-sidebar ps ps--active-y" style="top: 40px;">
        <div class="side-header">
            <a class="header-brand1" href="index.php">
                <img src="/images/cabinkingmanagement-logo-small.webp" class="header-brand-img desktop-logo" alt="logo">
                <img src="/images/cabinkingmanagement-logo-small.webp" class="header-brand-img toggle-logo" alt="logo">
                <img src="/images/cabinkingmanagement-logo-small.webp" class="header-brand-img light-logo" alt="logo">
                <img src="/images/cabinkingmanagement-logo-small.webp" class="header-brand-img light-logo1" alt="logo">
            </a>
            <!-- LOGO -->
        </div>
        <div class="main-sidemenu">
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
                <li class="slide"><a href="/authorizedResource.php?action=10"
                                     class="btn btn-md btn-block m-3 mr-3 btn-lime tracking-wide">
                        <strong>Add An Enquiry</strong></a></li>


                <li class="sub-category">
                    <h3>Main</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="/authorizedResource.php"><i
                                class="side-menu__icon fe fe-home"></i><span
                                class="side-menu__label">Dashboard</span></a>
                </li>


                <li><a class="side-menu__item" href="/authorizedResource.php?action=100"><i
                                class="side-menu__icon fe fe-help-circle"></i><span
                                class="side-menu__label">Enquiries</span></a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=90">Invoices & Payments</a></li>


                <li class="slide is-expanded">
                    <a class="side-menu__item" data-bs-toggle="slide" href="/authorizedResource.php?action=16"><i
                                class="side-menu__icon fe fe-alert-circle"></i><span
                                class="side-menu__label">Outstanding Rents</span><i class="angle fa fa-angle-right"></i></a>

                    <ul class="slide-menu open" style="display: block;">
                        <li><a href="/authorizedResource.php?action=16" class="slide-item">
                                Reminders</a>
                        </li>
                        <li><a href="/authorizedResource.php?action=160" class="slide-item">
                                Management</a>
                        </li>
                    </ul>
                </li>


                <li><a class="side-menu__item" href="/authorizedResource.php?action=13">Cabins</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=17">Message Templates</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=18">Messages Sent</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=5">Customers</a>
                </li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=11">Cabin Locations</a></li>
                <li><a class="side-menu__item" href="/index.php?action=logoff">Log Off</a></li>
                <!--<li class="sub-category">
                    <h3>Incomplete</h3>
                </li>

                <li><a class="side-menu__item" href="/vehicleLog.php?action=1">Vehicle Log</a></li>
                <li><a class="side-menu__item" href="/vehicleLog.php?action=2">Add Trip</a></li>-->

                <li class="sub-category">
                    <h3 title="Sarah Only">Admin</h3></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=6">Get JWT Claims</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=1">Get Organisation Name</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=9">Invoices</a></li>

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
        </div>
    </aside>
</div>
