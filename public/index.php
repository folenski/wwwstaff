<?php

/**
 * main  
 */

$rep_root = dirname(__DIR__);

if (file_exists("{$rep_root}/index.html")) {
    include "{$rep_root}/index.html";
    exit(0);
}

$autoload = "{$rep_root}/vendor/autoload.php";
require $autoload;

use Staff\Controller\Router;

if (!Router::start($rep_root)) {
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    require __DIR__ . "/404.html";
}
