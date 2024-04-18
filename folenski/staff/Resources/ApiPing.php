<?php

/**
 * Gestion REST 
 * 
 * @author folenski
 * @version 1.0 25/08/2023: Version Initiale
 */

namespace Staff\Resources;

final class ApiPing implements RestInterface
{
    use RestTrait;

    /**
     * @OA\Get(
     *     path="/api/ping",
     *     description="Test if the website is alive",
     *     operationId="GetPing",
     *     tags={"Test website"},
     *     @OA\Response(response=200, description="Values are online | maintenance ",
     *        @OA\JsonContent(@OA\Property(property="message", type="string", example="online")),
     *     ),
     * )
     */
    function get(array $body, array $param, object $Env): array
    {
        if (($Env->Option->maintenance ?? true))
            return  $this->retOk(message: "maintenance");
        return $this->retOk(message: "online");
    }
}
