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
 * @version 1.3.0 16/12/2022, Utilisation de l'api retour 
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
        if (!$controle) return $this->retCrlFail($fails);

        [$retour, $token, $mail, $last] = Authen::login($user, $pass, 60);
        if ($retour != Authen::USER_OK) {
            $libelle = Authen::get_lib($retour);
            return $this->retApi(errorcode: $retour, isApp: true, content: $libelle);
        }
        return $this->retApi(content: null, data: [
            "token" => $token, "mail" => $mail, "last_cnx" => $last
        ]);
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
        $token = (array_key_exists("token", $param)) ? $param["token"] : "";
        if ($token == "") return $this->retTokenNeeded();
        if (!Authen::revoke($token)) return $this->retUnAvail();
        return $this->retApi();
    }
}
