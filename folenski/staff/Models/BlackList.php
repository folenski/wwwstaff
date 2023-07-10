<?php

/**
 * Table black_list, table pour lutte contre le spam
 *
 * @author  folenski
 * @version 1.0 22/12/2022: version initiale 
 * @version 1.1 09/07/2023: propriété _error ajoutée pour supprimer un warning
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Lib\Carray;

final class BlackList implements TableInterface
{
    private const _NAME = "black_list";
    private const _DESC = [
        "name"       => "#TXT_SM PRIMARY KEY",
        "rules"      => "#JSON",
        "active"     => "INTEGER DEFAULT 1",
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
        [$ret, $fail, $name, $rules, $active] = Carray::arrayCheck($fields, [
            "name" => ["type" => "string"],
            "rules" => ["json" => true],
            "active" => ["mandatory" => false, "default" => 1]
        ]);
        if (!$ret) {
            $this->_error = $fail;
            return false;
        }
        $this->_error = false;
        return compact("name", "rules", "active");
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
        return ["name" => "?"];
    }
}
