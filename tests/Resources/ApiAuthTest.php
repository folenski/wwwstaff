<?php

/**
 * Module de test la class ApiAuth
 * 
 * @author  folenski
 * @since 1.0 08/08/2023: version initiale 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiAuth;
use PHPUnit\Framework\TestCase;

final class ApiAuthTest extends TestCase
{
    /** 
     * Connexion avec l'utilisateur SVC
     */
    public function testPostSvcOk(): void
    {
        $Api = new ApiAuth();
        $Env = new \stdClass();

        // Connexion avec USER_SVC
        $ret = $Api->post([
            "user" => USER_SVC,
            "password" => USER_SVC_PASS
        ], [], $Env);
        $this->assertSame(
            201,
            $ret["http"]
        );
        $this->assertArrayHasKey(
            "token",
            $ret["response"]
        );
        $this->assertArrayHasKey(
            "mail",
            $ret["response"]
        );
        $this->assertArrayHasKey(
            "last",
            $ret["response"]
        );
        $this->assertSame(
            USER_SVC_MAIL,
            $ret["response"]["mail"]
        );

        // deconnexion
        $ret = $Api->delete([], ["authorization" => ["token" => $ret["response"]["token"]]], $Env);
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
    }

    /** 
     * Connexion avec l'utilisateur SVC avec mauvais mot de passe
     */
    public function testPostSvcPassKo(): void
    {
        $Api = new ApiAuth();
        $Env = new \stdClass();

        // Connexion avec USER_SVC
        $ret = $Api->post([
            "user" => USER_SVC,
            "password" => "xxxxxxxxxxxxx"
        ], [], $Env);
        $this->assertSame(
            ["http" => 200, "errorcode" => 131, "response" => ["message" => "User or password is incorrect"]],
            $ret
        );
    }

    /** 
     * Connexion avec l'utilisateur SVC avec un mauvais body
     */
    public function testPostSvcSansPass(): void
    {
        $Api = new ApiAuth();
        $Env = new \stdClass();
        $ret = $Api->post([
            "user" => USER_SVC
        ], [], $Env);
        $this->assertSame(
            ["http" => 400, "errorcode" => 22, "response" => ["message" => "password"]],
            $ret
        );
        $ret = $Api->post([], [], $Env);
        $this->assertSame(
            ["http" => 400, "errorcode" => 22, "response" => ["message" => "user,password"]],
            $ret
        );
    }

    /** 
     * Connexion avec l'utilisateur avec mauvais utilisateur
     */
    public function testPostBadUser(): void
    {
        $Api = new ApiAuth();
        $Env = new \stdClass();

        // Connexion avec USER_SVC
        $ret = $Api->post([
            "user" => "mario",
            "password" => "xxxxxxxxxxxxx"
        ], [], $Env);
        $this->assertSame(
            ["http" => 200, "errorcode" => 131, "response" => ["message" => "User or password is incorrect"]],
            $ret
        );
    }

    /** 
     * Deconnexion sans token ou mauvais token
     * @depends testPostSvcOk
     */
    public function testDeleteBadToken(): void
    {
        $Api = new ApiAuth();
        $Env = new \stdClass();

        $ret = $Api->delete([], [], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
        // Pas de bug car la vérif du token n'est pas effectuée dans la méthode delete
        $ret = $Api->delete([], ["authorization" => ["token" => "MDhlMjNhNWQ1YjNjNjlhNDQ4ZjU1YTM5ZTFiNDYxZjQyNjY1NDg3MTdlYjFiYzgwZWE2YjZiZjE2YjRhMmZmYg=="]], $Env);
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
        $ret = $Api->delete([], ["authorization" => ["token" => "bidon=="]], $Env);
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
    }
}
