<?php

/**
 * Module de test de la class Render
 *
 * @author  folenski
 * @version 1.0 21/07/2022: Version initiale  
 * @version 1.2 07/08/2022: test du service Render
 * @version 1.3 21/12/2022: ajout de tests
 * 
 */

declare(strict_types=1);
require_once dirname(__DIR__) . "/dependances/config.php";

use Staff\Lib\Render;
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
            ["menu"],
            $Rend->fetch("fr_menu", "nav")
        );

        $this->assertSame(
            ["content"],
            $Rend->fetch("fr_accueil", "content")
        );
    }

    /** 
     *  @depends testFetch
     */
    public function testRecupMenu(): void
    {
        $rep = dirname(__DIR__) .  "/dependances/tmp/";
        $Rend = new Render(PREFIXE, "", $rep);

        $this->assertSame(
            ["menu"],
            $Rend->fetch("fr_menu", "nav")
        );

        $Rend->update_uri(["menu"], "nav", "fr/contact");

        $obj =$Rend->get_metadata("fr/contact");

        $this->assertSame(
            "contact",
            $obj->ref
        );
    }
}
