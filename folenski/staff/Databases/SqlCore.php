<?php

/**
 * Gestion des ordres SQL de base
 * 
 * @author folenski
 * @version 1.0 ajout d'une méthode OR
 * @version 1.1 08/08/2022: ajout de l'ordre inner_join
 * @version 1.2 16/12/2022: fixed clause limit
 * 
 */

namespace Staff\Databases;

use stdClass;

class SqlCore
{
    private const _PRINT_FIELD            = 0;
    private const _PRINT_VALUE            = 10;
    private const _PRINT_FIELD_VALUE      = 20;
    private const _PRINT_FIELD_VALUE_PREF = 30;

    private const _NO_QUOTE = ["int"];

    protected object $_work;
    protected string $_fullname;

    /**
     * Constructeur de la class
     */
    function __construct(
        private string $_nom,
        private array $_schema,
        private string $_prefixe = "",
        private bool $_simpleQuote = false
    ) {
        $this->_fullname = "{$this->_prefixe}{$this->_nom}";
        $this->_work = new stdClass();
    }

    public function __set(string $name, mixed $value): void
    {
        switch ($name) {
            case "simpleQuote":
                $this->_simpleQuote = $value;
                break;
            default:
                break;
        }
    }

    public function __get(string $name): mixed
    {
        switch ($name) {
            case "name":
                return $this->_fullname;
            default:
                return null;
        }
    }
    /**
     * SELECT doit être terminé par toStr() 
     * @throws string : si la clause est vide
     * @param array|string $clause liste de champs ou une clause SQL libre 
     * @param bool $prepare ordre sql prépare
     * @return SqlCore  objet pour chainer les requêtes
     */
    public function select(array|string $clause, bool $prepare = false): SqlCore
    {
        $this->_work->addSqlfrom = true;
        $this->_work->prepare = $prepare;
        $this->_statement(clause: $clause, ordre: "SELECT", sep: ", ", ope: "");
        return $this;
    }

    /**
     * DELETE doit être terminé par toStr() 
     * @param bool $prepare ordre sql prépare
     * @return SqlCore objet pour chainer les requêtes
     */
    public function delete(bool $prepare = false): SqlCore
    {
        $this->_work->addSqlfrom = false;
        $this->_work->prepare = $prepare;
        $this->_work->sql = "DELETE FROM {$this->name}";
        return $this;
    }

    /**
     * UPDATE doit être terminé par toStr() 
     * @param array liste des champs
     * @param bool $prepare ordre sql prépare
     * @return SqlCore objet pour chainer les requêtes
     */
    public function update(array $champs, bool $prepare = false): SqlCore
    {
        $this->_work->addSqlfrom = false;
        $this->_work->prepare = $prepare;

        $this->_work->sql = "UPDATE {$this->name} SET ";
        $this->_work->sql .= $this->_print($champs, self::_PRINT_FIELD_VALUE);
        return $this;
    }

    /**
     * INSERT doit être terminé par toStr() 
     * @param array liste des champs
     * @param bool $prepare ordre sql prépare
     * @return SqlCore objet pour chainer les requêtes
     */
    public function insert(array $champs, bool $prepare = false): SqlCore
    {
        $this->_work->addSqlfrom = false;
        $this->_work->prepare = $prepare;

        $this->_work->sql = "INSERT INTO {$this->name} (";
        $this->_work->sql .= $this->_print($champs, self::_PRINT_FIELD);
        $this->_work->sql .= ") VALUES (";
        $this->_work->sql .= $this->_print($champs, self::_PRINT_VALUE);
        $this->_work->sql .= ")";
        return $this;
    }

    /**
     * FROM de l'ordre SQL ( facultative )
     * @return SqlCore objet pour chainer les requêtes
     */
    public function from(): SqlCore
    {
        if ($this->_work->addSqlfrom) {
            $this->_work->sql .= " FROM {$this->name}";
            $this->_work->addSqlfrom = false;
        }
        return $this;
    }

    /**
     * INNER JOIN de l'ordre SQL ( facultative )
     * @return SqlCore objet pour chainer les requêtes
     */
    public function inner_join(string $table, array $id): SqlCore
    {
        $this->from();
        $fullNameExt = "{$this->_prefixe}{$table}";
        $this->_work->sql .= " INNER JOIN {$fullNameExt} ON ";
        $sep = "";

        foreach ($id as $val) {
            $this->_work->sql .= "{$sep}{$fullNameExt}.{$val}={$this->name}.{$val}";
            $sep = " AND ";
        }

        return $this;
    }

    /**
     * WHERE
     * @throws string : si la clause est vide
     * @param array|string $clause liste de champs ou une clause SQL libre 
     * @param string $ope l'opérateur à utiliser entre le champs et sa valeur (si clause est un tableau)
     * @return SqlCore objet pour chainer les requêtes
     */
    public function where(array|string $clause, string $ope = "="): SqlCore
    {
        $this->from();
        return $this->_statement(clause: $clause, ordre: "WHERE", ope: $ope);
    }

