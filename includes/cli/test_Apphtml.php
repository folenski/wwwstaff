<?php
// test unitaire pour la class statique Apphtml

$racine = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR; 

require_once( $racine . "includes/class/Apphtml.php");

echo "Debut des tests unitaires";
echo "\nTest fonction pageNotFound ----\n";
echo Apphtml::pageNotFound('hello le monde');
echo "\nTest fonction msg --------------\n";
echo Apphtml::msg('Hello le monde');
echo "\nTest fonction css fw Materialize-1 -------\n";
echo "REP_CSS={$racine}/css/";
echo Apphtml::lien('css_materialize-1', "{$racine}css/\n");
echo "\nTest fonction css fw mario -------\n";
echo Apphtml::lien('mario');
echo "\nTest fonction css fw bootstrap-4 -------\n";
echo Apphtml::lien('js_bootstrap-4');

echo "\n\nFin des tests unitaires\n";