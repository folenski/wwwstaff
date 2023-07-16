<?php

/**
 * Point d'entrée pour la génération des pages en HTML
 *
 * @author folenski
 * @since 1.0 09/08/2022: Version initiale
 * @since 1.1 19/08/2022: correction bug sur le choix de l'index lorsque l'uri est renseignée 
 * @since 1.2 12/12/2022: utlisation class Config
 * 
 */

namespace Staff\Controller;

use Staff\Lib\Render;
use Staff\Config\Config;

class Www
{
    /**
     * Controleur pour le site WEB
     * @param string $action start ou progress
     * @param object $Env les paramétres d'environnement du site
     * @param array $param les paramétres lus par le routeur
     * @param Render $Render l'objet afin de générer la page web
     * @return false|array avec le nom et les variables nécessaires à la vue
     */
    static function start(string $action, object $Env, ?array $param, Render &$Render): false|array
    {
        $uri = $param["uri"] ?? "";

        if ($action == "start") {
            $Index = self::choose_by_lang($Env->index, self::nav_langages());
        } else {
            $Index = self::choose_by_uri($Env->index, $uri);
            if ($Index === false) return self::error_Fatal("sorry, link breaking");
        }
        if ($Index === false) return self::error_Fatal("oups something wrong with environment config");

        if (
            !property_exists($Index, Config::ENV_INDEX["start"]) ||
            !property_exists($Index, Config::ENV_INDEX["entry"])
        ) {
            return self::error_Fatal("mandantory properties was missed on the index");
        }

        if (isset($_SERVER["HTTP_HOST"])) {
            $www["url"] = self::get_url(
                https: $_SERVER["HTTPS"] ?? null,
                host: $_SERVER["HTTP_HOST"]
            );
        }
        $www["lang"] = $Index?->{Config::ENV_INDEX["lang"]} ?? "en";
        $www["meta"] = $Index?->{Config::ENV_INDEX["meta"]} ?? "";
        $www["title"] = $Index?->{Config::ENV_INDEX["title"]}  ?? "";
        if ($Env->Option->prod !== true) $www["title"] = "[{$Env->name}] {$www["title"]}";

        if (property_exists($Index, Config::ENV_INDEX["nav"])) {
            $id_templates_nav = $Render->fetch_data($Index->{Config::ENV_INDEX["nav"]}, "nav");
            if (count($id_templates_nav) != 1) return self::error_fatal("oups something wrong, check props nav");

            if ($action == "start") {
                $nav_sel = $Render->set_active_first($id_templates_nav[0], "nav");
            } else {
                $nav_sel = $Render->set_active_uri($id_templates_nav[0], "nav", $uri);
            }

            if ($nav_sel === false) return self::error_fatal("oups something wrong, link breaking");
            if (property_exists($nav_sel, "meta"))  $www["meta"] = $nav_sel->meta;

            $Render->update_prefixe($id_templates_nav[0], "nav");
            $content_ref = $nav_sel->ref;
        }

        $divs = $Render->fetch_data($content_ref, "content");
        if (count($divs) == 0)
            return self::error_fatal("oups something wrong, check this ref {$content_ref}");

        $Render->sort($divs, "content");

        $www["view"]  = $Index->{Config::ENV_INDEX["entry"]};
        return $www;
    }

    /**
     * @return array retourne le tableau avec les langues préférées
     */
    static function nav_langages(string $langs = null): array
    {
        $tab = [];
        if ($langs === null) $langs = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
        foreach (explode(",", $langs) as $value) {
            array_push($tab, substr($value, 0, 2));
        }
        return array_unique($tab);
    }

    /**
     * Choisit l'index du site en fonction de la langue ou sinon il prend l'index par défaut
     * @param array $indexs le tableau des indexs
     * @param array $langs tableau sur les langues supportées par le navigateur
     * @return object|false l'index ou null si celui n'est pas trouvé
     */
    static function choose_by_lang(array $indexs, array $langs): object|false
    {
        foreach ($langs as $lg) {
            foreach ($indexs as $Val) {
                if ($Val->language === $lg) return $Val;
            }
        }
        foreach ($indexs as $Val) {  // on recherche la valeur par defaut 
            if (@$Val->default === true) return $Val;
        }
        return false;
    }

    /**
     * Choisit l'index du site en fonction de l'uri
     * @param array $indexs le tableau des indexs
     * @param string $uri l'uri positionné
     * @return object|false l'index ou null si celui n'est pas trouvé
     */
    static function choose_by_uri(array $indexs, string $uri): object|false
    {
        //var_dump($indexs, $uri);
        if ($uri === "") return false;
        $filtrer_uri = array_filter($indexs, function ($Val) {
            return $Val?->uri !== "";
        });
        if (count($filtrer_uri) == 1) return $filtrer_uri[array_key_first($filtrer_uri)];
        foreach ($filtrer_uri as $Val) {
            if (str_starts_with($uri, $Val->uri)) return $Val;
        }
        return false;
    }

    /**
     * @param string  $host
     * @param ?string $https 
     * @return string retourne l'url du site reconstituée
     */
    static function get_url(string $host, ?string $https): string
    {
        return ($https !== null)  ? "https://{$host}/" : "http://{$host}/";
    }

    /**
     * Fonction pour afficher le contenu d'une variable php 
     * utile pour en phase de développement
     * @param string $libelle le libellé
     * @param mixed $obj l'objet à inspecter
     * @param bool $trace vrai si on affiche
     */
    static function debug(string $libelle, mixed $obj, bool $trace = true): void
    {
        // affiche l'objet PHP
        if ($trace) {
            echo "<strong>$libelle</strong><pre>";
            print_r($obj);
            echo "</pre>";
        }
    }

    /**
     * @param string $msg à écrire
     * @return bool retourne faux
     */
    static function error_fatal(string $msg): bool
    {
        echo "<br><h1>Internal Error -{$msg}-</h1><br>";
        return false;
    }
}
