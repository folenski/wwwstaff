<?php

/**
 * Class CliFonct utile pour le mode client PHP.
 *
 * @author  folenski
 * @version 1.0  12/08/2021: version initiale 
 * @version 1.1  12/08/2021: ajout methode cbPrint 
 *
 */

namespace Staff\Lib;

class CliFonct
{
    const TERM_NOIR     = "\033[30m";
    const TERM_ROUGE    = "\033[31m";
    const TERM_VERT     = "\033[32m";
    const TERM_BLEU     = "\033[34m";
    const TERM_BLANC    = "\033[37m";
    const TERM_BG_NOIR  = "\033[40m";
    const TERM_BG_ROUGE = "\033[41m";
    const TERM_BG_VERT  = "\033[42m";
    const TERM_BG_BLEU  = "\033[44m";
    const TERM_BG_BLANC = "\033[47m";
    const TERM_RESET    = "\033[0m";

    static function print(string $message, string $color = "", bool $newline = true): void
    {
        $new = ($newline) ? "\n" : "";
        echo  "{$new}{$color}{$message}" . self::TERM_RESET;
    }

    /**
     * @param string $message le message à afficher
     * @param int $code le code retour
     */
    static function exit(string $message, int $code = 1): void
    {
        self::print($message, self::TERM_ROUGE);
        self::print("Exit...\n", self::TERM_BLEU);
        exit($code);
    }

    /**
     * Callback pour les methodes de la call Admin
     * @param string $text le message à afficher
     * @param int $level le type de message
     */
    static function cbPrint(?string $text = null, int $level = Admin::DISP_DEF)
    {
        switch ($level) {
            case Admin::DISP_DANGER:
                CliFonct::print($text, CliFonct::TERM_ROUGE);
                break;
            case Admin::DISP_UPDATE:
                CliFonct::print($text, CliFonct::TERM_BG_VERT . CliFonct::TERM_BLANC);
                break;
            case Admin::DISP_DONE:
                CliFonct::print($text, CliFonct::TERM_VERT);
                break;
            case Admin::DISP_ALERT:
                CliFonct::print($text, CliFonct::TERM_BG_ROUGE . CliFonct::TERM_BLANC);
                break;
            default:
                if ($text === null) $text = "................................";
                CliFonct::print($text, CliFonct::TERM_BLEU);
        }
    }

    static function checkArgs(array $args, array $listOpt, bool $hasArgs = true): array
    {
        $tabOpt  = [];
        $tabErr  = [];
        $tabArgs = [];
        $hasOptValue = false;
        $a = "";

        foreach ($args as $key => $arg) {
            if ($key == 0) continue;
            if (substr($arg, 0, 1) == "-") {
                $a = substr($arg, 1);
                if (array_key_exists($a, $listOpt)) {
                    $hasOptValue = $listOpt[$a]["value"] ?? false;
                    $tabOpt[$listOpt[$a]["name"]] = "";
                } else {
                    $hasOptValue = false;
                    array_push($tabErr, $arg);
                }
            } elseif ($hasOptValue) {
                $hasOptValue = false;
                $tabOpt[$listOpt[$a]["name"]] = $arg;
            } elseif ($hasArgs) {
                array_push($tabArgs, $arg);
            } else {
                array_push($tabErr, $arg);
            }
        }

        return ["errors" => [...$tabErr], "options" => [...$tabOpt], "args" => [...$tabArgs]];
    }
}
