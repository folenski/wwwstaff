<?php
// script de création de la base 
// v1.00 - le 07/11/2020 - mfe

//$__modeTest_siteInitDB = true;

class siteInitDB {
    var $dbTables = array(
        'conf'     => '(idConf INTEGER PRIMARY KEY, urlRW INTEGER, defaut INTEGER, menuOrdre INTEGER, titre TEXT NOT NULL, langue TEXT NOT NULL)'
        ,'menu'    => '(idMenu INTEGER PRIMARY KEY, idConf INTEGER, idPage TEXT, sousMenu  INTEGER,ordre  INTEGER NOT NULL, libelle  TEXT NOT NULL)'
        ,'page'    => '(idPage TEXT NOT NULL PRIMARY KEY, idConf INTEGER, ordre INTEGER, titre TEXT, keywords  TEXT, contenu  TEXT NOT NULL, dateCreat TEXT, dateMaj TEXT)'
        ,'article' => '(idArticle TEXT NOT NULL PRIMARY KEY, titre TEXT NOT NULL, contenu TEXT NOT NULL, dateCreat TEXT, dateMaj TEXT)'
    );

    var $dbinsertDef = array(
        'conf'      => '(idConf, urlRW, defaut, menuOrdre, titre , langue) VALUES (1, 0, 1, 1, "Site par défaut", "fr")' 
        ,'menu'     => '(idConf, idMenu, sousMenu, idPage, ordre, libelle) VALUES (1,  1, null,  "site-accueil",  1, "Accueil")'
        ,'page'     => '(idConf, idPage, ordre ,titre, contenu) VALUES (1, "site-accueil", 1, "Accueil", "<h2>Essai</h2><p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum repudiandae dolorem voluptates at beatae quasi dolores minus temporibus ab accusantium repellat, doloremque culpa ullam consequatur quos aut nostrum eveniet architecto!</p>")'
    );

    function __construct() {
    // A completer peut etre
    }

    function CreateSQLSite(string $ordreSQL, array $tabSql, string $prefixe, object $cbExecSql, string $typeBase='SQLITE'): bool {
        if ($typeBase='SQLITE') {
            switch($ordreSQL) {
                case "ligne": 
                    $_preSql="INSERT INTO"; break;
                case "table":
                    $_preSql="CREATE TABLE"; break;
                default :
                    return false;
            }

            foreach ($tabSql as $_key => $_val) {
                $_requete = "{$_preSql} {$prefixe}_{$_key} {$_val}";

                $_ret = $cbExecSql->execSQL($_requete);   // traité le code retour
            }
        }
        return true;
    }

    function execSQL(string $sql): bool {
        //  fonction pour les tests
        echo "{$sql}\n";
        return true;
    }
}

if (isset($__modeTest_siteInitDB)) {
    echo "Mode test-------------------------------------\n";
    $__testDB = new siteInitDB();
    // $__testDB->CreateTableSite("test", "callbackNoSql");
    echo "Creation des tables****\n";
    $__testDB->CreateSQLSite("table", $__testDB->dbTables, "test", $__testDB);
    echo "Insert des lignes def****\n";
    $__testDB->CreateSQLSite("ligne", $__testDB->dbinsertDef, "test", $__testDB);
    echo "fin-------------------------------------------\n";
}