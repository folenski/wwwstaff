<?php

/**
 * Gestion REST pour l'authentification
 * 
 * @author folenski
 * @since   1.2.1 09/12/2022
 * @version 1.0.0 Version initialie
 * @version 1.1.0 Utilisation de la class Carray
 * @version 1.2.0 Refactoring de code
 * @version 1.2.1 09/12/2022, Utilisation du trait + response
 * @version 1.3.0 16/12/2022, Utilisation de l'api retour 
 * 
 */

namespace Staff\Resources;

use Staff\Security\Authen;
use Staff\Lib\Carray;

final class ApiAuth implements RestInterface
{
    use RestTrait;

    /**
     * @OA\Post(
     *     path="/api/auth",
     *     description="This service provides a security token",
     *     operationId="PostAuth",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *        required=true, 
     *        @OA\JsonContent(
     *           required={"user", "password"},
     *           @OA\Property(property="user", type="string", example="jessica"),
     *           @OA\Property(property="password", type="string", example="12345Pass@"),
     *         ),
     *     ),
     *     @OA\Response(response=201, description="User is granted and his token is created", 
     *     @OA\JsonContent(
     *         @OA\Property(property="token", type="string"),
     *         @OA\Property(property="mail", type="string"),
     *         @OA\Property(property="last", type="string", format="date-time" ),
     *     )),
     *     @OA\Response(response=200, description="An error was encountered", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=400, description="Invalid body", @OA\JsonContent(ref="#/components/schemas/controlsFailed")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/resourcesUnavail")),
     * )
     */
    function post(array $data, array $param, object $Env): array
    {
        $this->_method = HttpMethod::POST;

        [$controle, $fails, $user, $pass] = Carray::arrayCheck(
            $data,
            [
                "user" => ["protected" => true, "limit" => 100, "type" => "string"],
                "password" => ["type" => "string"]
            ]
        );
        if (!$controle) return $this->controlsFailed($fails);

        [$retour, $token, $mail, $last] = Authen::login($user, $pass, 60);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return $this->retOk(errorcode: 131, message: $libelle);
        }
        return $this->retOk(data: [
            "token" => $token, "mail" => $mail, "last" => $this->convDate($last)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/auth",
     *     description="This service revokes the security token",
     *     operationId="DeleteAuth",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="The disconnection is done", @OA\JsonContent(ref="#/components/schemas/GenericOk")),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/unauthorized")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/resourcesUnavail")),
     * )
     */
    function delete(array $data, array $param, object $Env): array
    {
        $this->_method = HttpMethod::DELETE;
        if (!$this->hasToken($param)) return $this->unauthorized();
        $token = $param["authorization"]["token"];
        if (!Authen::revoke($token)) return $this->resourcesUnavail();
        return $this->retOk();
    }
}
