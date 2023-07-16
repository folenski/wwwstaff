<?php

/**
 * Module de test la class Www
 * 
 * @author  folenski
 * @since 1.1 14/07/2023: adaptation apres refactoring de code
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Controller\Www;
use Staff\Lib\Render;
use PHPUnit\Framework\TestCase;
use stdClass;

final class WwwTest extends TestCase
{
    public function testWwwChooseIndexByLang1(): void
    {
        $Indexs = [new \stdClass(), new \stdClass(), new \stdClass()];
        $Indexs[0]->language = "fr";
        $Indexs[0]->default = true;
        $Indexs[1]->language = "en";
        $Indexs[2]->language = "sp";

        $Index = Www::choose_by_lang($Indexs, ["sp"]);
        $this->assertSame(
            $Index->language,
            "sp"
        );
        $Index = Www::choose_by_lang($Indexs, ["oo", "--"]);
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
            false,
            Www::choose_by_lang($Indexs, [])
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

        $Index = Www::choose_by_uri($Indexs, "fr/index");
        $this->assertSame(
            "fr/",
            $Index->uri,
        );

        $Index = Www::choose_by_uri($Indexs, "sp/frfr");
        $this->assertSame(
            "sp/",
            $Index->uri,
        );
    }

    public function testWwwLangNav(): void
    {
        $this->assertSame(
            ["0" => "fr", "2" => "en"],
            Www::nav_langages("fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3")
        );
    }


    public function testWwwStart(): void
    {
        $json = <<<EOL
        [
            {
              "language": "fr",
              "uri": "fr/",
              "default": true,
              "ref_nav": "fr_menu",
              "ref_content": "fr_accueil",
              "entry_file": "demofr.php",
              "title": "Démo en francais",
              "meta": "Lorem ipsum, dolor sit amet consectetur"
            },
            {
              "language": "en",
              "uri": "en/",
              "ref_nav": "en_menu",
              "ref_content": "en_home",
              "entry_file": "demo.php",
              "title": "English demo",
              "meta": "Lorem ipsum"
            }
        ]
        EOL;

        $Render = new Render(dirname(__DIR__)  . "/dependances/tmp", "@", "/toto");
        $param["uri"] = "fr/news";
        $Env = new stdClass();
        $Env->index = (array)json_decode($json);
        $Env->Option = new stdClass();
        $Env->Option->prod = true;

        $www = Www::start("progress", $Env, $param, $Render);
        $this->assertIsArray($www);
        $this->assertArrayHasKey("view", $www);
        $this->assertSame(
            "demofr.php",
            $www["view"]
        );
    }
}
