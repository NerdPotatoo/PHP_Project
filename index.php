<?php
require_once('vendor/autoload.php');

use App\controllers\HomeController as Home;

$home = new Home();
$home->index();