<?php

/**
 * Init de l'environnement de tests
 *
 * @author  folenski
 * @version 1.0 07/08/2022: Version initiale  
 * @version 1.1 15/12/2022: Adaptation à la table user
 *   
 */

declare(strict_types=1);
require_once  dirname(__DIR__) . "/dependances/config.php";

use Staff\Databases\SqlAdmin;
use Staff\Databases\Database;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Drivers\Sqlite;
use Staff\Services\Authen;
use PHPUnit\Framework\TestCase;

final class Init extends TestCase
{
    public function testCreateTable(): void
    {
        $cpt = 0;
        foreach (DBParam::TABLE as $nomtable) {
            $Adm = new SqlAdmin(DBParam::$prefixe, new Sqlite(), DBParam::get_table($nomtable));
            Database::exec($Adm->create()->exists()->table()->toStr());
            $cpt++;
        }
        $this->assertSame(count(DBParam::TABLE), $cpt);
    }

    /** 
     *  @depends testCreateTable
     */
    public function testCreateUser(): void
    {
        $Usr = new Table(DBParam::$prefixe, DBParam::get_table("user"));
        $Usr->put([
            "user" => USER_SVC,
            "mail" => "no@no.lan",
            "password" => Authen::cyPass(USER_SVC_PASS),
            "role" => 2
        ]);
        $rows = $Usr->get(["user" => USER_SVC], limit: 0);
        $this->assertSame(1, count($rows));
    }
}
