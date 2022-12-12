<?php

/**
 * Module de test la class ApiMsg
 * 
 * @author  folenski
 * @since 1.2 01/07/2022 : Mise à jour 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Api\ApiMsg;
use PHPUnit\Framework\TestCase;

final class ApiMsgTest extends TestCase
{
    /** 
     * Test de la méthode post Msg
     */
    public function testPost(): void
    {
        $Api = new ApiMsg();
        $Env = new \stdClass();

        $ddd = <<< EOL
        {
            "mail": "sample@mail.com",
            "user": "svc",
            "msgMax": 2000,
            "sendMail": false,
            "msgOk": "Message envoyé, merci",
            "msgKo": "Une erreur a été rencontrée<br/>Merci d&apos;essayer plus tard",
            "message": "Bonjour,<br/><p>Message reçu de {{nom}} (email {{mail}}), sur le site ({host}}.</p><p>{{message}}</p><br/>Cordialement,<br/><br/>- Admin - "
          }
        EOL;
        $Env->Contact = json_decode($ddd);
        $Env->Option = new \stdClass();
        $Env->Option->user = USER_SVC;

        $ret = $Api->post([
            "nom" => "anonyme",
            "mail" => "a@a.fr",
            "message" => "coucou"
        ], [], $Env);

        $this->assertSame(
            200,
            $ret["http"]
        );
    }
}
