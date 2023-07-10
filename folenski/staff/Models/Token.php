<?php

/**
 * Table Token
 *
 * @author  folenski
 * @version 1.0 09/08/2022: Version initiale
 * @version 1.1 10/12/2022: utilisation des tags
 * @version 1.2 09/07/2023: propriété _error ajoutée pour supprimer un warning
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;

final class Token implements TableInterface
{
    private const _NAME = "token";
    private const _DESC = [
        "token"      => "#TXT_SM PRIMARY KEY",
        "user"       => "#TXT_SM NOT NULL",
        "revoked"    => "INTEGER DEFAULT 0",
        "expired_at" => "DATETIME NOT NULL",
        "created_at" => "DATETIME NOT NULL",
        "_key"       => "FOREIGN KEY (user) REFERENCES %suser(user)"
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
     * @return array|false liste des champs ou false si il y a eu une erreur 
     */
    function check(array $fields): array|false
    {
        $this->_error = "Not permitted";
        return false;
    }

    /**
     * @return false|string retourne l'erreur recontrée lors du check 
     */
    function errors(): false|string
    {
        return $this->_error;
    }

    /**
     * @return array les champs permettant de selectionner un element unique
     */
    function keys(): array
    {
        return ["token" => "?"];
    }
}
