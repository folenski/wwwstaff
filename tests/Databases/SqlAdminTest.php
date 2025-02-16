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
use Staff\Drivers\Mysql;
use Staff\Drivers\Sqlite;
use Staff\Models\Environment;
use Staff\Models\Log;
use Staff\Models\Message;

final class SqlAdminTest extends TestCase
{
    public function testCreateTableLogSqlite(): void
    {
        $ttest = new SqlAdmin("test_", new Sqlite(), new Log());
        $this->assertEquals(
            'test_log',
            $ttest->name
        );

        $this->assertEquals(
            'CREATE TABLE test_log (id  INTEGER PRIMARY KEY AUTOINCREMENT, http_code  INTEGER DEFAULT 0, error_code  INTEGER DEFAULT 0, component VARCHAR(256) NOT NULL, message VARCHAR(10000) NOT NULL, created_at  DATETIME NOT NULL)',
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

    public function testCreateTableEnvMysql(): void
    {
        $ttest = new SqlAdmin("car_", new Mysql(), new Environment());
        $this->assertEquals(
            'car_environment',
            $ttest->name
        );

        $this->assertEquals(
            'CREATE TABLE car_environment (name  VARCHAR(3) PRIMARY KEY, j_option VARCHAR(4000) COLLATE utf8_unicode_ci NOT NULL, created_at  DATETIME NOT NULL, updated_at  DATETIME NOT NULL)',
            $ttest->create()->table()->toStr()
        );
    }

    public function testCreateTableMessageSqlite(): void
    {
        $ttest = new SqlAdmin("test_", new Sqlite(), new Message());
        $this->assertEquals(
            'test_message',
            $ttest->name
        );

        $this->assertEquals(
            'CREATE TABLE test_message (id  INTEGER PRIMARY KEY AUTOINCREMENT, user VARCHAR(256) NOT NULL, host VARCHAR(256), hash VARCHAR(256) NOT NULL, spam  INTEGER DEFAULT 0, j_msg VARCHAR(15000) NOT NULL, done  INTEGER DEFAULT 0, created_at  DATETIME NOT NULL,  FOREIGN KEY (user) REFERENCES test_user(user))',
            $ttest->create()->table()->toStr()
        );
    }

    public function testDropTable(): void
    {
        $table = new SqlAdmin("test_", new Sqlite(), new Log());
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
