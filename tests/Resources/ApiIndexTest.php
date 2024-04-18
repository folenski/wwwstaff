<?php

/**
 * Module de test de la class ApiIndex
 * 
 * @author  folenski
 * @since 1.0 14/08/2023: version initiale
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiIndex;
use PHPUnit\Framework\TestCase;

final class ApiIndexTest extends TestCase
{
    /** 
     * Lecture des données par référence 
     */
    public function testGetIndex(): void
    {
        $Api = new ApiIndex();
        $Env = new \stdClass();
        $iii = <<<EOB
        [
            {
              "language": "fr",
              "uri": "fr/",
              "default": true,
              "ref_nav": "fr_menu",
              "ref_content": "fr_accueil",
              "entry_file": "demo.php",
              "title": "Démo en francais",
              "meta": "Lorem ipsum, dolor sit amet consectetur"
            }
        ]
        EOB;
        $Env->index = (array)json_decode($iii);
        $Env->Option = new \stdClass();
        $Env->Option->maintenance = false;

        $ret = $Api->get([], [], $Env);
        $this->assertSame(200, $ret["http"]);
        $this->assertCount(1, $ret["response"]);

        $Env->Option->maintenance = true;
        $ret = $Api->get([], [], $Env);
        $this->assertSame(200, $ret["http"]);
        $this->assertCount(0, $ret["response"]);
    }

}
