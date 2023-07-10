<?php

/**
 * Class Database 
 * Class statique pour accèder à une base SQL PDO
 * Dépend des variables globales sur projet BASE_INIT et REP_DB (pour sqlite3)
 * 
 * @author  folenski
 * @since   20/01/2021
 * @version 1.0.1 Version initiale
 * @version 1.1.0 Utilisation de la variable $base_init & $rep_DB poir eviter l'adhérence au fichier config.ini
 * @version 1.1.1 ajout de la propriete $driver
 * @version 1.1.2 Deprecated Features use "$var"/"{$var}"
 * 
 */

namespace Staff\Databases;

class Database
{
    static string $dirBDD  = ""; // répertoire ou est la base SQLITE, uniquement pour SQLITE   
    static string $fileIni = ""; // fichier pour le driver PDO 

    static string $driver = "";  // set for sqlite 
    private static $link = null;

    /**
     * init 
     * @param string $fileini, le fichier ini qui contient les paramétres pour la PDO
     * @param string $dirSQLite, le répetoire ou est situé le fichier sqlite
     */
    static function init(string $fileIni, string $dirSQLite = "") {
        self::$dirBDD = $dirSQLite;
        self::$fileIni = $fileIni;
    }

    /**
     * Fonction privée pour retour le lien de la base 
     * @throw  : fichier ini n'existe pas
     */
    private static function getLink()
    {
        if (self::$link) {
            return self::$link;
        }

        if (!$parse = parse_ini_file(self::$fileIni, true))
            throw new \Exception("Unable to open "  . self::$fileIni . ".");

        self::$driver = $parse["db_driver"];
        $dsn = self::$driver . ":";

        if (self::$driver == "sqlite") {
            $dsn .= self::$dirBDD . $parse["db_sqlite"];
        } else {
            foreach ($parse["dsn"] as $k => $v) {
                $dsn .= "{$k}={$v};";
            }
        }

        $user = $parse["db_user"] ?? null;
        $password = $parse["db_password"] ?? null;
        $options = $parse["db_options"]  ?? null;

        self::$link = new \PDO($dsn, $user, $password, $options);

        $attributes = $parse["db_attributes"] ?? [];
        foreach ($attributes as $k => $v) {
            self::$link->setAttribute(
                constant("PDO::{$k}"),
                constant("PDO::{$v}")
            );
        }
        return self::$link;
    }

    public static function __callStatic($name, $args)
    {
        $callback = array(self::getLink(), $name);
        return call_user_func_array($callback, $args);
    }
}
