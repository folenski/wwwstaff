#!/usr/bin/env php
<?php

/**
 * Programme pour la création des tables de la base de données
 * @author  folenski
 * 
 * @since  17/12/2022
 * @version 1.5 ajout de la class Driver/sqlite
 * @version 1.6 suppression ajout de l'utilisateur
 * @version 1.7 utilisation de la table admin
 * @version 1.8 ajout du driver Mysql
 * 
 */

use Staff\Databases\Database;
use Staff\Models\DBParam;
use Staff\Lib\CliFonct;
use Staff\Config\Config;
use Staff\Drivers\Mysql;
use Staff\Drivers\Sqlite;
use Staff\Lib\Admin;

$rep_root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require "{$rep_root}vendor/autoload.php";

/******************************************************************************************* 
 * 
 *                            P R O G R A M M E    P R I N C I P A L
 *
 ********************************************************************************************/

CliFonct::print("Init Site ", CliFonct::TERM_BLANC . CliFonct::TERM_BG_BLEU, false);

$pOpt = [
  "d" => ["name" => "delete", "value" => false],
  "e" => ["name" => "env", "value" => true]
];
$ret = CliFonct::checkArgs($argv, $pOpt, false);

if (count($ret["errors"]) > 0) {
  CliFonct::exit("Usage : create_base.php [-d] [-e DEV|QUA|PRD]");
}
$optDelete = array_key_exists("delete", $ret["options"]);
$env = array_key_exists("env", $ret["options"]) ? $ret["options"]["env"] : "DEV";

// read file's config
$fichierIni = $rep_root . Config::REP_CONFIG . Config::FILE_INI;
$init = DBParam::parse($fichierIni, env: $env);
if ($init !== true) CliFonct::exit("Parse {$fichierIni}, code error {$init}");
Database::init(DBParam::$file_pdo, $rep_root . Config::REP_SQLITE);
$base = DBParam::$db;

CliFonct::print("Environnement [{$env}]");
CliFonct::print("Base de donnée [{$base}]");
CliFonct::print("");

if ($optDelete) CliFonct::print("L'option drop est activée", CliFonct::TERM_VERT);

$Adm = new Admin(
  sqldrv: match (DBParam::$db) {
    "mysql" => new Mysql(),
    default => new Sqlite()
  }
);

$Adm->createAllTables(
  $optDelete,
  function (?string $text = null, int $lvl = Admin::DISP_DEF) {
    CliFonct::cbPrint($text, $lvl);
  }
);

CliFonct::print("");
CliFonct::print("Fin de l'initialisation", CliFonct::TERM_BLANC . CliFonct::TERM_BG_BLEU);
