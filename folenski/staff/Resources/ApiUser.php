<?php

/**
 * Class ApiUser, Gestion REST pour l'authentification 
 * 
 * @author folenski
 * @version 1.0 Version initiale
 * @version 1.1 04/07/2022: Refactoring
 * @version 1.2 16/12/2022: Prise en compte nouveau model table USER et les retours API
 * @version 1.2.3 11/07/2023, Mise en place des annotations pour générer le swagger
 * 
 */

namespace Staff\Resources;

use Staff\Security\Authen;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Models\User;
use Staff\Lib\Carray;

final class ApiUser implements RestInterface
{
    use RestTrait;

    /**
     * @OA\Schema(schema="GetApiUser", 
     *    @OA\Property(property="name", type="string", example="john"),
     *    @OA\Property(property="mail", type="string", example="john@gmail.com"),
     *    @OA\Property(property="admin", type="boolean"),
     *    @OA\Property(property="last", type="string", format="date-time" ),
     * )
     * @OA\Get(
     *     path="/api/user/{user}",
     *     description="Return a array of user",
     *     operationId="GetUser",
     *     tags={"Manage Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *        required=true,
     *        name="user",
     *        in="path",
     *        description="Name",
     *        @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="User object", 
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/GetApiUser"))
     *     ),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/unauthorized")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/resourcesUnavail")),
     * )
     * @OA\Get(
     *     path="/api/user",
     *     description="List a user or users",
     *     operationId="GetUsers",
     *     tags={"Manage Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="User object", 
     *           @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/GetApiUser"))
     *     ),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/unauthorized")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/resourcesUnavail")),
     * )
     */
    function get(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::GET;
        $User = new Table(DBParam::$prefixe, new User());

        [$controle, $fails, $name] = Carray::arrayCheck(
            $param,
            ["name" => ["mandatory" => false, "default" => "", "protected" => true, "limit" => 100]]
        );
        if (!$controle) return $this->controlsFailed($fails);
        if (!$this->isPermitted($name, $param)) return $this->unauthorized();

        if ($name === "") {
            $rows = $User->get(limit: 0);
        } else {
            $rows = $User->get(id: ["user" => $name]);
        }
        if ($rows === false) return $this->resourcesUnavail();

        $bodyOut = [];
        foreach ($rows as $value) {
            array_push(
                $bodyOut,
                [
                    "name" => $value->user, "mail" => $value->mail,
                    "admin" => $this->_role2group($value->role), "last" => $this->convDate($value->updated_at)
                ]
            );
        }
        return $this->retOk(data: $bodyOut);
    }

    /**
     * @OA\Post(
     *     path="/api/user",
     *     description="Add a new user",
     *     operationId="PostUser",
     *     tags={"Manage Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *        required=true, 
     *        description="The 'group' property must have either 'admin' or 'user', the 'password' property must have at least 8 characters",
     *        @OA\JsonContent(
     *           required={"name", "password", "mail"},
     *           @OA\Property(property="name", type="string", example="jessica"),
     *           @OA\Property(property="password", type="string", example="12345Pass@"),
     *           @OA\Property(property="mail", type="string", example="hello, I would like ..."),
     *           @OA\Property(property="group", type="string", example="admin or user" ),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="An error was encountered", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=201, description="User is created", @OA\JsonContent(ref="#/components/schemas/GenericOk")),
     *     @OA\Response(response=400, description="Invalid body", @OA\JsonContent(ref="#/components/schemas/controlsFailed")),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/unauthorized")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/resourcesUnavail")),
     * )
     */
    function post(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::POST;
        if (!$this->hasAdminToken($param)) return $this->unauthorized();

        [$controle, $fails, $user, $password, $mail, $group] = Carray::arrayCheck(
            $body,
            [
                "name" => ["protected" => true, "limit" => 100, "type" => "string"],
                "password" => ["type" => "string"],
                "mail" => ["protected" => true, "limit" => 200, "type" => "string"],
                "group" => ["protected" => true, "default" => "user", "limit" => 10, "type" => "string"]
            ]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $retour = Authen::add($user, $mail, $password, $this->_group2role($group));
        if ($retour == Authen::USER_OK) return $this->retOk();
        else return $this->retOk(errorcode: $retour, message: Authen::get_lib($retour));
    }

    /**
     * @OA\Put(
     *     path="/api/user/{user}",
     *     description="Update a user",
     *     operationId="PutUser",
     *     tags={"Manage Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *        name="user",
     *        in="path",
     *        description="User",
     *        required=true,
     *        @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *        required=true, 
     *        description="Update password or mail",
     *        @OA\JsonContent(
     *           @OA\Property(property="password", type="string", example="12345Pass@"),
     *           @OA\Property(property="mail", type="string", example="hello, I would like ..."),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Update failed", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=201, description="Update done", @OA\JsonContent(ref="#/components/schemas/GenericOk")),
     *     @OA\Response(response=400, description="The user parameter is missing", @OA\JsonContent(ref="#/components/schemas/controlsFailed")),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/unauthorized")),
     * )
     */
    function put(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::PUT;

        [$controle, $fails, $name] = Carray::arrayCheck(
            $param,
            ["name" => ["mandatory" => true, "protected" => true, "limit" => 100]]
        );
        if (!$controle) return $this->controlsFailed($fails);
        if (!$this->isPermitted($name, $param)) return $this->unauthorized();

        [$controle, $fails, $password, $mail] = Carray::arrayCheck(
            $body,
            [
                "password" => ["mandatory" => false],
                "mail" => ["mandatory" => false, "protected" => true, "limit" => 200]
            ]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $retour = Authen::update($name, $password, $mail);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return $this->retOk(errorcode: $retour, message: $libelle);
        }
        //if ($password !== null) Authen::revoke($token);
        return $this->retOk();
    }

    /**
     * @OA\Delete(
     *     path="/api/user/{user}",
     *     description="Remove a user",
     *     operationId="DelUser",
     *     tags={"Manage Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *        name="user",
     *        in="path",
     *        description="User",
     *        required=true,
     *        @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="The user is removed", @OA\JsonContent(ref="#/components/schemas/GenericOk")),
     *     @OA\Response(response=400, description="The user parameter is missing", @OA\JsonContent(ref="#/components/schemas/controlsFailed")),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/unauthorized")),
     * )
     */
    function delete(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::DELETE;

        [$controle, $fails, $name] = Carray::arrayCheck(
            $param,
            ["name" => ["mandatory" => true, "protected" => true, "limit" => 100]]
        );
        if (!$controle) return $this->controlsFailed($fails);
        if (!$this->isPermitted($name, $param)) return $this->unauthorized();

        Authen::del(Carray::protected($name));
        return $this->retOk();
    }

    /**
     * @return int the role of $grp given
     */
    private function _group2role(string $grp): int
    {
        return match ($grp) {
            "admin" => Authen::ROLE_ADMIN,
            "user" => Authen::ROLE_USER,
            default => Authen::ROLE_SVC
        };
    }

    /**
     * @return string the groupe of $role given
     */
    private function _role2group(string $role): string
    {
        return match ($role) {
            2 => "admin",
            1 => "user",
            default => "service"
        };
    }
}
