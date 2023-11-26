<?php

/**
 * Module de test de la class ApiIndex
 * 
 * @author  folenski
 * @since 1.0 14/08/2023: version initiale
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiPing;
use PHPUnit\Framework\TestCase;
use Staff\Models\Message;

final class ApiPingTest extends TestCase
{
    /** 
     * Lecture des données par référence 
     */
    public function testPing(): void
    {
        $Api = new ApiPing();
        $Env = new \stdClass();
        $Env->Option = new \stdClass();
        $Env->Option->maintenance = false;
        $ret = $Api->get([], [], $Env);
        $this->assertSame(["http" => 200, "errorcode" => 0, "response" => ["message" => "online"]], $ret);

        $Env->Option->maintenance = true;
        $ret = $Api->get([], [], $Env);
        $this->assertSame(["http" => 200, "errorcode" => 0, "response" => ["message" => "maintenance"]], $ret);
    }
}
