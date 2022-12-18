<?php

/**
 * Module  de test la classe CliFonct
 * 
 * @author  folenski
 * @since 1.0 21/07/2022 : Version initiale 
 */

declare(strict_types=1);

use Staff\Lib\CliFonct;
use PHPUnit\Framework\TestCase;

final class CliTest extends TestCase
{
    public function testCheckArgs(): void
    {
        $patternOpt = [
            "d" => ["name" => "delete", "value" => false]
        ];
        $args = ["prog.php", "-d", "-c", "fichier.xml"];
        $ret = CliFonct::checkArgs($args, $patternOpt);
        $this->assertSame(
            ["-c"],
            $ret["errors"]
        );
        $this->assertSame(
            ["delete" => ""],
            $ret["options"]
        );
        $this->assertSame(
            ["fichier.xml"],
            $ret["args"]
        );
    }
    public function testCheckOptEnv(): void
    {
        $patternOpt = [
            "d" => ["name" => "delete", "value" => false],
            "e" => ["name" => "env", "value" => true]
        ];
        $args = ["prog.php", "-d", "-e", "TST", "fichier.xml"];
        $ret = CliFonct::checkArgs($args, $patternOpt);
        $this->assertSame(
            [],
            $ret["errors"]
        );
        $this->assertSame(
            ["delete" => "", "env" => "TST"],
            $ret["options"]
        );
        $this->assertSame(
            ["fichier.xml"],
            $ret["args"]
        );
    }
}
