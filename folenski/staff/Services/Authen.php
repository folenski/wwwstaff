<?php

/**
 * Class **User**, Gestion de la table user 
 * 
 * @author  folenski
 * @since 1.3  26/07/2022 
 * @version 1.0.0 Version initiale
 * @version 1.1.0  revision du code
 * @version 1.2.0  Prise en compte évol modèle de données
 * @version 1.3.0  Prise en compte class Table
 * 
 */

namespace Staff\Services;

use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Models\User;
use Staff\Models\Token;
use Staff\Security\Security;

use function PHPUnit\Framework\returnSelf;

class Authen
{
    const USER_OK            = 30;
    const USER_ERROR         = 31;
    const USER_MAIL_ERROR    = 32;
    const USER_LOCK          = 33;
    const USER_BAD_PIN       = 34;
    const USER_EXIST         = 35;
    const USER_NOT_FOUND     = 36;
    const USER_NOT_AUTHORIZE = 37;
    const USER_PIN_ERROR     = 38;

    const PERM_ADD_USER  = 0b0010;

    const GRP_ADMIN      = 0b1111;
    const GRP_USER       = 0b0001;

    private const _ERROR = [
        self::USER_OK => "Ok",
        self::USER_ERROR => "Une erreur a été rencontrée",
        self::USER_MAIL_ERROR => "Le mail comporte une erreur de format",
        self::USER_LOCK => "L'utilisateur est verrouillé",
        self::USER_BAD_PIN => "Le mot de passe est erroné",
        self::USER_PIN_ERROR => "Le mot de passe n'est pas conforme",
        self::USER_EXIST => "L'utilisateur existe déjà",
        self::USER_NOT_FOUND => "L'utilisateur n'a pas été trouvé",
        self::USER_NOT_AUTHORIZE => "L'utilisateur n'a pas l'autorisation pour cette opération "
    ];

    private Table $_User;

    public function __construct(public int $delai = 90)
    {
        $this->_User = new Table(DBParam::$prefixe, DBParam::get_table("user"));
    }

    /**
     * Retourne l'utilisateur si le token est autorisé
     * @param string $token le token lu
     * @return array|false l'utilisateur ou faux si le token n'est valide
     */
    static function is_valid(string $token): array|false
    {
        // utilisation de la jointure ??
        $Tok = new Table(DBParam::$prefixe, DBParam::get_table("token"));
        $rows = $Tok->get([
            "token" => $token,
            "revoke" => 0,
            "expired_at" => "> " . date('Y-m-d H:i:s')
        ], join: [new User(), "user"]);
        if ($rows === false) return false;
        if (count($rows) == 0) return false;
        $user = $rows[0]->user;
        $perm = $rows[0]->permission;

        return ["token" => $token, "user" => $user, "permission" => $perm];
    }

    /**
     * Revoque le token
     * @return string|false l'utilisateur ou faux si le token n'est valide
     */
    static function revoke(string $token): bool
    {
        // utilisation de la jointure ??
        $Tok = new Table(DBParam::$prefixe, new Token());
        return  $Tok->put(
            ["revoke" => 1],
            ["token" => $token]
        );
    }

    /**
     * Permet de chiffrer le mot de passe
     * @param string $permission
     * @return bool vrai si trouvé
     */
    static function autorize(int $permission, int $autor): bool
    {
        if (($permission & $autor) == 0) return false;
        else return true;
    }

    /**
     * Permet d'ajouter un utilisateur
     * @param string $nom  
     * @param string $mail  
     * @param string $pass en clair
     * @param bool   $service compte de service c est a dire qu'il ne peut pas de connecter 
     * @return int   
     * RET_OK|USER_MAIL_ERROR|USER_NOT_FOUND|USER_NOT_AUTHORIZE|USER_PIN_ERROR|RET_ERROR
     */
    static function add(string $user, string $mail, string $password, int $permission): int
    {
        $User = new Table(DBParam::$prefixe, new User());
        $rows = $User->get(["user" => $user]);
        if ($rows === false) return self::USER_ERROR;
        if (count($rows) != 0) return self::USER_EXIST;
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) return self::USER_MAIL_ERROR;
        if (!Security::check_pass($password)) return self::USER_PIN_ERROR;
        if (($pass = Security::crypt_pass($password)) === false) return self::USER_BAD_PIN;

        if (!$User->put([
            "user" => $user,
            "mail" => $mail,
            "pass" => $pass,
            "permission" => $permission
        ]))
            return self::USER_ERROR;

