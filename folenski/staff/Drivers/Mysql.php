<?php

/**
 * Class pour construire les tables Mysql
 * 
 * @author  folenski
 * @since 1.0 18/12/2022: Version initiale
 *  
 */

namespace Staff\Drivers;

class Mysql implements DriversInterface
{

    /**
     * @return string retourne le type json
     */
    function typeJson(bool $small = true): string
    {
        if ($small) return "VARCHAR(" . self::SZ_SM_JSON . ") COLLATE utf8_unicode_ci";
        return "VARCHAR(" . self::SZ_JSON . ") COLLATE utf8_unicode_ci";
    }

    /**
     * @return string retourne le type txt
     */
    function typeText(bool $small = true): string
    {
        if ($small) return "VARCHAR(" . self::SZ_SM_TXT . ") COLLATE latin1_swedish_ci";
        return "VARCHAR(" . self::SZ_LG_TXT . ") COLLATE latin1_swedish_ci";
    }

    /**
     * @return string retourne l'autoincrement
     */
    function increment(): string
    {
        return "AUTO_INCREMENT";
    }

    function showTables(): string 
    {
        return "SHOW TABLES";
    }
    
}
