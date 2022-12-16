#!/usr/bin/env php
<?php

/**
 * Programme de chargement des tables au format json en de la base de données
 * @author  folenski
 * 
 * @since   05/08/2022
 * @version 1.2.0  maj avec l'objet PAGE
 * @version 1.3.0  gestion des balises <item>
 * @version 1.4.0  utilisation de la class Config
 * @version 1.5.0  prise en compte refactoring séparation des modèles
 * @version 1.6.0  Utilisation de la class Admin
 * @version 1.7.0  Utilisation de la class Config
 * 
 */

use Staff\Databases\Database;
use Staff\Services\Admin;
use Staff\Models\DBParam;
use Staff\Services\CliFonct;
use Staff\Config\Config;

$rep_root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require "{$rep_root}vendor/autoload.php";
require __DIR__ . "/env.php";


/******************************************************************************************* 
 * 
 *                            P R O G R A M M E    P R I N C I P A L
 *
 ********************************************************************************************/

CliFonct::print("Initialisation Site", CliFonct::TERM_BLEU, false);

$pOpt = [
  "d" => ["name" => "delete", "value" => false],
  "p" => ["name" => "path", "value" => false],
  "e" => ["name" => "env", "value" => true]
];
$ret = CliFonct::checkArgs($argv, $pOpt);

if (count($ret["errors"]) > 0) {
  CliFonct::exit("Usage : Paramètres : \n[-d] : Suppression au préalable\n
    [-e] : trigramme environnement\n[-p] repertoire | fichier xml ");
}
$optDelete = array_key_exists("delete", $ret["options"]);
$env = array_key_exists("env", $ret["options"]) ? $ret["options"]["env"] : "DEV";
$path = array_key_exists("path", $ret["options"]) ? $ret["options"]["path"] : "";
if (count($ret["args"]) > 0)
  $fichier = $ret["args"][0]; // on prend le 1er fichier pour être compatible

// read file's config
$fichierIni = $rep_root . Config::REP_CONFIG . Config::FILE_INI;
$init = DBParam::parse($fichierIni, env: $env);
if ($init !== true) 
  CliFonct::exit("Parse {$fichierIni}, code error {$init}");
Database::init(DBParam::$file_pdo, DIR_SQLITE);

CliFonct::print("Environnement      => {$env}");

if ($optDelete)
  CliFonct::print("L'option delete activée", CliFonct::TERM_VERT);

if ($path != "")
  CliFonct::exit("L'option path n'est pas encore implementé");

if (!str_contains($fichier, 'json') || !file_exists($fichier))
  CliFonct::exit("{$fichier}: Le fichier non trouvé ou n'est pas au format json");

CliFonct::print("********************* Fichier {$fichier} ***********************", CliFonct::TERM_BG_BLANC .  CliFonct::TERM_NOIR);

$Admin = new Admin();
$cr = [];
[$ret, $nbr, $lib] = $Admin->load($fichier, $optDelete, $cr);

if ($ret != $Admin::RET_OK) {
  CliFonct::exit($lib);
}
$table = $lib;
CliFonct::print("===== Table {$table} =====", CliFonct::TERM_BLEU);
CliFonct::print("La table {$table} => {$nbr} enr");

foreach ($cr as $key => $retour) {
  if ($retour == $Admin::RET_MAJ) {
    $color = CliFonct::TERM_ROUGE;
    $crlib = "{$key}: Mise à jour";
  } elseif ($retour == $Admin::RET_OK) {
    $color = CliFonct::TERM_BLANC;
    $crlib = "{$key}: Ajout de l'enregistrement";
  } else {
    $color = CliFonct::TERM_VERT;
    $crlib = "{$key}: Rejeté car il est déja présent";
  }
  CliFonct::print($crlib, $color);
}

CliFonct::print("Maj OK...", CliFonct::TERM_BG_VERT);

CliFonct::print("************************* {$fichier} OK ***************************", CliFonct::TERM_BG_BLANC .  CliFonct::TERM_NOIR);
CliFonct::print("Fin de l'initialisation", CliFonct::TERM_BLEU);
