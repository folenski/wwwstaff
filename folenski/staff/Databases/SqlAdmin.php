<?php

/**
 * Gestion des ordres SQL de base
 * 
 * @author  folenski
 * @since 15/07/2022
 * @version 1.0.0
 *  
 */

namespace Staff\Databases;

use Exception;

class SqlAdmin extends SqlCore 
{
    private array $_index;
    private array $_field;
    private array $_list;

    /**
     * Constructeur de la class
     */
    function __construct(private string $_prefixe, TableInterface $Table)
    {
        [$nom, $schema] = $Table->init();
        $this->_list = [];
        foreach ($schema as $key => $value) {
            if (substr($key, 0, 6) == "_index") {
                array_push($this->_list, $key);
                $this->_index[$key] = $value;
            } else {
                $this->_field[$key] = $value;
            }
        }
        parent::__construct(_nom: $nom, _schema: $schema, _prefixe: $this->_prefixe);
    }

    public function __get(string $name): mixed
    {
        switch ($name) {
            case "listIndex":
                return $this->_list;
            case "name":
                return $this->_fullname;
            default:
                return null;
        }
    }

    /**
     * CREATE doit être terminé par toStr() 
     * @return SqlAdmin objet pour chainer les requêtes
     */
    public function create(): SqlAdmin
    {
        $this->_work->addSqlfrom = false;
        $this->_work->exist = "";
        $this->_work->sql = "CREATE ";
        return $this;
    }

    /**
     * DROP doit être terminé par toStr() 
     * @return SqlAdmin objet pour chainer les requêtes
     */
    public function drop(): SqlAdmin
    {
        $this->_work->addSqlfrom = false;
        $this->_work->exist = "";
        $this->_work->sql = "DROP ";
        return $this;
    }

    /**
     * TABLE genère l'ordre de création de la table
     * @return SqlAdmin objet pour chainer les requêtes
     */
    public function table(): SqlAdmin
    {
        $this->_work->sql .= "TABLE {$this->_work->exist}{$this->_fullname}";
        if (substr($this->_work->sql, 0, 1) == "C") {
            $this->_work->sql .= " (";
            $sep = "";
            foreach ($this->_field as $key => $value) {
                if (substr($key, 0, 4) == "_key") {
                    $this->_work->sql .= sprintf(", {$value}", $this->_prefixe);
                } else {
                    $this->_work->sql .= "{$sep}{$key} {$value}";
                    $sep = ", ";
                }
            }
            $this->_work->sql .= ")";
        }
        return $this;
    }

    /**
     * INDEX genère l'ordre de création de la index
     * @return SqlAdmin objet pour chainer les requêtes
     */
    public function index(string $index): SqlAdmin
    {
        if (array_key_exists($index, $this->_index)) {
            $this->_work->sql .= <<<EOL
                INDEX {$this->_work->exist}{$this->name}{$index} ON {$this->_fullname} ({$this->_index[$index]})
                EOL;
            return $this;
        }
        throw new Exception("Index {$index} not found", 10);
        return $this;
    }

    /**
     * EXISTS ajout la directive if exists pour les instructions DROP et CREATE
     * @return SqlAdmin objet pour chainer les requêtes
     */
    public function exists(): SqlAdmin
    {
        if ($this->_work->sql == "CREATE ")  $this->_work->exist = "IF NOT EXISTS ";
        elseif ($this->_work->sql == "DROP ")  $this->_work->exist = "IF EXISTS ";
        return $this;
    }
}
