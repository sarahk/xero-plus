<?php
declare(strict_types=1);

namespace App;

class Loader
{
    private array $priorities = ['high', 'med', 'low'];
    private array $js = ['high' => [], 'med' => [], 'low' => []];
    private array $css = ['high' => [], 'med' => [], 'low' => []];
    private array $modals = [];

    public function __construct()
    {
        $this->addBuilderFiles();
//        $this->addJquery();
//        $this->addBootstrap();
//        $this->addJqueryUI();
//        $this->addDataTables();
        $this->addFontAwesome();
//        $this->addCookies();
        // todo find out why perfect scrollbar makes datatable buttons look good.
        // todo not using perfect scrollbar anywhere
        //$this->addPerfectScrollbar();
        //
        //$this->addSweetAlert();
        $this->addGoogleFonts();
        //$this->addSimpleBar();

        $this->addMiscScripts();
    }

    public function outputJS($where = 'footer'): void
    {
        echo '<!-- L O A D E R   J S -->' . PHP_EOL;

        if ($where === 'footer') {
            $priorities = $this->priorities;
        } else {
            //This allows JavaScript like tinymce to be placed in the header
            $priorities = ['head'];
        }

        foreach ($priorities as $priority) {
            if (isset($this->js[$priority]) && count($this->js[$priority])) {
                echo '<!-- ' . $priority . ' -->' . PHP_EOL;
                foreach ($this->js[$priority] as $source) {

                    if (is_array($source)) {
                        $innards = implode(' ', array_map(function ($key, $value) {
                            return "$key='$value'";
                        }, array_keys($source), $source));
                        echo '<script defer ' . $innards . '></script>' . PHP_EOL;
                    } else {
                        echo "<script defer src='$source'></script>" . PHP_EOL;
                    }
                }
            }
        }
        echo '<!-- /L O A D E R   J S -->' . PHP_EOL;
    }

    public function outputCSS(): void
    {
        echo '<!-- L O A D E R   C S S -->' . PHP_EOL;

        foreach ($this->priorities as $priority) {
            if (count($this->css[$priority])) {
                foreach ($this->css[$priority] as $source) {
                    echo "<link rel='stylesheet' href='$source'>" . PHP_EOL;
                }
            }
        }
        echo '<!-- /L O A D E R   C S S -->' . PHP_EOL;
    }

    public function outputModals(): void
    {
        echo '<!-- L O A D E R    M O D A L S -->' . PHP_EOL;
        if (count($this->modals)) {
            foreach ($this->modals as $filename) {
                include("Views/Modals/$filename");
            }
        }
        echo '<!-- /L O A D E R    M O D A L S -->' . PHP_EOL;
    }

    public function addModal(string $modal): void
    {
        // no hierarchy with modals
        $this->modals[] = $modal;
    }

    /*
     * to regenerate these files run
     * php build/build-assets.php
     * from the project root
     */
    private function addBuilderFiles(): void
    {
        $this->js['high'][] = "/build/app.vendor.min.js";
        $this->css['high'][] = "/build/app.vendor.min.css";
    }

