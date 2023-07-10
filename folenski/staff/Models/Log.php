<?php

/**
 * Table Log
 *
 * @author  folenski
 * @version 1.0 09/08/2022: Version Initiale 
 * @version 1.1 10/12/2022: Ajout champ error_code , supp level
 * @version 1.2 10/12/2022: utilisation des tags
 * @version 1.3 09/07/2023: propriété _error ajoutée pour supprimer un warning
 * 
 */

namespace Staff\Models;

use Staff\Databases\TableInterface;

final class Log implements TableInterface
{
    private const _NAME = "log";
    private const _DESC = [
        "id"         => "INTEGER PRIMARY KEY #AUTOINCR",
        "http_code"  => "INTEGER DEFAULT 0",
        "error_code" => "INTEGER DEFAULT 0",
        "component"  => "#TXT_SM NOT NULL",
        "message"    => "#TXT_LG NOT NULL",
        "created_at" => "DATETIME NOT NULL",
        "_index"     => "created_at"
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
     * @return false|string  retourne l'erreur recontrée lors du check 
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
        return ["id" => "?"];
    }
}