        return self::USER_OK; // success
    }

    /**
     * Permet de retouver les permissions en fonction d'un groupe 
     * @param string $groupe = admin|user|service
     * @return int permission
     */
    static function get_permission(string $groupe): int
    {
        if ($groupe == "admin") return self::GRP_ADMIN;
        return self::GRP_USER;
    }

    /**
     * @param int $error l'erreur
     * @return string retourne le libellé associé à l'erreur
     */
    static function get_lib(int $error): string
    {
        if (array_key_exists($error, self::_ERROR))
            return self::_ERROR[$error];
        return "Une erreur inconnue a été rencontrée";
    }

    /**
     * Login de l'utilisateur et génére le token d'autorisation
     * @param string $user 
     * @param string $pass le mot de passe en clair
     * @return array [int code_retour, string $token, string $mail, string $lastcnx]
     * Les codes retours possibles : 
     * RET_OK|RET_ERROR|USER_MAIL_ERROR|USER_NOT_FOUND|USER_NOT_AUTHORIZE|USER_BAD_PIN|USER_LOCK
     */
    static function login(string $user, string $pass, int $delai = 60): array
    {
        $dataVoid = ["", "", ""];
        $User = new Table(DBParam::$prefixe, new User());

        $rows = $User->get(["user" => $user]);
        if ($rows === false) return [self::USER_ERROR, ...$dataVoid];
        if (count($rows) == 0) return [self::USER_NOT_FOUND, ...$dataVoid];
        $Myuser = $rows[0];

        // si + 3 codefaux et le dernier acces < 15 minutes
        if (($Myuser->bad_pin >= 3) && ($Myuser->updated_at < date('Y-m-d H:i:s', strtotime("15 minutes"))))
            return [self::USER_LOCK, ...$dataVoid];

        // verificaton du mot de passe & mise à jour de la table
        if (password_verify($pass, $Myuser->pass)) $Myuser->bad_pin = 0;
        else  $Myuser->bad_pin++;  // mot de passe inconnu 

        if (!$User->put(
            ["bad_pin" => $Myuser->bad_pin],
            ["user" => $Myuser->user]
        )) return [self::USER_ERROR, ...$dataVoid];

        if ($Myuser->bad_pin > 0) return [self::USER_BAD_PIN, ...$dataVoid];  // mot de passe inconnu

        $Tok = new Table(DBParam::$prefixe, new Token());
        $genTok = Security::token($Myuser->user);
        $timeDelai = date('Y-m-d H:i:s', strtotime("{$delai} day"));;
        if (!$Tok->put([
            "user" => $Myuser->user,
            "token" => $genTok,
            "expired_at" => $timeDelai
        ]))
            return [self::USER_ERROR, ...$dataVoid];

        return [self::USER_OK, $genTok, $Myuser->mail, $Myuser->updated_at]; // success
    }

    /**
     * Mise à jour du mot de passe et ou le mail
     * @param string $password facultatif le mot de passe en clair
     * @param string $mail facultatif
     * @return int : RET_OK|USER_NOT_AUTHORIZE|USER_PIN_ERROR|RET_ERROR  
     */
    static function update(string $user, ?string $password = null, ?string $mail = null): int
    {
        $User = new Table(DBParam::$prefixe, new User());
        if ($password === null && $mail === null) return self::USER_ERROR;

        if ($mail !== null) {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) return self::USER_MAIL_ERROR;
            $upd["mail"] = $mail;
        } else {
            if (!Security::check_pass($password)) return self::USER_BAD_PIN;
            if (($pass = Security::crypt_pass($password)) === false) return self::USER_BAD_PIN;
            $upd["pass"] = $pass;
        }
        if (!$User->put($upd, ["user" => $user]))
            return self::USER_ERROR;

        return self::USER_OK; // success

    }

    /**
     * Permet de supprimer un utilisateur ainsi que les tokens
     * @param string $user à supprimer
     * @return bool  vrai si ok
     */
    static function del(string $user): bool
    {
        $Tok = new Table(DBParam::$prefixe, new Token());
        if ( $Tok->del(["user" => $user]) === false) return false;
        $User = new Table(DBParam::$prefixe, new User());
        if ( ($nbr = $User->del(["user" => $user])) === false) return false;
        if ( $nbr != 1) return false;
        return true; // success
    }

    /** ----------------------------------------------------------------------------------------------
     *                                       P R I V E
     *  ----------------------------------------------------------------------------------------------
     */
}
