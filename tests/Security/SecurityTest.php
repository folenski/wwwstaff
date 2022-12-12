<?php

/**
 * Module de test la class Security
 * 
 * @author  folenski
 * @since 1.2 01/07/2022 : Mise à jour 
 */

declare(strict_types=1);
require_once  __DIR__  . "/dependances/config.php";

use Staff\Security\Security;
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    /**
     *  Test de la méthode vérification mot de passe
     */
    public function testPassCheck(): void
    {
        //  mot de passe correct
        $this->assertSame(
            true,
            Security::check_pass("Admin123")
        );
        //  mot de passe correct
        $this->assertSame(
            true,
            Security::check_pass("!Dmin123")
        );
        //  mot de passe trop court
        $this->assertSame(
            false,
            Security::check_pass("Admin12")
        );
        //  pas assez complexe
        $this->assertSame(
            false,
            Security::check_pass("admin12345")
        );
        //  caractere interdit
        $this->assertSame(
            false,
            Security::check_pass("admi'n12345")
        );
    }
}
