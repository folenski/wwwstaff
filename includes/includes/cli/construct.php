<?php
/**
 * Déclare les tables dans le SGBD 
 *
 * @author  Mario Ferraz
 * @since 1.0  13/01/2021 
 * @version 1.0.0 version initiale
 */

$www = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
require_once($www . "config.php");
require_once(REP_INC . "inc_modules.php");
require_once(REP_INC . "connexionDB.php");

$repDonnees = REP_INC . "donnees" . DIRECTORY_SEPARATOR;
$phpDonnees = REP_INC . "inc_tables.php";
$ClassDonnees = "DbTable";
$headerDonnees=<<< EOF
<?php
/**
 * Module pour inclure les tables du projet 
 */

EOF;
$requireDonnees = 'require_once(REP_INC . "donnees/%s");';

$repRest = REP_INC . "rest" . DIRECTORY_SEPARATOR;
$phpRest = REP_INC . "inc_apiREST.php";
$ClassRest = "ApiRestMsg";
$headerRest=<<< EOF
<?php
/**
 * Module pour inclure les classes de l'API REST du projet 
 */

EOF;
$requireRest = 'require_once(REP_INC . "rest/%s");';


$tabPhp = [];

/**
 * Lit un répertoire pour trouver les class à inclure 
 */
function litRepertoire(string $rep, string $class): array  {
    $tab = [];
    echo "\nOn parcoure le repertoire {$rep} ";  
    if (is_dir($rep)) {
        if ($dh = opendir($rep)) {
            while (($file = readdir($dh)) !== false) {
                if ( substr($file, -3) === "php") {
                    $fileClass = file_get_contents("{$rep}/{$file}");
                    preg_match_all("|(.*)=.*new {$class}|U", $fileClass, $class, PREG_PATTERN_ORDER);
                    $tab[$file] = $class[1][0];
                    echo "\n===> La classe '{$class[1][0]}' est trouvée ";
                }
            }
            closedir($dh);
        }
    }
    return $tab;
}

/**
 * ecrit le fichier include avec les classes trouvées 
 */
function ecritIncModule(string $php, array $tab, string $header, string $require): void  {

    echo "\nOn construit le fichier {$php} ";
    file_put_contents($php, $header);
    foreach ($tab as $file => $class) {
        $line=sprintf($require . "\n",  $file);
        file_put_contents($php, $line, FILE_APPEND);
    }
}

// Mise à jour du fichier inc_tables.php
$tabPhp = litRepertoire($repDonnees, $ClassDonnees);
ecritIncModule($phpDonnees, $tabPhp, $headerDonnees, $requireDonnees);

// Mise à jour du fichier inc_apiREST.php
$tabRest = litRepertoire($repRest, $ClassRest);
ecritIncModule($phpRest, $tabRest, $headerRest, $requireRest);


echo "\nMise à jour de la base de données " . BASE_DB;
require_once(REP_INC . "inc_tables.php");

foreach ($tabPhp as $value) {
    // echo "db value  {$value}";
    eval ( "\$class = {$value};" );
    if ($siteDB->tableExiste($class->nom)) {
        echo "\nLa table {$class->nom} existe déjà ";
        if ( $siteDB->count($class->nom) > 0) {
            echo "\nLa table {$class->nom} contient des enregistrements, confirmer la suppression o/n ";
            $line = readline("Commande : ");
            if ( $line === "o" ) {
                echo "\nDestruction de la table {$class->nom}";
                $siteDB->executeSQL($class->drop());
                echo "\nCreation de la table {$class->nom}";
                $siteDB->executeSQL($class->create());                
            }
        } else {
            echo "\nDestruction de la table {$class->nom}";
            $siteDB->executeSQL($class->drop());
            echo "\nCreation de la table {$class->nom}";
            $siteDB->executeSQL($class->create());
        }
    }
    else {
        echo "\nCreation de la table {$class->nom}";
        $siteDB->executeSQL($class->create());
    }
}

echo "\nFin\n";