    /**
     * AND
     * @throws string : si la clause est vide
     * @param array|string $clause liste de champs ou une clause SQL libre 
     * @param string $ope l'opérateur à utiliser entre le champs et sa valeur (si clause est un tableau)
     * @return SqlCore objet pour chainer les requêtes
     */
    public function and(array|string $clause, string $ope = "="): SqlCore
    {
        return $this->_statement(clause: $clause, ordre: "AND", ope: $ope);
    }

    /**
     * OR
     * @throws string : si la clause est vide
     * @param array|string $clause liste de champs ou une clause SQL libre 
     * @param string $ope l'opérateur à utiliser entre le champs et sa valeur (si clause est un tableau)
     * @return SqlCore objet pour chainer les requêtes
     */
    public function or(array|string $clause, string $ope = "="): SqlCore
    {
        return $this->_statement(clause: $clause, ordre: "OR", sep: " OR ", ope: $ope);
    }

    /**
     * @return string retourne l'ordre sql en mémoire
     */
    public function toStr(): string
    {
        return $this->from()->_work->sql;
    }

    /**
     * ORDER BY
     * @param string $clause les champs ou une requête libre
     * @param bool $asc true si ascending
     * @return SqlCore objet pour chainer les requêtes
     */
    public function order_by(array|string $clause, bool $asc = true): SqlCore
    {
        $this->from();
        $this->_statement(clause: $clause, ordre: "ORDER BY", sep: ",", ope: "");
        $this->_work->sql .= ($asc) ? " ASC" : " DESC";
        return $this;
    }

    /**
     * @return SqlCore retourne la requête
     */
    public function limit(int $nbr = 1): SqlCore
    {
        if ($nbr < 1) return $this;
        $this->from();
        $this->_work->sql .= " LIMIT {$nbr}";
        return $this;
    }

    /** ----------------------------------------------------------------------------------------------
     *                                       P R I V E
     *  ----------------------------------------------------------------------------------------------
     */
    /**
     * Fonction privée : met en forme une lise de colonnes en fonction du format 
     * @param array $data tableau de champs
     * @param int $format 0 : les noms, 10 : les valeurs, 20 : nom = valeur
     * @param string $separateur à utiliser
     * @param string $operateur ( =, <, > ) utile pour le format _PRINT_FIELD_VALUE
     */
    private function _print(array $data, int $format = 0, string $separateur = ", ", string $operateur = "="): string
    {
        $sep = "";
        $sql = "";

        foreach ($data as $key => $value) {
            switch ($format) {
                case self::_PRINT_FIELD:
                    $sql .= "{$sep}{$key}";
                    break;
                case self::_PRINT_VALUE: // les valeurs uniquements
                    if ($this->_work->prepare)
                        $sql .= "{$sep}:{$key}";
                    else
                        $sql .= $sep . $this->_addQuote($key, $value);
                    break;
                case self::_PRINT_FIELD_VALUE: // le couple clé, valeur
                    $sql .= "{$sep}{$key}{$operateur}";
                    if ($this->_work->prepare)
                        $sql .= ":{$key}";
                    else
                        $sql .= $this->_addQuote($key, $value);
                    break;
                case self::_PRINT_FIELD_VALUE_PREF: // le couple clé, valeur
                    $sql .= "{$sep}{$this->name}.{$key}{$operateur}";
                    if ($this->_work->prepare)
                        $sql .= ":{$key}";
                    else
                        $sql .= $this->_addQuote($key, $value);
                    break;
                default:
                    throw new \Exception("problème sur SqlCore->_print");
            }
            $sep = $separateur;
        }
        return $sql;
    }
    /**
     * surcouche de la fonction this->_print
     * @param $clause si string alors l'ordre est appliqué, si tableau alors on appelle la 
     *        fonction _print
     * @param $ope l'opérateur a appliqué
     * @param $sep le séparateur entre item du tableau
     */
    private function _statement(array|string $clause, string $ordre, string $ope = "=", string $sep = " AND "): SqlCore
    {
        if ($ordre == "SELECT")
            $this->_work->sql = "";
        else
            $this->_work->sql .= " ";

        if (gettype($clause) == "string") {
            if (($clause) == "") throw new \Exception("SqlCore->{$ordre}: clause vide");
            $this->_work->sql .= "{$ordre} {$clause}";
        } else {
            if (count($clause) == 0) throw new \Exception("SqlCore->{$ordre}: clause vide");
            $this->_work->sql .= "{$ordre} " . $this->_print(
                data: $clause,
                format: ($ope == "") ? self::_PRINT_FIELD :  self::_PRINT_FIELD_VALUE_PREF,
                operateur: $ope,
                separateur: $sep
            );
        }
        return $this;
    }
    /**
     * Protège une variable de type TEXT en fonction de la variable self::_simpleQuote
     */
    private function _addQuote(string $key, string $value): string
    {
        if (array_key_exists($key, $this->_schema)) {
            foreach (self::_NO_QUOTE as $field) {
                if (strripos($this->_schema[$key], $field) !== false) return $value;
            }
        }
        if ($this->_simpleQuote) return "'{$value}'";
        return '"' . $value . '"';
    }
}
