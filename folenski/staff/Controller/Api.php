<?php

/**
 * Point d'entrée pour les appels REST
 *
 * @author folenski
 * @version 1.1 11/08/2022: version initiale
 * @version 1.4 10/07/2023: ajout d'une trace plus explicite quand il y a un mauvais paramétrage
 * @version 1.5 11/07/2023, Mise en place des annotations pour générer le swagger
 * @version 1.6 17/07/2023, ajustement lié à mise en place de l'administration
 * 
 */

namespace Staff\Controller;

use Staff\Resources\ApiMsg;
use Staff\Resources\ApiAuth;
use Staff\Resources\ApiData;
use Staff\Resources\ApiIndex;
use Staff\Resources\ApiUser;
use Staff\Resources\ApiLog;
use Staff\Lib\Rest;

class Api
{
    /**
     * @OA\Info(version="1.0", title="Staff API", description="REST API framework Staff")
     * @OA\Server(url="https://example.localhost", description="URL")
     * @OA\SecurityScheme(type="http", scheme="bearer", securityScheme="bearerAuth")
     * 
     * Contrôleur d'API, la partie spécifique se trouve dans le répertoire Staff/Api
     * @param string $nomApi le nom de de API
     * @param object $Env les paramétres d'environnement du site
     * @param array $param les paramétres lus par le routeur
     */
    static function start(string $nomApi, object $Env, array $param): void
    {
        Rest::clean($Env);

        $Api = match ($nomApi) {
            "auth" => new ApiAuth(),
            "message" => new ApiMsg(),
            "user" => new ApiUser(),
            "log" => new ApiLog(),
            "index" => new ApiIndex(),
            "data" => new ApiData(),
            default => null
        };

        Rest::header('*'); // Headers requis

        if ($Api === null) {
            Rest::reponse([
                "http" => Rest::HTTP_UNAVAIL,
                "errorcode" => ApiAuth::ERR_INTERNAL,
                "response" => ["message" => "unknown endpoint -{$nomApi}-, check routing "]
            ]);
        }

        $methode = $_SERVER["REQUEST_METHOD"];
        $param = array_replace($param, $_GET ?? []);
        $headerAuth = (array_key_exists("HTTP_AUTHORIZATION", $_SERVER)) ? $_SERVER["HTTP_AUTHORIZATION"] : false;
        if ($headerAuth !== false) {
            if (($auth = Rest::bearer($headerAuth)) === false) {
                Rest::reponse([
                    "http" => Rest::HTTP_AUTH_KO,
                    "errorcode" => $Api::ERR_BAD_TOKEN,
                    "response" => ["message" => "bad credentials"]
                ]);
                return;
            }
            $param["authorization"] = $auth;
        }

        $body = (array)json_decode(file_get_contents("php://input")) ?? [];

        $ret = match ($methode) {
            "GET" => $Api->get($body, $param, $Env),
            "POST" => $Api->post($body, $param, $Env),
            "PUT" => $Api->put($body, $param, $Env),
            "DELETE" => $Api->delete($body, $param, $Env),
            default => ["http" => Rest::HTTP_DENIED, "errorcode" => $Api::ERR_INTERNAL, "response" => ["message" => "{$methode} denied"]]
        };
        Rest::reponse($ret, $nomApi, $Env->Option->log);
    }
}
