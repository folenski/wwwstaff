<?php

/**
 * Module de test la classe Environnement
 *
 * @author  folenski
 * @since 1.2 01/07/2022 : Mise à jour avec class Option  
 * @since 1.3 13/07/2022 : php8  
 */

declare(strict_types=1);
require_once  __DIR__  . "/dependances/config.php";

use Staff\Classes\Tables\Environment;
use PHPUnit\Framework\TestCase;

final class DbEnvironmentTest extends TestCase
{
    public function testloadXmlItem(): void
    {
        $Environment = new Environment(PREFIXE);
        $Environment->del();
        $item = new stdClass();

        $item->name = "TST";
        $item->j_option = <<<EOF
        {
            "lock": false,
            "maintenance": false,
            "putIn": false,
            "shorts": { "start": "accueil", "mail": "contact" }
          }
        EOF;
        $item->j_index = <<<EOF
        [
            {
              "language": "fr",
              "uri": "fr/",
              "default": true,
              "start": "fr_accueil",
              "entry_file": "demo.php",
              "nav": "fr_menu",
              "const": {
                "title": "Démo en francais",
                "meta": {
                  "description": "Lorem ipsum, dolor sit amet consectetur",
                  "revised": "25/05/2021"
                }
              }
            }
        ]
        EOF;
        $item->j_contact = <<<EOF
        {
          "mail": "sample@mail.com",
          "msgMax": 2000,
          "msgMaxUser": 5,
          "sendMail": false,
          "msgOk": "Message envoyé, merci",
          "msgKo": "Une erreur a été rencontrée<br/>Merci d&apos;essayer plus tard"
        }
        EOF;
        $item->j_route = <<<EOF
        [
            ["GET", "/", "start.php", "home"],
            ["GET", "/menu/[app:menu]", "start.php", "home-1"],
            ["GET", "/menu/[lg:langue]/[app:menu]", "start.php", "home-lg-1"],
            ["GET", "/menu/[app:menu]/[i:smenu]", "start.php", "home-2"],
            ["GET", "/menu/[lg:langue]/[app:menu]/[i:smenu]", "start.php", "home-lg-2"],
            ["POST", "/action/msg", "action.php", "submit-mail"],
            ["POST", "/api/msg",  "api.php", "message"],
            ["POST|DELETE", "/api/auth", "api.php", "authen"],
            ["POST|PUT|DELETE", "/api/user", "api.php", "user"],
            ["GET|DELETE", "/api/log", "api_log.php", "log"]
          ]
        EOF;
        $item->j_dir = <<<EOF
        {
              "INC": "includes",
              "PHP": "vendor",
              "VIEWS": "views",
              "COMPO": "includes/composants",
              "DB":  "sqldb"
          }
        EOF;
        // 1er insertion, ca doit être ok 
        $this->assertContains(
            $Environment::RET_OK,
            $Environment->save((array)$item)
        );
        // 2eme insertion,  on gere l'erreur de duplication
        $this->assertContains(
            $Environment::RET_DUP,
            $Environment->save((array)$item)
        );
    }
    /** 
     *  @depends testloadXmlItem
     */
    public function testloadItemNoCompl(): void
    {
        $Environment = new Environment(PREFIXE);

        $name = "TST2";
        $j_option = '{lock}';
        $j_contact = $j_dir = $j_route = $j_index = '{}';
        // on fait une insertion directe pour bypasser les controles
        $this->assertSame(
            $Environment::RET_OK,
            $Environment->put(compact("name", "j_index", "j_option", "j_contact", "j_route", "j_dir"))
        );
        try {
            $Environment->load("TST2");
        } catch (Exception $e) {
            $this->assertSame(
                "xxx",
                $e->getMessage()
            );
        }
    }

    public function testloadItemNoExist(): void
    {
        $Environment = new Environment(PREFIXE);
        try {
            $Environment->load("TST3");
        } catch (Exception $e) {
            $this->assertSame(
                'Environment->load: Environment TST3 not found',
                $e->getMessage()
            );
        }
    }

    /** 
     *  Chargement des options, plus lecture des options
     *  @depends testloadXmlItem
     */
    public function testSaveItemKo(): void
    {
        $Environment = new Environment(PREFIXE);
        $item = new stdClass();
        $item->name = "TST";
        $item->j_option = <<<EOF
        {
            'lock': false,
        }
        EOF;
        $this->assertContains(
            $Environment::RET_ERROR,
            $Environment->save((array)$item)
        );

        $item->json = <<<EOF
        {
            lock: false,
        }
        EOF;
        $this->assertContains(
            $Environment::RET_ERROR,
            $Environment->save((array)$item)
        );
        $item->name = "";
        $this->assertContains(
            $Environment::RET_ERROR,
            $Environment->save((array)$item)
        );
    }

    /** 
     *  Chargement des options, plus lecture des options
     *  @depends testloadXmlItem
     */
    public function testPropertyOption(): void
    {
        $Environment = new Environment(PREFIXE);
        $Environment->load("TST");
        $Json = $Environment->Option;
        $this->assertIsObject($Json);
        $this->assertSame(false,  $Json->lock);
        $Json = $Environment->Contact;
        $this->assertIsObject($Json);
        $this->assertSame("sample@mail.com",  $Json->mail);
    }

    /** 
     *  Chargement des options, plus lecture des options
     *  @depends testPropertyOption
     */
    public function testPropertyRoute(): void
    {
        $Environment = new Environment(PREFIXE);
        $Environment->load("TST", controler: "COMPO"); // lecture de l'environnement TST
        $route = $Environment->route;
        $this->assertIsArray($route);
        $this->assertSame(
            ["GET", "/", "includes/composants/start.php", "home"],
            $route[0]
        );
    }

    /** 
     *  Chargement des options, plus lecture des options
     *  @depends testPropertyOption
     */
    public function testPropertyDir(): void
    {
        $Environment = new Environment(PREFIXE);
        // lecture de l'environnement TST
        $Environment->load("TST", dirWww: "/tTs/");
        $dir = $Environment->directories;
        $this->assertIsArray($dir);
        $this->assertSame(
            "/tTs/views/",
            $dir["VIEWS"]
        );
    }

    public function testPropertyUrl(): void
    {
        $Environment = new Environment(PREFIXE);
        $Environment->set_url(httpServer: "localhost");
        $this->assertSame(
            "http://localhost/",
            $Environment->url
        );
    }
}
