<?php

/**
 * Module de test la classe SqlAdmin
 * 
 * @author folenski
 * @version 1.0 15/07/2022: Version Initiale
 * @version 1.1 11/12/2022: Prise en compte du refactoring, dépend de la table log
 */

declare(strict_types=1);

use Staff\Databases\SqlAdmin;
use PHPUnit\Framework\TestCase;
use Staff\Models\Log;

final class SqlAdminTest extends TestCase
{
    private $descInfo = [
        "idInfo"   => "INTEGER PRIMARY KEY",
        "titre"    => "TEXT",
        "meta"     => "TEXT",
        "_index1"  => "titre, meta",
        "_key"     => "FOREIGN KEY (titre) REFERENCES %spage(ttt)"
    ];

    public function testCreateTable(): void
    {
        $ttest = new SqlAdmin("test_", new Log());
        $this->assertEquals(
            'test_log',
            $ttest->name
        );

        $this->assertEquals(
            'CREATE TABLE test_log (id INTEGER PRIMARY KEY AUTOINCREMENT, http_code INTEGER DEFAULT 0, error_code INTEGER DEFAULT 0, component VARCHAR(256) NOT NULL, message VARCHAR(10000) NOT NULL, created_at DATETIME NOT NULL)',
            $ttest->create()->table()->toStr()
        );

        $index = $ttest->listIndex;

        $this->assertSame(["_index"], $index);
        $this->assertEquals(
            'CREATE INDEX test_log_index ON test_log (created_at)',
            $ttest->create()->index($index[0])->toStr()
        );

        $this->assertEquals(
            'CREATE INDEX IF NOT EXISTS test_log_index ON test_log (created_at)',
            $ttest->create()->exists()->index($index[0])->toStr()
        );
    }

    public function testDropTable(): void
    {
        $table = new SqlAdmin("test_", new Log());
        $this->assertSame(
            'DROP TABLE test_log',
            $table->drop()->table()->toStr()
        );
        $this->assertSame(
            'DROP TABLE IF EXISTS test_log',
            $table->drop()->exists()->table()->toStr()
        );
    }
}
