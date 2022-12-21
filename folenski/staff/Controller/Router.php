<?php

/**
 * Class Router : Permet d'initialiser le site de démarrer le site
 *
 * @author folenski
 * @version  1.0 09/08/2022 : Version initiale
 * @version  1.1 09/12/2022 : maj routage
 * 
 */

namespace Staff\Controller;

use Staff\Models\DBParam;
use Staff\Databases\Database;
use Staff\Databases\Table;
use Staff\Config\Config;
use stdClass;

class Router
{
    /**
     * Compile les templates avec la lib LightnCandy (champs compile = 1)
     * @param string $rep_out, le répertoire ou seront les fichiers
     * @return bool faux si il y a eu une erreur 
     */
    static function start(string $www): bool
    {
        if (($ret = DBParam::parse(file: $www . Config::REP_CONFIG . Config::FILE_INI, server: $_SERVER["SERVER_NAME"])) !== true)
            die(DBParam::ERROR[$ret]);
        Database::init(DBParam::$file_pdo, $www . Config::REP_SQLITE);
        $load = self::load_env(DBParam::$env);
        if ($load === false) {
            $root = $www;
            $filefull = $root . Config::FILE_START;
            if (file_exists($filefull)) {
                require "$filefull";
                return true;
            }
            return false;
        }
        [$WwwCfg, $routes] = $load;
        // Initialize route
        $Router = new \AltoRouter();
        foreach ($routes as $val)
            $Router->map(...$val);

        /* Match the current request */
        $match = $Router->match();
        if (is_array($match)) {
            if (str_contains($match["target"], "@")) {
                [$controller, $action]  = explode('@', $match["target"]);
                if (!is_callable(["self",  $controller])) return false;
                $param = (array_key_exists("params", $match)) ? $match["params"] : null;
                call_user_func_array(["self", $controller], [$www, $action, $WwwCfg, $param]);
            }
            return true;
        }
        return false;
    }

    static function www(string $root, string $action, object $Env, ?array $param): void
    {
        // html#start.html
        if (str_starts_with($action, "link")) {
            [$html, $file] = explode("#", $action);
            $filefull = "{$root}/{$file}";
            if (file_exists($filefull)) require "$filefull";
        } else Www::start($root, $Env, $param); // start php
    }

    static function api(string $root, string $action, object $Env, ?array $param): void
    {
        Api::start($action, $Env, $param);
    }

    /**
     * Permet de charger l'environnement
     * @param string $environnement 
     * @return array|false l'enregistrement de l'environnement
     */
    static function load_env(string $environnement): array|false
    {
        $Env = new Table(Entite: DBParam::get_table("environment"), prefixe: DBParam::$prefixe);

        if (($rows = $Env->get(["name" => $environnement])) === false ||
            count($rows) == 0
        ) return false;

        $WwwCfg = new stdClass();
        $enrlu = $rows[0];

        $WwwCfg->name = $environnement;
        $WwwCfg->Option = (object)json_decode($enrlu->j_option) ?? new stdClass();
        $WwwCfg->index = (array)json_decode($enrlu->j_index);
        $WwwCfg->Contact = (object)json_decode($enrlu->j_contact);
        $route = (array)json_decode($enrlu->j_route);

        // mise à jour des valeurs par défaut
        $WwwCfg->Option->prod = $WwwCfg->Option->prod ?? false;
        $WwwCfg->Option->log = $WwwCfg->Option->log ?? false;
        $WwwCfg->Option->pref_uri  = $WwwCfg->Option->pref_uri ?? "/menu/";
        $WwwCfg->Option->revised  = date("d/m/Y", strtotime("{$enrlu->updated_at}"));
        return [$WwwCfg, $route];
    }
}
