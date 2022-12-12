#!/usr/bin/env php
<?php

/**
 * Programme pour la création des tables de la base de données
 * @author  folenski
 * 
 * @since   11/08/2022
 * @version 1.0.1  prise en compte de l'objet ModeleBDD
 * @version 1.1.0  Ajout d'un utilisateur admin générique
 * @version 1.2.0  Utilisation de l'objet Table
 * @version 1.3.0  on utilise la classe DBdata
 * @version 1.4.0  Utilisation de la class Admin
 */

use Staff\Databases\Database;
use Staff\Databases\SqlAdmin;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Services\CliFonct;

$rep_root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
$rep_init = $rep_root . "includes" . DIRECTORY_SEPARATOR . "init" . DIRECTORY_SEPARATOR;
require __DIR__ . "/env.php";
require DIR_VENDOR . "autoload.php";

/******************************************************************************************* 
 * 
 *                            P R O G R A M M E    P R I N C I P A L
 *
 ********************************************************************************************/

CliFonct::print("Initialisation Site", CliFonct::TERM_BLANC . CliFonct::TERM_BG_BLEU, false);
CliFonct::print("Initialisation de la base de donnée");

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
$init = DBParam::parse(file: DIR_INI . "config.ini", env: $env);
if ($init !== true)
  CliFonct::exit("Parse " . DIR_INI . "config.ini, code error" . $init);
Database::init(DBParam::$file_pdo, DIR_SQLITE);

CliFonct::print("Environnement          => {$env}");

if ($optDelete) CliFonct::print("L'option drop est activée", CliFonct::TERM_VERT);

CliFonct::print("==> Vérification ", CliFonct::TERM_BLEU);

foreach (DBParam::TABLE as $nomtable) {
  $Adm = new SqlAdmin(DBParam::$prefixe, DBParam::get_table($nomtable));
  $Table = new Table(DBParam::$prefixe, DBParam::get_table($nomtable));
  CliFonct::print("+ {$Table->name} ", CliFonct::TERM_BLEU);

  if ($optDelete) {
    CliFonct::print("Drop demandé pour la table {$nomtable} ", CliFonct::TERM_BG_ROUGE);
    Database::exec($Adm->drop()->exists()->table()->toStr());
  } else {
    if (($nbr = $Table->count()) === false) {
      CliFonct::print("La table {$nomtable} n'existe pas !!!!!", CliFonct::TERM_ROUGE);
      Database::exec($Adm->create()->table()->toStr());
      foreach ($Adm->listIndex as $index) {
        CliFonct::print("Creation index {$index} pour la table {$nomtable} ", CliFonct::TERM_VERT);
        Database::exec($Adm->create()->index($index)->toStr());
      }
    } else {
      CliFonct::print(" => {$nbr} enr", CliFonct::TERM_VERT, false);
    }
  }
  CliFonct::print("................................", CliFonct::TERM_BLEU);
}

/**
 * Création d'un utilisateur d'administrateur
 */
// lecture du fichier pour le compte admin
$Usr = new Table(DBParam::$prefixe, DBParam::get_table("user"));
$fichier_ini = DIR_INI . "user.ini";
$nbr = $Usr->count();

if ($nbr == 0) {
  if (file_exists($fichier_ini)) {
    $ini = parse_ini_file($fichier_ini, true);
    if ($ini !== false) {
      $iUser = $ini["USER"];
      $retour = $Usr->save(
        [
          "user" => $iUser["user"],
          "mail" => $iUser["mail"],
          "password" => $iUser["pass"],
          "permission" => 0b1111
        ]
      );
      if ($retour[0] != $Usr::RET_OK) CliFonct::print("Impossible de créer le compte admin !!!", CliFonct::TERM_ROUGE);
      else CliFonct::print("Création d'un compte admin {$iUser['user']}", CliFonct::TERM_BLEU);
    }
  } else {
    CliFonct::print("+ Fichier déclaration de l'utilisateur absent {$fichier_ini}",  CliFonct::TERM_BLEU);
  }
} else {
  CliFonct::print("+ Utilisateurs présents",  CliFonct::TERM_BLEU);
}
CliFonct::print("Fin de l'initialisation", CliFonct::TERM_BLANC . CliFonct::TERM_BG_BLEU);
