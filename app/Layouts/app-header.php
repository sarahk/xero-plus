<div class="app-header header sticky">
    <div class="container-fluid main-container">
        <div class="d-flex align-items-center">
            <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar"
               href="javascript:void(0);"></a>
            <div class="responsive-logo">
                <a href="/index.php" class="header-logo">
                    <img src="/images/cabinkingmanagement-logo-small.webp" class="mobile-logo logo-1" alt="CKM logo">
                    <img src="/images/cabinkingmanagement-logo-small.webp" class="mobile-logo dark-logo-1"
                         alt="CKM logo">
                </a>
            </div>
            <!-- sidebar-toggle-->
            <a class="logo-horizontal " href="/index.php">
                <img src="/images/cabinkingmanagement-logo-small.webp" class="header-brand-img desktop-logo" alt="logo">
                <img src="/images/cabinkingmanagement-logo-small.webp" class="header-brand-img light-logo1"
                     alt="logo">
            </a>
            <!-- LOGO -->
            <div class="main-header-center ms-3 d-none d-lg-block">
                <form action="/page.php" method="get">
                    <input type="hidden" name="action" value="5">
                    <?php $search_term = $_GET['search_term'] ?? ''; ?>
                    <input class="form-control" name="search_term" placeholder="Search for anything..." type="search"
                           value="<?= $search_term; ?>">
                    <button class="btn shadow-none"><i class="fa fa-search" aria-hidden="true"></i></button>
                </form>
            </div>
            <div class="d-flex order-lg-2 ms-auto header-right-icons">
                <!-- SEARCH -->
                <button class="navbar-toggler navresponsive-toggler d-lg-none ms-auto" type="button"
                        data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                        aria-controls="navbarSupportedContent-4" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon fe fe-more-vertical text-dark"></span>
                </button>
                <div class="navbar navbar-collapse responsive-navbar p-0 shadow-none">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                        <div class="d-flex order-lg-2">
                            <div class="dropdown d-block d-lg-none">
                                <a href="javascript:void(0);" class="nav-link icon" data-bs-toggle="dropdown">
                                    <i class="fe fe-search"></i>
                                </a>
                                <div class="dropdown-menu header-search dropdown-menu-start">
                                    <div class="input-group w-100 p-2">
                                        <input type="text" class="form-control" placeholder="Search....">
                                        <div class="input-group-text btn btn-primary">
                                            <i class="fa fa-search" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Theme-Layout -->
                            <div class="dropdown d-md-flex">
                                <a class="nav-link icon full-screen-link nav-link-bg">
                                    <i class="fe fe-minimize fullscreen-button"></i>
                                </a>
                            </div>
                            <!-- FULL-SCREEN removed notifications-messages.php-->
                            <?php
                            $loggedOut = !defined('\App\LOGGEDOUT') || (bool)\App\LOGGEDOUT;

                            if (!$loggedOut): ?>
                                <div class="dropdown d-md-flex header-settings">
                                    <a href="javascript:void(0);" class="nav-link icon " data-bs-toggle="sidebar-right"
                                       data-target=".sidebar-right">
                                        <i class="fe fe-menu"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <!-- SIDE-MENU -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
