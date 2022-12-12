<?php

/**
 * Class **CliFonct** utile pour le mode client PHP.
 *
 * @author  folenski
 * @since 1.0  12/08/2021 : Version Initiale 
 *
 */

namespace Staff\Services;

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

    public static function print(string $message, string $color = "", bool $newline = true): void
    {
        $new = ($newline) ? "\n" : "";
        echo  "{$new}{$color}{$message}" . self::TERM_RESET;
    }

    public static function exit(string $message, int $code = 1): void
    {
        self::print($message, self::TERM_ROUGE);
        self::print("Exit...\n", self::TERM_BLEU);
        exit($code);
    }

    public static function checkArgs(array $args, array $listOpt, bool $hasArgs = true): array
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
