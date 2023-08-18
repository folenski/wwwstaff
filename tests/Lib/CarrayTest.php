<?php

/**
 * Module  de test la class Carray
 * 
 * @author  folenski
 * @since 1.0 21/07/2022 : Version initiale 
 */

declare(strict_types=1);

use Staff\Lib\Carray;
use PHPUnit\Framework\TestCase;

final class CarrayTest extends TestCase
{
    public function testProtected(): void
    {
        $this->assertSame(
            "il est une alert",
            Carray::protected("il est une <script>alert</script>")
        );

        // a revoir
        $this->assertSame(
            "il est une ;insrt",
            Carray::protected("il est une ;insrt")
        );
        $this->assertSame(
            "1234567890",
            Carray::protected("<script>12345678901234", 10)
        );
    }

    /**
     * Test la méthode statique CheckArray 
     */
    public function testCheckArray(): void
    {
        $pattern = [
            "nom" => ["mandatory" => true, "protected" => true, "limit" => 50],
            "pass" => ["mandatory" => false, "protected" => false]
        ];
        $data["nom"] = "hello";
        $this->assertSame(
            [true, "", "hello", null],
            Carray::arrayCheck($data, $pattern)
        );
        $this->assertSame(
            [true, ""],
            Carray::arrayCheck($data, [])
        );
        $pattern = [
            "nom" => ["mandatory" => true, "protected" => true, "limit" => 50],
            "pass" => ["mandatory" => true, "protected" => false]
        ];
        $this->assertSame(
            [false, "pass", "hello", null],
            Carray::arrayCheck($data, $pattern)
        );
        $pattern = [
            "nom" => ["mandatory" => true, "protected" => true, "limit" => 50],
            "pass" => ["mandatory" => true, "protected" => false],
            "autredonnee" => ["mandatory" => true, "protected" => false],
            "autredonnee_2" => ["mandatory" => true, "protected" => false]
        ];
        $this->assertSame(
            [false, "pass,autredonnee,autredonnee_2", "hello", null, null, null],
            Carray::arrayCheck($data, $pattern)
        );
        //  boolean
        $data["autredonnee"] = false;
        $this->assertSame(
            [false, "pass,autredonnee_2", "hello", null, false, null],
            Carray::arrayCheck($data, $pattern)
        );
        // integer
        $data["autredonnee_2"] = 100;
        $this->assertSame(
            [false, "pass", "hello", null, false, 100],
            Carray::arrayCheck($data, $pattern)
        );
        $pattern = [
            "nom" => ["mandatory" => true, "protected" => true, "limit" => 50],
            "pass" => ["mandatory" => true, "protected" => false, "default" => 1234],
            "autredonnee" => ["mandatory" => true, "protected" => false],
            "autredonnee_2" => ["mandatory" => true, "protected" => false]
        ];
        $this->assertSame(
            [true, "", "hello", 1234, false, 100],
            Carray::arrayCheck($data, $pattern)
        );

        // test si il y a 1 parametre
        $pattern = [
            "nom2" => ["mandatory" => false],
            "nom3" => ["mandatory" => false]
        ];
        $this->assertSame(
            [true, "", null, null],
            Carray::arrayCheck($data, $pattern)
        );
    }

    /**
     * Test la méthode statique CheckArray 
     */
    public function testCheckArraytype(): void
    {
        $pattern = [
            "nom" => ["mandatory" => true, "protected" => true, "type" => "string"],
            "pass" => ["mandatory" => false, "protected" => false, "type" => "string"]
        ];
        $data["nom"] = "hello";
        $this->assertSame(
            [true, "", "hello", null],
            Carray::arrayCheck($data, $pattern)
        );
        $data["nom"] = 1;
        $this->assertSame(
            [false, "nom[!string]", null, null],
            Carray::arrayCheck($data, $pattern)
        );
        $pattern = [
            "nom" => ["mandatory" => true, "protected" => true, "type" => "integer"],
            "pass" => ["mandatory" => false, "protected" => false, "type" => "string"]
        ];
        $data["pass"] = "helooo";
        $this->assertSame(
            [true, "", 1, "helooo"],
            Carray::arrayCheck($data, $pattern)
        );
    }

    public function testCheckArrayJson(): void
    {
        $pattern = [
            "jdata" => ["mandatory" => true, "json" => true]
        ];
        $data["jdata"] = <<<EOL
        {
            "chp1": true,
            "data": "heelo "
        }
        EOL;
        $this->assertSame(
            [true, "", '{"chp1":true,"data":"heelo "}'],
            Carray::arrayCheck($data, $pattern)
        );
        $data["jdata"] = <<<EOL
        {
            "chp1": true,
            "data": "heelo xxx\" "
        }
        EOL;
        $this->assertSame(
            [true, "", '{"chp1":true,"data":"heelo xxx\" "}'],
            Carray::arrayCheck($data, $pattern)
        );
        $data["jdata"] = "pp1";
        $this->assertSame(
            [false, "jdata[json]", null],
            Carray::arrayCheck($data, $pattern)
        );
    }

    /**
     * Test la méthode statique CompareData 
     */
    public function testCompare(): void
    {
        $target = new stdClass();
        $name =  $target->name = "test";
        $joption  = $target->joption = "option";
        $jroute = $target->jroute = "route";
        $more = "extra data";

        $this->assertSame(
            true,
            Carray::arrayCompare(compact("joption", "name", "jroute"), (array)$target)
        );
        $this->assertSame(
            true,
            Carray::arrayCompare(compact("joption", "jroute"), (array)$target)
        );
        $this->assertSame(
            false,
            Carray::arrayCompare(compact("joption", "jroute", "more"), (array)$target)
        );
        $target->jroute = "route2";
        $this->assertSame(
            false,
            Carray::arrayCompare(compact("joption", "jroute"), (array)$target)
        );
    }

    /**
     * Analyse d'une anomalie rencontrée lors de mes tests 
     * Api ne fusionne pas les clés et alimente 2 tableaux
     */
    public function testCheckCustom1(): void
    {
        $param = unserialize("a:2:{i:0;a:1:{s:4:\"name\";s:7:\"testadm\";}s:13:\"authorization\";a:3:{s:5:\"token\";s:88:\"OGRjNTgxZjgyNTZmYTM3NzYyMDM1YTIwMzJkZDMwMTI1M2IzMDQzZTIyMDM4OTNiM2U1MmI3MWM4NTRhN2M0ZQ==\";s:4:\"user\";s:8:\"svcadmin\";s:4:\"role\";i:2;}}");
        [$controle, $fails, $name] = Carray::arrayCheck(
            $param[0],
            ["name" => ["mandatory" => true, "type" => "string", "protected" => true, "limit" => 50]]
        );
        $this->assertTrue($controle);
    }
}
