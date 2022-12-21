<?php

/**
 * Class ApiMsg, Gestion des messages en REST 
 * 
 * @author folenski
 * @version 1.0.0 Version initialie
 * @version 1.2.0 12/08/2022, Refactoring
 * @version 1.2.1 09/12/2022, utilisation d'un trait
 *
 */

namespace Staff\Api;

use Staff\Models\Message;
use Staff\Models\DBParam;
use Staff\Databases\Table;
use Staff\Services\Rest;
use Staff\Services\Carray;

final class ApiMsg implements RestInterface
{
    const ERR_MSG_MAIL = 51;

    use RestTrait;

    /**
     * Méthode POST
     * @param array $data tableau avec les informations du body 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function post(array $data, array $param, object $Env): array
    {
        $msgKo = $Env->Contact?->msgKo ?? "an error was encountered";
        $msgOk = $Env->Contact?->msgOk ?? "message sent";

        [$controle, $fails, $nom, $mail, $message, $tel, $sujet] = Carray::arrayCheck($data, [
            "nom" => ["protected" => true, "limit" => 80],
            "mail" => ["protected" => true, "limit" => 200],
            "message" => ["protected" => true, "limit" => 3200],
            "tel" =>  ["protected" => true, "limit" => 20, "default" => ""],
            "sujet" =>  ["protected" => true, "limit" => 200, "default" => ""]
        ]);
        if (!$controle) return $this->retCrlFail($fails);

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            return $this->retApi(errorcode: self::ERR_MSG_MAIL, content: null, data: ["msg" => $msgKo], isApp: true);

        $user = $Env->Option->user;
        $hash  = sha1($_SERVER["REMOTE_ADDR"] ?? "");
        $host  = $_SERVER["HTTP_HOST"] ?? "";
        $j_msg = json_encode(compact("nom", "tel", "mail", "sujet", "message"));
        $fields = compact("user", "hash", "host", "j_msg");

        $Msg = new Table(DBParam::$prefixe, new Message());
        if (Rest::spam($Msg, $fields))
            return $this->retApi(content: null, data: ["msg" => $msgOk, "extra" => "sp"]);

        if (!$Msg->put($fields)) return $this->retUnavail();

        if ($Env->Contact->sendMail) {
            $message = preg_replace(
                ['/{{nom}}/', '/{{mail}}/', '/{{host}}/', '/{{message}}/'],
                [$nom, $mail, $host, $message],
                $Env->Contact->message
            );
            if (!Rest::envoi_mail($Env->Contact->mail, $message)) {
                return $this->retApi(
                    errorcode: self::ERR_INSERT,
                    content: null,
                    data: ["msg" => $msgKo]
                );
            }
            return $this->retApi(content: null, data: ["msg" => $msgOk]);
        }
        return $this->retApi(content: null, data: ["msg" => $msgOk]);
    }
}
