<?php

/**
 * Class AdminDB, class temporaire, elle sera modifiée avec le portage mysql 
 *
 * @author  folenski
 * @since 19/02/2022
 * @version 1.0.0
 * @version 1.0.1 Correctif _select
 *  
 */

namespace Staff\Databases;

class AdminDB
{
    /**
     * @return bool vrai si la table est vrai
     */
    public static function exist(string $table): bool
    {
        if (Database::$driver == "") Database::exec("SELECT 1");
        if (Database::$driver == 'sqlite') {
            $Master = new SqlCore("sqlite_master", []);
            $name = $table;
            $type = 'table';

            $con = Database::query($Master->select(["name"])->where(compact("name", "type"))->toStr());
            if ($con === false) return false;

            if ($con->fetch() === false) return false;
            return true;
        }
        return false;
    }
}
