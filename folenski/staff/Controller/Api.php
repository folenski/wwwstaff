<?php

/**
 * Point d'entrée pour les appels REST
 *
 * @author folenski
 * @version 1.1 11/08/2022: version initiale
 * @version 1.4 10/07/2023: ajout d'une trace plus explicite quand il y a un mauvais paramétrage
 * @version 1.5 11/07/2023, Mise en place des annotations pour générer le swagger
 * 
 */

namespace Staff\Controller;

use Staff\Api\ApiMsg;
use Staff\Api\ApiAuth;
use Staff\Api\ApiData;
use Staff\Api\ApiIndex;
use Staff\Api\ApiUser;
use Staff\Api\ApiLog;
use Staff\Lib\Rest;

class Api
{
    /**
     * @OA\Info(version="1.0", title="Staff API", description="REST API framework Staff")
     * @OA\Server(url="https://example.localhost", description="URL")
     * @OA\SecurityScheme(type="http", scheme="bearer", securityScheme="bearerAuth")
     * @OA\Schema(schema="GenericError", 
     *    @OA\Property(property="errorcode", type="integer", nullable=true), 
     *    @OA\Property(property="content", type="string"),
     * )
     * 
     * Contrôleur d'API, la partie spécifique se trouve dans le répertoire Staff/Api
     * 
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
                "response" => ["content" => "unknown endpoint -{$nomApi}-, check routing "]
            ]);
        }

        $methode = $_SERVER["REQUEST_METHOD"];
        $headerAuth = (array_key_exists("HTTP_AUTHORIZATION", $_SERVER)) ? $_SERVER["HTTP_AUTHORIZATION"] : false;
        if ($headerAuth !== false) {
            if (($auth = Rest::bearer($headerAuth)) === false) {
                Rest::reponse([
                    "http" => Rest::HTTP_AUTH_KO,
                    "errorcode" => $Api::ERR_BAD_TOKEN,
                    "response" => ["content" => "token not valid"]
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

        $ret = match ($methode) {
            "GET" => $Api->get($donnees, $param, $Env),
            "POST" => $Api->post($donnees, $param, $Env),
            "PUT" => $Api->put($donnees, $param, $Env),
            "DELETE" => $Api->delete($donnees, $param, $Env),
            default => ["http" => Rest::HTTP_DENIED, "errorcode" => $Api::ERR_INTERNAL, "response" => ["content" => "{$methode} denied"]]
        };
        Rest::reponse($ret, $nomApi, $Env->Option->log);
    }
}
