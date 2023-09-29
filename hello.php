<?php
echo 'hello world';
include 'functions.php';
//debug($_SERVER);
debug("https://{$_SERVER['HTTP_HOST']}/callback.php");
