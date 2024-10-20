<?php
echo 'hello world';
include 'ExtraFunctions.php';
//debug($_SERVER);
debug("https://{$_SERVER['HTTP_HOST']}/callback.php");
