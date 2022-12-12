<?php

/**
 * Module de test la classe AuthenTest 
 * 
 * @author  folenski
 * @since 1.3 13/08/2022 : Refactoring 
 */

declare(strict_types=1);
require_once  dirname(__DIR__)  . "/dependances/config.php";

use Staff\Services\Authen;
use PHPUnit\Framework\TestCase;

final class AuthenTest extends TestCase
{
    const USER1  = "admin";
    const USER2 = "admin-bck";
    const USER3 = "service";
    const PASS  = "Admin1234";
    const MAIL  = "admin@test.com";

    /**
     * Suppression des utilisateurs pour rendre rejouable les tests
     */
    public function testInitUser(): void
    {
        //    $Usr = new User(PREFIXE);
        $this->assertIsBool(Authen::del(self::USER1));
        $this->assertIsBool(Authen::del(self::USER2));
        $this->assertIsBool(Authen::del(self::USER3));
    }

    /**
     * Ajout de l'utilisateur USER1
     * @depends testInitUser
     */
    public function testNewAddUser(): void
    {
        // Creation d'un compte avec une table vide
        $this->assertSame(
            Authen::USER_OK,
            Authen::add(self::USER1, self::MAIL, self::PASS, Authen::GRP_ADMIN)
        );
        // Creation d'un compte avec une table vide
        $temps = date('Y-m-d H:i:s');
        [$retour, $token, $mail, $last] = Authen::login(self::USER1, self::PASS, 30);
        $this->assertSame(
            Authen::USER_OK,
            $retour
        );
        $this->assertSame(88, strlen($token));
        $this->assertSame(self::MAIL, $mail);
        $this->assertSame($temps, $last);
    }


    /**
     * test des différentes types d'erreur pour l'ajour d'un utilisateur
     * @depends testNewAddUser
     */
    public function testAddUserKo(): void
    {
        $this->assertSame(
            Authen::USER_EXIST,
            Authen::add(self::USER1, "admin@test.com", self::PASS, Authen::GRP_ADMIN)
        );
        // Creation d'un compte avec un mail au mauvais format
        $this->assertSame(
            Authen::USER_MAIL_ERROR,
            Authen::add(self::USER2, "admin@testcom", self::PASS, Authen::GRP_ADMIN)
        );
        // Creation d'un compte avec un mot de passe trop simple
        $this->assertSame(
            Authen::USER_PIN_ERROR,
            Authen::add(self::USER2, "admin@test.com", "123456789", Authen::GRP_ADMIN)
        );
    }

    /**
     *  Test de la méthode afficher un message à partir d'un code retour
     */
    public function testRetLib(): void
    {
        $this->assertSame(
            "L'utilisateur existe déjà",
            Authen::get_lib(Authen::USER_EXIST)
        );
        $this->assertSame(
            "Ok",
            Authen::get_lib(Authen::USER_OK)

        );
        $this->assertSame(
            "L'utilisateur n'a pas été trouvé",
            Authen::get_lib(Authen::USER_NOT_FOUND)
        );
    }

    /**
     *  Test de la méthode afficher un message à partir d'un code retour
     */
    public function testRetPerm(): void
    {
        $this->assertSame(
            Authen::GRP_ADMIN,
            Authen::get_permission("admin")
        );
        $this->assertSame(
            Authen::GRP_USER,
            Authen::get_permission("user")
        );
    }

    /** 
     * Test de la méthode login
     */
    public function testLogin(): void
    {
        [$retour, $token] = Authen::login(USER_SVC, USER_SVC_PASS, 60);
        $this->assertSame(
            Authen::USER_OK,
            $retour
        );
        $this->assertSame(88, strlen($token));   // 88 est la taille du token

        $ret = Authen::is_valid($token);
        $this->assertSame(
            "svc",
            $ret["user"]
        );
    }

    /** 
     * blocage suite à 3 cause faux sur USER2
     * @depends testAddUserKo
     */
    public function testLoginPinKo(): void
    {
        $this->assertSame(
            Authen::USER_OK,
            Authen::add(self::USER2, "admin@test.com", self::PASS, Authen::GRP_ADMIN)
        );
        for ($cpt = 1; $cpt <= 3; $cpt++) {
            [$retour] = Authen::login(self::USER2, "mauvaispass", 60);
            $this->assertSame(
                Authen::USER_BAD_PIN,
                $retour
            );
        }
        // l utilisateur est bloqué il faut attendre 15 min, test à faire a la main
        [$retour] = Authen::login(self::USER2, "mauvaispass", 60);
        $this->assertSame(
            Authen::USER_LOCK,
            $retour
        );
    }

    /** 
     * Tentative de connection avec un compte de service
     * @depends testLogin
     */
    public function testDisconnect(): void
    {
        $token = "";
        [$retour, $token] = Authen::login(USER_SVC, USER_SVC_PASS, 60);
        // login ok
        $this->assertSame(
            Authen::USER_OK,
            $retour
        );
        $this->assertSame(
            true,
            Authen::revoke($token)
        );
    }

    /** 
     * Mise à jour du mot de passe
     *  @depends testDisconnect
     */
    public function testMajPass(): void
    {
        [$retour, $token] = Authen::login(self::USER1, self::PASS, 60);
        // login ok
        $this->assertSame(
            Authen::USER_OK,
            $retour
        );
        $this->assertSame(
            Authen::USER_OK,
            Authen::update(self::USER1, "Adminadmin2")
        );
        $this->assertSame(
            Authen::USER_OK,
            Authen::update(self::USER1, self::PASS)
        );
    }

    /** 
     * Mise à jour du mail
     * @depends testMajPass
     */
    public function testMajMail(): void
    {
        $this->assertSame(
            Authen::USER_OK,
            Authen::update(user: self::USER2, mail: "tmp@nobody.com")
        );
        $this->assertSame(
            Authen::USER_MAIL_ERROR,
            Authen::update(user: self::USER2,  mail: "tmp@nobody")
        );
    }

    /** 
     * Mise à jour du mail
     * @depends testMajMail
     */
    public function testSuppUser(): void
    {
        $this->assertSame(
            true,
            Authen::del(self::USER2)
        );
        $this->assertSame(
            false,
            Authen::del(self::USER2)
        );
    }
}
