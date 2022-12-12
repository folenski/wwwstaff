<?php

/**
 * Module  de test la class index
 *
 * @author  folenski
 * @since 1.3 26/07/2022 : Mise à jour avec class Token
 */

declare(strict_types=1);
require_once  __DIR__  . "/dependances/config.php";

use Staff\Classes\Commun\Index;
use Staff\Classes\Tables\Data;
use Staff\Classes\Tables\Environment;
use Staff\Classes\Tables\Template;
use PHPUnit\Framework\TestCase;

final class IndexTest extends TestCase
{
    public function testPrepareTemplate(): void
    {
        $Template = new Template(PREFIXE);

        $Template->save([
            "id_div" => "menu",
            "template" => "coucou",
            "file_php" => "coucou"
        ]);
        $Template->save([
            "id_div" => "content",
            "template" => "coucou",
            "file_php" => "coucou"
        ]);

        $this->assertSame(
            1,
            $Template->count(["id_div" => "content"])
        );
    }

    /**
     * @depends testPrepareTemplate
     */
    public function testPrepareData(): void
    {
        $Data = new Data(PREFIXE);
        $Data->del(["id_div" => "menu"]);
        $Data->del(["id_div" => "content"]);

        $ref = "test_menu";
        $id_div = "menu";
        $title = "Menu";
        $j_content = <<<MSG
            [
                { "name": "Home", "uri": "@fr/home", "ref": "home" },
                { "name": "News", "uri": "@fr/news", "ref": "news" },
                {
                  "name": "Miscellaneous",
                  "class": "dropdown",
                  "down": true,
                  "uri": "#",
                  "dropdown": [
                    { "name": "Lorem", "uri": "@fr/more/20", "ref": "test_content" },
                    { "name": "Lorem2", "uri": "@fr/more/25", "ref": "lorem2" }
                  ]
                }
            ]
            MSG;
        $this->assertContains(
            $Data::RET_OK,
            $Data->save(compact("j_content", "title", "id_div", "ref"))
        );

        $ref = "test2_menu";
        $j_content = <<<MSG
            [
                { "name": "Home2", "uri": "@it/home", "ref": "home" },
                { "name": "News2", "uri": "@it/news", "ref": "news" },
                {
                  "name": "Miscellaneous2",
                  "class": "dropdown",
                  "down": true,
                  "uri": "#",
                  "dropdown": [
                    { "name": "Lorem", "uri": "@it/more/18", "ref": "test2_content" }
                  ]
                }
            ]
            MSG;
        $this->assertContains(
            $Data::RET_OK,
            $Data->save(compact("j_content", "title", "id_div", "ref"))
        );

        $ref = "test_content";
        $id_div = "content";
        $title = "content";
        unset($j_content);
        $j_content["section"] = "here we are test_content";
        $this->assertContains(
            $Data::RET_OK,
            $Data->save(compact("j_content", "title", "id_div", "ref"))
        );

        $ref = "test2_content";
        $j_content["section"] = "here we are test2_content////";
        $this->assertContains(
            $Data::RET_OK,
            $Data->save(compact("j_content", "title", "id_div", "ref"))
        );
    }

    /**
     * @depends testPrepareTemplate
     */
    public function testPrepareEnv(): void
    {
        $Environment = new Environment(PREFIXE);
        $name = "INDEX";
        $j_option = <<<EOF
        {
            "pref_uri": "/menu/"
        }
        EOF;
        $j_index = <<<EOF
        [
            {
                "language": "fr",
                "uri": "fr/",
                "default": true,
                "start": "test_content",
                "nav": "test_menu",
                "entry_file": "demo.php"
              },
              {
                "language": "it",
                "uri": "it/",
                "start": "test2_content",
                "nav": "test2_menu",
                "entry_file": "demo_it.php"
              }
        ]
        EOF;
        $j_contact = $j_route = $j_dir = '{}';
        $Environment->del(["name" => $name]);
        $this->assertContains(
            $Environment::RET_OK,
            $Environment->save(compact("j_contact", "j_dir", "j_route", "j_index", "j_option", "name"))
        );
    }

    /**
     * @depends testPrepareEnv
     */
    public function testIndexNav(): void
    {
        $Env = new Environment(PREFIXE);
        $Www = new Index($Env);
        $Env->load("INDEX");

        $Www->set_param(
            accept: "it-Italien,bu",
            match: ["name" => "start"]
        );
        $this->assertSame(
            "test2_menu",
            $Www->nav
        );
        $Www->set_param(
            accept: "bu",
            match: ["name" => "start"]
        );
        $this->assertSame(
            "test_menu",
            $Www->nav
        );
        $Www->set_param(
            accept: "bu",
            match: ["name" => "none", "params" => ["uri" => "it/more/18"]]
        );
        $this->assertSame(
            "test2_menu",
            $Www->nav
        );
    }

    /**
     * @depends testPrepareEnv
     */
    public function testIndexPrepare(): void
    {
        $Env = new Environment(PREFIXE);
        $Www = new Index($Env);
        $Env->load("INDEX");

        $Www->set_param(
            accept: "bu",
            match: ["name" => "start"]
        );
        $Www->prepare();
        $this->assertSame(
            ["name" => "Home", "uri" => "/menu/fr/home", "ref" => "home"],
            $Www->data["menu"]["nav"][0]
        );

        $this->assertSame(
            "test_menu",
            $Www->nav
        );
        $Www->set_param(
            accept: "bu",
            match: ["name" => "none", "params" => ["uri" => "it/more/18"]]
        );
        $Www->prepare();
        $this->assertSame(
            ["name" => "Lorem", "uri" => "/menu/it/more/18", "ref" => "test2_content", "active" => true],
            $Www->data["menu"]["nav"][2]["dropdown"][0]
        );
    }
}
