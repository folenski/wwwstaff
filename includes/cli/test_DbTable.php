<?php
// test unitaire pour la class statique test_DbSqite.php 

$www = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;

require_once($www . "config.php");
require_once(REP_INC . "class/DbTable.php");

$descInfo = [
    "idInfo" => "INTEGER PRIMARY KEY",
    "titre"  => "TEXT",
    "meta"   => "TEXT"
];

echo "Debut des tests unitaires";
$ttt = new DbTable("info", $descInfo);

echo "\nCreation de la table";
echo "\n==>" . $ttt->create();

echo "\nDestruction de la table";
echo "\n==>" . $ttt->drop();

$idInfo = 1;
$titre="hello";

echo "\nSelect table";
echo "\n==>" . $ttt->select([],compact("idInfo", "titre"));
echo "\n==>" . $ttt->select(compact("idInfo"),compact("idInfo", "titre"), true);

echo "\nInsert table";
echo "\n==>" . $ttt->insert(compact("idInfo", "titre"));
echo "\n==>" . $ttt->insert(compact("idInfo", "titre"), true);

echo "\nUpdate table";
echo "\n==>" . $ttt->update(compact("titre"), compact("idInfo"));
echo "\n==>" . $ttt->update(compact("titre"), compact("idInfo"), true);

echo "\nDelete table";
echo "\n==>" . $ttt->delete(compact("idInfo"));
echo "\n==>" . $ttt->delete(compact("idInfo"), true);

echo "\n\nFin des tests unitaires\n";