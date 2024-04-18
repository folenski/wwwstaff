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

use Staff\Controller\Patch;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Error 404</title>
    <style>
        h1 {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        h2 {
            font-size: 1.4em;
            font-weight: 300;
            font-style: normal;
            margin-left: 2em;
            color:brown
        }
        .text-info {
            color: #17a2b8;
        }
        .text-danger {
            color: #dc3545;
        }
    </style>

</head>

<body>
    <h1>
        <pre>🅸🅽🆂🆃🅰🅻🅻</pre>
    </h1>
    <div>
        <?= Patch::apply($rep_root); ?>
    </div>
</body>

</html>