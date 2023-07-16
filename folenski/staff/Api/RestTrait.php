<?php

/**
 * Trait utilisé par les API spécialisées  
 * 
 * @author  folenski
 * @version 1.0 10/12/2022: version initiale 
 * @version 1.1 10/07/2023: ajout d'un paramétre à la méthode retUnAvail
 * @version 1.2 10/07/2023: ajout d'une propriété pour connaître la méthode HTTP en cours
 *  
 */

namespace Staff\Api;

enum HttpMethod
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case UNKNOWN;
};

trait RestTrait
{
    private HttpMethod $_method = HttpMethod::UNKNOWN;

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
    function retUnAvail(string $msg = "internal error"): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => [
                "content" => $msg
            ]
        ];
    }

    /**
     * @return array retourne la valeur si il n'y a pas de bearer dans l'entête
     */
    function retTokenNeeded(): array
    {
        return [
            "http" => RestInterface::HTTP_AUTH_KO,
            "errorcode" => RestInterface::ERR_BAD_TOKEN,
            "response" => [
                "content" => "token is required"
            ]
        ];
    }

    /**
     * Les contrôles ont échoués
     * 
     * @param $lib le champs où ce situe le problème
     * @param $get (deprecated) vrai si méthode get car les valeurs sont transmises par l'uri
     * @return array retourne la champs valeur qui est en erreur de format
     */
    function retCrlFail(string $lib, bool $get = false): array
    {
        $errorcode = ($get) ? RestInterface::ERR_BAD_URI : RestInterface::ERR_BAD_BODY;
        if ($this->_method == HttpMethod::GET) $errorcode = RestInterface::ERR_BAD_URI;
        return [
            "http" => RestInterface::HTTP_BAD,
            "errorcode" => $errorcode,
            "response" => [
                "content" => $lib
            ]
        ];
    }

    /**
     * @param $errorcode le code erreur par défaut 0
     * @param $content message retour générique par défaut "done", il faut l'affecter à null pour le retirer
     * @param $data tableaux de données
     * @param $isApp est un code erreur applicatif, on doit rajouter 100
     * @return array retourne la réponse formatée
     */
    function retApi(
        int $errorcode = RestInterface::ERR_OK,
        ?string $content = "done",
        ?array $data = null,
        bool $isApp = false,
    ): array {
        $response = [];
        $http = RestInterface::HTTP_OK;

        if ($content !== null) {
            $response["content"] = $content;
            if ($data !== null) $response["data"] = $data;
        } else {
            $response = $data ?? [];
        }

        if ($isApp) {
            $errorcode = ($errorcode != RestInterface::ERR_OK) ?
                $errorcode + RestInterface::ERR_CUSTOM_APP : $errorcode;
        }

        if ($errorcode == 0 && ($this->_method == HttpMethod::POST || $this->_method == HttpMethod::PUT)) {
            $http = RestInterface::HTTP_CREATED;
        }

        return
            [
                "http" => $http,
                "errorcode" => $errorcode,
                "response" => $response
            ];
    }
}
