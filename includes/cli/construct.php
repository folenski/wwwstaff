<?php

$repDonnees = dirname(__DIR__) . DIRECTORY_SEPARATOR . "donnees" . DIRECTORY_SEPARATOR;
$phpDonnees = dirname(__DIR__) . DIRECTORY_SEPARATOR . "DbClass.php";

$tabPhp = [];

// liste tous les fichiers php
if (is_dir($repDonnees)) {
    if ($dh = opendir($repDonnees)) {
        while (($file = readdir($dh)) !== false) {
            if ( substr($file, -3) === "php") {
                array_push($tabPhp, $file);
            }
        }
        closedir($dh);
    }
}

foreach ($tabPhp as $file) {
    echo "Fichier {$file}\n";
}
echo "\n";
