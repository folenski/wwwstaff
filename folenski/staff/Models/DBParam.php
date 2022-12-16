<?php

/**
 * Constantes pour la définition des champs pour la base de données
 * 
 * @author  folenski
 * @version 1.0 04/08/2022: Version initiale 
 * @version 1.1 14/12/2022: supp interface DBParam 
 *  
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;

class DBParam
{
    const RET_OK = 0;
    const NOT_FOUND = 2;
    const PARAM_ERROR = 3;
    const PARSE_ERROR = 4;

    const ERROR = [
        self::NOT_FOUND => "file ini, key not found",
        self::PARSE_ERROR => "file ini, parse error ",
        self::PARAM_ERROR => "Parse parameters error"
    ];

    const TABLE = ["data", "environment", "template", "user", "token", "log", "message"];

    static string $prefixe;
    static string $env;
    static string $db;
    static string $file_pdo;

    /**
     * Permet d'analyser le fichier de configuration du site
     *   
     * @param string $file le fichier de config du projet
     * @param string|null $env l'environnement ( acces via cli) 
     * @param string|null $server le hosts (accès via ihm)
     *  "server, doit être alimenté par la super variable $_SERVER["SERVER_NAME"]
     * @return array [environnement, prefixe, fichier database]
     */
    static function parse(string $file, ?string $env = null, ?string $server = null): bool|int
    {
        if ($env === null && $server === null) return self::PARAM_ERROR;

        self::$file_pdo = dirname($file) . "/";

        $ini = parse_ini_file($file, true);
        if ($ini === false) return self::PARSE_ERROR;

        if ($env !== null) {
            if (!array_key_exists($env, $ini)) return self::NOT_FOUND;
            if (($prefixe = $ini[$env]["prefixe"] ?? "") != "") $prefixe .= "_";
            self::$prefixe = $prefixe;
            self::$env = $env;
            self::$db = $valeur["db"] ?? "sqlite";
            self::$file_pdo .= $ini[$env]["pdo"];
            return true;
        }

        // on recherche avec le servername
        foreach ($ini as $key => $valeur) {
            if (!array_key_exists("servername", $valeur)) return self::PARSE_ERROR;
            if ($server == $valeur["servername"]) {
                if (($prefixe = $valeur["prefixe"] ?? "") != "") $prefixe .= "_";
                self::$prefixe = $prefixe;
                self::$env = $key;
                self::$db = $valeur["db"] ?? "sqlite";
                self::$file_pdo .= $valeur["pdo"];
                return true;
            }
            if (array_key_exists("default", $valeur)) {
                if (($prefixe = $valeur["prefixe"] ?? "") != "") $prefixe .= "_";
                self::$prefixe = $prefixe;
                self::$env = $key;
                self::$db = $valeur["db"] ?? "sqlite";
                self::$file_pdo .= $valeur["pdo"];
            }
        }
        return isset(self::$prefixe) ? true : self::NOT_FOUND;
    }
    /**
     * Permet d'obtenir la définition de la table en fonction du nom
     * @param string $nom nom de table
     * @return TableInterface|null
     */
    static function get_table(string $nom): TableInterface|null
    {
        switch ($nom) {
            case "environment":
                return new Environment();
            case "template":
                return new Template();
            case "data":
                return new Data();
            case "user":
                return new User();
            case "token":
                return new Token();
            case "log":
                return new Log();
            case "message":
                return new Message();

            default:
                return null;
        }
    }
}
