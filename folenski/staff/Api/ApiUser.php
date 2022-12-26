<?php

/**
 * Class ApiUser, Gestion REST pour l'authentification 
 * 
 * @author folenski
 * @version 1.0 Version initiale
 * @version 1.1 04/07/2022: Refactoring
 * @version 1.2 16/12/2022: Prise en compte nouveau model table USER et les retours API
 * 
 */

namespace Staff\Api;

use Staff\Security\Authen;
use Staff\Lib\Carray;

final class ApiUser implements RestInterface
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
        $usrRole = (array_key_exists("role", $param)) ? (int)$param["role"] : -1;
        if ($usrRole == -1) return $this->retTokenNeeded();
        if ($usrRole < 2)
            return $this->retApi(errorcode: Authen::USER_NOT_AUTHORIZE, isApp: true, content: "not authorized");

        [$controle, $fails, $user, $password, $mail, $group] = Carray::arrayCheck(
            $data,
            [
                "user" => ["protected" => true, "limit" => 100, "type" => "string"],
                "password" => ["type" => "string"],
                "mail" => ["protected" => true, "limit" => 200, "type" => "string"],
                "group" => ["protected" => true, "default" => "user", "limit" => 10, "type" => "string"]
            ]
        );
        if (!$controle) return $this->retCrlFail($fails);

        $role = ($group == "admin") ? Authen::ROLE_ADMIN : (($group == "user") ? Authen::ROLE_USER : Authen::ROLE_SVC);
        $retour = Authen::add($user, $mail, $password, $role);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return $this->retApi(errorcode: $retour, isApp: true, content: $libelle);
        }
        return $this->retApi();
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
        $user = (array_key_exists("user", $param)) ? $param["user"] : "";
        $token = (array_key_exists("token", $param)) ? $param["token"] : "";
        if ($user == "" || $token == "") return $this->retTokenNeeded();

        [$controle, $fails, $password, $mail] = Carray::arrayCheck(
            $data,
            [
                "password" => ["mandatory" => false],
                "mail" => ["mandatory" => false, "protected" => true, "limit" => 200]
            ]
        );
        if (!$controle) return $this->retCrlFail($fails);

        $retour = Authen::update($user, $password, $mail);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return $this->retApi(errorcode: $retour, isApp: true, content: $libelle);
        }
        if ($password !== null) Authen::revoke($token);
        return $this->retApi();
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
        if (!array_key_exists("token", $param)) return $this->retTokenNeeded();

        [$controle, $fails, $user] = Carray::arrayCheck(
            $data,
            [
                "user" => ["protected" => true, "limit" => 200, "type" => "string"]
            ]
        );
        if (!$controle) return $this->retCrlFail($fails);

        $retour = Authen::del($user);
        if (!$retour) {
            $libelle = Authen::get_lib(Authen::USER_NOT_FOUND);
            return $this->retApi(errorcode: Authen::USER_NOT_FOUND, isApp: true, content: $libelle);
        }
        return $this->retApi();
    }
}
