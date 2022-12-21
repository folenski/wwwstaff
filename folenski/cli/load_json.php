#!/usr/bin/env php
<?php

/**
 * Programme de chargement des tables au format json en de la base de données
 * @author  folenski
 * 
 * @since  17/12/2022
 * @version 1.5 prise en compte refactoring séparation des modèles
 * @version 1.6 utilisation de la class Admin
 * @version 1.7 utilisation de la class Config
 * @version 1.8 utilisation de la table admin
 * 
 */

use Staff\Databases\Database;
use Staff\Lib\Admin;
use Staff\Models\DBParam;
use Staff\Lib\CliFonct;
use Staff\Config\Config;

$rep_root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require "{$rep_root}vendor/autoload.php";

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
Database::init(DBParam::$file_pdo, $rep_root . Config::REP_SQLITE);

CliFonct::print("Environnement      => {$env}");

if ($optDelete)
  CliFonct::print("L'option delete activée", CliFonct::TERM_VERT);

if ($path != "")
  CliFonct::exit("L'option path n'est pas encore implementé");

if (!str_contains($fichier, 'json') || !file_exists($fichier))
  CliFonct::exit("Le fichier {$fichier} non trouvé où il n'est pas au format json");

CliFonct::print("********************* Fichier {$fichier} ***********************", CliFonct::TERM_BG_BLANC .  CliFonct::TERM_NOIR);

// $cbPrint = CliFonct::cbPrint;

$Admin = new Admin(DBParam::$prefixe);
$Admin->load(
  $fichier,
  $optDelete,
  function (?string $text = null, int $lvl = Admin::DISP_DEF) {
    CliFonct::cbPrint($text, $lvl);
  }
);

CliFonct::print("Maj OK...", CliFonct::TERM_BG_VERT);

CliFonct::print("************************* {$fichier} OK ***************************", CliFonct::TERM_BG_BLANC .  CliFonct::TERM_NOIR);
CliFonct::print("Fin de l'initialisation", CliFonct::TERM_BLEU);
