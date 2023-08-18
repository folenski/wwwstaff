<?php

/**
 * Trait utilisé par les ressources REST  
 * 
 * @author  folenski
 * @version 1.0 10/12/2022: version initiale 
 * @version 1.1 10/07/2023: ajout d'un paramétre à la méthode resourcesUnavail
 * @version 1.2 10/07/2023: 
 *    - ajout d'une propriété pour connaître la méthode HTTP en cours
 *    - ajout méthode convDate et retOk
 */

namespace Staff\Resources;

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
    private string $_token = "authorization";

    private HttpMethod $_method = HttpMethod::UNKNOWN;

    function get(array $data, array $param, object $Env): array
    {
        return $this->resourcesUnavail("not available");
    }

    function post(array $data, array $param, object $Env): array
    {
        return $this->resourcesUnavail("not available");
    }

    function put(array $data, array $param, object $Env): array
    {
        return $this->resourcesUnavail("not available");
    }

    function delete(array $data, array $param, object $Env): array
    {
        return $this->resourcesUnavail("not available");
    }

    /**
     *  @OA\Schema(schema="resourcesUnavail", 
     *    @OA\Property(property="errorcode", type="integer", example=13), 
     *    @OA\Property(property="message", type="string", example="internal error"),
     *  ) 
     * 
     * @return array [http=503, errorcode=13, message="internal error"|$message]
     */
    private function resourcesUnavail(string $message = "internal error"): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => [
                "message" => $message
            ]
        ];
    }

    /**
     * @OA\Schema(schema="unauthorized", 
     *    @OA\Property(property="errorcode", type="integer", example=20), 
     *    @OA\Property(property="message", type="string", example="bad credentials"),
     * )
     *
     * @return array [http=401, errorcode=20, message="bad credentials"|$message]
     */
    private function unauthorized(string $message = "bad credentials", int $errorcode = RestInterface::ERR_NO_TOKEN): array
    {
        return [
            "http" => RestInterface::HTTP_AUTH_KO,
            "errorcode" => $errorcode,
            "response" => [
                "message" => "$message"
            ]
        ];
    }

    /**
     *  @OA\Schema(schema="controlsFailed", 
     *    @OA\Property(property="errorcode", type="integer", example=21), 
     *    @OA\Property(property="message", type="string", example="fields missing"),
     *  ) 
     * 
     * Response when checks is failed
     * @return array [http=400, errorcode=21|22, message=$message]
     */
    private function controlsFailed(string $message): array
    {
        $errorcode = ($this->_method === HttpMethod::GET) ? $errorcode = RestInterface::ERR_BAD_URI : RestInterface::ERR_BAD_BODY;
        return [
            "http" => RestInterface::HTTP_BAD,
            "errorcode" => $errorcode,
            "response" => [
                "message" => $message
            ]
        ];
    }

    /**
     * 
     *  @OA\Schema(schema="GenericOk", 
     *    @OA\Property(property="message", type="string", example="done"),
     *  )
     * 
     *  @OA\Schema(schema="GenericError", 
     *    @OA\Property(property="errorcode", type="integer", example=200), 
     *    @OA\Property(property="message", type="string", example="an error was encountered"),
     *  ) 
     * 
     * @return array [http=20x, errorcode=$errorcode, data | message=$message]
     */
    private function retOk(
        int $errorcode = RestInterface::ERR_OK,
        ?array $data = null,
        ?string $message = null,
    ): array {
        $response = [];

        if ($errorcode == 0 && ($this->_method == HttpMethod::POST || $this->_method == HttpMethod::PUT)) {
            $http = RestInterface::HTTP_CREATED;
        } else {
            $http = RestInterface::HTTP_OK;
        }
        if ($data !== null) {
            $response = $data;
        } else {
            $response["message"] = ($message === null) ? "done" : $message;
        }
        return
            [
                "http" => $http,
                "errorcode" => $errorcode,
                "response" => $response
            ];
    }

    /**
     * Ckeck owner's token
     * @return bool true is admin's token
     */
    private function hasAdminToken(array $param): bool
    {
        if (!array_key_exists($this->_token, $param)) return false;
        return ($param[$this->_token]["role"] === 2);
    }

    /**
     * Check if token is given
     * @return bool true token is found
     */
    private function hasToken(array $param): bool
    {
        return (array_key_exists($this->_token, $param));
    }

    /**
     * @return bool true if admin's token or user's token is owned by $name given 
     */
    private function isPermitted(string $name, array $param): bool
    {
        if (!$this->hasToken($param)) return false;
        if ($this->hasAdminToken($param)) return true;
        return $name === $param[$this->_token]["user"];
    }

    /**
     * @return string format AAAA-MM-JJTHH:MM:SS.00000
     */
    function convDate(string $date): string
    {
        return str_replace(" ", "T", $date) . ".000Z";
    }
}
