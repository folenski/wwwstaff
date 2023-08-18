<?php

/**
 * Module de test de la class ApiLog
 * 
 * @author  folenski
 * @since 1.0 13/08/2023: version initiale
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiLog;
use PHPUnit\Framework\TestCase;

final class ApiLogTest extends TestCase
{
    /** 
     * Lecture de toutes les logs
     */
    public function testGetLog(): void
    {
        $Api = new ApiLog();
        $Env = new \stdClass();

        $ret = $Api->get([], ["authorization" => ["role" => 1]], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray($ret["response"]);
        $this->assertGreaterThan(1, $ret["response"]);
    }

    /** 
     * Lecture d'une log limit à 1
     */
    public function testGetLogLimit(): void
    {
        $Api = new ApiLog();
        $Env = new \stdClass();

        $ret = $Api->get([], ["limit" => 1, "authorization" => ["role" => 1]], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray($ret["response"]);
        $this->assertCount(1, $ret["response"]);
    }

    /** 
     * Lecture de toutes les log sans token
     */
    public function testGetLogToken(): void
    {
        $Api = new ApiLog();
        $Env = new \stdClass();

        $ret = $Api->get([], [], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }
}
