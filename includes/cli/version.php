<?php 
// module pour tester les versions des modules wwwstaff

$www = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;

require_once($www . "config.php");
require_once(REP_INC . "class/DbSqlite.php");
require_once(REP_INC . "class/SiteCore.php");
require_once(REP_INC . "class/createBase.php");
require_once(REP_INC . "fonctUtile.php");
require_once(REP_INC . "class/Apphtml.php");

echo "\nTest des versions installées";
echo "\nLa version de la classe Sitecore est : " .  SiteCore::version() . "...";
echo "\nLa version de la classe DbSqlite est : " .  DbSqlite::version() . "...";
echo "\nLa version de la classe Apphtml est  : " .  Apphtml::version() . "...";

echo "\n";
