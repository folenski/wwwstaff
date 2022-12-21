<?php

/**
 * Module de test la classe SqlCore
 * 
 * @author folenski
 * @version 1.0 15/07/2022: Version Initiale
 * @version 1.1 11/12/2022: Prise en compte du refactoring
 */

declare(strict_types=1);

use Staff\Databases\SqlCore;
use PHPUnit\Framework\TestCase;

final class SqlCoreTest extends TestCase
{
    private $descInfo = [
        "idInfo"   => "INTEGER PRIMARY KEY",
        "titre"    => "TEXT",
        "meta"     => "TEXT",
        "_index1"  => "titre, meta",
        "_key"     => "FOREIGN KEY (titre) REFERENCES %spage(ttt)"
    ];

    /**
     * testSelectFctTable, test du select avec une clause libre
     */
    public function testSelectTable(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        // sans clause from
        $this->assertSame(
            'SELECT count(*) as nbr FROM info',
            $Table->select("count(*) as nbr")->toStr()
        );

        // avec une clause libre
        $this->assertSame(
            'SELECT count(*) as nbr FROM info',
            $Table->select("count(*) as nbr")->from()->toStr()
        );

        // avec les champs dans tableau
        $this->assertSame(
            'SELECT id, champ2 FROM info',
            $Table->select(["id" => 2, "champ2" => "ici"])->from()->toStr()
        );
    }

    /**
     * testSelectFctTable, test du select avec une clause libre
     */
    public function testSelectWhereTable(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            'SELECT count(*) as nbr FROM info WHERE champs > "ll"',
            $Table->select("count(*) as nbr")->where('champs > "ll"')->toStr()
        );

        $this->assertSame(
            'SELECT count(*) as nbr FROM info WHERE info.champs="ll"',
            $Table->select("count(*) as nbr")->where(["champs" => "ll"])->toStr()
        );

        $this->assertSame(
            'SELECT count(*) as nbr FROM info WHERE info.champs>"ll"',
            $Table->select("count(*) as nbr")->where(["champs" => "ll"], ">")->toStr()
        );

        // avec les champs dans tableau
        $this->assertSame(
            'SELECT id, champ2 FROM info WHERE info.idInfo=:idInfo AND info.meta=:meta',
            $Table->select(["id" => 2, "champ2" => "ici"], true)
                ->where(["idInfo" => 1, "meta" => "toto"])
                ->from()->toStr()
        );
    }
    /**
     * 
     */
    public function testSelectWhereAnd(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            'SELECT count(*) as nbr FROM info WHERE champs > "ll" AND info.id="1" AND info.cp2="ooo"',
            $Table->select("count(*) as nbr")->where('champs > "ll"')->and(["id" => 1, "cp2" => "ooo"])->toStr()
        );
    }
    /**
     * 
     */
    public function testSelectWhereOr(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            'SELECT count(*) as nbr FROM info WHERE champs > "ll" OR info.id<="1" OR info.cp2<="ooo"',
            $Table->select("count(*) as nbr")->where('champs > "ll"')->or(["id" => 1, "cp2" => "ooo"], "<=")->toStr()
        );
    }
    /**
     * 
     */
    public function testInsertTable(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            'INSERT INTO info (idInfo, chp2) VALUES (1, "ici")',
            $Table->insert(["idInfo" => 1, "chp2" => "ici"])->toStr()
        );

        $this->assertSame(
            'INSERT INTO info (idInfo, chp2) VALUES (:idInfo, :chp2)',
            $Table->insert(["idInfo" => 1, "chp2" => "ici"], true)->toStr()
        );
    }

    /**
     * Ordre Update
     */
    public function testUpdateTable(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            'UPDATE info SET idInfo=1, chp2="ici"',
            $Table->update(["idInfo" => 1, "chp2" => "ici"])->toStr()
        );

        $this->assertSame(
            'UPDATE info SET idInfo=:idInfo, chp2=:chp2',
            $Table->update(["idInfo" => "?", "chp2" => "?"], true)->toStr()
        );

        $this->assertSame(
            'UPDATE info SET idInfo=1, chp2="la" WHERE info.chp2="coucou"',
            $Table->update(["idInfo" => 1, "chp2" => "la"])->where(["chp2" => "coucou"])->toStr()
        );

        $this->assertSame(
            'UPDATE info SET idInfo=:idInfo, chp2=:chp2 WHERE info.chp2=:chp2',
            $Table->update(["idInfo" => "?", "chp2" => "?"], true)->where(["chp2" => "ici"])->toStr()
        );
    }

    /**
     * testSelectFctTable, test du select avec une clause libre
     */
    public function testOrderByTable(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            'SELECT * FROM info WHERE hello > 10 ORDER BY id DESC',
            $Table->select("*")->where("hello > 10")->order_by("id", false)->toStr()
        );

        $this->assertSame(
            'SELECT * FROM info ORDER BY id DESC',
            $Table->select("*")->order_by("id", false)->toStr()
        );
    }

    /**
     * testSelectFctTable, test du select avec une clause libre
     */
    public function testInnerJoin(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $Table->select("*")
            ->inner_join("info2", ["idInfo", "meta"])
            ->where(["idInfo" => 10]);

        $this->assertSame(
            'SELECT * FROM info INNER JOIN info2 ON info2.idInfo=info.idInfo AND info2.meta=info.meta WHERE info.idInfo=10',
            $Table->toStr()
        );
    }

    /**
     * 
     */
    public function testLimitTable(): void
    {
        $Table = new SqlCore("info", $this->descInfo);

        $this->assertSame(
            "SELECT * FROM info LIMIT 5",
            $Table->select("*")->limit(5)->toStr()
        );
    }
}
