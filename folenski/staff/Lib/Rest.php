<?php

/**
 * Class Api, fournit une liste de méthodes pour les accès API 
 * 
 * @author  folenski
 * @since 1.3.2 13/07/2023 
 * @version 1.0.0 Version initiale
 * @version 1.1.0  revision du code
 * @version 1.2.0  Prise en compte évol modèle de données
 * @version 1.3.0  Prise en compte class Table
 * @version 1.3.1  on filtre le champs http
 * @version 1.3.2  modif méthode log, response : gestion du champs errorcode 
 * @version 1.3.3  modif méthode clean pris en compte du refactoring de l'objet option 
 * @version 1.3.4  suppression de la valeur du token dans la table log 
 * 
 */

namespace Staff\Lib;

use Staff\Databases\Table;
use Staff\Databases\TableInterface;
use Staff\Models\BlackList;
use Staff\Models\DBParam;
use Staff\Models\Environment;
use Staff\Models\Message;
use Staff\Models\Token;
use Staff\Models\Log;
use Staff\Security\Authen;

class Rest
{
    /*Les principaux codes retour HTTP */
    const HTTP_OK      = 200;
    const HTTP_BAD     = 400;
    const HTTP_AUTH_KO = 401;
    const HTTP_DENIED  = 403;
    const HTTP_ERROR   = 500;
    const HTTP_UNAVAIL = 503;

    private const _LIB = [
        self::HTTP_OK => "Ok",
        self::HTTP_BAD => "Bad Request",
        self::HTTP_AUTH_KO => "Unauthorized",
        self::HTTP_DENIED => "Forbidden",
        self::HTTP_ERROR => "Internal Server Error",
        self::HTTP_UNAVAIL => "Service Unavailable"
    ];

    /**
     * Permet de savoir si le header contient un token valide et le retourne
     * @param string $header $_SERVER["HTTP_AUTHORIZATION"]
     * @return array|false [string $token, string $user, int permission]
     */
    static function bearer(string $header): array|false
    {
        $tab = explode(" ", $header, 2);
        if (count($tab) != 2) return false;
        [$type, $data] = $tab;
        if (strcasecmp($type, "Bearer") != 0) return false;

        $token = ltrim($data);
        if (($ret = Authen::is_valid($token)) === false) return false;
        return $ret;
    }

    /** 
     * Affiche la réponse et la consigne dans la table Log
     * @param array $data les données à convertir en json et à renvoyer
     * @param string $composant  
     * @param bool $log vrai si on doit logger le message
     * @return bool vrai si la réponse a été affichée
     */
    static function reponse(array $data, string $composant = "COMMUN", bool $log = false): bool
    {
        $http = (array_key_exists("http", $data)) ? $data["http"] : self::HTTP_OK;
        $error = (array_key_exists("errorcode", $data)) ? $data["errorcode"] : 0;
        $responseLog = $response = (array_key_exists("response", $data)) ? $data["response"] : [];
        if (array_key_exists("token", $responseLog)) {
            $responseLog["token"] = "***";
        }
        self::log(
            component: $composant,
            http_code: $http,
            error_code: $error,
            message: json_encode($responseLog),
            log: $log
        );
        if ($error != 0) $response["errorcode"] = $error;
        http_response_code($http);
        if (($retour = json_encode($response)) === false) {
            echo json_encode('{"errorcode" : 99}');
            return false;
        }
        echo $retour;
        return true;
    }

    /**
     * Ecrit le header de la réponse
     * @param string $method la méthode de l'api POST|GET|DELETE|PUT
     */
    static function header(string $method = "POST"): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: {$method}");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    /**
     * Ecrit dans la table des logs, le message si celui un niveau supérieur au niveau de trace
     * @param string $component le nom du composant
     * @param string $message le message à écrire
     * @param int $code_http
     * @param int $error_http si error_code = 0 alors le niveau est 0
     * @param int $trace le niveau de log 
     * @return bool faux si erreur
     */
    static function log(string $component, string $message, int $http_code = 200, int $error_code = 0, bool $log = false): bool
    {
        if ($error_code == 0 && $log == false) return true;
        $Log = new Table(Entite: DBParam::get_table("log"), prefixe: DBParam::$prefixe);
        return $Log->put(compact("component", "message", "http_code", "error_code"));
    }

