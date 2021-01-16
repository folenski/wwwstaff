<?php 
/**
 * module pour tester les versions des modules wwwstaff 
 *
 * @author  Mario Ferraz
 * @since 1.0  16/01/2021 
 * @version 1.0.0 version initiale
 */

$www = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require_once($www . "config.php");
require_once(REP_INC . "inc_modules.php");

echo "\nTest des versions installées";
echo "\nLa version de la classe Sitecore est : " .  SiteCore::version() . "...";
echo "\nLa version de la classe DbSqlite est : " .  DbSqlite::version() . "...";
echo "\nLa version de la classe Apphtml est  : " .  Apphtml::version() . "...";
echo "\nLa version de la classe ApiRest est  : " .  ApiRest::version() . "...";
echo "\nLa version de la classe DbTable est  : " .  DbTable::version() . "...";

echo "\n";
