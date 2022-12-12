#!/usr/bin/env php
<?php

/**
 * Programme pour patcher les mises à jours des tables de la base de données
 * @author  folenski
 * @since   28/01/2022
 * @version 1.0.0  version initiale
 */

use Staff\Core\DbTable;
use Staff\Core\Database;
use Staff\Core\Config;
use Staff\Classes\Extra\CliFonct;
use Staff\Tables\ModeleBDD;

require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

// read file's config
$Cfg = new Config();
$Cfg->setConfigDB();
$prefixe = $Cfg->prefixe;

/**
 * Verif si la table existe
 */
function verifTable(DbTable $table): bool
{
  try {
    $_req = Database::query($table->select("count (1) as nbr"))->fetch();
    CliFonct::affiche("La table {$table->nom()} => {$_req->nbr} enr", CliFonct::TERM_VERT);
  } catch (Exception $e) {
    CliFonct::affiche("La table {$table->nom()} n'existe pas !!!!!", CliFonct::TERM_ROUGE);
    return false;
  }
  return true;
}

/******************************************************************************************* 
 * 
 *                            P R O G R A M M E    P R I N C I P A L
 *
 ********************************************************************************************/

CliFonct::affiche("Application Patch ", CliFonct::TERM_BLEU, false);

$Bdd = new ModeleBDD();


if ($argc === 1) {
  CliFonct::affiche("Liste des patches possibles ", CliFonct::TERM_VERT);

  foreach ($Bdd->listPatch as $key => $valeur) {
    CliFonct::affiche("N° {$key} - {$valeur['description']}");
  }

  CliFonct::sortir("Usage :  numéro du patch ");
}

$numero = intval($argv[1]);
if (!array_key_exists($numero, $Bdd->listPatch)) {
  CliFonct::sortir("Le numero de patch {$numero} n'existe pas");
}

$desc = $Bdd->listPatch[$numero]["description"];
$tables = $Bdd->listPatch[$numero]["table"];

CliFonct::affiche("Prefixe des tables => {$prefixe}");
CliFonct::affiche("Environnement      => {$Cfg->env}");
CliFonct::affiche("Host machine       => {$Cfg->host}");
CliFonct::affiche("Patch              => {$numero}");
CliFonct::affiche("Description        => {$desc}");

CliFonct::affiche("===== Debut de l'application du patch {$numero} =====", CliFonct::TERM_BLEU);

foreach ($tables as $table) {
  CliFonct::affiche("Maj de la table {$table} : ");
  $Table = new DbTable("{$Cfg->prefixe}{$table}", $Bdd->champs($table));
  $ret = $Bdd->patch($numero, $Table);
  CliFonct::affiche(substr($ret, 0, 120), "", false);
}

CliFonct::affiche("************************* patch {$numero} OK ***************************", CliFonct::TERM_BG_BLANC .  CliFonct::TERM_NOIR);
CliFonct::affiche("Fin de l'initialisation", CliFonct::TERM_BLEU);
