<?php

/**
 * Module  de test la classe **Rest**
 * 
 * @author  folenski
 * @since 1.2 01/07/2022 : Mise à jour avec class Rest  
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Services\Rest;
use Staff\Services\Authen;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use PHPUnit\Framework\TestCase;
use Staff\Models\Environment;
use Staff\Models\Log;

final class RestTest extends TestCase
{

    /** 
     * Lire les options ne sont pas obligatoires
     */
    public function testResponse(): void
    {
        $this->expectOutputString('{"data":"no"}');
        Rest::reponse(["http" => Rest::HTTP_OK, "errorcode" => 0, "response" => ["data" => "no"]]);
    }

    /**
     *  @depends testResponse
     */
    public function testResponseKo(): void
    {
        $this->expectOutputString('{"data":"no","errorcode":2}');
        Rest::reponse(["http" => Rest::HTTP_OK, "errorcode" => 2, "response" => ["data" => "no"]]);
    }

    /**
     *  @depends testResponse
     */
    public function testResponseData(): void
    {
        $this->expectOutputString('{"token":"123456"}');
        Rest::reponse(["http" => Rest::HTTP_OK, "response" => ["token" => "123456"]], log: true);
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
    public function testSpam(): void
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
                Rest::spam($Msg, $fff)
            );
            $Msg->put($fff);
        }

        $this->assertSame(
            true,
            Rest::spam($Msg, $fff)
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
        $cleanDelai = 60;
        $new = date("Y-m-d", strtotime("{$date} + {$cleanDelai} days"));

        $Rows = $Env->get(["name" => "PRD"]);

        $Eee = new \stdClass();
        $Eee->name = "PRD";
        $Eee->Option = json_decode($Rows[0]->j_option);
        $Eee->Option->clean_at = $cleanAt;
        $Eee->Option->clean_delai = $cleanDelai;
        Rest::clean($Eee);
        // on verifie que la nouvelle date a bien été calculée
        $Rows = $Env->get(["name" => "PRD"]);
        $Eee->Option = json_decode($Rows[0]->j_option);
        $this->assertSame(
            $new,
            $Eee->Option->clean_at
        );
    }
}
