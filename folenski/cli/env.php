<?php

/**
 * Définition des constantes nécessaires pour démarrer le site
 * défini la fonction init pour le chargement du fichier base données
 *
 * @author  folenski
 * @since 1.0 08/07/2022 : Version initiale 
 */

define("DIR_INI", "{$rep_root}app" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR);
define("DIR_SQLITE", "{$rep_root}sqldb" . DIRECTORY_SEPARATOR);
define("DIR_VENDOR", "{$rep_root}vendor" . DIRECTORY_SEPARATOR);
date_default_timezone_set('Europe/Paris');

/**
 * Permet de charger la configuration du site en fonction de la variable envName (php en mode cli) 
 * 
 * @throws string  si la recherche n'aboutie une leve une exception
 * @param string $iniFile le fichier de config du projet
 * @param ?string $env l'environnement ou le server ( $_SERVER["SERVER_NAME"] ) doivent être positionés
 * @param ?string $server l'environnement ou le server ( $_SERVER["SERVER_NAME"] ) doivent être positionés
 * @return array [environnement, prefixe, fichier database]
 */
function init(string $iniFile, ?string $env = null, ?string $server = null): array
{
    if (!file_exists($iniFile))
        throw new Exception("Fichier config.ini non trouvé");

    $ini = parse_ini_file($iniFile, true);
    if ($ini === false)
        throw new Exception("Fichier config.ini non lisible");

    if ($env !== null) {
        if (!array_key_exists($env, $ini))
            throw new Exception("Environnement non trouvé dans le fichier config.ini");

        if (($prefixe = $ini[$env]["prefixe"] ?? "") != "")  $prefixe .= "_";
        return [$env, $prefixe, $ini[$env]["database"]];
    }

    if ($server === null)
        throw new Exception("Environnement ou servername non communiqués");

    $refDef = [];

    foreach ($ini as $env => $valeur) {
        if ($server === $valeur["servername"]) {
            if (($prefixe = $ini[$env]["prefixe"] ?? "") != "")  $prefixe .= "_";
            return [$env, $prefixe, $valeur["database"]];
        }
        if (isset($valeur["default"])) {
            if (($prefixe = $ini[$env]["prefixe"] ?? "") != "")  $prefixe .= "_";
            $retDef = [$env, $prefixe, $valeur["database"]];
        }
    }

    if (count($refDef) == 0)
        throw new Exception("ServerName non trouvé dans le fichier ini");

    return $retDef;
}