    /**
     * Permet de faire le menage sur l'environnement
     * @param object $Env table Environment
     */
    static function clean(object $Env): void
    {
        $date = date("Y-m-d");
        $last  = $Env->Option->clean_at ?? $date;
        $delai = $Env->Option->clean_limit;
        $previous = date("Y-m-d", strtotime("{$last} + {$delai} days"));
        if ($date <= $previous) return;
        $new = date("Y-m-d", strtotime("{$date} + {$delai} days"));

        $purgeTables = [new Message(), new Log(), new Token()];

        foreach ($purgeTables  as $Table) {
            self::_clean_table($Table, $Env->Option->purge);
        }

        $Environment = new Table(DBParam::$prefixe, new Environment());
        $Env->Option->clean_at = $new;
        $j_option = json_encode($Env->Option);
        $Environment->put(["j_option" => $j_option], ["name" => $Env->name]);
    }

    /**
     * Cette fonction permet d'envoyer un email 
     *  
     */
    static function envoi_mail(string $dest, string $mailMsg, string $sujet = "Demande de contact"): bool
    {
        $domaine = str_replace("www.", "", $_SERVER["HTTP_HOST"]);

        $form = "noreply@" . $domaine;
        $sujet = "{$domaine} : {$sujet}";

        if ($mailMsg === "") return false;

        $headers  = "MIME-Version: 1.0\n";
        $headers  .= "From: {$form} \nContent-Type: text/html; charset='iso-8859-1'\n";

        return (bool)mail($dest, $sujet, $mailMsg, $headers);
    }

    /**
     * Permet de déterminer si le message est un spam
     * @param string $mail mail du message
     * @param string $msg message 
     * @return bool vrai si un spam
     */
    static function spam(string $mail, string $msg): bool
    {
        $BlackList = new Table(Entite: new BlackList(), prefixe: DBParam::$prefixe);

        $rows = $BlackList->get(["active" => 1], limit: 0);
        if ($rows === false) return false;

        foreach ($rows as $val) {
            $rules = json_decode($val->rules);
            $mailsBL = $rules->mails ?? [];
            $patternBL = $rules->patterns ?? [];
            if (in_array($mail, $mailsBL)) return true;

            foreach ($patternBL as $pattern) {
                if (str_starts_with($pattern, "/")) {
                    if (preg_match($pattern, $msg) !== 0) return true;
                }
            }
        }

        return false;
    }

    /**
     * Permet de voir si il y a trop de messages par adresse ip
     * @param string $hash de l'adresse ip
     * @return bool vrai si il y a trop de messages sur une adresse ip
     */
    static function stopMsg(string $hash): bool
    {
        $Msg = new Table(Entite: new Message(), prefixe: DBParam::$prefixe);
        $delai = date("Y-m-d", strtotime("- 2 days"));
        $nbr = $Msg->count(["hash" => $hash, "created_at" => "> {$delai}"]);
        if ($nbr === null || $nbr > 3) return true;
        return false;
    }

    /** ----------------------------------------------------------------------------------------------
     *                                       P R I V E
     *  ----------------------------------------------------------------------------------------------
     */

    /**
     * Nettoyage d'une table avec une log de l'action
     * @param TableInterface $TabInter  l'interface de la table
     * @param int $delai la rétention
     */

    private static function _clean_table(TableInterface $TabInter, int $delai): void
    {
        [$nom, $desc] = $TabInter->init();
        $Message = new Table(DBParam::$prefixe, $TabInter);
        $datedel = date("Y-m-d", strtotime("- {$delai} days"));
        $nbr = $Message->del(["created_at" => "< {$datedel}"]);
        if ($nbr === false)
            $msg = "Nettoyage table [{$nom}] date < {$datedel} en erreur";
        else
            $msg = "Nettoyage table [{$nom}] date < {$datedel}, nombre de suppression = {$nbr}";
        self::log(
            component: "CLEAN",
            message: $msg,
            http_code: 0
        );
    }

    /**
     * Retourne un libelle en fonction du code erreur
     * @param int $code code supporté par l'API
     * @return string le libellé du code http
     */
    private static function _get_lib(int $code): string
    {
        if (array_key_exists($code, self::_LIB))
            return self::_LIB[$code];
        return "Internal Server Error";
    }
}
