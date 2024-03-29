<?php

/**
 * Table User
 *
 * @author  folenski
 * @version 1.0  4/08/2022: version initiale 
 * @version 1.1 10/12/2022: utilisation des tags, corr bug sur methode save
 * @version 1.2 09/07/2023: propriété _error ajoutée pour supprimer un warning
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Lib\Carray;
use Staff\Security\Authen;

final class User implements TableInterface
{
    private const _NAME = "user";
    private const _DESC = [
        "user"       => "#TXT_SM PRIMARY KEY",
        "mail"       => "#TXT_SM NOT NULL",
        "password"   => "#TXT_SM",
        "role"       => "INTEGER DEFAULT 0",
        "bad_pin"    => "INTEGER DEFAULT 0",
        "created_at" => "DATETIME NOT NULL",
        "updated_at" => "DATETIME NOT NULL",
    ];
    private string|bool $_error = false;

    /**
     * @return array retourne le nom et la description de la table
     */
    function init(): array
    {
        return [self::_NAME, self::_DESC];
    }

    /**
     * Permet de contrôler la présence des champs obligatoires pour la table
     * @param array $fields les champs à vérifier 
     * @return array|false  liste des champs ou false si il y a eu une erreur 
     */
    function check(array $fields): array|false
    {
        [$ret, $fail, $user, $mail, $passCl, $role] = Carray::arrayCheck($fields, [
            "user" => ["type" => "string"],
            "mail" => ["type" => "string"],
            "password" => ["type" => "string"],
            "role" => ["mandatory" => false, "default" => 0],
        ]);
        if (!$ret) {
            $this->_error = $fail;
            return false;
        }
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->_error = "[!mail]";
            return false;
        }
        $password = Authen::cyPass($passCl);
        if (gettype($password) == "integer") {
            $this->_error = "[!password]";
            return false;
        }
        $this->_error = false;
        return compact("user", "mail", "password", "role");
    }

    /**
     * @return false|string retourne l'erreur recontrée lors du check 
     */
    function errors(): false|string
    {
        return  $this->_error;
    }

    /**
     * @return array les champs permettant de selectionner un element unique
     */
    function keys(): array
    {
        return ["user" => "?"];
    }
}
