<?php
namespace App\Layouts;
?>
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <aside class="app-sidebar">
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
                                     class="btn btn-md btn-block m-3 btn-lime tracking-wide">
                        <strong>Add An Enquiry</strong></a></li>


                <li class="sub-category">
                    <h3>Main</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="index.php"><i
                                class="side-menu__icon fe fe-home"></i><span
                                class="side-menu__label">Dashboard</span></a>
                </li>
                <li class="sub-category">
                    <h3>Widgets</h3>
                </li>
                <li>
                    <a class="side-menu__item" href="widgets.php"><i class="side-menu__icon fe fe-grid"></i><span
                                class="side-menu__label">Widgets</span></a>
                </li>
                <li class="sub-category">
                    <h3>Xero Examples</h3>
                </li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=2">Create one Contact</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=1">Get Organisation Name</a></li>

                <li><a class="side-menu__item" href="/authorizedResource.php?action=100">Enquiries</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=9">Invoices</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=90">Invoices & Payments</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=16">Bad Debts</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=13">Cabins</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=17">Message Templates</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=4">Create multiple contacts and
                        summarizeErrors</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=5">Get Contact List with Filters</a>
                </li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=6">Get JWT Claims</a></li>
                <li><a class="side-menu__item" href="/authorizedResource.php?action=11">Cabin Locations</a></li>
                <li><a class="side-menu__item" href="/vehicleLog.php?action=1">Vehicle Log</a></li>
                <li><a class="side-menu__item" href="/vehicleLog.php?action=2">Add Trip</a></li>
                <li><a class="side-menu__item" href="/index.php?action=logoff">Log Off</a></li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-database"></i><span
                                class="side-menu__label">Components</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">


                        <li class="side-menu-label1"><a href="javascript:void(0)">Components</a></li>
                        <li><a href="cards.php" class="slide-item"> Cards design</a></li>
                        <li><a href="calendar.php" class="slide-item"> Default calendar</a></li>
                        <li><a href="calendar2.php" class="slide-item"> Full calendar</a></li>
                        <li><a href="chat.php" class="slide-item"> Default Chat</a></li>
                        <li><a href="notify.php" class="slide-item"> Notifications</a></li>
                        <li><a href="sweetalert.php" class="slide-item"> Sweet alerts</a></li>
                        <li><a href="rangeslider.php" class="slide-item"> Range slider</a></li>
                        <li><a href="scroll.php" class="slide-item"> Content Scroll bar</a></li>
                        <li><a href="loaders.php" class="slide-item"> Loaders</a></li>
                        <li><a href="counters.php" class="slide-item"> Counters</a></li>
                        <li><a href="rating.php" class="slide-item"> Rating</a></li>
                        <li><a href="timeline.php" class="slide-item"> Timeline</a></li>
                        <li><a href="treeview.php" class="slide-item"> Treeview</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-package"></i><span class="side-menu__label">Elements</span><i
                                class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Elements</a></li>
                        <li><a href="alerts.php" class="slide-item"> Alerts</a></li>
                        <li><a href="buttons.php" class="slide-item"> Buttons</a></li>
                        <li><a href="colors.php" class="slide-item"> Colors</a></li>
                        <li><a href="avatarsquare.php" class="slide-item"> Avatar-Square</a></li>
                        <li><a href="avatar-round.php" class="slide-item"> Avatar-Rounded</a></li>
                        <li><a href="avatar-radius.php" class="slide-item"> Avatar-Radius</a></li>
                        <li><a href="dropdown.php" class="slide-item"> Drop downs</a></li>
                        <li><a href="list.php" class="slide-item"> List</a></li>
                        <li><a href="tags.php" class="slide-item"> Tags</a></li>
                        <li><a href="pagination.php" class="slide-item"> Pagination</a></li>
                        <li><a href="navigation.php" class="slide-item"> Navigation</a></li>
                        <li><a href="typography.php" class="slide-item"> Typography</a></li>
                        <li><a href="breadcrumbs.php" class="slide-item"> Breadcrumbs</a></li>
                        <li><a href="badge.php" class="slide-item"> Badges</a></li>
                        <li><a href="panels.php" class="slide-item"> Panels</a></li>
                        <li><a href="thumbnails.php" class="slide-item"> Thumbnails</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-file"></i><span class="side-menu__label">Advanced
                            Elements</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Advanced Elements</a></li>
                        <li><a href="mediaobject.php" class="slide-item"> Media Object</a></li>
                        <li><a href="accordion.php" class="slide-item"> Accordions</a></li>
                        <li><a href="tabs.php" class="slide-item"> Tabs</a></li>
                        <li><a href="chart.php" class="slide-item"> Charts</a></li>
                        <li><a href="modal.php" class="slide-item"> Modal</a></li>
                        <li><a href="tooltipandpopover.php" class="slide-item"> Tooltip and popover</a></li>
                        <li><a href="progress.php" class="slide-item"> Progress</a></li>
                        <li><a href="carousel.php" class="slide-item"> Carousels</a></li>
                        <li><a href="headers.php" class="slide-item"> Headers</a></li>
                        <li><a href="footers.php" class="slide-item"> Footers</a></li>
                        <li><a href="users-list.php" class="slide-item"> User List</a></li>
                        <li><a href="search.php" class="slide-item">Search</a></li>
                        <li><a href="crypto-currencies.php" class="slide-item"> Crypto-currencies</a></li>
                    </ul>
                </li>
                <li class="sub-category">
                    <h3>Charts & Tables</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-pie-chart"></i><span class="side-menu__label">Charts</span><i
                                class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Charts</a></li>
                        <li><a href="chart-chartist.php" class="slide-item">Chart Js</a></li>
                        <li><a href="chart-flot.php" class="slide-item"> Flot Charts</a></li>
                        <li><a href="chart-echart.php" class="slide-item"> ECharts</a></li>
                        <li><a href="chart-morris.php" class="slide-item"> Morris Charts</a></li>
                        <li><a href="chart-nvd3.php" class="slide-item"> Nvd3 Charts</a></li>
                        <li><a href="charts.php" class="slide-item"> C3 Bar Charts</a></li>
                        <li><a href="chart-line.php" class="slide-item"> C3 Line Charts</a></li>
                        <li><a href="chart-donut.php" class="slide-item"> C3 Donut Charts</a></li>
                        <li><a href="chart-pie.php" class="slide-item"> C3 Pie charts</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-clipboard"></i><span
                                class="side-menu__label">Tables</span><span
                                class="badge bg-secondary side-badge">2</span><i
                                class="angle fa fa-angle-right hor-rightangle"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Tables</a></li>
                        <li><a href="tables.php" class="slide-item">Default table</a></li>
                        <li><a href="datatable.php" class="slide-item"> Data Tables</a></li>
                    </ul>
                </li>
                <li class="sub-category">
                    <h3>Pages</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-layers"></i><span class="side-menu__label">Pages</span><i
                                class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Pages</a></li>
                        <li><a href="profile.php" class="slide-item"> Profile</a></li>
                        <li><a href="editprofile.php" class="slide-item"> Edit Profile</a></li>
                        <li><a href="email.php" class="slide-item"> Mail-Inbox</a></li>
                        <li><a href="emailservices.php" class="slide-item"> Mail-Compose</a></li>
                        <li><a href="gallery.php" class="slide-item"> Gallery</a></li>
                        <li><a href="about.php" class="slide-item"> About Company</a></li>
                        <li><a href="services.php" class="slide-item"> Services</a></li>
                        <li><a href="faq.php" class="slide-item"> FAQS</a></li>
                        <li><a href="terms.php" class="slide-item"> Terms</a></li>
                        <li><a href="invoice.php" class="slide-item"> Invoice</a></li>
                        <li><a href="pricing.php" class="slide-item"> Pricing Tables</a></li>
                        <li><a href="empty.php" class="slide-item"> Empty Page</a></li>
                        <li><a href="construction.php" class="slide-item"> Under Construction</a></li>
                        <li><a href="switcher.php" class="slide-item"> Theme Style</a></li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0);"><span
                                        class="sub-side-menu__label">Blog</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a href="blog.php" class="sub-slide-item">Blog</a></li>
                                <li><a href="blog-details.php" class="sub-slide-item">Blog Details</a></li>
                                <li><a href="blog-post.php" class="sub-slide-item">Blog Post</a></li>
                            </ul>
                        </li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0);"><span
                                        class="sub-side-menu__label">Maps</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a href="maps1.php" class="sub-slide-item">Leaflet Maps</a></li>
                                <li><a href="maps2.php" class="sub-slide-item">Mapel Maps</a></li>
                                <li><a href="maps.php" class="sub-slide-item">Vector Maps</a></li>
                            </ul>
                        </li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0);"><span
                                        class="sub-side-menu__label">E-Commerce</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a href="shop.php" class="sub-slide-item">Shop</a></li>
                                <li><a href="shop-description.php" class="sub-slide-item">Shopping Details</a></li>
                                <li><a href="cart.php" class="sub-slide-item">Shopping Cart</a></li>
                                <li><a href="wishlist.php" class="sub-slide-item">Wishlist</a></li>
                                <li><a href="checkout.php" class="sub-slide-item">Checkout</a></li>
                            </ul>
                        </li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0);"><span
                                        class="sub-side-menu__label">File Manager</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a href="file-manager.php" class="sub-slide-item">File Manager</a></li>
                                <li><a href="filemanager-list.php" class="sub-slide-item">File Manager List</a></li>
                                <li><a href="filemanager-details.php" class="sub-slide-item">File Details</a></li>
                                <li><a href="file-attachments.php" class="sub-slide-item">File Attachments</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="sub-category">
                    <h3>Custom & Error Pages</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-settings"></i><span class="side-menu__label">Custom
                            Pages</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Custom Pages</a></li>
                        <li><a href="login.php" class="slide-item"> Login</a></li>
                        <li><a href="register.php" class="slide-item"> Register</a></li>
                        <li><a href="forgot-password.php" class="slide-item"> Forgot Password</a></li>
                        <li><a href="lockscreen.php" class="slide-item"> Lock screen</a></li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0);"><span
                                        class="sub-side-menu__label">Error Pages</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a class="sub-slide-item" href="400.php">400</a></li>
                                <li><a class="sub-slide-item" href="401.php">401</a></li>
                                <li><a class="sub-slide-item" href="403.php">403</a></li>
                                <li><a class="sub-slide-item" href="404.php">404</a></li>
                                <li><a class="sub-slide-item" href="500.php">500</a></li>
                                <li><a class="sub-slide-item" href="503.php">503</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <i class="side-menu__icon fe fe-sliders"></i>
                        <span class="side-menu__label">Submenus</span><i class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Submenus</a></li>
                        <li><a href="javascript:void(0);" class="slide-item">Level-1</a></li>
                        <li class="sub-slide">
                            <a class="sub-side-menu__item" data-bs-toggle="sub-slide" href="javascript:void(0);"><span
                                        class="sub-side-menu__label">Level-2</span><i
                                        class="sub-angle fa fa-angle-right"></i></a>
                            <ul class="sub-slide-menu">
                                <li><a class="sub-slide-item" href="javascript:void(0);">Level-2.1</a></li>
                                <li><a class="sub-slide-item" href="javascript:void(0);">Level-2.2</a></li>
                                <li class="sub-slide2">
                                    <a class="sub-side-menu__item2" href="javascript:void(0);"
                                       data-bs-toggle="sub-slide2"><span
                                                class="sub-side-menu__label2">Level-2.3</span><i
                                                class="sub-angle2 fa fa-angle-right"></i></a>
                                    <ul class="sub-slide-menu2">
                                        <li><a href="javascript:void(0);" class="sub-slide-item2">Level-2.3.1</a></li>
                                        <li><a href="javascript:void(0);" class="sub-slide-item2">Level-2.3.2</a></li>
                                        <li><a href="javascript:void(0);" class="sub-slide-item2">Level-2.3.3</a></li>
                                    </ul>
                                </li>
                                <li><a class="sub-slide-item" href="javascript:void(0);">Level-2.4</a></li>
                                <li><a class="sub-slide-item" href="javascript:void(0);">Level-2.5</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="sub-category">
                    <h3>Forms & Icons</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-file-text"></i><span
                                class="side-menu__label">Forms</span><span
                                class="badge bg-success side-badge">5</span><i
                                class="angle fa fa-angle-right hor-rightangle"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Forms</a></li>
                        <li><a href="form-elements.php" class="slide-item"> Form Elements</a></li>
                        <li><a href="form-advanced.php" class="slide-item"> Form Advanced</a></li>
                        <li><a href="wysiwyag.php" class="slide-item"> Form Editor</a></li>
                        <li><a href="form-wizard.php" class="slide-item"> Form Wizard</a></li>
                        <li><a href="form-validation.php" class="slide-item"> Form Validation</a></li>
                    </ul>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);"><i
                                class="side-menu__icon fe fe-command"></i><span class="side-menu__label">Icons</span><i
                                class="angle fa fa-angle-right"></i></a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Icons</a></li>
                        <li><a href="icons.php" class="slide-item"> Font Awesome</a></li>
                        <li><a href="icons2.php" class="slide-item"> Material Design Icons</a></li>
                        <li><a href="icons3.php" class="slide-item"> Simple Line Icons</a></li>
                        <li><a href="icons4.php" class="slide-item"> Feather Icons</a></li>
                        <li><a href="icons5.php" class="slide-item"> Ionic Icons</a></li>
                        <li><a href="icons6.php" class="slide-item"> Flag Icons</a></li>
                        <li><a href="icons7.php" class="slide-item"> pe7 Icons</a></li>
                        <li><a href="icons8.php" class="slide-item"> Themify Icons</a></li>
                        <li><a href="icons9.php" class="slide-item">Typicons Icons</a></li>
                        <li><a href="icons10.php" class="slide-item">Weather Icons</a></li>
                        <li><a href="icons11.php" class="slide-item">Bootstrap Icons</a></li>
                    </ul>
                </li>
            </ul>


            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24"
                     height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/>
                </svg>
            </div>
        </div>
    </aside>
</div>
