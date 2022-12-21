<?php

/**
 * Class Www : Controleur pour site WEB, lorsqu'on veut que les pages soient rendues par php
 *
 * @author folenski
 * @since 1.0 09/08/2022: Version initiale
 * @since 1.1 19/08/2022: correction bug sur le choix de l'index lorsque l'uri est renseignée 
 * @since 1.2 12/12/2022: utlisation class Config
 * 
 */

namespace Staff\Controller;

use Staff\Models\DBParam;
use Staff\Lib\Render;
use Staff\Config\Config;

class Www
{
    /**
     * Controleur pour le site WEB
     * @param string $root le répertoire du site
     * @param object $Env les paramétres d'environnement du site
     * @param array $param les paramétres lus par le routeur
     */
    static function start(string $root, object $Env, ?array $param): void
    {
        $repViews = $root . Config::REP_VIEWS;

        if ($Env->Option->maintenance) {
            require "{$repViews}under.html";
            return;
        }

        if ($param === null) $uri = "";
        else $uri = $param["uri"] ?? "";

        $Index = self::choose_index(
            $Env->index,
            self::lang_nav($_SERVER["HTTP_ACCEPT_LANGUAGE"]),
            $uri
        );
        if (
            $Index === null
            || !property_exists($Index, Config::ENV_INDEX["start"]) ||
            !property_exists($Index, Config::ENV_INDEX["entry"])
        ) {
            self::errorFatal("mistake index");
            return;
        }

        $www["url"] = self::get_url(
            https: $_SERVER["HTTPS"] ?? null,
            httpServer: $_SERVER["HTTP_HOST"]
        );

        $www["lang"] = $Index?->{Config::ENV_INDEX["lang"]} ?? "en";
        $www["meta"] = $Index?->{Config::ENV_INDEX["meta"]} ?? "";
        $www["title"] = $Index?->{Config::ENV_INDEX["title"]}  ?? "";
        if ($Env->Option->prod !== true) $www["title"] = "[{$Env->name}] {$www["title"]}";

        $Render = new Render(
            DBParam::$prefixe,
            $Env->Option->pref_uri,
            $root . Config::REP_TMP
        );
        if (property_exists($Index, Config::ENV_INDEX["nav"])) {
            $divs = $Render->fetch($Index->{Config::ENV_INDEX["nav"]}, "nav");
            if ($divs === null) {
                self::errorFatal("fetch menu");
                return;
            }
            $Render->update_uri($divs, "nav", $uri);
        }
        if ($uri != "") {
            if (($Data = $Render->get_metadata($uri)) === null) {
                self::errorFatal("mistake ref nav");
                return;
            }
            $ref = $Data->ref;
            if ($Data->meta != "") $www["meta"] = $Data->meta;
        } else {
            $ref = $Index->{Config::ENV_INDEX["start"]};
        }
        if (($divs = $Render->fetch($ref, "content")) === null) {
            self::errorFatal("mistake ref content");
            return;
        }
        $Render->sort($divs, "content");
        $render = $Render->render();
        if ($render === null) {
            self::errorFatal("render page");
            return;
        }

        $fileEntry= $repViews . $Index->{Config::ENV_INDEX["entry"]}; 
        require $fileEntry;
    }

    /**
     * @param string $accept => $_SERVER["HTTP_ACCEPT_LANGUAGE"]
     * @return array retourne le tableau unique des langues supportées
     */
    static function lang_nav(string $accept): array
    {
        $tab = [];
        foreach (explode(",", $accept) as $value) {
            array_push($tab, substr($value, 0, 2));
        }
        return array_unique($tab);
    }

    /**
     * Choisi l'index du site en fonction de l'uri, à défaut de la langue ou 
     * l'index par défaut
     * @param array $indexs le tableau des indexs
     * @param array $langs tableau sur les langues supportées par le navigateur
     * @param string $uri l'uri positionné
     * @return object|null l'index ou null si celui n'est pas trouvé
     */
    static function choose_index(array $indexs, array $langs, string $uri): object|null
    {
        switch (count($indexs)) {
            case 0:
                return null;
            case 1:
                return $indexs[0];
        }
        if ($uri == "") {
            foreach ($langs as $lg) {
                foreach ($indexs as $Val) {
                    if ($Val->language === $lg) return $Val;
                }
            }
            foreach ($indexs as $Val) {  // on recherche la valeur par defaut 
                if (($Val->default ?? false) === true) return $Val;
            }
        } else {
            $filtrer_uri = array_filter($indexs, function ($Val) {
                return ($Val->uri ?? "") != "";
            });
            if (count($filtrer_uri) == 1) return $filtrer_uri[array_key_first($filtrer_uri)];
            foreach ($filtrer_uri as $Val) {
                if (str_starts_with($uri, $Val->uri)) return $Val;
            }
        }
        return null;
    }

    /**
     * @param string $httpServer le host
     * @param ?string $https 
     * @return string Permet de retrouver l'url du site 
     */
    static function get_url(string $httpServer, ?string $https): string
    {
        return ($https !== null)  ? "https://{$httpServer}/" : "http://{$httpServer}/";
    }

    /**
     * Fonction pour afficher le contenu d'une variable php 
     * utile pour en phase de développement
     * @param string $libelle le libellé
     * @param mixed $obj l'objet à inspecter
     * @param bool $trace vrai si on affiche
     */
    static function debug(string $libelle, $obj, bool $trace = true): void
    {
        // affiche l'objet PHP
        if ($trace) {
            echo "<br><strong>$libelle</strong><pre>";
            print_r($obj);
            echo "</pre>";
        }
    }

    /**
     * @param string $msg 
     */
    static function errorFatal(string $msg): void
    {
        echo "<br><h1>Internal Error -{$msg}-</h1><br>";
    }
}
