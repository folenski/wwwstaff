<?php

/**
 * Interface pour un drivers SQL
 * 
 * @author  folenski
 * @since 1.0  13/12/2022: Version initiale
 *  
 */

namespace Staff\Drivers;

interface DriversInterface
{

    const SZ_SM_TXT = "256";
    const SZ_LG_TXT = "10000";
    const SZ_JSON = "10000";

    /**
     * @return string retourne le type json
     */
    function typejson(): string;

    /**
     * @return string retourne le type txt
     */
    function typeText(bool $small = true): string;

    /**
     * @return string retourne le autoincrement
     */
    function increment(): string;

    /**
     * @return string retourne la requête pour lister toutes les tables
     */
    function showTables(): string;

}
