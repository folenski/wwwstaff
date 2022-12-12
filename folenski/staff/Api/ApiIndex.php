<?php

/**
 * Gestion REST pour la lecture de l'index
 * 
 * @author folenski
 * @version 1.0.0 09/12/2022, Version Initiale
 * @version 1.0.1 10/12/2022, ajout errorcode
 */

namespace Staff\Api;

final class ApiIndex implements RestInterface
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
        if ($Env->Option->maintenance) return ["http" => self::HTTP_OK, "errorcode" => self::ERR_OK, "response" => []];
        return  ["http" => self::HTTP_OK, "errorcode" => self::ERR_OK, "response" => $Env->index];
    }
}
