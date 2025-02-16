<?php

/**
 * Module  de test la classe **Rest**
 * 
 * @author  folenski
 * @since 1.2 01/07/2022 : Mise à jour avec class Rest  
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Lib\Rest;
use Staff\Security\Authen;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use PHPUnit\Framework\TestCase;
use Staff\Models\Environment;

final class RestTest extends TestCase
{

    /** 
     * Lire les options ne sont pas obligatoires
     */
    public function testResponse(): void
    {
        $this->expectOutputString('{"data":"no"}');
        Rest::reponse(
            ["http" => Rest::HTTP_OK, "errorcode" => 0, "response" => ["data" => "no"]],
            test: true
        );
    }

    /**
     *  @depends testResponse
     */
    public function testResponseKo(): void
    {
        $this->expectOutputString('{"data":"no","errorcode":2}');
        Rest::reponse(
            ["http" => Rest::HTTP_OK, "errorcode" => 2, "response" => ["data" => "no"]],
            test: true
        );
    }

    /**
     *  @depends testResponse
     */
    public function testResponseData(): void
    {
        $this->expectOutputString('{"token":"123456"}');
        Rest::reponse(
            ["http" => Rest::HTTP_OK, "response" => ["token" => "123456"]],
            log: true,
            test: true
        );
    }

    /**
     * Test d'autorisation du token 
     * @depends testResponse
     */
    public function testAutorisation(): void
    {
        $Api = new Rest(PREFIXE);

        [$retour, $token] = Authen::login(USER_SVC, USER_SVC_PASS);
        $this->assertSame(
            Authen::USER_OK,
            $retour
        );
        // [$tok, $user] = Rest::bearer("bearer $token");
        $ret = Rest::bearer("bearer $token");
        $this->assertSame(
            USER_SVC,
            $ret["user"]
        );
    }

    /**
     * Test d'autorisation du token 
     * @depends testAutorisation
     */
    public function testAutorisationKo(): void
    {
        $ret = Rest::bearer("bearer ==445343434==");
        $this->assertSame(
            false,
            $ret
        );
    }

    /**
     * @depends testAutorisation
     */
    public function teststopMsg(): void
    {
        $Msg = new Table(DBParam::$prefixe, DBParam::get_table("message"));
        $Msg->del();
        // pour sqlite
        $fff["user"] = USER_SVC;
        $fff["host"] = "coucou";
        $fff["hash"] = "172.256.11.56";
        $fff["j_msg"] = "xxxx";

        for ($i = 0; $i <= 3; $i++) {
            $this->assertSame(
                false,
                Rest::stopMsg($fff["hash"])
            );
            $Msg->put($fff);
        }

        $this->assertSame(
            true,
            Rest::stopMsg($fff["hash"])
        );
    }

    /**
     * @depends testAutorisation
     */
    public function testSpam(): void
    {
        $this->assertSame(
            false,
            Rest::Spam("normal@mail.xxx", "hello c'est moi")
        );

        $this->assertSame(
            true,
            Rest::Spam("staff@spam.xxx", "hello c'est moi")
        );

        $this->assertSame(
            false,
            Rest::Spam("staff@spam.xx", "hello c'est moi")
        );

        $this->assertSame(
            true,
            Rest::Spam("normal@mail.xxx", "hello c'est moi your SEO’s working. la la la ")
        );
    }


    /**
     * @depends testAutorisation
     */
    public function testClean(): void
    {
        $Env = new Table(DBParam::$prefixe, new Environment());

        $date = date("Y-m-d");
        $cleanAt = "2022-01-01";

        $Rows = $Env->get(["name" => "PRD"]);
        $Eee = new \stdClass();
        $Eee->name = "PRD";
        $Eee->Option = json_decode($Rows[0]->j_option);
        $Eee->Option->clean_at = $cleanAt;
        Rest::clean($Eee);
        // on verifie que la nouvelle date a bien été calculée
        $Rows = $Env->get(["name" => "PRD"]);
        $Eee->Option = json_decode($Rows[0]->j_option);
        $this->assertGreaterThan($date, $Eee->Option->clean_at);
    }
}
