<?php

/**
 * Interface pour les méthodes http GET, POST, PUT, DELETE 
 * 
 * @author  folenski
 * @since 1.0  06/07/2022: Version initiale 
 * @since 1.1  10/12/2022: add error code: ERR_OK, ERR_NOT_FOUND, ERR_SQL, ERR_BAD_URI
 *  
 */

namespace Staff\Api;

interface RestInterface
{
    /*Les principaux codes retour HTTP */
    const HTTP_OK      = 200;
    const HTTP_BAD     = 400;
    const HTTP_AUTH_KO = 401;
    const HTTP_DENIED  = 403;
    const HTTP_ERROR   = 500;
    const HTTP_UNAVAIL = 503;

    const ERR_OK = 0;
    const ERR_NOT_FOUND = 2;
    const ERR_INSERT = 3;
    const ERR_INTERNAL = 10;
    const ERR_SQL = 11;
    const ERR_NO_TOKEN = 20;
    const ERR_BAD_URI = 21;
    const ERR_BAD_BODY = 22;
    const ERR_BAD_TOKEN = 23;
    const ERR_CUSTOM_APP = 100;

    /**
     * Méthode GET
     * @param array $data tableau les parametres passés par l'url 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function get(array $data, array $param, object $Env): array;

    /**
     * Méthode POST
     * @param array $data tableau avec les informations du body 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function post(array $data, array $param, object $Env): array;

    /**
     * Méthode PUT
     * @param array $data tableau avec les informations du body 
     * @param array $param paramétre du routeur 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function put(array $data, array $param, object $Env): array;

    /**
     * Méthode DELETE
     * @param array $param paramétre du routeur 
     * @param array $data tableau avec les informations du body 
     * @param object $Env object avec les options du site
     * @return array reponse à emettre
     */
    function delete(array $data, array $param, object $Env): array;
}
