<?php
include 'phpsprocket.php';
header('Content-type: application/x-javascript');
$sprocket = new PHPSprocket(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));