#!/usr/bin/env php
<?php
declare(strict_types=1);
// run with
// php build/build-assets.php
//
// project root
$root = dirname(__DIR__);
$publicBuild = $root . '/app/build';

require $root. '/vendor/autoload.php';

// Ensure output dir
if (!is_dir($publicBuild) && !mkdir($publicBuild, 0775, true) && !is_dir($publicBuild)) {
    fwrite(STDERR, "Cannot create $publicBuild\n");
    exit(1);
}

use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\CSS;

function addIfExists($min, string $root, string $rel): void {
    $path = $root . '/' . ltrim($rel, '/');
    if (file_exists($path)) {
        $min->add($path);
        echo " + $rel\n";
    } else {
        echo " - (missing) $rel\n";
    }
}

echo "Building JS bundle…\n";
$js = new JS();

// jQuery & jQuery UI
addIfExists($js, $root, 'vendor/npm-asset/jquery/dist/jquery.min.js');
addIfExists($js, $root, 'vendor/npm-asset/jquery-ui-dist/dist/jquery-ui.min.js');

// Bootstrap (bundle includes Popper)
addIfExists($js, $root, 'vendor/npm-asset/bootstrap/dist/js/bootstrap.bundle.min.js');

// DataTables core + BS5
addIfExists($js, $root, 'vendor/npm-asset/datatables.net/js/dataTables.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-bs5/js/dataTables.bootstrap5.min.js');

// DataTables extensions
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-buttons/js/dataTables.buttons.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-buttons/js/buttons.colVis.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-buttons/js/buttons.print.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-buttons/js/buttons.html5.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-responsive/js/dataTables.responsive.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-select/js/dataTables.select.min.js');
addIfExists($js, $root, 'vendor/npm-asset/datatables.net-select-bs5/js/select.bootstrap5.min.js');

// Runtime deps for Buttons HTML5 export
// If you removed these from Composer (due to conflicts), place self-hosted copies in public/vendor and we pick them up.
addIfExists($js, $root, 'vendor/npm-asset/jszip/vendor/FileSaver.js');
//addIfExists($js, $root, 'vendor/npm-asset/pdfmake/build/pdfmake.min.js');
//addIfExists($js, $root, 'vendor/npm-asset/pdfmake/build/vfs_fonts.js');


// Utilities
addIfExists($js, $root, 'vendor/npm-asset/js-cookie/dist/js.cookie.min.js');
addIfExists($js, $root, 'vendor/npm-asset/simplebar/dist/simplebar.min.js');
// Optional MDB (can conflict with BS)
addIfExists($js, $root, 'vendor/npm-asset/mdb-ui-kit/js/mdb.min.js');

// Your app file(s) if any
//addIfExists($js, $root, 'app/js/app.js');

$jsOut = $publicBuild . '/app.vendor.min.js';
$js->minify($jsOut);
echo "JS → $jsOut\n";

// (Optional) CSS bundle — add if you want one file
echo "Building CSS bundle…\n";
$css = new CSS();
addIfExists($css, $root, 'vendor/npm-asset/bootstrap/dist/css/bootstrap.min.css');
addIfExists($css, $root, 'vendor/npm-asset/jquery-ui-dist/dist/themes/base/jquery-ui.min.css');
addIfExists($css, $root, 'vendor/npm-asset/datatables.net-bs5/css/dataTables.bootstrap5.min.css');
addIfExists($css, $root, 'vendor/npm-asset/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css');
addIfExists($css, $root, 'vendor/npm-asset/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css');
addIfExists($css, $root, 'vendor/npm-asset/datatables.net-select-bs5/css/select.bootstrap5.min.css');
addIfExists($css, $root, 'vendor/npm-asset/sweetalert2/dist/sweetalert2.min.css');
addIfExists($css, $root, 'vendor/npm-asset/simplebar/dist/simplebar.min.css');
// your overrides
//addIfExists($css, $root, 'app/css/app.css');
// Optional MDB (can conflict with BS)
addIfExists($js, $root, 'vendor/npm-asset/mdb-ui-kit/css/mdb.min.css');

$cssOut = $publicBuild . '/app.vendor.min.css';
$css->minify($cssOut);
echo "CSS → $cssOut\n";

echo "Done.\n";
