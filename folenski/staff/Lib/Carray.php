<?php

/**
 * Class Carray Liste de fonctions contrôlée des tableaux en entrée 
 * 
 * @author folenski
 * @version 1.0.0 09/07/2022, Version initiale, reorganiation de la class Config
 * @version 1.0.1 11/12/2022, ajout option JSON_UNESCAPED_UNICODE pour le russe
 */

namespace Staff\Lib;

class Carray 
{
    /**
     * Protège une entrée afin de l'ajouter en base de données
     * @param mixed $strUnSecure la chaine à vérifier
     * @param int $limit > 0 tronque la chaine 
     * @return string retourne la chaine filtrée
     */
    static function protected(mixed $strUnSecure, int $limit = 0): string
    {
        if ($limit > 0)
            return substr(htmlspecialchars(strip_tags($strUnSecure)), 0, $limit);
        else
            return htmlspecialchars(strip_tags($strUnSecure));
    }

    /**
     * Permet de verifier que le tableau $data contient bien les données attendues
     * @param array $data
     * @param array $properties la description des données
     * @return array [retour, erreur, ...liste des champs attendus]
     */
    static function arrayCheck(array $data, array $properties): array
    {
        $fails = "";
        $out = [];

        foreach ($properties as $property => $meta) {
            foreach ($meta as $key => $value) {
                if (
                    $key != "default" && $key != "protected" &&
                    $key != "json"    && $key != "type" &&
                    $key != "limit"   && $key != "mandatory"
                ) {
                    throw new \Exception("Mot clé [{$key}] non authorisé dans la variable 'pattern'");
                }
            }
            $default = array_key_exists("default", $properties[$property]);
            if (array_key_exists($property, $data)) {
                $protected = $properties[$property]["protected"] ?? false;
                $limit     = $properties[$property]["limit"] ?? 0;
                $json = $properties[$property]["json"] ?? false;
                $type = $properties[$property]["type"] ?? "";
                $typeIn = gettype($data[$property]);

                if ($type != "" && $typeIn != $type) {
                    $fails .= "{$property}[!{$type}],";
                    array_push($out, null);
                    continue;
                }
                if ($protected && $typeIn == "string") {
                    $value = self::protected($data[$property], $limit);
                    array_push($out, $value);
                    continue;
                }
                if ($json && $typeIn == "string") { // necessite une conversion au préalable
                    $value = json_decode($data[$property]);
                    if ($value === null) {
                        $fails .= "{$property}[json],";
                        array_push($out, $value);
                    } else {
                        array_push($out, json_encode($value, JSON_UNESCAPED_UNICODE));
                    }
                    continue;
                }
                if ($json) { // sans conversion
                    array_push($out, json_encode($data[$property], JSON_UNESCAPED_UNICODE));
                    continue;
                }
                array_push($out, $data[$property]);
            } elseif ($default) {
                array_push($out, $properties[$property]["default"]);
            } else {
                $mandatory = $properties[$property]["mandatory"] ?? true;
                array_push($out, null);
                if ($mandatory)
                    $fails .= "{$property},";
            }
        }
        $check = $fails == "";

        return [$check, substr($fails, 0, -1), ...$out];
    }

    /**
     * Permet de comparer 2 tableaux
     * @param array $source
     * @param array $target
     * @return array vrai si les 2 tableaux sont identiques
     */
    static function arrayCompare(array $source, array $target): bool
    {
        foreach ($source as $key => $val) {
            if (key_exists($key, $target) && $val == $target[$key])
                continue;
            return false;
        }
        return true;
    }
}
