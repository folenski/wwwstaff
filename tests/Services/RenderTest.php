<?php

/**
 * Module de test de la class Render
 *
 * @author  folenski
 * @since 1.0 21/07/2022 : Version initiale  
 * @since 1.2 07/08/2022 : test du service Render
 */

declare(strict_types=1);
require_once  __DIR__ . "/dependances/config.php";

use Staff\Services\Render;
use Staff\Models\Template;
use Staff\Databases\Table;
use PHPUnit\Framework\TestCase;

final class RenderTest extends TestCase
{
    /**
     *  
     */
    public function testFetch(): void
    {
        $rep = dirname(__DIR__) .  "/dependances/tmp/";
        $Rend = new Render(PREFIXE, "", $rep);

        $this->assertSame(
            [],
            $Rend->fetch("fr_menu", "nav")
        );
    }

}
