<?php
/**
 * Module de tests pour la Classe DbSqlite 
 *
 * @author  Mario Ferraz
 * @since 1.0  08/01/2021 
 * @version 1.0.0 version initiale
 */

$www = dirname(__DIR__) . DIRECTORY_SEPARATOR;

require_once($www . "config.php");
require_once(REP_INC . "inc_modules.php");

echo "Debut des tests unitaires";
echo "\nOn provoque une erreur d'ouverture de la base ";
try {
    $db = new DbSqlite("/xxxx/mario.db", "-");
} catch (Exception $e) {
    echo "\nException reçue : " .  $e->getMessage();
}
echo "\nOuverture de la base " . BASE_DB;
$db = new DbSqlite(BASE_DB, PREFIXE_DB);

echo "\nOn regarde si la table toto existe ";

if ($db->tableExiste("toto"))
    echo "\n la table toto existe";
else
    echo "\n la table toto n'existe pas";

echo "\nOn regarde si la table site_info existe ";
if ($db->tableExiste("site_info"))
    echo "\n====>la table toto existe";
else
    echo "\n====>la table toto n'existe pas";

echo "\nOn compte les éléments de la table site_info " . $db->count("site_info");

echo "\nOn provoque une erreur de requete ";
try {
    $db->selectSQL("select * from toto where 1=1");
} catch (Exception $e) {
    echo "\nException reçue : " .  $e->getMessage();
}

$db->fin();

echo "\n\nFin des tests unitaires\n";