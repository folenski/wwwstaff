<?php

/**
 * Table Template
 *
 * @author  folenski
 * @since 1.0  4/08/2022 : Version Initiale 
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Services\Carray;

final class Template  implements DBParamInterface, TableInterface
{
    private const _NAME = "template";
    private const _DESC = [
        "id_div"     => "VARCHAR(" . self::SZ_SM_TXT . ") PRIMARY KEY",
        "template"   => "VARCHAR(" . self::SZ_LG_TXT . ") NOT NULL",
        "file_php"   => "VARCHAR(" . self::SZ_SM_TXT . ")",
        "order_by"   => "INTEGER DEFAULT 0",    
        "compile"    => "INTEGER DEFAULT 0",
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
        [$ret, $fail, $id_div, $template, $file_php, $compile, $order_by] = Carray::arrayCheck($fields, [
            "id_div"  => ["protected" => true, "type" => "string"],
            "template" => ["type" => "string"],
            "file_php" => ["type" => "string"],
            "compile" => ["mandatory" => false, "type" => "integer", "default" => 0],
            "order_by" => ["mandatory" => false, "type" => "integer", "default" => 0]
        ]);
        if (!$ret) {
            $this->_error = $fail;
            return false;
        }
        unset($this->_error);
        return compact("id_div", "template", "file_php", "compile", "order_by");
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
        return ["id_div" => "?"];
    }
}
