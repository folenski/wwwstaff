<?php

/**
 * Module de test la class ApiUser
 * 
 * @author  folenski
 * @since 1.0 08/08/2023: version initiale 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiUser;
use PHPUnit\Framework\TestCase;

final class ApiUserTest extends TestCase
{
    /** 
     * Lecture de tous les users
     */
    public function testGetAllUser(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $ret = $Api->get([], ["authorization" => ["role" => 2]], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray(
            $ret["response"]
        );
        $this->assertGreaterThanOrEqual(2, $ret["response"]);
    }

    /** 
     * Lecture de l'user ADMIN
     * @depends testGetAllUser
     */
    public function testGetUserAdmin(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $ret = $Api->get([], ["name" => USER_ADMIN, "authorization" => ["role" => 2]], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray(
            $ret["response"]
        );
        $this->assertCount(1, $ret["response"]);
        $this->assertSame(
            USER_ADMIN,
            $ret["response"][0]["name"]
        );
    }

    /** 
     * Lecture de l'user SVC par lui même
     * @depends testGetAllUser
     */
    public function testGetUserSvc(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();
        $ret = $Api->get([], [
            "name" => USER_SVC,
            "authorization" => ["role" => 1, "user" => USER_SVC]
        ], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertSame(
            USER_SVC,
            $ret["response"][0]["name"]
        );
    }

    /** 
     * lecture d'un user avec des droits utilisateurs, test ko
     */
    public function testGetUserNotPermit(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();
        $ret = $Api->get([], ["name" => "bad", "authorization" => ["role" => 2]], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray(
            $ret["response"]
        );
        $this->assertCount(0, $ret["response"]);
        $ret = $Api->get([], [
            "name" => "bad",
            "authorization" => ["role" => 1, "user" => USER_SVC]
        ], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Lecture sans token
     */
    public function testGetUserTokenKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();
        $ret = $Api->get(
            [],
            [],
            $Env
        );
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Ajout d'un utilisateur supp_1
     * @depends testGetUserAdmin
     */
    public function testPostUser(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_1";

        $ret = $Api->get([], ["name" => $name, "authorization" => ["role" => 2]], $Env);
        if (count($ret) != 0) {
            $ret = $Api->delete([], ["name" => $name, "authorization" => ["user" => USER_ADMIN, "role" => 2]], $Env);
            $this->assertSame(
                200,
                $ret["http"]
            );
        }

        $ret = $Api->post(
            ["name" => $name, "password" => "suppp12344@", "mail" => "supp@no.lan"],
            ["authorization" => ["role" => 2]],
            $Env
        );
        $this->assertSame(
            201,
            $ret["http"]
        );
        $this->assertSame(
            "done",
            $ret["response"]["message"]
        );
    }

    /** 
     * Ajout de utilisateur admin mais il existe déjà
     */
    public function testPostUserDup(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $ret = $Api->post(
            ["name" => USER_ADMIN, "password" => "suppp12344@", "mail" => "supp@no.lan"],
            ["authorization" => ["role" => 2]],
            $Env
        );
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertSame(
            "User already exists",
            $ret["response"]["message"]
        );
    }

    /** 
     * Ajout d'un utilisateur supp_pass_ko
     */
    public function testPostUserPassShort(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_pass_ko";

        $ret = $Api->post(
            ["name" => $name, "password" => "s@", "mail" => "supp_ko@no.lan"],
            ["authorization" => ["role" => 2]],
            $Env
        );
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertSame(
            "The password is too weak",
            $ret["response"]["message"]
        );
    }

    /** 
     * Ajout d'un utilisateur sans token
     */
    public function testPostUserTokenKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_pass_ko";
        $ret = $Api->post(["name" => $name, "password" => "s@", "mail" => "supp_ko@no.lan"], [], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Ajout d'un utilisateur supp_pass_ko avec un utilisateur ne disposant des droits nécessaires
     */
    public function testPostUserRoleKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_pass_ko";

        $ret = $Api->post(
            ["name" => $name, "password" => "s11111111111111@", "mail" => "supp_ko@no.lan"],
            ["authorization" => ["role" => 1]],
            $Env
        );
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * maj de l'email de l'utilisateur supp_1
     * @depends testPostUser
     */
    public function testPutUserSuppRoleAdmin(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_1";
        $ret = $Api->put(
            ["mail" => "maj_supp@no.lan"],
            [
                "name" => $name,
                "authorization" => ["user" => USER_ADMIN, "role" => 2]
            ],
            $Env
        );
        $this->assertSame(
            201,
            $ret["http"]
        );
        $this->assertSame(
            "done",
            $ret["response"]["message"]
        );
        $ret = $Api->get([], ["name" => $name, "authorization" => ["role" => 2]], $Env);
        $this->assertSame(
            "maj_supp@no.lan",
            $ret["response"][0]["mail"]
        );
    }

    /** 
     * maj de l'email de l'utilisateur supp_1 par lui même
     * @depends testPutUserSuppRoleAdmin
     */
    public function testPutUserSuppRoleUser(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_1";
        $mail = "maj2_supp@no.lan";
        $ret = $Api->put(
            ["mail" => $mail],
            [
                "name" => $name,
                "authorization" => ["user" => $name, "role" => 1]
            ],
            $Env
        );
        $this->assertSame(
            201,
            $ret["http"]
        );
        $this->assertSame(
            "done",
            $ret["response"]["message"]
        );
        $ret = $Api->get([], ["name" => $name, "authorization" => ["role" => 2]], $Env);
        $this->assertSame(
            $mail,
            $ret["response"][0]["mail"]
        );
    }

    /** 
     * maj de l'email de l'utilisateur supp_1 par lui même
     * @depends testPutUserSuppRoleAdmin
     */
    public function testPutUserSuppPass(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_1";
        $mail = "maj3_supp@no.lan";
        $ret = $Api->put(
            [
                "mail" => $mail,
                "password" => "1234567!Aaaaa"
            ],
            [
                "name" => $name,
                "authorization" => ["user" => $name, "role" => 1]
            ],
            $Env
        );
        $this->assertSame(
            201,
            $ret["http"]
        );
        $this->assertSame(
            "done",
            $ret["response"]["message"]
        );
        $ret = $Api->get([], ["name" => $name, "authorization" => ["role" => 2]], $Env);
        $this->assertSame(
            $mail,
            $ret["response"][0]["mail"]
        );
    }

    /** 
     * maj de l'email de l'utilisateur admin par supp_1
     */
    public function testPutUserSuppRoleUserKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_1";
        $mail = "maj2_supp@no.lan";
        $ret = $Api->put(
            ["mail" => $mail],
            [
                "name" => USER_ADMIN,
                "authorization" => ["user" => $name, "role" => 1]
            ],
            $Env
        );
        $this->assertSame(
            401,
            $ret["http"]
        );
    }

    /** 
     * maj de l'email de l'utilisateur sans token
     */
    public function testPutUserTokenKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();

        $name = "supp_1";
        $mail = "maj3_supp@no.lan";
        $ret = $Api->put(["mail" => $mail, "password" => "1234567!Aaaaa"], ["name" => $name], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Ajout d'un utilisateur supp_2 puis on le supprimer
     * @depends testGetUserAdmin
     */
    public function testDeleteUserKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();
        $name = "supp_2";

        $ret = $Api->get([], ["name" => $name, "authorization" => ["role" => 2]], $Env);
        if (count($ret) != 0) {
            $ret = $Api->delete([], ["name" => $name, "authorization" => ["user" => USER_ADMIN, "role" => 2]], $Env);
            $this->assertSame(
                200,
                $ret["http"]
            );
        }

        $ret = $Api->post(
            ["name" => $name, "password" => "suppp12344@", "mail" => "supp@no.lan"],
            ["authorization" => ["role" => 2]],
            $Env
        );
        $this->assertSame(
            201,
            $ret["http"]
        );
        $this->assertSame(
            "done",
            $ret["response"]["message"]
        );

        // erreur le paramétre name non présent
        $ret = $Api->delete([], ["authorization" => ["user" => USER_ADMIN, "role" => 2]], $Env);
        $this->assertSame(
            400,
            $ret["http"]
        );
        $this->assertSame(
            "name",
            $ret["response"]["message"]
        );
    }

    /** 
     * suppression de l'utilisateur supp_2
     * @depends testDeleteUserKo
     */
    public function testDeleteUser(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();
        $name = "supp_2";

        $ret = $Api->delete(
            [],
            [
                "name" => $name,
                "authorization" => ["user" => USER_ADMIN, "role" => 2]
            ],
            $Env
        );
        $this->assertSame(
            200,
            $ret["http"]
        );
    }

    /** 
     * suppression de l'utilisateur supp_2 sans token
     */
    public function testDeleteUserTokenKo(): void
    {
        $Api = new ApiUser();
        $Env = new \stdClass();
        $name = "supp_2";

        $ret = $Api->delete([], ["name" => $name], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }
}
