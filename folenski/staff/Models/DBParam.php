<?php

/**
 * Constantes pour la définition des champs pour la base de données
 * 
 * @author  folenski
 * @version 1.0 04/08/2022: version initiale 
 * @version 1.1 14/12/2022: supp interface DBParam 
 * @version 1.2 17/12/2022: ajout de la table change, modif get_table 
 * @version 1.3 17/04/2024: gestion du json pour le fichier d'environnement
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

    const TABLE = ["environment", "template", "user", "data", "token", "log", "message", "change", "black_list"];

    static string $prefixe;
    static string $env;
    static string $db;
    static string $file_pdo;
    static string $mail;

    /**
     * Analyse le fichier de configuration du site, le format doit être en json
     *   
     * @param string $file fichier de config du projet
     * @param string|null $env l'environnement (accès via le cli) 
     * @param string|null $server doit être alimenté à partir de la variable $_SERVER["SERVER_NAME"]
     * @return array [environnement, prefixe, fichier database]
     */
    static function parse(string $file, ?string $env = null, ?string $server = null): bool|int
    {
        if ($env === null && $server === null) return self::PARAM_ERROR;

        self::$file_pdo = dirname($file) . "/";
        $ini = json_decode(file_get_contents($file), true);
        if ($ini === null || $ini === false) return self::PARSE_ERROR;

        if ($env !== null) {
            if (!array_key_exists($env, $ini)) return self::NOT_FOUND;
            self::_setParam($ini[$env], $env);
            return true;
        }

        // on recherche avec le servername
        foreach ($ini as $key => $valeur) {
            if (!array_key_exists("servername", $valeur)) return self::PARSE_ERROR;
            if ($server == $valeur["servername"]) {
                self::_setParam($valeur, $key);
                return true;
            }
            if (array_key_exists("default", $valeur)) {
                self::_setParam($valeur, $key);
                return true;
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
        return match ($nom) {
            "environment" => new Environment(),
            "template" => new Template(),
            "data" => new Data(),
            "user" => new User(),
            "token" => new Token(),
            "log" => new Log(),
            "message" => new Message(),
            "change" => new Change(),
            "black_list" => new BlackList(),
            default => null
        };
    }

    /**
     * Permet d'initialiser les paramétrages de la base de données
     * @param object $valeur    contient les paramétrages contenus dans la table environment 
     * @param string $key       environnement determiné en fonction de l'url principalement
     */
    private static function _setParam(mixed $valeur, string $key): void{
        $prefixe = $valeur["prefixe"] ?? "";
        $prefixe .= ($prefixe !== "") ? "_" : "";
        self::$prefixe = $prefixe;
        self::$env = $key;
        self::$db = $valeur["db"] ?? "sqlite";
        self::$file_pdo .= $valeur["pdo"];
        self::$mail = $valeur["mail"] ?? "";
    }
}
