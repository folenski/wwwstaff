<?php

/**
 * Module de test la class ApiAuth
 * 
 * @author  folenski
 * @since 1.2 01/07/2022 : Mise à jour 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Api\ApiAuth;
use PHPUnit\Framework\TestCase;

final class ApiAuthTest extends TestCase
{
    const USER1  = "admin";
    const USER2 = "admin-bck";
    const USER3 = "service";
    const PASS  = "Admin1234";
    const MAIL  = "admin@test.com";

    /** 
     * Test de la méthode login
     */
    public function testPost(): void
    {
        $Api = new ApiAuth();
        $Env = new \stdClass();
        $ret = $Api->post([
            "user" => USER_SVC,
            "password" => USER_SVC_PASS
        ], [], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );

        $ret2 = $Api->delete([], ["token" => $ret["token"]], $Env);
        $this->assertSame(
            ["http" => 200, "errorcode" => 0],
            $ret2
        );
    }
}
