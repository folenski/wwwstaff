<?php

/**
 * Class ApiMsg, Gestion des messages en REST 
 * 
 * @author folenski
 * @version 1.0.0 Version initialie
 * @version 1.2.1 09/12/2022, utilisation d'un trait
 * @version 1.2.2 21/12/2022, alimentation du champs spam, log enrichi lors qu'il y a une erreur d'insertion
 * @version 1.2.3 11/07/2023, Mise en place des annotations pour générer le swagger
 *
 */

namespace Staff\Api;

use Staff\Models\Message;
use Staff\Models\DBParam;
use Staff\Databases\Table;
use Staff\Lib\Rest;
use Staff\Lib\Carray;

final class ApiMsg implements RestInterface
{
    use RestTrait;
    const ERR_MSG_MAIL = 51;

    /** 
     * @OA\Post(
     *     path="/api/msg",
     *     description="Post a message that can be use in a contact form",
     *     operationId="PostMsg",
     *     tags={"Contact Form"},
     *     @OA\RequestBody(
     *        required=true, 
     *        @OA\JsonContent(
     *           required={"nom", "mail", "message"},
     *           @OA\Property(property="nom", type="string", example="Jessica Smith"),
     *           @OA\Property(property="mail", type="string", example="jessica@smith.org"),
     *           @OA\Property(property="message", type="string", example="hello, I would like ..."),
     *           @OA\Property(property="tel", type="string"),
     *           @OA\Property(property="sujet", type="string"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK but an error was encountered", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=201, description="Message posted, ", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=400, description="Invalid body", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     * )
     */
    function post(array $data, array $param, object $Env): array
    {
        $this->_method = HttpMethod::POST;

        if (!isset($Env->Option->contact)) return $this->retUnavail("objet contact doesn't found in environment table");

        $Contact = $Env->Option->contact;

        [$controle, $fails, $nom, $mail, $message, $tel, $sujet] = Carray::arrayCheck($data, [
            "nom" => ["protected" => true, "limit" => 80],
            "mail" => ["protected" => true, "limit" => 200],
            "message" => ["protected" => true, "limit" => 3200],
            "tel" =>  ["protected" => true, "limit" => 20, "default" => ""],
            "sujet" =>  ["protected" => true, "limit" => 200, "default" => ""]
        ]);
        if (!$controle) return $this->retCrlFail($fails);

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            return $this->retApi(errorcode: self::ERR_MSG_MAIL, isApp: true, content: "invalid mail");

        $user = $Contact->user;
        $hash  = sha1($_SERVER["REMOTE_ADDR"] ?? "");
        $host  = $_SERVER["HTTP_HOST"] ?? "";
        $j_msg = json_encode(compact("nom", "tel", "mail", "sujet", "message"));
        $fields = compact("user", "hash", "host", "j_msg");

        $Msg = new Table(DBParam::$prefixe, new Message());
        if (Rest::spam($mail, $message) || Rest::stopMsg($hash)) {
            $fields["spam"] = 1;
            $Msg->put($fields);
            return $this->retApi(content: "Ok");
        }
        if (!$Msg->put($fields)) return $this->retUnavail("insert message to {$user} failed");

        if ($Contact->sendMail === true) {
            $message = preg_replace(
                ['/{{nom}}/', '/{{mail}}/', '/{{host}}/', '/{{message}}/'],
                [$nom, $mail, $host, $message],
                $Contact->mail_template
            );
            if (!Rest::envoi_mail($Contact->mail, $message)) {
                return $this->retApi(
                    errorcode: self::ERR_INSERT,
                    content: "internal error"
                );
            }
        }
        
        return $this->retApi(content: "Ok");
    }
}
