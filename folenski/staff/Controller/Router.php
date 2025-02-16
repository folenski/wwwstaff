<?php

/**
 * Class Router : Initialise l'environnement et appelle les contrôleurs en fonction du contexte
 *
 * @author folenski
 * @version  1.0 09/08/2022: Version initiales
 * @version  1.1 09/12/2022: maj routage
 * @version  1.2 10/01/2023: maj suppression champs j_contact
 * @version  1.3 12/07/2023: refonte du routage
 * @version  1.4 19/04/2024: prise en compte de la structure de la table environment
 * 
 */

namespace Staff\Controller;

use Staff\Models\DBParam;
use Staff\Databases\Database;
use Staff\Databases\Table;
use Staff\Lib\Render;
use Staff\Config\Config;
use stdClass;

class Router
{
    /**
     * [[Point d'entrée]], initialise l'environnement et appelle les contrôleurs. Si la base de données
     * n'a pas été initialisée, créé les tables et charge les données via des fichiers json
     * 
     * @param string $www le répertoire racine du projet
     * @return bool retourne false en cas d'erreur 
     */
    static function start(string $www): bool
    {
        $www .= (substr($www, -1) === '/') ? '' : '/';
        if (($ret = DBParam::parse(file: $www . Config::REP_CONFIG . Config::FILE_INI, server: $_SERVER["SERVER_NAME"])) !== true)
            die(DBParam::ERROR[$ret]);

        Database::init(DBParam::$file_pdo, $www . Config::REP_SQLITE);

        $load = self::_load(DBParam::$env, DBParam::$mail);
        if ($load === false) {
            self::link(root: $www, action: Config::VIEWS_INIT);
            return true;
        }

        [$WwwCfg, $routes] = $load;
        Www::debug("> Number of route are generated...", count($routes), $WwwCfg->Option->debug);

        $Router = new \AltoRouter();
        foreach ($routes as $val)
            $Router->map(...$val);

        $match = $Router->match();
        if (is_array($match)) {
            Www::debug("> current match ...", $match["target"], $WwwCfg->Option->debug);

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

    /**
     * Appelle le contrôleur pour la génération des pages web
     * @param string $root le répertoire racine du projet
     * @param string $action determinée par la route active
     * @param object $Env contient les paramétrages contenus dans la table environment 
     * @param ?array $param les paramétres peuvent être des sections de l'url déclarées dans les routes
     */
    static function www(string $root, string $action, object $Env, ?array $param): void
    {
        if ($Env->Option?->maintenance === true) {
            self::link(root: $root, action: Config::VIEWS_UNDER);
        } else {
            $Render = new Render($root . Config::REP_TMP, Config::PREFIXE_NAV, $Env->Option->pref_uri);
            $www = Www::start($action, $Env, $param, $Render);

            if ($www !== false) {
                $render = $Render->render();
                if ($render === null) {
                    Www::error_fatal("rendering html");
                } else {
                    Www::debug("> views selected ...", $www["view"], $Env->Option->debug);
                    $fileEntry = $root . Config::REP_VIEWS . $www["view"];
                    require $fileEntry;
                }
            }
        }
    }

    /**
     * Renvoie vers le lien contenu dans la variable action
     * @param string $root le répertoire racine du projet
     * @param string $action determinée par la route active
     * @param object $Env contient les paramétrages contenus dans la table environment 
     * @param ?array $param les paramétres peuvent être des sections de l'url déclarées dans les routes
     */
    static function link(string $root, string $action, ?object $Env = null, ?array $param = null): void
    {
        $file = "{$root}{$action}";
        if (!file_exists($file))
            echo ("file not exist {$file}");
        else
            require "$file";
    }

    /**
     * Active le contrôleur pour les API REST du site
     * @param string $root le répertoire racine du projet
     * @param string $action determinée par la route active
     * @param object $Env contient les paramétrages contenus dans la table environment 
     * @param ?array $param les paramétres peuvent être des sections de l'url déclarées dans les routes
     */
    static function api(string $root, string $action, object $Env, ?array $param): void
    {
        Api::start($action, $Env, $param);
    }

    /**
     * Lit la table environment en fonction du contexte déterminé dans le fichier de config
     * @param string $environnement déterminé en fonction de l'url principalement
     * @param string $mail le mail recevoir les données de contact
     * @return array|false retourne un tableau [ options, routes] ou false si la base est vide
     */
    private static function _load(string $environnement, string $mail = ""): array|false
    {
        $Env = new Table(Entite: DBParam::get_table("environment"), prefixe: DBParam::$prefixe);

        if (($rows = $Env->get(["name" => $environnement])) === false ||
            count($rows) == 0
        ) return false;

        $WwwCfg = new stdClass();
        $enrlu = $rows[0];

        $WwwCfg->name = $environnement;
        $WwwCfg->Option = (object)json_decode($enrlu->j_option) ?? new stdClass();
        $WwwCfg->index = $WwwCfg->Option->index ?? new stdClass();
        $WwwCfg->Option->prod = $WwwCfg->Option->prod ?? false;
        $WwwCfg->Option->log = $WwwCfg->Option->log ?? false;
        $WwwCfg->Option->debug = $WwwCfg->Option->debug ?? false;
        $WwwCfg->Option->pref_uri  = $WwwCfg->Option->pref_uri ?? "/menu/";
        $WwwCfg->Option->clean_limit = $WwwCfg->Option->clean_limit ?? 7;
        $WwwCfg->Option->purge = $WwwCfg->Option->purge ?? 30;
        $WwwCfg->Option->revised  = date("d/m/Y", strtotime("{$enrlu->updated_at}"));

        if (isset($WwwCfg->Option->contact)) {  // si il n'existe pas de propriété contact, on ne fait rien
            if ($mail != "") {
                $WwwCfg->Option->contact->send_mail = true; // activer l'envoi de mail
                $WwwCfg->Option->contact->mail = $mail;
            } 
        }

        $routes = self::_get_routes($WwwCfg->Option);
        if (isset(($WwwCfg->Option->custom_link)) && (gettype($WwwCfg->Option->custom_link) == "array")) {
            if (count($WwwCfg->Option->custom_link) == 4)
                array_push($routes, $WwwCfg->Option->custom_link);
        }

        return [$WwwCfg, $routes];
    }

    /**
     * @param object $Option afin d'avoir le tableau "routes" qui contient les routes demandées
     * @return array retourne un tableau avec les routes internes
     */
    private static function _get_routes(object $Option): array
    {
        $routes = [];
        if (isset(($Option->routes)) && (gettype($Option->routes) == "array")) {
            foreach ($Option->routes as $rte_in) {
                switch ($rte_in) {
                    case "www":
                        array_push($routes, Config::ROUTE_WWW_START);
                        $add_route = Config::ROUTE_WWW_PROGRESS;
                        $add_route[1] =  "{$Option->pref_uri}{$add_route[1]}";
                        array_push($routes, $add_route);
                        break;
                    case "message":
                        array_push($routes, Config::ROUTE_API_MSG);
                        break;
                    case "reload":
                        $add_route = Config::ROUTE_RELOAD;
                        $add_route[2] .= Config::VIEWS_INIT;
                        array_push($routes, $add_route);
                        break;
                    case "admin":
                        $routes = array_merge($routes, Config::ROUTE_API_ADMIN);
                        break;
                    case "rest":
                        array_push($routes, Config::ROUTE_API_ENV);
                        array_push($routes, Config::ROUTE_API_DATA);
                        break;
                    default:
                }
            }
        }
        return $routes;
    }
}
