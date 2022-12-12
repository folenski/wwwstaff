<?php

/**
 * Gestion REST pour l'authentification
 * 
 * @author folenski
 * @since   1.2.1 09/12/2022
 * @version 1.0.0 Version initialie
 * @version 1.1.0 Utilisation de la class Carray
 * @version 1.2.0 Refactoring de code
 * @version 1.2.1 09/12/2022, Utilisation du trait + response
 * 
 */

namespace Staff\Api;

use Staff\Services\Authen;
use Staff\Services\Carray;

final class ApiAuth implements RestInterface
{
    use RestTrait;

    /**
     * Méthode POST
     * @param array $data tableau avec les informations du body 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function post(array $data, array $param, object $Env): array
    {
        [$controle, $fails, $user, $pass] = Carray::arrayCheck(
            $data,
            [
                "user" => ["protected" => true, "limit" => 100, "type" => "string"],
                "password" => ["type" => "string"]
            ]
        );
        if (!$controle)
            return [
                "http" => self::HTTP_BAD,
                "errorcode" => self::ERR_BAD_URI,
                "response" => [
                    "content" => $fails
                ]
            ];

        //$User = new User($Rest->prefixe);
        [$retour, $token, $mail, $last] = Authen::login($user, $pass, 60);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return [
                "http" => self::HTTP_OK,
                "errorcode" => self::ERR_CUSTOM_APP + $retour,
                "response" => [
                    "content" => $libelle
                ]
            ];
        }
        return [
            "http" => self::HTTP_OK,
            "errorcode" => self::ERR_OK,
            "response" => [
                 "token" => $token, "mail" => $mail, "last_cnx" => $last
            ]
        ];
    }
    /**
     * Méthode DELETE
     * @param array $param paramétre du routeur 
     * @param array $data tableau avec les informations du body 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function delete(array $data, array $param, object $Env): array
    {
        if (!array_key_exists("token", $param))
            return [
                "http" => self::HTTP_AUTH_KO,
                "errorcode" => self::ERR_NO_TOKEN,
                "response" => [
                    "content" => "token needed"
                ]
            ];

        $token = $param["token"];

        if (!Authen::revoke($token))
            return [
                "http" => self::HTTP_UNAVAIL,
                "errorcode" => self::ERR_INTERNAL,
                "response" => [
                    "content" => "Internal error"
                ]
            ];
        return
            [
                "http" => self::HTTP_OK,
                "errorcode" => self::ERR_OK
            ];
    }
}
