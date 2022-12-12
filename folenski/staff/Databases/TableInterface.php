<?php

/**
 * Interface pour la class abtraites Table
 * 
 * @author  folenski
 * @since 1.0  16/07/2022 : Version initiale 
 */

namespace Staff\Databases;

interface TableInterface
{
    /**
     * @return array retourne le nom et la description de la table
     */
    function init(): array;

    /**
     * Permet de contrôler la présence des champs obligatoires pour la table
     * @param array $fields les champs à vérifier 
     * @return array|false liste des champs ou false si il y a eu une erreur 
     */
    function check(array $fields): array|false;

    /**
     * @return false|string retourne l'erreur recontrée lors du check 
     */
    function errors(): false|string;

    /**
     * @return array les champs permettant de selectionner un element unique
     */
    function keys(): array;
}
