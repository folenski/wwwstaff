<?php

/**
 * Class ApiUser, Gestion REST pour l'authentification 
 * 
 * @author folenski
 * @since   1.0.0 04/07/2022
 * @version 1.0.0 Version initiale
 * @version 1.1.0 Refactoring
 */

namespace Staff\Api;

use Staff\Services\Authen;
use Staff\Services\Carray;

final class ApiUser implements RestInterface
{
    /**
     * Méthode GET
     * @param array $data tableau les parametres passés par l'url 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function get(array $data, array $param, object $Env): array
    {
        return ["http" => self::HTTP_UNAVAIL, "content" => "Not implemented"];
    }

    /**
     * Méthode POST
     * @param array $data tableau avec les informations du body 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function post(array $data, array $param, object $Env): array
    {
        if (!array_key_exists("permission", $param))
            return [
                "http" => self::HTTP_AUTH_KO,
                "content" => "token needed"
            ];
        [$controle, $fails, $user, $password, $mail, $admin] = Carray::arrayCheck(
            $data,
            [
                "user" => ["protected" => true, "limit" => 100, "type" => "string"],
                "password" => ["type" => "string"],
                "mail" => ["protected" => true, "limit" => 200, "type" => "string"],
                "group" => ["protected" => true, "default" => "admin", "limit" => 10, "type" => "string"]
            ]
        );
        if (!$controle)
            return [
                "http" => self::HTTP_BAD,
                "content" => $fails
            ];
        if (!Authen::autorize(Authen::PERM_ADD_USER, $param["permission"]))
            return [
                "http" => self::HTTP_OK, "errorcode" => -1,
                "content" => "no autorize"
            ];

        $groupPerm = Authen::get_permission($admin);
        $retour = Authen::add($user, $mail, $password, $groupPerm);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return
                ["http" => self::HTTP_OK, "errorcode" => $retour, "content" => $libelle];
        }
        return
            ["http" => self::HTTP_OK, "errorcode" => 0, "user" => $user];
    }

    /**
     * Méthode PUT
     * @param array $data tableau avec les informations du body 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function put(array $data, array $param, object $Env): array
    {
        if (!array_key_exists("user", $param))
            return [
                "http" => self::HTTP_AUTH_KO,
                "content" => "token needed"
            ];
        [$controle, $fails, $password, $mail] = Carray::arrayCheck(
            $data,
            [
                "password" => ["mandatory" => false],
                "mail" => ["mandatory" => false, "protected" => true, "limit" => 200]
            ]
        );
        if (!$controle)
            return [
                "http" => self::HTTP_BAD,
                "content" => $fails
            ];

        $retour = Authen::update($param["user"], $password, $mail);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return
                [
                    "http" => self::HTTP_OK,
                    "errorcode" => $retour, "content" => $libelle
                ];
        }
        if ( $password !== null) Authen::revoke($param["token"]);
        return ["http" => self::HTTP_OK, "errorcode" => 0, "content" => "done"];
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
                "content" => "token needed"
            ];

        [$controle, $fails, $user] = Carray::arrayCheck(
            $data,
            [
                "user" => ["protected" => true, "limit" => 200, "type" => "string"]
            ]
        );
        if (!$controle)
            return [
                "http" => self::HTTP_BAD,
                "content" => $fails
            ];

        $retour = Authen::del($user);
        if ( ! $retour ) {
            $libelle = Authen::get_lib(Authen::USER_NOT_FOUND);
            return
                ["http" => self::HTTP_OK, "errorcode" => Authen::USER_NOT_FOUND, "content" => $libelle];
        }
        return
            ["http" => self::HTTP_OK, "errorcode" => 0, "content" => "Suppression du compte {$user}"];
    }
}
