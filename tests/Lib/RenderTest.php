<?php

/**
 * Module de test de la class Render
 *
 * @author  folenski
 * @version 1.0 21/07/2022: Version initiale  
 * @version 1.3 21/12/2022: ajout de tests
 * @version 1.4 15/07/2023: ajout de tests suite à refactoring de la class
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
        $Rend = new Render(_rep_out: $rep);

        $this->assertSame(
            ["menu"],
            $Rend->fetch_data("fr_menu", "nav")
        );

        $this->assertSame(
            [],
            $Rend->fetch_data("noexist", "nav")
        );

        $this->assertSame(
            ["content"],
            $Rend->fetch_data("fr_accueil", "content")
        );
    }

    /** 
     *  @depends testFetch
     */
    public function testSelectByUri(): void
    {
        $rep = dirname(__DIR__) .  "/dependances/tmp/";
        $Rend = new Render(_rep_out: $rep);

        $this->assertSame(
            ["menu"],
            $Rend->fetch_data("fr_menu", "nav")
        );

        $Obj = $Rend->set_active_uri("menu", "nav", "fr/contact");
        $this->assertIsObject($Obj);
        //var_dump($Obj);
        $this->assertSame(
            true,
            $Obj->active
        );

        $Obj = $Rend->set_active_uri("menu", "nav", "noexist");
        $this->assertSame(
            false,
            $Obj
        );
    }

    /** 
     *  @depends testFetch
     */
    public function testSelectFirst(): void
    {
        $rep = dirname(__DIR__) .  "/dependances/tmp/";
        $Rend = new Render(_rep_out: $rep);

        $this->assertSame(
            ["menu"],
            $Rend->fetch_data("fr_menu", "nav")
        );

        $Obj = $Rend->set_active_first("menu", "nav");
        $this->assertIsObject($Obj);
        $this->assertSame(
            true,
            $Obj->active
        );

        $Obj = $Rend->set_active_first("menu2", "nav");
        $this->assertSame(
            False,
            $Obj
        );
    }

    /** 
     *  @depends testSelectByUri
     */
    public function testUpdatePref(): void
    {
        $rep = dirname(__DIR__) .  "/dependances/tmp/";
        $Rend = new Render(_rep_out: $rep, _external_pref: "/mymy/");

        $template = $Rend->fetch_data("fr_menu", "nav");
        $this->assertSame(
            1,
            count($template)
        );

        $Rend->update_prefixe($template[0], "nav");
        $sel = $Rend->set_active_first($template[0], "nav");
        $this->assertIsObject($sel);
        $this->assertStringStartsWith(
            "/mymy/",
            $sel->uri
        );

        $sel = $Rend->set_active_uri($template[0], "nav", "fr/more/18");
        $this->assertIsObject($sel);
        $this->assertStringStartsWith(
            "/mymy/",
            $sel->uri
        );
    }
}
