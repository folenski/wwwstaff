<?php

/**
 * Table Log
 *
 * @author  folenski
 * @since 1.0  09/08/2022: Version Initiale 
 * @since 1.1  10/12/2022: Ajout champ error_code , supp level
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;

final class Log implements DBParamInterface, TableInterface
{
    private const _NAME = "log";
    private const _DESC = [
        "id"         => "INTEGER PRIMARY KEY AUTOINCREMENT",
        "http_code"  => "INTEGER DEFAULT 0",
        "error_code" => "INTEGER DEFAULT 0",
        "component"  => "VARCHAR(" . self::SZ_SM_TXT . ") NOT NULL",
        "message"    => "VARCHAR(" . self::SZ_LG_TXT . ") NOT NULL",
        "created_at" => "DATETIME NOT NULL",
        "_index"     => "created_at"
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
        return ["id" => "?"];
    }
}
