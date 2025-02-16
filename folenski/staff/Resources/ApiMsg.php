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

namespace Staff\Resources;

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
     * @OA\Get(
     *     path="/api/msg",
     *     description="Return the messages posted from the website",
     *     operationId="GetMsg",
     *     tags={"Manage Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Array of message object",
     *        @OA\JsonContent(type="array", @OA\Items(
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="name", type="string", example="john doe"),
     *           @OA\Property(property="mail", type="string", example="john@gmail.com"),
     *           @OA\Property(property="message", type="string", example="hi..."),
     *           @OA\Property(property="tel", type="string", example="+014455"),
     *           @OA\Property(property="subject", type="string", example="contact"),
     *           @OA\Property(property="read", type="boolean"),
     *           @OA\Property(property="date", type="string", format="date-time"),
     *         )),
     *     ),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     * )
     */
    function get(array $body, array $param, object $Env): array
    {
        $Msg = new Table(DBParam::$prefixe, new Message());
        $this->_method = HttpMethod::GET;

        if (!$this->hasToken($param)) return $this->unauthorized();
        [$controle, $fails, $limit] = Carray::arrayCheck(
            $param,
            ["limit" => ["mandatory" => false, "protected" => true, "default" => 0]]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $rows = $Msg->get(
            limit: $limit,
            order: $Msg->orderby(["created_at"], false)
        );
        if ($rows === false) return $this->resourcesUnavail();

        $bodyOut = [];
        foreach ($rows as $value) {
            $dmsg = (array)json_decode($value->j_msg);
            if (!array_key_exists("nom", $dmsg)) continue;
            array_push(
                $bodyOut,
                [
                    "id" => $value->id,
                    "name" => $dmsg["nom"], "mail" => $dmsg["mail"],
                    "message" => $dmsg["message"], "tel" => $dmsg["tel"],
                    "subject" => $dmsg["sujet"], "read" => $value->done === 1,
                    "date" => $this->convDate($value->created_at)
                ]
            );
        }
        return $this->retOk(data: $bodyOut);
    }

    /** 
     * @OA\Post(
     *     path="/api/msg",
     *     description="Post a message that can be use by a contact form",
     *     operationId="PostMsg",
     *     tags={"Contact Form"},
     *     @OA\RequestBody(
     *        required=true, 
     *        description="Message object",
     *        @OA\JsonContent(
     *           required={"name", "mail", "message"},
     *           @OA\Property(property="name", type="string", example="Jessica Smith"),
     *           @OA\Property(property="mail", type="string", example="jessica@smith.org"),
     *           @OA\Property(property="message", type="string", example="hello, I would like ..."),
     *           @OA\Property(property="tel", type="string", example="+01.485566"),
     *           @OA\Property(property="subject", type="string", example="contact"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="An error was encountered", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=201, description="Message posted, ", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=400, description="Invalid body", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     * )
     */
    function post(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::POST;

        if (!isset($Env->Option->contact)) return $this->resourcesUnavail("objet contact doesn't found in environment table");

        $Contact = $Env->Option->contact;

        [$controle, $fails, $nom, $mail, $message, $tel, $sujet] = Carray::arrayCheck($body, [
            "name" => ["protected" => true, "limit" => 80],
            "mail" => ["protected" => true, "limit" => 200],
            "message" => ["protected" => true, "limit" => 3200],
            "tel" =>  ["protected" => true, "limit" => 20, "default" => ""],
            "subject" =>  ["protected" => true, "limit" => 200, "default" => ""]
        ]);
        if (!$controle) return $this->controlsFailed($fails);

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL))
            return $this->retOk(errorcode: self::ERR_MSG_MAIL, message: "Invalid mail");

        $user = $Contact->user;
        $hash  = sha1($_SERVER["REMOTE_ADDR"] ?? "");
        $host  = $_SERVER["HTTP_HOST"] ?? "";
        $j_msg = json_encode(compact("nom", "tel", "mail", "sujet", "message"));
        $fields = compact("user", "hash", "host", "j_msg");

        $Msg = new Table(DBParam::$prefixe, new Message());
        if (Rest::spam($mail, $message) || Rest::stopMsg($hash)) {
            $fields["spam"] = 1;
            $Msg->put($fields);
            return $this->retOk();
        }
        if (!$Msg->put($fields)) return $this->resourcesUnavail("insert message to {$user} failed");

        if ($Contact?->send_mail === true) {
            $message = preg_replace(
                ['/{{nom}}/', '/{{mail}}/', '/{{host}}/', '/{{message}}/'],
                [$nom, $mail, $host, $message],
                $Contact->mail_template ?? "no template"
            );
            if (!Rest::envoi_mail($Contact->mail, $message)) {
                return $this->retOk(
                    errorcode: self::ERR_INSERT,
                    message: "internal error"
                );
            }
        }
        return $this->retOk();
    }

    /**
     * @OA\Put(
     *     path="/api/msg/{id}",
     *     description="Update the property 'read'",
     *     operationId="PutMsg",
     *     tags={"Manage Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID",
     *        required=true,
     *        @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *        required=true, 
     *        @OA\JsonContent(
     *           @OA\Property(property="read", type="boolean"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Update failed", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=201, description="Update done", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=400, description="ID parameter is missing", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=503, description="internal error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     * )
     */
    function put(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::PUT;
        $Msg = new Table(DBParam::$prefixe, new Message(), true);

        if (!$this->hasToken($param)) return $this->unauthorized();
        [$controle, $fails, $id] = Carray::arrayCheck(
            $param,
            ["id" => ["mandatory" => true, "protected" => true]]
        );
        if (!$controle) return $this->controlsFailed($fails);

        [$controle, $fails, $read] = Carray::arrayCheck(
            $body,
            [
                "read" => ["mandatory" => true],
            ]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $ret = $Msg->put(data: ["done" => ($read) ? 1 : 0], id: ["id" => $id]);
        if (!$ret) $this->resourcesUnavail();
        return $this->retOk();
    }

    /**
     * @OA\Delete(
     *     path="/api/msg/{id}",
     *     description="Remove a message",
     *     operationId="DelMsg",
     *     tags={"Manage Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *        name="id",
     *        in="path",
     *        description="ID",
     *        required=true,
     *        @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="The message is removed", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=400, description="The ID parameter is missing", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     * )
     */
    function delete(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::DELETE;
        $Msg = new Table(DBParam::$prefixe, new Message());

        if (!$this->hasToken($param)) return $this->unauthorized();
        [$controle, $fails, $id] = Carray::arrayCheck(
            $param,
            ["id" => ["mandatory" => true, "protected" => true]]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $Msg->del(["id" => $id]);
        return $this->retOk();
    }
}
