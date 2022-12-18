<?php

/**
 * Class Admin : fonction pour administrer les données et la création de la base
 *
 * @author folenski
 * @version 1.0 05/08/2022: Version initiale
 * @version 1.1 16/12/2022: add showTables
 * 
 */

namespace Staff\Lib;

use Exception;
use Staff\Databases\Database;
use Staff\Databases\SqlAdmin;
use Staff\Databases\Table;
use Staff\Drivers\DriversInterface;
use Staff\Models\DBParam;

class Admin
{
    const DISP_DEF    = 0;
    const DISP_DONE   = 1;
    const DISP_UPDATE = 4;
    const DISP_ALERT  = 2;
    const DISP_DANGER = 3;

    function __construct(
        private ?string $prefixe = null,
        private ?DriversInterface $sqldrv = null
    ) {
        if ($this->prefixe === null) $this->prefixe = DBParam::$prefixe;
    }

    /**
     * @throws string pb avec l'accès base de données
     * @return array retourne la liste des tables avec la requete propre (non standard) 
     */
    function showTables(): array
    {
        if ($this->sqldrv == null) throw ("driver DB not set");
        try {
            $rows = Database::query($this->sqldrv->showTables())->fetchAll();
            return array_filter($rows, function (object $el) {
                return str_starts_with($el->name, $this->prefixe);
            });
        } catch (Exception) {
            throw ("show tables error");
        }
    }

    /**
     * Permet de créer toutes les tables de la base de données
     * @throws string pb avec l'accès base de données
     * @param bool $optDelete vrai pour effacer les tables
     * @param callable function pour l'affichage
     */
    function createAllTables(bool $optDelete, callable $print): void
    {
        if ($this->sqldrv == null) throw ("driver DB not set");
        if (!is_callable($print)) throw ("function print is not callable");

        foreach (DBParam::TABLE as $nomtable) {
            $Adm = new SqlAdmin(DBParam::$prefixe, $this->sqldrv, DBParam::get_table($nomtable));
            $Table = new Table(Entite: DBParam::get_table($nomtable), prefixe: DBParam::$prefixe);
            $prStart = "{$Table->name} ";
            // $print("+ {$Table->name} ", self::DISP_DEF, false);

            if ($optDelete) {
                $print("{$prStart} drop demandé  ", self::DISP_ALERT);
                Database::exec($Adm->drop()->exists()->table()->toStr());
            } else {
                if (($nbr = $Table->count()) === false) {
                    $print("{$prStart} n'existe pas !!!!!", self::DISP_DANGER);
                    Database::exec($Adm->create()->table()->toStr());
                    foreach ($Adm->listIndex as $index) {
                        $print("===> Creation index {$index} ", self::DISP_DONE);
                        Database::exec($Adm->create()->index($index)->toStr());
                    }
                } else {
                    $print("{$prStart} => {$nbr} enr");
                }
            }
            $print();  // on demande un séparateur
        }
    }

    /**
     * Permet de charger un fichier json en base, les tables autorisées sont :
     * environment, data, template.
     * @param string $fichier à charger
     * @param bool $delete, flag si on doit supprimer la table au préalable
     * @param callable function pour l'affichage
     * @return array|null retourne un tableau de stat ["add", "exist", "upd"]
     */
    function load(string $fichier, bool $delete, callable $print): array|null
    {
        if (($Json = json_decode(file_get_contents($fichier))) === null) {
            $print("lecture impossible", self::DISP_ALERT);
            return null;
        }
        $rep = dirname($fichier);

        if (!property_exists($Json, "nom")) {
            $print("la propriété [nom] non présente", self::DISP_ALERT);
            return null;
        }
        $table = $Json->nom;

        if (($desc = DBParam::get_table($table)) === null) {
            $print("La table [{$table}] n'est pas connue", self::DISP_ALERT);
            return null;
        }

        $Table = new Table(Entite: $desc, prefixe: DBParam::$prefixe);
        if (($nbr = $Table->count()) === false) {
            $print("Problème d'accès à la table [{$table}]", self::DISP_ALERT);
            return null;
        }
        if ($delete) $Table->del();

        $stat = [
            "upd" => 0,
            "add" => 0,
            "exist" => 0
        ];

        foreach ($Json->row as $Row) {
            $err = $this->_parseJsonData($Row, $rep);
            if ($err === false) {
                $print($this->_error, self::DISP_DANGER);
                break;
            }
            try {
                [$retTab, $key] = $Table->save((array)$Row);
                switch ($retTab) {
                    case $Table::RET_MAJ:
                        $print("[{$key}] => update", self::DISP_UPDATE);
                        $stat["upd"]++;
                        break;
                    case $Table::RET_DUP:
                        $print("[{$key}] => exist", self::DISP_DEF);
                        $stat["exist"]++;
                        break;
                    default:
                        $print("[{$key}] => add", self::DISP_DONE);
                        $stat["exist"]++;
                        break;
                }
            } catch (\Exception $e) {
                $print($e->getMessage(), self::DISP_ALERT);
            }
        }
        return $stat;
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
