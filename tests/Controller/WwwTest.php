<?php

/**
 * Module de test la class Www
 * 
 * @author  folenski
 * @since 1.0 19/11/2022 : Version initiale 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Controller\Www;
use PHPUnit\Framework\TestCase;

final class WwwTest extends TestCase
{
    public function testWwwChooseIndexByLang1(): void
    {
        $Indexs = [new \stdClass(), new \stdClass(), new \stdClass()];
        $Indexs[0]->language = "fr";
        $Indexs[0]->default = true;
        $Indexs[1]->language = "en";
        $Indexs[2]->language = "sp";

        $Index = Www::choose_index(
            $Indexs,
            ["sp"],
            ""
        );
        $this->assertSame(
            $Index->language,
            "sp"
        );
        $Index = Www::choose_index(
            $Indexs,
            ["oo"],
            ""
        );
        $this->assertSame(
            $Index->language,
            "fr" // langue par défaut
        );
    }

    public function testWwwChooseIndexByLang2(): void
    {
        // pas d'index par defaut
        $Indexs = [new \stdClass(), new \stdClass(), new \stdClass()];
        $Indexs[0]->language = "fr";
        $Indexs[1]->language = "en";
        $Indexs[2]->language = "sp";

        $this->assertSame(
            null,
            Www::choose_index(
                $Indexs,
                [],
                ""
            )
        );
    }

    public function testWwwChooseIndexByUri(): void
    {
        $Indexs = [new \stdClass(), new \stdClass(), new \stdClass()];
        $Indexs[0]->uri = "fr/";
        $Indexs[0]->language = "fr";
        $Indexs[0]->default = true;
        $Indexs[1]->uri = "en/";
        $Indexs[1]->language = "en";
        $Indexs[2]->uri = "sp/";
        $Indexs[2]->language = "sp";

        $Index = Www::choose_index(
            $Indexs,
            ["fr"],
            "fr/index"
        );
        $this->assertSame(
            "fr/",
            $Index->uri,
        );

        $Index = Www::choose_index(
            $Indexs,
            ["sp"],
            "sp/frfr"
        );
        $this->assertSame(
            "sp/",
            $Index->uri,
        );
    }

    public function testWwwLangNav(): void
    {
        $this->assertSame(
            ["0" => "fr", "2" => "en"],
            Www::lang_nav("fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3")
        );
    }
}
