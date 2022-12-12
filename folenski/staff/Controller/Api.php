<?php

/**
 * Class Api : Controleur pour les API REST
 *
 * @author folenski
 * @version 1.0.1 11/08/2022: Version initiale
 * @version 1.0.2 09/12/2022: ajout de la méthode index, gestion des reponses
 * 
 */

namespace Staff\Controller;

use Staff\Api\ApiMsg;
use Staff\Api\ApiAuth;
use Staff\Api\ApiData;
use Staff\Api\ApiIndex;
use Staff\Api\ApiUser;
use Staff\Api\ApiLog;
use Staff\Services\Rest;

class Api
{
    /**
     * Controleur pour le site WEB
     * @param string $nomApi le nom de l'appli
     * @param object $Env les paramétres d'environnement du site
     * @param array $param les paramétres lus par le routeur
     */
    static function start(string $nomApi, object $Env, array $param): void
    {
        Rest::clean($Env);
        switch ($nomApi) {
            case "auth":
                $Api = new ApiAuth();
                break;
            case "message":
                $Api = new ApiMsg();
                break;
            case "user":
                $Api = new ApiUser();
                break;
            case "log":
                $Api = new ApiLog();
                break;
            case "index":
                $Api = new ApiIndex();
                break;
            case "data":
                $Api = new ApiData();
                break;
            default:
                Rest::reponse([
                    "http" => Rest::HTTP_UNAVAIL,
                    "errorcode" => ApiAuth::ERR_INTERNAL,
                    "response" => [
                        "content" => "internal error"
                    ]
                ]);
                return;
        }
        Rest::header('*'); // Headers requis
        $methode = $_SERVER["REQUEST_METHOD"];
        if (array_key_exists("HTTP_AUTHORIZATION", $_SERVER)) {
            if (($auth = Rest::bearer($_SERVER["HTTP_AUTHORIZATION"])) === false) {
                Rest::reponse([
                    "http" => Rest::HTTP_AUTH_KO,
                    "errorcode" => $Api::ERR_BAD_TOKEN,
                    "response" => [
                        "content" => "token not valid"
                    ]
                ]);
                return;
            }
            $param = [$param, ...$auth];
        }
        if ($methode == "GET") {
            $donnees = [...$_GET];
        } else {
            $donnees = (array)json_decode(file_get_contents("php://input"));
            if ($donnees === null) $donnees = new \stdClass();
        }
        switch ($methode) {
            case "GET":
                $ret = $Api->get($donnees, $param, $Env);
                break;
            case "POST":
                $ret = $Api->post($donnees, $param, $Env);
                break;
            case "PUT":
                $ret = $Api->put($donnees, $param, $Env);
                break;
            case "DELETE":
                $ret = $Api->delete($donnees, $param, $Env);
                break;
            default:
                Rest::reponse([
                    "http" => Rest::HTTP_DENIED,
                    "errorcode" => $Api::ERR_INTERNAL,
                    "response" => [
                        "content" => "{$methode} denied"
                    ]
                ]);
                return;
        }
        Rest::reponse($ret, $nomApi, $Env->Option->log);
    }
}
