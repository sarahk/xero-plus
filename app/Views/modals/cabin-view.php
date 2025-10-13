<?php

use App\Classes\ViewFunctions;

ob_start();
include __DIR__ . '/partials/cabin-view.php';
$stub = ob_get_clean();

echo '<!-- Modal cabin-view.php-->';

echo ViewFunctions::render('components/modal.php', [
    'modalAction' => intval($_GET['action'] ?? 0),
    'modalStub' => 'cabinView', // not used
    'title' => 'Cabin',
    'bodyHtml' => $stub,
    'validate' => false,
    'jsFunction' => 'initCabinView',
    'jsFile' => 'Modals/cabinView.js',
    'formType' => 'cabin',
    'showButtons' => false,
]);
?>
<!-- /Modal -->
