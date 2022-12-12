<?php

/**
 * Class Admin : fonction pour adminstrer les données et la création de la base
 *
 * @author folenski
 * @since 1.0 05/08/2022 : Version initiale
 * 
 */

namespace Staff\Services;

use Staff\Databases\Table;
use Staff\Models\DBParam;

class Admin
{
    const RET_OK = 0;
    const RET_FICH = 1;
    const RET_SQL = 2;
    const RET_MAJ = 3;
    const RET_DUP = 4;


    function __construct(private string $_prefixe = "")
    {
        if ($this->_prefixe == "")
            $this->_prefixe = DBParam::$prefixe;
    }

    /**
     * Compile les templates avec la lib LightnCandy (champs compile = 1)
     * @param string $rep_out, le répertoire ou seront les fichiers
     * @return bool faux si il y a eu une erreur 
     */
    function compile(string $rep_out): bool
    {
        return true;
    }


    /**
     * Permet de charger un fichier json en base, les tables autorisées sont :
     * environment, data, template.
     * @param string $fichier à charger
     * @param bool $delete, flag si on doit supprimer la table au préalable
     * @return array [code erreur, nbr d'enregistrement, libellé]
     */
    function load(string $fichier, bool $delete, array &$cr): array
    {
        if (($Json = json_decode(file_get_contents($fichier))) === null) {
            // gestion de l'erreur
            return [self::RET_FICH, -1, "lecture impossible"];
        }
        $rep = dirname($fichier);

        if (!property_exists($Json, "nom")) {
            // gestion de l'erreur
            return [self::RET_FICH, -1, "la propriété [nom] non présente"];
        }
        $table = $Json->nom;

        if (($desc = DBParam::get_table($table)) === null)
            return [self::RET_FICH, -1, "La table [{$table}] n'est pas connue"];
        $Table = new Table(DBParam::$prefixe, $desc);
        if (($nbr = $Table->count()) === false)
            return [self::RET_SQL, -1, "Problème d'accès à la table [{$table}]"];

        if ($delete) $Table->del();

        foreach ($Json->row as $Row) {
            $err = $this->_parseJsonData($Row, $rep);
            if ($err === false) return [self::RET_SQL, $nbr, $this->_error];

            try {
                [$retTab, $key] = $Table->save((array)$Row);
                if ($retTab == $Table::RET_OK) $ret = self::RET_OK;
                else $ret = ($retTab == $Table::RET_MAJ) ? self::RET_MAJ : self::RET_DUP;
                $cr[$key] = $ret;
            } catch (\Exception $e) {
                return [self::RET_FICH, $nbr, $e->getMessage()];
            }
        }

        return [self::RET_OK, $nbr, $table];
    }

    /**
     * Les champs json peuvent avoir besoin d'informations contenus dans des fichiers externes, 
     * pour cela ils doivent commencer par un underscore
     * La fonction permet de 
     * - rechercher les fichiers externes
     * - de les ajouter au champs sans le _
     * 
     * Permet d'inclure des fichiers dans le champs, les champs doivent commencer par _
     * @param object $json in/out les données json à parser
     * @param string $rep le répertoire racine ou sont les données externes
     * @return bool, false si il y a une erreur
     */
    private function _parseJsonData(object &$json, string $rep): bool
    {
        unset($this->_error);
        foreach ($json as $key => $val) {
            if (substr($key, 0, 1) == "_") {
                $fichier = "{$rep}/{$val}";
                $newkey = substr($key, 1);
                if (($newval = file_get_contents($fichier)) === false) {
                    $this->_error = $fichier;
                    return false;
                }
                unset($json->{$key});
                $json->{$newkey} = $newval;
            } elseif (gettype($val) == "object") {
                if ($this->_parseJsonData($json->$key, $rep) == null) return null;
            }
        }
        return true;
    }
}
