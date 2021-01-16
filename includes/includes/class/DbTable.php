<?php
/**
 * Class **DbTable** 
 * Classe pour gerer la création du SQL pour une table 
 *
 * @package includes\class\DbTable.php
 * 
 * @author  Mario Ferraz
 * @since 09/01/2021
 * @version 1.0.0 version initiale
 *
 */

class DbTable {

    const MODULEVS = "1.00";

    public  $nom;
    private $description;

    function __construct(string $nom, array $champs) {
    // Initialisation du nom de la table 
        $this->nom = $nom;
        $this->description = $champs;
    }
    
    /**
     * Retourne la version du module.
     */
    public static function  version(): string {
        return self::MODULEVS;
    }

    /**
     * Retourne la requête SQL pour la creation de la table.
     */
    public function create(): string {
        $_sep = "";  
        $_sql = "CREATE TABLE {$this->nom} (";
        foreach ($this->description as $_key => $_value) {
            $_sql .= "{$_sep} {$_key} {$_value}";
            $_sep = ",";
        }
        return $_sql . ")" ;
    }

    /**
     * Retourne la requete SQL pour la suppression d'une table
     * @return  string le requete SQL
     */
    public function drop(): string {
        return "DROP TABLE {$this->nom}";
    }

    /**
     * Retourne la requete SQL pour une selection dans la table
     * @param string $data champs à selectionner si le tableau est vide alors on selectionne tous les champs
     * @param string $where clause where
     * @return  string le requete SQL
     */
    public function select(string $sel, array $where, bool $prepare = false): string {
        $_sql = "SELECT {$sel} FROM {$this->nom} ";
        if (count($where) > 0)
            $_sql .= " WHERE ";
        $_sql .= $this->SQLlist ($where, " AND ", $prepare);
        return $_sql ;
    }
    
    /**
     * Retourne la requete SQL pour une insertion dans la table
     * @param string $data les champs à insèrer
     * @return  string le requete SQL
     */
    public function insert(array $data, bool $prepare = false): string {
        $_sep = "";  
        $_sql = "INSERT INTO {$this->nom} (";
        foreach ($data as $_key => $_value) {
            $_sql .= "{$_sep} {$_key}";
            $_sep = ",";
        }
        $_sep = "";
        $_sql .= ") VALUES (";

        $_sql .= $this->SQLlist($data, ",", $prepare);

        return $_sql . ")" ;
    }
    
    public function update(array $data, array $where, bool $prepare = false): string {
    // retourne la requete SQL pour la mise à jour dans une table
        $_sep = "";  
        $_sql = "UPDATE TABLE {$this->nom} SET ";
        foreach ($data as $_key => $_value) {
            $_sql .= "{$_sep} {$_key} = {$_value}";
            $_sep = ",";
        }
        $_sep = "";
        if (count($where) > 0)
            $_sql .= " WHERE ";

        $_sql .= $this->SQLlist ($where, " AND ", $prepare);


        return $_sql ;
    }

    public function delete(array $where, bool $prepare = false): string {
    // retourne la requete SQL pour la mise à jour dans une table
        $_sep = "";  
        $_sql = "DELETE TABLE {$this->nom} ";

        if (count($where) > 0)
            $_sql .= " WHERE ";

        $_sql .= $this->SQLlist ($where, " AND ", $prepare);
        
        return $_sql ;
    }

    /**
     * Fonction privée : protege d'une simple cote les champs passés 
     * en paramétre en fonction de la déclaration de la table.
     *  
     */
    private function SQLlist(array $data, string $sep, bool $prepare): string {
        $_sep="";
        $_sql="";

        foreach ($data as $_key => $_value) {

            $_sql .= $_sep . ( ($sep == ",") ? "" : "{$_key}=" ) ;
            
            if ($prepare)
                $_sql .= ":{$_key}";
            else
                $_sql .= $this->addQuote($_key, $_value);

            $_sep = $sep;
        }
        return $_sql;
    }

    /**
     * Fonction privée : protege une variable de type TEXT avec des double cote 
     */
    private function addQuote(string $key, string $value): string {
        if (array_key_exists($key, $this->description)) {
            // echo "\nthis " . $value . "*" .$this->description[$key] .  "*".  strripos($this->description[$key], "int") ;
            if ( strripos($this->description[$key], "int") !== false )
                return $value;
        }

        return '"' . $value . '"' ;
    } 
}