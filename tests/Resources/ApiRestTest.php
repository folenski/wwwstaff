<?php

/**
 * Module de test du trait RestTrait
 * 
 * @author  folenski
 * @since 1.0 16/12/2022: Version initiale
 * @since 1.1 15/08/2023: Adaptation sur à un refactoring
 *  
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use PHPUnit\Framework\TestCase;
use Staff\Resources\RestTrait;

final class ApiRestTest extends TestCase
{
    use RestTrait;

    /** 
     * 
     */
    public function testMethodNotImp(): void
    {
        $this->assertSame(
            ["http" => 503, "errorcode" => 10, "response" => ["message" => "not available"]],
            $this->get([], [], new \stdClass())
        );
        $this->assertSame(
            ["http" => 503, "errorcode" => 10, "response" => ["message" => "not available"]],
            $this->post([], [], new \stdClass())
        );
        $this->assertSame(
            ["http" => 503, "errorcode" => 10, "response" => ["message" => "not available"]],
            $this->put([], [], new \stdClass())
        );
        $this->assertSame(
            ["http" => 503, "errorcode" => 10, "response" => ["message" => "not available"]],
            $this->delete([], [], new \stdClass())
        );
    }

    public function testRetUnAvailable(): void
    {
        $this->assertSame(
            ["http" => 503, "errorcode" => 10, "response" => ["message" => "internal error"]],
            $this->resourcesUnavail()
        );
    }

    public function testRetTokenNeeded(): void
    {
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $this->unauthorized()
        );
    }

    public function testRetCrlFail(): void
    {
        $this->assertSame(
            ["http" => 400, "errorcode" => 22, "response" => ["message" => "test"]],
            $this->controlsFailed("test")
        );
    }

    public function testRetApi(): void
    {
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["message" => "done"]],
            $this->retOk()
        );
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["msg" => "hello"]],
            $this->retOk(data: ["msg" => "hello"])
        );
    }

    public function testHasToken(): void
    {
        $this->assertTrue(
            $this->hasToken(["authorization" => []])
        );
        $this->assertFalse(
            $this->hasToken([])
        );
    }

    public function testHasAdminToken(): void
    {
        $this->assertTrue(
            $this->hasAdminToken(["authorization" => ["role" => 2]])
        );
        $this->assertFalse(
            $this->hasAdminToken(["authorization" => ["role" => 0]])
        );
    }

    public function testIsPermitted(): void
    {
        $name = "name";
        $this->assertTrue($this->isPermitted($name, ["authorization" => ["role" => 2]]));
        $this->assertFalse($this->isPermitted($name, []));
        $this->assertTrue($this->isPermitted($name, ["authorization" => ["role" => 1, "user" => $name]]));
        $this->assertFalse($this->isPermitted($name, ["authorization" => ["role" => 1, "user" => "bad"]]));
    }
}
