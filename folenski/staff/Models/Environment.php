<?php

/**
 * Table Environment
 *
 * @author  folenski
 * @since 1.0  4/08/2022 : Version Initiale 
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Services\Carray;

final class Environment implements DBParamInterface, TableInterface
{
    private const _NAME = "environment";
    private const _DESC = [
        "name"       => "VARCHAR(3) PRIMARY KEY",
        "j_option"   => "VARCHAR(" . self::SZ_JSON . ") NOT NULL",
        "j_contact"  => "VARCHAR(" . self::SZ_JSON . ")",
        "j_index"    => "VARCHAR(" . self::SZ_JSON . ") NOT NULL",
        "j_route"    => "VARCHAR(" . self::SZ_JSON . ") NOT NULL",
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
        [$ret, $fail, $name, $j_option, $j_contact, $j_index, $j_route]
            = Carray::arrayCheck($fields, [
                "name" => ["type" => "string"],
                "j_option" => ["json" => true],
                "j_contact" => ["json" => true],
                "j_index" => ["json" => true],
                "j_route" => ["json" => true]
            ]);
        if (!$ret) {
            $this->_error = $fail;
            return false;
        }
        unset($this->_error);
        return compact("name", "j_option", "j_contact", "j_index", "j_route");
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
        return ["name" => "?"];
    }
}
