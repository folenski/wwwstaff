<?php

/**
 * Table Message
 *
 * @author  folenski
 * @since 1.0  09/08/2022 : Version Initiale 
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;

final class Message implements DBParamInterface, TableInterface
{
    private const _NAME = "message";
    private const _DESC = [
        "id"         => "INTEGER PRIMARY KEY AUTOINCREMENT",
        "user"       => "VARCHAR(" . self::SZ_SM_TXT . ") NOT NULL",
        "host"       => "VARCHAR(" . self::SZ_SM_TXT . ")",
        "hash"       => "VARCHAR(" . self::SZ_SM_TXT . ") NOT NULL",
        "j_msg"      => "VARCHAR(" . self::SZ_JSON . ") NOT NULL",
        "done"       => "INTEGER DEFAULT 0",
        "created_at" => "DATETIME NOT NULL",
        "_key"       => "FOREIGN KEY (user) REFERENCES %suser(user)",
        "_index"     => "hash"
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
     * @return false|string retourne l'erreur recontrée lors du check 
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
