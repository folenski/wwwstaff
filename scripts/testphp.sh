#!/usr/bin/env bash
#
# Lancement des tests unitaires PHP

REP=$HOME/perso/wwwstaff
echo Lancement des tests unitaires
$REP/vendor/bin/phpunit --testdox $REP/tests
echo --- Fin ----
