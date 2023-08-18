<?php

/**
 * Module de test de la class ApiData
 * 
 * @author  folenski
 * @since 1.0 14/08/2023: version initiale
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiData;
use PHPUnit\Framework\TestCase;

final class ApiDataTest extends TestCase
{
    /** 
     * Lecture des données par référence 
     */
    public function testGetDataRef(): void
    {
        $Api = new ApiData();
        $Env = new \stdClass();

        $ret = $Api->get([], ["ref" => "news"], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray($ret["response"]);
        $this->assertGreaterThan(1, $ret["response"]);

    }

}
