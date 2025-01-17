<?php
declare(strict_types=1);

namespace App;

class Loader
{
    protected array $js = [];
    protected array $css = [];

    public function __construct()
    {
        $this->addDataTables();
        $this->addFontAwesome();
        $this->addCookies();
        $this->addPerfectScrollbar();
        $this->addSweetAlert();
        $this->addGoogleFonts();
    }

    public function outputJS(): void
    {
        foreach ($this->js as $script) {
            echo '<script src="' . htmlspecialchars($script, ENT_QUOTES, 'UTF-8') . '"></script>' . PHP_EOL;
        }
    }

    public function outputCSS(): void
    {
        foreach ($this->css as $style) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
        }
    }

    public function addApexCharts(): void
    {
        $this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.js';
        $this->css[] = 'https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.css';
    }

    private function addDataTables(): void
    {
        $this->js[] = 'https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-html5-3.1.1/b-print-3.1.1/fh-4.0.1/r-3.0.2/sl-2.0.5/sr-1.4.1/datatables.min.js';
        $this->css[] = 'https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-html5-3.1.1/b-print-3.1.1/fh-4.0.1/r-3.0.2/sl-2.0.5/sr-1.4.1/datatables.min.css';

        // todo - check that these are up-to-date and used

        $this->js[] = "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js";
        $this->js[] = "https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js";
        $this->js[] = "https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-2.1.4/b-3.1.1/b-html5-3.1.1/b-print-3.1.1/fh-4.0.1/r-3.0.2/sl-2.0.5/sr-1.4.1/datatables.min.js";


    }

    private function addFontAwesome(): void
    {
        $this->css[] = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css';
    }

    public function addOwlCarousel(): void
    {
        $this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js';
        $this->css[] = 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css';
    }

    public function addSlickSlider(): void
    {
        $this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js';
        $this->css[] = 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css';
        //cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css
        //cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js
    }

    private function addCookies(): void
    {
        //<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
        $this->js[] = 'https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js';
    }

    private function addPerfectScrollbar(): void
    {
        $this->js[] = 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.js';
        // Google Fonts -->
        $this->css[] = "https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap";
        //<!-- MDB -->
        $this->css[] = "https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.css";
    }

    private function addSweetAlert(): void
    {
        $this->css[] = "https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css";
    }

    /**
     * Adds pretty tooltips - was called Popper
     * https://floating-ui.com/docs/getting-started
     * @return void
     */
    public function addPopper(): void
    {
        $this->js[] = "https://cdn.jsdelivr.net/npm/@floating-ui/core@1.6.8";
        $this->js[] = "https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.12";
    }

    private function addGoogleFonts(): void
    {
        $this->css[] = "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap";
    }
}

