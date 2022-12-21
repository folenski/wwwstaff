<?php

/**
 * Trait pour repondre si les méthodes http GET, POST, PUT, DELETE ne sont pas implementées 
 * 
 * @author  folenski
 * @version 1.0 10/12/2022: version initiale 
 *  
 */

namespace Staff\Api;

trait RestTrait
{
    function get(array $data, array $param, object $Env): array
    {
        return $this->retNotImpl();
    }

    function post(array $data, array $param, object $Env): array
    {
        return $this->retNotImpl();
    }

    function put(array $data, array $param, object $Env): array
    {
        return $this->retNotImpl();
    }

    function delete(array $data, array $param, object $Env): array
    {
        return $this->retNotImpl();
    }

    /**
     * @return array retourne la valeur quand la méthode REST n'est pas implementée
     */
    function retNotImpl(): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => ["content" => "not implemented"]
        ];
    }

    /**
     * @return array retourne la valeur quand il y a un problème de ressource type SQL
     */
    function retUnAvail(): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => [
                "content" => "internal error"
            ]
        ];
    }

    /**
     * @return array retourne la valeur si il n'y a pas de bearer dans l'entête
     */
    function retTokenNeeded(): array
    {
        return [
            "http" => RestInterface::HTTP_BAD,
            "errorcode" => RestInterface::ERR_BAD_TOKEN,
            "response" => [
                "content" => "token needed"
            ]
        ];
    }

    /**
     * @param $lib le champs ou se situe le problème
     * @param $get vrai si méthode get car les valeurs sont transmises par l'uri
     * @return array retourne la valeur dans le cas d'une erreur de format
     */
    function retCrlFail(string $lib, bool $get = false): array
    {
        return [
            "http" => RestInterface::HTTP_BAD,
            "errorcode" => ($get) ? RestInterface::ERR_BAD_URI : RestInterface::ERR_BAD_BODY,
            "response" => [
                "content" => $lib
            ]
        ];
    }

    /**
     * @param $errorcode le code erreur par défaut 0
     * @param $content message retour générique par défaut "done", il faut mettre null pour le retirer
     * @param $data tableaux de données
     * @param $isApp code erreur applicatif on rajoute 100
     * @return array retourne la réponse formatée
     */
    function retApi(
        int $errorcode = RestInterface::ERR_OK,
        ?string $content = "done",
        ?array $data = null,
        bool $isApp = false,
    ): array {
        $response = [];
        if ($isApp) {
            $errorcode = ($errorcode != RestInterface::ERR_OK) ?
                $errorcode + RestInterface::ERR_CUSTOM_APP : $errorcode;
        }
        if ($content !== null) {
            $response["content"] = $content;
            if ($data !== null) $response["data"] = $data;
        } else {
            $response = $data ?? [];
        }
        return
            [
                "http" => RestInterface::HTTP_OK,
                "errorcode" => $errorcode,
                "response" => $response
            ];
    }
}
