<?php
declare(strict_types=1);

namespace App;

class Loader
{
    private array $priorities = ['high', 'med', 'low'];
    private array $js = ['head' => [], 'high' => [], 'med' => [], 'low' => []];
    private array $css = ['high' => [], 'med' => [], 'low' => []];
    private array $preconnect = [];
    private array $modals = [];

    public function __construct()
    {
        $this->addBuilderFiles();
        $this->addFontAwesome();
        $this->addGoogleFonts();
        $this->addMiscScripts();
    }

    public function outputJS($where = 'footer'): void
    {
        echo '<!-- L O A D E R   J S -->' . PHP_EOL;

//This allows JavaScript like tinymce to be placed in the header
        $priorities = ($where === 'footer') ? $this->priorities : ['head'];
        $defer = ($where === 'footer') ? 'defer ' : '';// keep space at the end of the word defer

        foreach ($priorities as $priority) {
            if (isset($this->js[$priority]) && count($this->js[$priority])) {
                echo '<!-- ' . $priority . ' -->' . PHP_EOL;
                foreach ($this->js[$priority] as $source) {

                    if (is_array($source)) {
                        $innards = implode(' ', array_map(function ($key, $value) {
                            return "$key='$value'";
                        }, array_keys($source), $source));
                        echo "<script $defer $innards ></script>" . PHP_EOL;
                    } else {
                        echo "<script $defer src='$source'></script>" . PHP_EOL;
                    }
                }
            }
        }
        echo '<!-- /L O A D E R   J S -->' . PHP_EOL;
    }

    public function outputCSS(): void
    {
        $this->outputPreconnect();

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

    private function outputPreconnect(): void
    {
        foreach ($this->preconnect as $preconnect) {
            echo "<link rel='preconnect' href='{$preconnect['href']}' crossorigin='{$preconnect['crossorigin']}' />" . PHP_EOL;
        }
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

    public function addJS(string $src, $priority = 'low'): void
    {
        if (!in_array($priority, $this->priorities)) {
            $priority = 'low';
        }
        $this->js[$priority][] = $src;
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


    private function addCookies(): void
    {
        //<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
        $this->js['med'][] = 'https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js';
    }


    private function addGoogleFonts(): void
    {
        $this->preconnect[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];

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
        $this->js['low'][] = "/assets/JS/sidebar.js";

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

