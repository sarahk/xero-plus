<?php

namespace App;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

error_reporting(E_ALL);

require_once '../vendor/autoload.php';

//$log = new Logger('Index');
//$log->pushHandler(new StreamHandler('monolog.index.log', Level::Info));
//$log->info('$_GET', $_GET);

if (array_key_exists('action', $_GET) && $_GET['action'] == 'logoff') {
    session_destroy();
}
const LOGGEDOUT = true;

require_once('Views/header.php');
?>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Cabin King Management Tools</h1>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12">
                <div class="card">
                    <!-- <div class="card-header">
                         <h3 class="card-title">Enquiry</h3>
                     </div>-->
                    <div class="card-body">
                        <p>Log into Xero to get started</p>
                        <a href="/authorization.php"><img src="images/connect-blue.svg" alt="Xero Login Button"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
require_once('Views/footer.php');
