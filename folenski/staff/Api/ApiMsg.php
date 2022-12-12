<?php

/**
 * Class ApiMsg, Gestion des messages en REST 
 * 
 * @author folenski
 * @since   1.2.1 09/12/2022
 * @version 1.0.0 Version initialie
 * @version 1.2.0 12/08/2022, Refactoring
 * @version 1.2.1 09/12/2022, utilisation d'un trait
 */

namespace Staff\Api;

use Staff\Models\Message;
use Staff\Models\DBParam;
use Staff\Databases\Table;
use Staff\Services\Rest;
use Staff\Services\Carray;

final class ApiMsg implements RestInterface
{
    use RestTrait;

    /**
     * Méthode POST
     * @param array $data tableau avec les informations du body 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function post(array $data, array $param, object $Env): array
    {
        [$controle, $fails, $nom, $mail, $message, $tel, $sujet] = Carray::arrayCheck($data, [
            "nom" => ["protected" => true, "limit" => 80],
            "mail" => ["protected" => true, "limit" => 200],
            "message" => ["protected" => true, "limit" => 3200],
            "tel" =>  ["protected" => true, "limit" => 20, "default" => ""],
            "sujet" =>  ["protected" => true, "limit" => 200, "default" => ""]
        ]);
        if (!$controle)
            return [
                "http" => self::HTTP_BAD,
                "errorcode" => self::ERR_BAD_BODY,
                "response" => [
                    "content" => $fails, "msg" => $Env->Contact->msgKo
                ]
            ];
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            return [
                "http" => self::HTTP_OK,
                "errorcode" => self::ERR_BAD_BODY,
                "response" => [
                    "msg" => $Env->Contact->msgKo,
                    "content" => "mail control fail", "msg" => $Env->Contact->msgKo
                ]
            ];

        $user = $Env->Option->user;
        $hash  = sha1($_SERVER["REMOTE_ADDR"] ?? "");
        $host  = $_SERVER["HTTP_HOST"] ?? "";
        $j_msg = json_encode(compact("nom", "tel", "mail", "sujet", "message"));
        $fields = compact("user", "hash", "host", "j_msg");

        $Msg = new Table(DBParam::$prefixe, new Message());
        if (Rest::spam($Msg, $fields))
            return [
                "http" => self::HTTP_OK,
                "errorcode" => self::ERR_OK,
                "response" => [
                    "msg" => $Env->Contact->msgOk,
                    "content" => "[sp]"
                ]
            ];
        if (!$Msg->put($fields))
            return [
                "http" => self::HTTP_UNAVAIL,
                "errorcode" => self::ERR_SQL,
                "response" => [
                    "msg" => $Env->Contact->msgKo,
                    "content" => "internal error"
                ]
            ];

        if ($Env->Contact->sendMail) {
            $message = preg_replace(
                ['/{{nom}}/', '/{{mail}}/', '/{{host}}/', '/{{message}}/'],
                [$nom, $mail, $host, $message],
                $Env->Contact->message
            );
            if (!Rest::envoi_mail($Env->Contact->mail, $message)) {
                return
                    ["http" => self::HTTP_OK, "errorcode" => self::ERR_INSERT, "response" => ["msg" => $Env->Contact->msgKo]];
            }
            return ["http" => self::HTTP_OK, "errorcode" => self::ERR_OK, "response" => ["msg" => $Env->Contact->msgOk]];
        }
        return ["http" => self::HTTP_OK, "errorcode" => self::ERR_OK, "response" => ["msg" => $Env->Contact->msgOk]];
    }
}
