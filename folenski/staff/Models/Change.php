<?php

/**
 * Table Chaage
 *
 * @author  folenski
 * @version 1.0 17/12/2022: Version Initiale 
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;

final class Change implements TableInterface
{
    private const _NAME = "change";
    private const _DESC = [
        "id"         => "INTEGER PRIMARY KEY #AUTOINCR",
        "version"    => "INTEGER DEFAULT 0",
        "lib"        => "#TXT_SM NOT NULL",
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
     * @return array|false liste des champs ou false si il y a eu une erreur 
     */
    function check(array $fields): array|false
    {
        $this->_error = "Not permitted";
        return false;
    }

    /**
     * @return false|string  retourne l'erreur recontrée lors du check 
     */
    function errors(): false|string
    {
        return (isset($this->_error)) ? $this->_error : false;
    }

    /**
     * @return array les champs permettant de selectionner un element unique
     */
    function keys(): array
    {
        return ["version" => "?", "lib" => "?"];
    }
}
