<?php

/**
 * Module de test de la class Admin
 *
 * @author  folenski
 * @since 1.0 07/08/2022 : Version initiale  
 */

declare(strict_types=1);
require_once  dirname(__DIR__) . "/dependances/config.php";

use Staff\Services\Admin;
use PHPUnit\Framework\TestCase;

final class AdminTest extends TestCase
{
    public function testLoadEnvironment(): void
    {
        $Adm = new Admin(PREFIXE);
        $root = dirname(__DIR__, 2);
        $ficEnv = "{$root}/app/data/environment.json";
        $cr = [];

        $this->assertContains(
            $Adm::RET_OK,
            $Adm->load($ficEnv, true, $cr)
        );

        $this->assertSame(
            0,
            $cr["TST"]
        );
    }

    /** 
     *  @depends testLoadEnvironment
     */
    public function testLoadTemplate(): void
    {
        $Adm = new Admin(PREFIXE);
        $root = dirname(__DIR__, 2);
        $ficEnv = "{$root}/app/data/template.json";
        $cr = [];

        $this->assertContains(
            $Adm::RET_OK,
            $Adm->load($ficEnv, true, $cr)
        );
    }

    /** 
     *  @depends testLoadEnvironment
     */
    public function testLoadData(): void
    {
        $Adm = new Admin(PREFIXE);
        $root = dirname(__DIR__, 2);
        $ficEnv = "{$root}/app/data/data.json";
        $cr = [];

        $this->assertContains(
            $Adm::RET_OK,
            $Adm->load($ficEnv, true, $cr)
        );
    }
}
