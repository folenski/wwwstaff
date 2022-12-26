<?php

/**
 * Module  de test la class Table, 
 * cette class a évolué afin d'offrir des méthodes pour manipuler une table
 * 
 * @author  folenski
 * @since 1.0 26/07/2022 version initiale
 * @since 1.1 05/08/2022 refactoring de la class
 * @since 1.2 16/12/2022 test des limits
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Models\Template;
use Staff\Models\Data;
use Staff\Models\Log;
use Staff\Databases\Table;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    public function testPutTable(): void
    {
        $Table = new Table(PREFIXE, new Template());
        $Table->del(["id_div" => "like __content%"]);
        $id_div = "__content";
        $file_php = "my content";
        $template = "here we are test_content";
        $this->assertSame(
            true,
            $Table->put(compact("file_php", "id_div", "template"))
        );

        $Data = new Table(Entite: new Data(), prefixe: PREFIXE, debug: true);
        $Data->del(["id_div" => "like __content%"]);
        $this->assertSame(
            true,
            $Data->put([
                "id_div" => "__content",
                "ref" => "mycontent",
                "j_content" => "mydata"
            ])
        );
    }

    /**
     * @depends testPutTable
     */
    public function testGetTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $enr = $Table->get(["id_div" => "__content"]);
        $this->assertIsArray($enr);
        $this->assertSame(1, count($enr));
        $this->assertSame("my content", $enr[0]->file_php);

        $this->assertSame(
            0,
            count($Table->get(["id_div" => "__content2"]))
        );
    }

    /**
     * @depends testGetTable
     */
    public function testGetJoinTable(): void
    {
        $Table = new Table(Entite: new Template(), prefixe: PREFIXE, debug: true);
        $enr = $Table->get(["id_div" => "__content"], join: $Table->join(new Data(), ["id_div"]));
        $this->assertIsArray($enr);
        $this->assertSame(1, count($enr));
        $this->assertSame("mycontent", $enr[0]->ref);
    }

    /**
     * 
     */
    public function testGetTableKo(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $enr = $Table->get(["id_divxxx" => "__content"]);
        $this->assertSame(false, $enr);
    }

    /**
     * @depends testGetTable
     */
    public function testGetOrderTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $enr = $Table->get(["id_div" => "__content"], order: $Table->orderBy(["template"]));
        $this->assertSame(
            1,
            count($enr)
        );

        $enr = $Table->get(["id_div" => "__content"], order: $Table->orderBy(["template"], asc: true));
        $this->assertSame(
            1,
            count($enr)
        );
    }

    /**
     * @depends @depends testGetTable
     */
    public function testGetAllTableLike(): void
    {
        $Table = new Table(PREFIXE, new Template());
        $enr = $Table->get(["id_div" => "like __conte%"]);

        $this->assertSame(
            1,
            count($enr)
        );
    }

    /**
     * @depends testPutTable
     */
    public function testupdTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $this->assertSame(
            true,
            $Table->put(["template" => "folenski"], ["id_div" => "__content"])
        );
    }

    /**
     * @depends testPutTable
     */
    public function testCountTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $this->assertSame(
            1,
            $Table->count(["id_div" => "__content"])
        );
    }

    /**
     * @depends testPutTable
     */
    public function testSaveTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $data = [
            "id_div" => "__content_test",
            "file_php" => "my content",
            "template" => "{{test}}"
        ];

        $this->assertSame(
            [$Table::RET_OK, "__content_test"],
            $Table->save($data)
        );
    }

    /**
     * @depends testSaveTable
     */
    public function testSaveDupTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $data = [
            "id_div" => "__content_test",
            "file_php" => "my content",
            "template" => "{{test}}"
        ];

        $this->assertSame(
            [$Table::RET_DUP, "__content_test"],
            $Table->save($data)
        );
    }

    /**
     * @depends testSaveDupTable
     */
    public function testSaveMajTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $data = [
            "id_div" => "__content_test",
            "file_php" => "my content",
            "template" => "{{test2}}"
        ];

        $this->assertSame(
            [$Table::RET_MAJ, "__content_test"],
            $Table->save($data)
        );
    }

    /**
     * @depends testSaveDupTable
     */
    public function testSaveErrTable(): void
    {
        $Table = new Table(PREFIXE, new Template());

        $data = [
            "id_div" => "__content_test",
            "template" => "{{test2}}"
        ];

        try {
            $Table->save($data);
        } catch (Exception $e) {
            $this->assertSame(
                "Check Error : file_php",
                $e->getMessage()
            );
        }
    }

    /**
     * @depends testSaveErrTable
     */
    public function testGetTableLimit(): void
    {
        $Log = new Table(PREFIXE, new Log());
        $nbr = $Log->count();
        for ($i = 0; $i < 10; $i++) {
            $Log->put([
                "component" => "test",
                "message" => "hello c'est moi",
                "http_code" => $i,
                "error_code" => $i
            ]);
        }

        $this->assertSame(
            $nbr + 10,
            $Log->count()
        );
        $rows = $Log->get(limit: 5);
        $this->assertNotNull($rows);

        $this->assertSame(
            5,
            count($rows)
        );
    }
}