    public function addApexCharts(): void
    {
        $this->js['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.js';
        $this->css['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.css';
    }

    private function addDataTables(): void
    {
        $this->css['med'][] = 'https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css';
        $this->js['med'][] = 'https://cdn.datatables.net/2.3.4/js/dataTables.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js';

        // add buttons
        $this->css['med'][] = 'https://cdn.datatables.net/buttons/3.2.5/css/buttons.bootstrap5.min.css';
        $this->js['med'][] = 'https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/buttons/3.2.5/js/buttons.bootstrap5.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/buttons/3.2.5/js/buttons.colVis.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js';

        // responsive
        $this->css['med'][] = 'https://cdn.datatables.net/responsive/3.0.6/css/responsive.bootstrap5.min.css';
        $this->js['med'][] = 'https://cdn.datatables.net/responsive/3.0.6/js/dataTables.responsive.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/responsive/3.0.6/js/responsive.bootstrap5.min.js';

        // select
        $this->css['med'][] = 'https://cdn.datatables.net/select/3.1.0/css/select.bootstrap5.min.css';
        $this->js['med'][] = 'https://cdn.datatables.net/select/3.1.0/js/dataTables.select.min.js';
        $this->js['med'][] = 'https://cdn.datatables.net/select/3.1.0/js/select.bootstrap5.min.js';
//    $this->js['med'][] = 'https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-html5-3.1.1/b-print-3.1.1/fh-4.0.1/r-3.0.2/sl-2.0.5/sr-1.4.1/datatables.min.js';
//        $this->css['med'][] = 'https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-html5-3.1.1/b-print-3.1.1/fh-4.0.1/r-3.0.2/sl-2.0.5/sr-1.4.1/datatables.min.css';

        // todo - check that these are up-to-date and used

        $this->js['low'][] = "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js";
        $this->js['low'][] = "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js";

    }

    private function addFontAwesome(): void
    {
        $this->css['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css';
    }

    public function addOwlCarousel(): void
    {
        $this->js['low'][] = 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js';
        $this->css['low'][] = 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css';
    }

    public function addSlickSlider(): void
    {
        $this->js['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js';
        $this->css['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css';

        //cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css
        //cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js
    }

    public function addSimpleBar(): void
    {
        $this->js['med'][] = 'https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js';
        $this->css['med'][] = 'https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.css';
    }


    private function addCookies(): void
    {
        //<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
        $this->js['med'][] = 'https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js';
    }

    /* I'm using SimpleBar */
    private function addPerfectScrollbar(): void
    {
        $this->js['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.js';
        // Google Fonts -->
        $this->css['med'][] = "https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap";
        //<!-- MDB -->
        $this->css['med'][] = "https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.css";
    }

    private function addSweetAlert(): void
    {
        $this->css['med'][] = "https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css";
    }

    private function addJquery(): void
    {
        $this->js['high'][] = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js';
    }

    private function addJqueryUI(): void
    {
        $this->js['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js';
        $this->css['med'][] = 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/themes/base/jquery-ui.min.css';
    }

    /**
     * Adds pretty tooltips - was called Popper
     * https://floating-ui.com/docs/getting-started
     * @return void
     */
    private function addBootstrap(): void
    {
        $this->js['high'][] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/js/bootstrap.min.js';
        $this->css['high'][] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap.min.css';
        $this->css['high'][] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap-grid.min.css';
        $this->css['high'][] = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.8/css/bootstrap-reboot.min.css';

        //$this->js['high'][] = 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/cjs/popper-lite.min.js';


        $this->js['low'][] = "https://cdn.jsdelivr.net/npm/@floating-ui/core@1.6.8";
        $this->js['low'][] = "https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.12";
    }

    private function addGoogleFonts(): void
    {
        $this->css['low'][] = "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap";
    }

    public function addTinyMCE(): void
    {
        $this->js['head'][] = [
            'src' => 'https://cdn.tiny.cloud/1/358b52j7udus5vp44svtm76psq44rezjrxzt0m3gwfosew62/tinymce/7/tinymce.min.js',
            'referrerpolicy' => 'origin'
        ];
    }

    private function addMiscScripts(): void
    {
        $this->js['low'][] = "/assets/js/slidemenu/slide_menu.js";

//<!-- STICKY JS -->
        $this->js['low'][] = "/JS/plugins/sticky.js";

//<!-- COLOR THEME JS -->
        //$this->js['low'][] = "/assets/js/themeColors.js";

//<!-- CUSTOM JS, handles scrolling -->
        $this->js['low'][] = "/assets/js/custom.js";

//<!-- SWITCHER STYLES JS -->
        //$this->js['low'][] = "/assets/js/switcher-styles.js";

//<!-- CUSTOM JS -->
        $this->js['low'][] = "/JS/plugins/custom.js";

        //<!-- STYLE CSS -->
        $this->css['low'][] = "/assets/css/style.css";
        $this->css['low'][] = "/assets/css/plugins.css";

//<!--- FONT-ICONS CSS -->
        // are we using all of these?
        $this->css['low'][] = "/assets/css/icons.css";
        $this->css['low'][] = "/css/ckm-styles.css";

    }

    public function addGoogleMaps(): void
    {
        $this->js['low'][] = "https://maps.googleapis.com/maps/api/js?key=AIzaSyB32Z6abVU4CzDmYdxfGX1kW4H6slcLjUw&libraries=places";
    }

}

