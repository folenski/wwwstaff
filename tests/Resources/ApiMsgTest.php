<?php

/**
 * Module de test de la class ApiMsg
 * 
 * @author  folenski
 * @since 1.2 01/07/2022: Ajout de nouveaux tests pour la mise en place du swagger 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Resources\ApiMsg;
use PHPUnit\Framework\TestCase;

final class ApiMsgTest extends TestCase
{
    /** 
     * Test de la méthode post Msg avec les propriétés minimales
     */
    public function testPostMsg(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();
        $user = USER_SVC;
        $ddd = <<< EOL
        {
            "mail": "sample@mail.com",
            "user": "{$user}",
            "send_mail": false,
            "message": "Bonjour,<br/><p>Message reçu de {{nom}} (email {{mail}}), sur le site ({host}}.</p><p>{{message}}</p><br/>Cordialement,<br/><br/>- Admin - "
          }
        EOL;
        $Env->Option = new \stdClass();
        $Env->Option->contact = json_decode($ddd);

        $ret = $Api->post([
            "name" => "anonyme",
            "mail" => "a@a.fr",
            "message" => "C'est un coucou"
        ], [], $Env);

        $this->assertSame(
            ["http" => 201, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
    }

    /** 
     * Test de la méthode post Msg avec le message manquant
     */
    public function testPostBodyKo(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();
        $user = USER_SVC;
        $ddd = <<< EOL
        {
            "mail": "sample@mail.com",
            "user": "{$user}",
            "send_mail": false,
            "message": "Bonjour,<br/><p>Message reçu de {{nom}} (email {{mail}}), sur le site ({host}}.</p><p>{{message}}</p><br/>Cordialement,<br/><br/>- Admin - "
          }
        EOL;
        $Env->Option = new \stdClass();
        $Env->Option->contact = json_decode($ddd);

        $ret = $Api->post([
            "name" => "anonyme",
            "mail" => "a@a.fr",
            "tel" => "0606060606"
        ], [], $Env);

        $this->assertSame(
            ["http" => 400, "errorcode" => 22, "response" => ["message" => "message"]],
            $ret
        );
    }

    /** 
     * Test de la méthode post Msg avec un défaut de paramétrage
     */
    public function testPostEnvironKo(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->post([
            "name" => "anonyme",
            "mail" => "a@a.fr",
            "message" => "0606060606"
        ], [], $Env);
        $this->assertSame(
            [
                "http" => 503, "errorcode" => 10,
                "response" => ["message" => "objet contact doesn't found in environment table"]
            ],
            $ret
        );
    }

    /** 
     * Lecture de tous les messages
     * @depends testPostMsg
     */
    public function testGetMsg(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->get([], ["authorization" => ["role" => 1]], $Env);
        $this->assertSame(
            200,
            $ret["http"]
        );
        $this->assertIsArray($ret["response"]);
        $this->assertGreaterThan(1, $ret["response"]);
    }

    /** 
     * Lecture de tous les messages sans token
     */
    public function testGetMsgToken(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->get([], [], $Env);
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Lecture de tous les messages
     * @depends testPostMsg
     */
    public function testPutMsg(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->get([], ["limit" => 1, "authorization" => ["role" => 1]], $Env);
        $this->assertIsArray($ret["response"]);
        $message = $ret["response"][0];
        $ret = $Api->put(
            ["read" => true],
            ["id" => $message["id"], "authorization" => ["role" => 1]],
            $Env
        );
        $this->assertSame(
            ["http" => 201, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
        $ret = $Api->get([], ["limit" => 1, "authorization" => ["role" => 1]], $Env);
        $message2 = $ret["response"][0];
        $this->assertSame(true, $message2["read"]);
    }

    /** 
     * Mise à jour sans ID
     * @depends testPostMsg
     */
    public function testPutMsgIdKo(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();
        $ret = $Api->put(
            ["read" => true],
            ["authorization" => ["role" => 1]],
            $Env
        );
        $this->assertSame(
            ["http" => 400, "errorcode" => 22, "response" => ["message" => "id"]],
            $ret
        );
    }

    /** 
     * Mise à jour sans Token
     */
    public function testPutMsgTokenKo(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();
        $ret = $Api->put(
            ["read" => true],
            ["id" => 100],
            $Env
        );
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Mise à jour avec un mauvais ID
     * il n'y aura de mise à jour, comme la requête est valide pas d'erreur
     */
    public function testPutMsgBadId(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->put(
            ["read" => true],
            ["id" => 9999999999, "authorization" => ["role" => 1]],
            $Env
        );
        $this->assertSame(
            ["http" => 201, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
    }

    /** 
     * Lecture de tous les messages
     * @depends testPutMsg
     */
    public function testDeleteMsg(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->get([], ["limit" => 1, "authorization" => ["role" => 1]], $Env);
        $this->assertIsArray($ret["response"]);
        $message = $ret["response"][0];
        $ret = $Api->delete(
            [],
            ["id" => $message["id"], "authorization" => ["role" => 1]],
            $Env
        );
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
        $ret = $Api->get([], ["limit" => 1, "authorization" => ["role" => 1]], $Env);
        if (count($ret["response"]) != 0) {  // La table peut être vide
            $message2 = $ret["response"][0];
            $this->assertNotSame($message["id"], $message2["id"]);
        }
    }

    /** 
     * Suppression sans Token
     */
    public function testDeleteMsgTokenKo(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();
        $ret = $Api->delete(
            [],
            ["id" => 100],
            $Env
        );
        $this->assertSame(
            ["http" => 401, "errorcode" => 20, "response" => ["message" => "bad credentials"]],
            $ret
        );
    }

    /** 
     * Suppression avec un mauvais ID
     * il n'y aura de mise à jour, comme la requête est valide pas d'erreur
     */
    public function testDeleteMsgBadId(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ret = $Api->delete(
            ["read" => true],
            ["id" => 9999999999, "authorization" => ["role" => 1]],
            $Env
        );
        $this->assertSame(
            ["http" => 200, "errorcode" => 0, "response" => ["message" => "done"]],
            $ret
        );
    }
}
