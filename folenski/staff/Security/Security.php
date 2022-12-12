<?php

/**
 * Class Security, fonctions pour gerer la sécurité 
 *
 * @author  folenski
 * @since 1.0  26/08/2021 : Version Initiale 
 */

namespace Staff\Security;

class Security
{
    const SYMBOLE = "#@&(){}%+-*$./!";

    /**
     * @param string $user
     * @return string le token généré à partit de l'utilisateur
     */
    static function token(string $user): string
    {
        $alea = $user;
        for ($i = 0; $i < 10; $i++)
            $alea .= rand();
        $token = base64_encode(hash('sha256', $alea));
        return $token;
    }

    /**
     * Permet de controler la validité d'un mot de passe, 
     * il doit avoir au minimum 8 caractères et il doit possèder les 3 combinaisons suivantes parmis :
     * - lettre mininuscule
     * - lettre majuscule
     * - chiffre
     * - symbôles : #@&(){}%+-*$./
     * @param string $password à tester  
     * @return bool vrai si le mot de passe est conforme
     */
    static function check_pass(string $password): bool
    {
        // Mot de passe trop court
        if (($taille = strlen($password)) < 8)  return false;
        // Vérification de la complexité   
        $has = [0, 0, 0, 0];
        for ($i = 0; $i < $taille; $i++) {
            if ($password[$i] >= '0' &&  $password[$i] <= '9') {
                $has[0] = 1;
            } elseif ($password[$i] >= 'a' &&  $password[$i] <= 'z') {
                $has[1] = 1;
            } elseif ($password[$i] >= 'A' &&  $password[$i] <= 'Z') {
                $has[2] = 1;
            } elseif (strstr(self::SYMBOLE, $password[$i]) !== false) {
                $has[3] = 1;
            } else
                return false; // caractere interdit
        }
        // var_dump($has);
        $score = 0;
        foreach ($has as $valeur) {
            $score += $valeur;
        }
        if ($score <= 2) return false;  // faux sur le contrôle de complexité
        return true;
    }

    /**
     * Permet de chiffrer le mot de passe
     * @param string $password
     * @return bool vrai si trouvé
     */
    static function crypt_pass(string $password): string|null
    {
        /* $options['salt'] = 'usesomesillystringforsalt';
        $options['cost'] = 3;
        echo password_hash($password, PASSWORD_BCRYPT, $options) */
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * This small helper function generates RFC 4122 compliant Version 4 UUIDs.
     *  
     */
    static function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
