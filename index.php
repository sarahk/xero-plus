<?php
if (array_key_exists('action',$_GET) && $_GET['action'] == 'logoff'){
    session_destroy();
}

$nosidebar = true;
require_once('views/header.php');
?>
<div class="container">
    <h1>xero-php-oauth2-starter</h1>
    <a href="authorization.php"><img src="images/connect-blue.svg"></a>
</div>
<?php
require_once('views/footer.php');