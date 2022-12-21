<?php

/**
 * Class pour construire les tables sqlite3
 * 
 * @author  folenski
 * @since 1.0  13/12/2022 : Version initiale
 *  
 */

namespace Staff\Drivers;

class Sqlite implements DriversInterface
{

    /**
     * @return string retourne le type json
     */
    function typeJson(): string
    {
        return "VARCHAR(" . self::SZ_JSON . ")";
    }

    /**
     * @return string retourne le type txt
     */
    function typeText(bool $small = true): string
    {
        if ($small) return "VARCHAR(" . self::SZ_SM_TXT . ")";
        return "VARCHAR(" . self::SZ_LG_TXT . ")";
    }

    /**
     * @return string retourne l'autoincrement
     */
    function increment(): string
    {
        return "AUTOINCREMENT";
    }

    function showTables(): string 
    {
        return "SELECT name as name FROM sqlite_schema WHERE type ='table' AND name NOT LIKE 'sqlite_%'";
    }
    
}
