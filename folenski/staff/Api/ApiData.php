<?php

/**
 * Gestion REST pour la lecture des données
 * 
 * @author folenski
 * @version 1.0.0 09/12/2022, Version Intiale
 * @version 1.0.1 10/12/2022, ajout des champs ref et id_div
 */

namespace Staff\Api;

use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Models\Data;

final class ApiData implements RestInterface
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
        $Data = new Table(DBParam::$prefixe, new Data());
        $ref = (array_key_exists("ref", $param)) ? $param["ref"] : "";

        if ($ref == "") return $this->retCrlFail("ref", true);

        $rows = $Data->get(id: ["ref" => $ref], limit: 1);
        if ($rows === false) return $this->retUnAvail();
        if (count($rows) == 0) {
            return $this->retApi(
                errorcode: self::ERR_NOT_FOUND,
                content: null,
                data: ["data" => []]
            );
        }
        $enr = $rows[0];
        return $this->retApi(
            content: null,
            data: [
                "ref" => $enr->ref,
                "id_div" => $enr->id_div,
                "data" => json_decode($rows[0]->j_content)
            ]
        );
    }
}
