#!/usr/bin/env php
<?php

/**
 * Programme de création d'une nouvelle version à déployer
 * @author  folenski
 * 
 * @since  14/04/2024
 * @version 1.0 version initiale
 * 
 */

use Staff\Config\Config;
use Staff\Lib\CliFonct;

$rep_root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require "{$rep_root}vendor/autoload.php";

/******************************************************************************************* 
 * 
 *                            P R O G R A M M E    P R I N C I P A L
 *
 ********************************************************************************************/

$config_file = $rep_root . Config::REP_CONFIG . Config::FILE_DEPLOY;
$rep_tmp = $rep_root . Config::REP_TMP;

CliFonct::createDirectory($rep_tmp);
CliFonct::clearDirectory($rep_tmp);

$config = json_decode(file_get_contents($config_file), true);
if ($config === null) {
    CliFonct::exit("Parse {$config_file}, code error");
}

$rep_delivery = $rep_root . Config::REP_DELIVERY;
CliFonct::createDirectory($rep_delivery);

$file_delivery = "{$rep_delivery}/{$config["name_delivery"]}_{$config["version"]}.zip";
if (file_exists($file_delivery)) {
    CliFonct::print("Le fichier {$file_delivery} existe de déjà", CliFonct::TERM_ROUGE);
    unlink($file_delivery);
}

CliFonct::print("Creation de la nouvelle version {$file_delivery}", CliFonct::TERM_BLEU);

foreach ($config["tasks"] as $task) {
    if (!array_key_exists("id", $task) || !array_key_exists("path", $task))
        continue;
    CliFonct::print("Creation du fichier {$task["id"]} > ", CliFonct::TERM_BLEU);
    $zip = new ZipArchive();
    $res = $zip->open("{$rep_tmp}/{$task["id"]}.zip", ZipArchive::CREATE);
    if ($res !== true) {
        CliFonct::exit("Erreur création du fichier zip {$rep_tmp}/{$task["id"]}.zip");
    }

    $dir = "{$rep_root}{$task["path"]}";
    $lenght_dir = strlen($dir) + 1;
    if (!is_dir($dir)) {
        CliFonct::exit("{$task["id"]} - Le dossier {$dir} n'existe pas");
    }

    if (!array_key_exists("files", $task)) {
        $rdi = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $file) {
            if (!$file->isDir()) {
                CliFonct::print(".", newline: false);
                $zip->addFile($file->getRealPath(), substr($file->getRealPath(), $lenght_dir));
            }
        }
    } else {
        foreach ($task["files"] as $nfile) {
            $file = "{$dir}/{$nfile}";
            if (!file_exists($file)) {
                CliFonct::print("Le fichier {$file} n'existe pas", CliFonct::TERM_ROUGE);
            }
            CliFonct::print(".", newline: false);
            $zip->addFile($file, substr($file, $lenght_dir));
        }
    }
    $zip->close();
}

CliFonct::print("Finalisation de l'archive {$file_delivery}", CliFonct::TERM_BLEU);

$zip = new ZipArchive();
$res = $zip->open($file_delivery, ZipArchive::CREATE);
if ($res !== true) {
    CliFonct::exit("Erreur création du fichier zip {$file_delivery}");
}
$zip->addFile($config_file, basename($config_file));
$files = glob("{$rep_tmp}/*");
foreach ($files as $file) {
    if (!is_dir($file)) {
        $zip->addFile($file, basename($file));
    }
}
$zip->close();

CliFonct::print("Fin", CliFonct::TERM_BLEU);
