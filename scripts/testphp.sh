#!/usr/bin/env bash
# Lancement des tests unitaires PHP

REP=$HOME/perso/wwwstaff
echo Lancement des tests unitaires
alltst=$1
if [ -z ${alltst} ]; then
    for test in Start Databases Lib; do
        echo ---- ${test} ----
        $REP/vendor/bin/phpunit --testdox $REP/tests/${test}
    done
else 
    $REP/vendor/bin/phpunit --testdox $REP/tests/${alltst}
fi

echo --- Fin ----
