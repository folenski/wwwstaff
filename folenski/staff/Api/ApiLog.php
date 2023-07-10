<?php

/**
 * Gestion REST pour la lecture des Logs 
 * 
 * @author folenski
 * @version 1.0 11/07/2022, Version initialie
 * @version 1.1 09/12/2022, Utilisation d'un trait pour les methondes non implementées
 * 
 */

namespace Staff\Api;

use Staff\Lib\Carray;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Models\Log;

final class ApiLog implements RestInterface
{
    use RestTrait;

    /**
     * Méthode GET
     * @param array $data tableau les parametres passés par l'url 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function get(array $data, array $param, object $Env): array
    {
        $Log = new Table(DBParam::$prefixe, new Log());
        if (!array_key_exists("token", $param)) return $this->retTokenNeeded();

        [$controle, $fails, $limit, $id] = Carray::arrayCheck($data, [
            "limit" => ["default" => 50],
            "id" => ["mandatory" => false],
        ]);
        if (!$controle) return $this->retCrlFail($fails);

        if ($id === null) $fields = null;
        else $fields["id"] = "> {$id}";

        $rows = $Log->get(id: $fields, limit: (int)$limit, order: $Log->orderBy(["id"]));
        if ($rows === false) return $this->retUnAvail();

        return $this->retApi(content: null, data: $rows);
    }
}
