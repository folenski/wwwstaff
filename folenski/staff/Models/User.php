<?php

/**
 * Table User
 *
 * @author  folenski
 * @since 1.0  4/08/2022 : Version Initiale 
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Services\Carray;
use Staff\Security\Security;

final class User implements DBParamInterface, TableInterface
{
    private const _NAME = "user";
    private const _DESC = [
        "user"       => "VARCHAR(" . self::SZ_SM_TXT . ") PRIMARY KEY",
        "mail"       => "VARCHAR(" . self::SZ_SM_TXT . ") NOT NULL",
        "pass"       => "VARCHAR(" . self::SZ_SM_TXT . ")",
        "permission" => "INTEGER DEFAULT 0",
        "bad_pin"    => "INTEGER DEFAULT 0",
        "created_at" => "DATETIME NOT NULL",
        "updated_at" => "DATETIME NOT NULL",
    ];

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
        [$ret, $fail, $user, $mail, $password, $permission] = Carray::arrayCheck($fields, [
            "user" => ["type" => "string"],
            "mail" => ["type" => "string"],
            "password"  => ["type" => "string"],
            "permission" => ["mandatory" => false, "default" => "admin"],
        ]);
        if (!$ret) {
            $this->_error = $fail;
            return null;
        }
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->_error = "[!mail]";
            return null;
        }
        if (!Security::check_pass($password)) {
            $this->_error = "[!password]";
            return null;
        }
        $pass = Security::crypt_pass($password);
        if ($pass === null) {
            $this->_error = "[!password]";
            return null;
        }
        unset($this->_error);
        return compact("user", "mail", "pass", "permission");
    }

    /**
     * @return false|string retourne l'erreur recontrée lors du check 
     */
    function errors(): false|string
    {
        return (isset($this->_error)) ? $this->_error: false;
    }

    /**
     * @return array les champs permettant de selectionner un element unique
     */
    function keys(): array
    {
        return ["user" => "?"];
    }
}
