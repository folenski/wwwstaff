#!/usr/bin/env bash
# Lancement des tests unitaires via reqman

REP=$HOME/perso/wwwstaff
echo Lancement des tests via reqman
alltst=$1
if [ -z ${alltst} ]; then
    $REP/reqman/reqman $REP/reqman/*.rml
else 
    $REP/reqman/reqman $REP/reqman/${alltst}
fi

echo --- Fin ----
