<?php

/**
 * Module de test du trait RestTrait
 * 
 * @author  folenski
 * @since 1.0 16/12/2022: Version initiale
 *  
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use PHPUnit\Framework\TestCase;
use Staff\Api\RestTrait;

final class ApiRestTest extends TestCase
{
    use RestTrait;

    /** 
     * 
     */
    public function testRetNotImp(): void
    {
        $this->assertContains(
            ["content" => "not implemented"],
            $this->retNotImpl()
        );
        $this->assertArrayHasKey(
            "http",
            $this->retNotImpl()
        );
        $this->assertArrayHasKey(
            "errorcode",
            $this->retNotImpl()
        );
        $this->assertArrayHasKey(
            "response",
            $this->retNotImpl()
        );
    }

    public function testRetUnAvailable(): void
    {
        $this->assertContains(
            ["content" => "internal error"],
            $this->retUnAvail()
        );
    }

    public function testRetTokenNeeded(): void
    {
        $this->assertContains(
            ["content" => "token needed"],
            $this->retTokenNeeded()
        );
    }

    public function testRetCrlFail(): void
    {
        $this->assertContains(
            ["content" => "test"],
            $this->retCrlFail("test")
        );
        $this->assertSame(
            22,
            $this->retCrlFail("test")["errorcode"]
        );
        $this->assertSame(
            21,
            $this->retCrlFail("test", true)["errorcode"]
        );
    }

    public function testRetApi(): void
    {
        $this->assertContains(
            ["content" => "done"],
            $this->retApi()
        );

        $this->assertContains(
            ["msg" => "hello"],
            $this->retApi(content: null, data: ["msg" => "hello"])
        );
    }
}
