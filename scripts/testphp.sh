#!/usr/bin/env bash
#
# Lancement des tests unitaires PHP
# ----------- exemples -------------------
# scripts/testphp.sh Resources/ApiAuth
# scripts/testphp.sh 

REP=$HOME/perso/wwwstaff
echo Lancement des tests unitaires
tst=$1
if [ -z ${tst} ]; then
    for test in Start Databases Lib Security Controller Resources; do
        echo ---- ${test} ----
        $REP/vendor/bin/phpunit --testdox $REP/tests/${test}
    done
else 
    $REP/vendor/bin/phpunit --testdox $REP/tests/${tst}Test.php
fi

echo --- Fin ----
