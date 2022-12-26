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

final class StartTest extends TestCase
{
    private string $repData =  "/dependances/data/";

    public function testCreateTables(): void
    {
        $Adm = new Admin(sqldrv: new Sqlite(), prefixe: PREFIXE);
        $Adm->createAllTables(
            false,
            function (...$arr) {
            }
        );
        $rows = $Adm->showTables();
        $nbr = count($rows);
        $this->assertGreaterThan(6, $nbr);
    }

    /** 
     *  @depends testCreateTables
     */
    public function testLoadTable(): void
    {
        $Adm = new Admin(prefixe: PREFIXE);
        $Rep = dirname(__DIR__) . "{$this->repData}";
        $this->assertIsArray(
            $Adm->load("{$Rep}user.json", true, function (...$arr) {
            })
        );
        $this->assertIsArray(
            $Adm->load("{$Rep}environment.json", true, function (...$arr) {
            })
        );
        $this->assertIsArray(
            $Adm->load("{$Rep}template.json", true, function (...$arr) {
            })
        );
        $this->assertIsArray(
            $Adm->load("{$Rep}data.json", true, function (...$arr) {
            })
        );
        $this->assertIsArray(
            $Adm->load("{$Rep}spam.json", true, function (...$arr) {
            })
        );
    }
}
