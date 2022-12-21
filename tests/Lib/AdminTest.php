<?php

/**
 * Admin: permet de creer la base de tests et de charger les données
 *
 * @author  folenski
 * @version 1.0 07/08/2022: Version initiale  
 * @version 1.1 16/12/2022: test show tables  
 * 
 */

declare(strict_types=1);
require_once dirname(__DIR__) . "/dependances/config.php";

use Staff\Lib\Admin;
use Staff\Drivers\Sqlite;
use PHPUnit\Framework\TestCase;

final class AdminTest extends TestCase
{
    public function testShowTables(): void
    {
        $Adm = new Admin(sqldrv: new Sqlite(), prefixe: PREFIXE);
        $rows = $Adm->showTables();
        $nbr = count($rows);
        $this->assertGreaterThan(6, $nbr);
    }

}
