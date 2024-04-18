<?php

/**
 * startpoint 
 * @version 1.1 2024-04-16 correction   
 */

$rep_root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
if (file_exists("{$rep_root}public/index.html")) {
    include "{$rep_root}public/index.html";
    exit(0);
}

$autoload = "{$rep_root}vendor/autoload.php";
require $autoload;

use Staff\Controller\Router;

if (Router::start($rep_root)) exit(0);
// en cas d'erreur  
header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Error 404</title>
    <style>
        .center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            text-align: center;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    </style>

</head>

<body>
    <br /><br />
    <div class="center-text">
        <pre>
        ▌║█║▌│║▌│║▌║▌█║Error 404 ▌│║▌║▌│║║▌█║▌║█
    </pre>
    </div>
</body>

</html>