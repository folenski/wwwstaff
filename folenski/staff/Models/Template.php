<?php

/**
 * Table Template
 *
 * @author  folenski
 * @version 1.0  4/08/2022 : Version initiale 
 * @version 1.1 10/12/2022: utilisation des tags
 * @version 1.2 09/07/2023: propriété _error ajoutée pour supprimer un warning
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Lib\Carray;

final class Template implements TableInterface
{
    private const _NAME = "template";
    private const _DESC = [
        "id_div"     => "#TXT_SM PRIMARY KEY",
        "template"   => "#TXT_LG NOT NULL",
        "file_php"   => "#TXT_SM",
        "order_by"   => "INTEGER DEFAULT 0",
        "compile"    => "INTEGER DEFAULT 0",
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
        $this->_error = false;
        return compact("id_div", "template", "file_php", "compile", "order_by");
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
        return ["id_div" => "?"];
    }
}
