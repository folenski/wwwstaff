<?php

/**
 * Table Environment
 *
 * @author  folenski
 * @version 1.0  4/08/2022: version initiale 
 * @version 1.1 10/12/2022: utilisation des tags
 * @version 1.2 26/12/2022: utilisation des types small pour mysql
 * @version 1.3 10/01/2023: suppression de la colonne j_contact
 * @version 1.4 09/07/2023: propriété _error ajoutée, suppression du champ j_route
 * @version 1.5 19/04/2024: suppression du champ j_index
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;
use Staff\Lib\Carray;

final class Environment implements TableInterface
{
    private const _NAME = "environment";
    private const _DESC = [
        "name"       => "VARCHAR(3) PRIMARY KEY",
        "j_option"   => "#JSON_SM NOT NULL",
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
     * @return array|false liste des champs ou false si il y a eu une erreur 
     */
    function check(array $fields): array|false
    {
        [$ret, $fail, $name, $j_option]
            = Carray::arrayCheck($fields, [
                "name" => ["type" => "string"],
                "j_option" => ["json" => true]
            ]);
        if (!$ret) {
            $this->_error = $fail;
            return false;
        }
        $this->_error = false;
        return compact("name", "j_option");
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
        return ["name" => "?"];
    }
}
