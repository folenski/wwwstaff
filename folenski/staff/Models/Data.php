<?php

/**
 * Table Data
 *
 * @author  folenski
 * 
 * @version 1.0.1 4/12/2022: supp du champs "title"
 * @version 1.1 10/12/2022: utilisation des tags
 * @version 1.2 21/12/2022: ajout d'un index sur la ref
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Lib\Carray;

final class Data  implements TableInterface
{
    private const _NAME = "data";
    private const _DESC = [
        "id"         => "INTEGER PRIMARY KEY #AUTOINCR",
        "ref"        => "#TXT_SM",
        "rank"       => "INTEGER DEFAULT 0",
        "j_content"  => "#JSON NOT NULL",
        "id_div"     => "#TXT_SM NOT NULL",
        "created_at" => "DATETIME NOT NULL",
        "updated_at" => "DATETIME NOT NULL",
        "_index"     => "ref"

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
     * @param array $fields, les champs à vérifier 
     * @return array|false  liste des champs ou false si il y a eu une erreur 
     */
    function check(array $fields): array|false
    {
        [$ret, $fail, $id_div, $rank, $ref, $j_content] = Carray::arrayCheck($fields, [
            "id_div"  => ["protected" => true, "type" => "string"],
            "rank" => ["mandatory" => false, "default" => 0],
            "ref" => ["type" => "string"],
            "j_content" => ["json" => true]
        ]);
        if (!$ret) {
            $this->_error = $fail;
            return null;
        }
        unset($this->_error);
        return compact("id_div", "rank", "ref", "j_content");
    }

    /**
     * @return false|string  retourne l'erreur recontrée lors du check 
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
        return ["ref" => "?", "rank" => "?"];
    }
}
